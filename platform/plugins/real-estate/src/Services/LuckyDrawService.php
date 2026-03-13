<?php

namespace Botble\RealEstate\Services;

use Botble\RealEstate\Models\LuckyDraw;
use Botble\RealEstate\Models\LuckyDrawParticipant;
use Botble\RealEstate\Models\DummyWinner;
use Botble\RealEstate\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LuckyDrawService
{
    /**
     * Execute a reward draw with smart winner selection
     */
    public function executeDraw(LuckyDraw $draw): array
    {
        if ($draw->status !== 'active') {
            return ['success' => false, 'message' => 'Draw is not active'];
        }

        try {
            DB::beginTransaction();

            // Get all paid participants
            $participants = $draw->participants()
                ->where('payment_status', 'paid')
                ->with('account')
                ->get();

            if ($participants->isEmpty()) {
                return ['success' => false, 'message' => 'No paid participants found'];
            }

            // Calculate total pool
            $totalPool = $participants->sum('entry_fee_paid');
            $draw->total_pool = $totalPool;

            // Smart winner selection logic
            $result = $this->selectWinner($draw, $participants, $totalPool);

            // Update draw status
            $draw->status = 'completed';
            $draw->winner_id = $result['winner_id'];
            $draw->winner_type = $result['winner_type'];
            $draw->is_profit = $result['is_profit'];
            $draw->profit_loss_amount = $totalPool - $draw->property_value;
            $draw->save();

            // Process participants based on result
            $this->processParticipants($draw, $participants, $result);

            // Send notifications
            $this->sendNotifications($draw, $result);

            // Log the execution
            $this->logDrawExecution($draw, $result, $participants->count());

            DB::commit();

            return [
                'success' => true,
                'message' => 'Draw executed successfully',
                'winner_type' => $result['winner_type'],
                'winner_name' => $result['winner_name'],
                'total_participants' => $participants->count(),
                'total_pool' => $totalPool,
                'is_profit' => $result['is_profit']
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Draw execution failed: ' . $e->getMessage(), [
                'draw_id' => $draw->id,
                'error' => $e->getTraceAsString()
            ]);

            return ['success' => false, 'message' => 'Draw execution failed: ' . $e->getMessage()];
        }
    }

    /**
     * Smart winner selection algorithm
     */
    private function selectWinner(LuckyDraw $draw, $participants, float $totalPool): array
    {
        $propertyValue = $draw->property_value;
        $profitMargin = $totalPool - $propertyValue;
        $profitPercentage = ($profitMargin / $propertyValue) * 100;

        // Decision matrix for winner selection
        if ($profitPercentage >= 20) {
            // High profit - definitely select real winner
            return $this->selectRealWinner($participants, true);
        } elseif ($profitPercentage >= 5) {
            // Moderate profit - 80% chance real winner
            $chance = rand(1, 100);
            if ($chance <= 80) {
                return $this->selectRealWinner($participants, true);
            } else {
                return $this->selectDummyWinner(false);
            }
        } elseif ($profitPercentage >= -5) {
            // Break-even - 50% chance real winner
            $chance = rand(1, 100);
            if ($chance <= 50) {
                return $this->selectRealWinner($participants, false);
            } else {
                return $this->selectDummyWinner(false);
            }
        } else {
            // Loss - 90% chance dummy winner (protect business)
            $chance = rand(1, 100);
            if ($chance <= 10) {
                return $this->selectRealWinner($participants, false);
            } else {
                return $this->selectDummyWinner(false);
            }
        }
    }

    /**
     * Select real winner from participants
     */
    private function selectRealWinner($participants, bool $isProfit): array
    {
        // Weighted selection - give slight preference to early joiners
        $weightedParticipants = [];
        foreach ($participants as $index => $participant) {
            $weight = max(1, $participants->count() - $index); // Early joiners get higher weight
            for ($i = 0; $i < $weight; $i++) {
                $weightedParticipants[] = $participant;
            }
        }

        $winner = $weightedParticipants[array_rand($weightedParticipants)];

        return [
            'winner_id' => $winner->account_id,
            'winner_type' => 'real',
            'winner_name' => $winner->account->name,
            'winner_email' => $winner->account->email,
            'winner_phone' => $winner->account->phone,
            'is_profit' => $isProfit,
            'winner_participant' => $winner
        ];
    }

    /**
     * Select dummy winner
     */
    private function selectDummyWinner(bool $isProfit): array
    {
        $dummyWinner = DummyWinner::getRandomWinner();

        if (!$dummyWinner) {
            // Fallback - create a temporary dummy winner
            $dummyWinner = new DummyWinner([
                'name' => 'Lucky Winner',
                'email' => 'winner@example.com',
                'phone' => '9876543210',
                'city' => 'Mumbai'
            ]);
        }

        return [
            'winner_id' => $dummyWinner->id,
            'winner_type' => 'dummy',
            'winner_name' => $dummyWinner->name,
            'winner_email' => $dummyWinner->email,
            'winner_phone' => $dummyWinner->phone,
            'is_profit' => $isProfit,
            'winner_participant' => null
        ];
    }

    /**
     * Process all participants after winner selection
     */
    private function processParticipants(LuckyDraw $draw, $participants, array $result): void
    {
        foreach ($participants as $participant) {
            $entryFee = $participant->entry_fee_paid;
            
            if ($result['winner_type'] === 'real' && 
                $participant->account_id === $result['winner_id']) {
                // Mark as winner
                $participant->is_winner = true;
                $participant->save();

                // Update wallet: move from on_hold (money is now used, no refund)
                DB::table('re_accounts')
                    ->where('id', $participant->account_id)
                    ->update([
                        'wallet_on_hold' => DB::raw('wallet_on_hold - ' . $entryFee),
                        'updated_at' => now(),
                    ]);

                // Update discount for winner (membership expires, no discount)
                $participant->account->refresh();
                $participant->account->updateDiscountAfterDraw($draw, true);

            } else {
                // Mark as loser and update discount
                $participant->is_winner = false;
                $participant->save();
                
                // Update wallet: move from on_hold (money is now used, but user gets discount)
                DB::table('re_accounts')
                    ->where('id', $participant->account_id)
                    ->update([
                        'wallet_on_hold' => DB::raw('wallet_on_hold - ' . $entryFee),
                        'updated_at' => now(),
                    ]);
                
                // Update discount for loser (add credit value to discount)
                $participant->account->refresh();
                $participant->account->updateDiscountAfterDraw($draw, false);
            }
        }
    }

    /**
     * Calculate credit amount for losing participants
     * Since we're using membership credits now, we give property discount credits
     */
    private function calculateCreditAmount(LuckyDrawParticipant $participant, LuckyDraw $draw, bool $isProfit): float
    {
        // Get the membership plan price as base credit
        $membershipPlan = $participant->account->membershipPlan;
        
        if (!$membershipPlan) {
            return 0;
        }

        $baseFee = $membershipPlan->price / $membershipPlan->draws_allowed;

        if ($isProfit) {
            // If draw was profitable, give full proportional credit
            return $baseFee;
        } else {
            // If draw was not profitable, give 80% as credit
            return $baseFee * 0.8;
        }
    }

    /**
     * Send notifications to winner and participants
     */
    private function sendNotifications(LuckyDraw $draw, array $result): void
    {
        try {
            if ($result['winner_type'] === 'real') {
                $this->sendWinnerNotification($draw, $result);
            }

            $this->sendParticipantNotifications($draw, $result);
            
        } catch (\Exception $e) {
            Log::error('Failed to send draw notifications: ' . $e->getMessage());
        }
    }

    /**
     * Send winner notification email
     */
    private function sendWinnerNotification(LuckyDraw $draw, array $result): void
    {
        $siteName = setting('site_title', 'AADS Property Portal');
        $subject = "🎉 Congratulations! You Won the Reward Draw - " . $siteName;
        
        $message = "
        <html>
        <head><title>Reward Draw Winner!</title></head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 10px;'>
                    <h1 style='margin: 0;'>🏆 CONGRATULATIONS!</h1>
                    <h2 style='margin: 10px 0 0 0;'>You Won the Reward Draw!</h2>
                </div>
                
                <div style='padding: 20px; background: #f9f9f9; margin: 20px 0; border-radius: 10px;'>
                    <h3 style='color: #28a745;'>Draw Details:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr><td><strong>Draw Name:</strong></td><td>" . $draw->name . "</td></tr>
                        <tr><td><strong>Property:</strong></td><td>" . ($draw->property->name ?? 'Premium Property') . "</td></tr>
                        <tr><td><strong>Property Value:</strong></td><td>₹" . number_format($draw->property_value, 2) . "</td></tr>
                        <tr><td><strong>Your Entry Fee:</strong></td><td>₹" . number_format($result['winner_participant']->entry_fee_paid ?? 0, 2) . "</td></tr>
                        <tr><td><strong>Draw Date:</strong></td><td>" . $draw->draw_date->format('M d, Y H:i') . "</td></tr>
                    </table>
                </div>
                
                <div style='background: #fff; border: 2px solid #28a745; padding: 20px; border-radius: 10px; text-align: center;'>
                    <h3 style='color: #28a745; margin-top: 0;'>🎁 You Won Property Worth</h3>
                    <h1 style='color: #28a745; font-size: 2.5em; margin: 10px 0;'>₹" . number_format($draw->property_value/100000, 1) . " Lakhs</h1>
                    <p><strong>Absolutely FREE!</strong></p>
                </div>
                
                <div style='padding: 20px; background: #e7f3ff; border-radius: 10px; margin: 20px 0;'>
                    <h4 style='color: #0066cc; margin-top: 0;'>📞 Next Steps:</h4>
                    <ol>
                        <li>Our team will contact you within 24 hours</li>
                        <li>Property documentation will be prepared</li>
                        <li>Legal formalities will be completed</li>
                        <li>Property handover within 30 days</li>
                    </ol>
                    <p><strong>Contact:</strong> 9876543210 | support@propertyportal.com</p>
                </div>
                
                <p style='text-align: center;'>
                    <strong>Thank you for choosing " . $siteName . "!</strong><br>
                    <em>Making dreams come true, one draw at a time.</em>
                </p>
            </div>
        </body>
        </html>";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $siteName . " <" . setting('email_from_address', 'noreply@propertyportal.com') . ">\r\n";

        mail($result['winner_email'], $subject, $message, $headers);
    }

    /**
     * Send notifications to all participants
     */
    private function sendParticipantNotifications(LuckyDraw $draw, array $result): void
    {
        // This can be implemented to send bulk emails to all participants
        // For now, we'll just log it
        Log::info('Draw completed notification sent', [
            'draw_id' => $draw->id,
            'winner_type' => $result['winner_type'],
            'winner_name' => $result['winner_name']
        ]);
    }

    /**
     * Log draw execution for audit
     */
    private function logDrawExecution(LuckyDraw $draw, array $result, int $participantCount): void
    {
        Log::info('Reward Draw Executed', [
            'draw_id' => $draw->id,
            'draw_name' => $draw->name,
            'property_value' => $draw->property_value,
            'total_pool' => $draw->total_pool,
            'profit_loss' => $draw->profit_loss_amount,
            'winner_type' => $result['winner_type'],
            'winner_name' => $result['winner_name'],
            'participant_count' => $participantCount,
            'is_profit' => $result['is_profit'],
            'executed_at' => now()
        ]);
    }

    /**
     * Auto-activate upcoming draws
     */
    public function autoActivateDraws(): int
    {
        $activated = 0;
        
        $upcomingDraws = LuckyDraw::where('status', 'upcoming')
            ->where('start_date', '<=', now())
            ->get();

        foreach ($upcomingDraws as $draw) {
            $draw->update(['status' => 'active']);
            $activated++;
            
            Log::info('Draw auto-activated', [
                'draw_id' => $draw->id,
                'draw_name' => $draw->name
            ]);
        }

        return $activated;
    }

    /**
     * Auto-execute completed draws
     */
    public function autoExecuteDraws(): int
    {
        $executed = 0;
        
        $readyDraws = LuckyDraw::where('status', 'active')
            ->where('draw_date', '<=', now())
            ->get();

        foreach ($readyDraws as $draw) {
            $result = $this->executeDraw($draw);
            if ($result['success']) {
                $executed++;
            }
        }

        return $executed;
    }

    /**
     * Get draw statistics for dashboard
     */
    public function getDrawStatistics(): array
    {
        return [
            'total_draws' => LuckyDraw::count(),
            'active_draws' => LuckyDraw::where('status', 'active')->count(),
            'completed_draws' => LuckyDraw::where('status', 'completed')->count(),
            'total_participants' => LuckyDrawParticipant::where('payment_status', 'paid')->count(),
            'total_revenue' => LuckyDrawParticipant::where('payment_status', 'paid')->sum('entry_fee_paid'),
            'total_profit' => LuckyDraw::where('is_profit', true)->sum('profit_loss_amount'),
            'total_loss' => LuckyDraw::where('is_profit', false)->sum('profit_loss_amount'),
            'real_winners' => LuckyDraw::where('winner_type', 'real')->count(),
            'dummy_winners' => LuckyDraw::where('winner_type', 'dummy')->count(),
        ];
    }
}