<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\RealEstate\Models\LuckyDraw;
use Botble\RealEstate\Models\LuckyDrawParticipant;
use Botble\RealEstate\Models\DummyWinner;
use Botble\RealEstate\Services\LuckyDrawService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LuckyDrawController extends BaseController
{
    /**
     * Reward Draw Service
     */
    protected $luckyDrawService;

    public function __construct(LuckyDrawService $luckyDrawService)
    {
        $this->luckyDrawService = $luckyDrawService;
    }
    /**
     * Display all draws
     */
    public function index()
    {
        page_title()->setTitle('Reward Draws Management');

        $draws = LuckyDraw::with(['property', 'participants'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('plugins/real-estate::lucky-draws.index', compact('draws'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        page_title()->setTitle('Create Reward Draw');

        // Get available properties using the Property model
        $properties = \Botble\RealEstate\Models\Property::query()
            ->select('id', 'name', 'price', 'location')
            ->where('moderation_status', 'approved')
            ->orderBy('name')
            ->get();

        return view('plugins/real-estate::lucky-draws.create', compact('properties'));
    }

    /**
     * Store new draw
     */
    public function store(Request $request, BaseHttpResponse $response)
    {
        $request->validate([
            'name' => 'required|max:255',
            'draw_type' => 'required|in:weekly,monthly',
            'property_id' => 'required|exists:re_properties,id',
            'property_value' => 'required|numeric|min:1',
            'entry_fee' => 'required|numeric|min:1',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'draw_date' => 'required|date|after:end_date',
            'description' => 'nullable|max:1000',
        ]);

        LuckyDraw::create([
            'name' => $request->input('name'),
            'draw_type' => $request->input('draw_type'),
            'property_id' => $request->input('property_id'),
            'property_value' => $request->input('property_value'),
            'entry_fee' => $request->input('entry_fee'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'draw_date' => $request->input('draw_date'),
            'status' => 'upcoming',
            'description' => $request->input('description'),
        ]);

        return $response
            ->setPreviousUrl(route('lucky-draws.index'))
            ->setMessage('Reward Draw created successfully!');
    }

    /**
     * Show draw details
     */
    public function show($id)
    {
        $draw = LuckyDraw::with(['property', 'participants.account'])->findOrFail($id);
        
        page_title()->setTitle('Draw Details: ' . $draw->name);

        // Calculate statistics
        $totalPool = $draw->participants()->where('payment_status', 'paid')->sum('entry_fee_paid');
        $profitLoss = $totalPool - $draw->property_value;
        
        $stats = [
            'total_participants' => $draw->participants()->count(),
            'paid_participants' => $draw->participants()->where('payment_status', 'paid')->count(),
            'pending_payments' => $draw->participants()->where('payment_status', 'pending')->count(),
            'total_pool' => $totalPool,
            'profit_loss' => $profitLoss,
            'is_profitable' => $profitLoss >= 0,
        ];

        return view('plugins/real-estate::lucky-draws.show', compact('draw', 'stats'));
    }

    /**
     * Show manual winner selection page
     */
    public function selectWinner($id)
    {
        $draw = LuckyDraw::with(['property', 'participants.account'])->findOrFail($id);
        
        if ($draw->status !== 'active') {
            return redirect()->route('lucky-draws.index')
                ->with('error', 'Only active draws can have winners selected');
        }

        // Get all paid participants
        $participants = $draw->participants()
            ->where('payment_status', 'paid')
            ->with('account')
            ->get();

        // Get all dummy winners
        $dummyWinners = \Botble\RealEstate\Models\DummyWinner::all();

        // Calculate statistics
        $totalPool = $participants->sum('entry_fee_paid');
        $propertyValue = $draw->property_value;
        $profitLoss = $totalPool - $propertyValue;
        $profitPercentage = $propertyValue > 0 ? (($profitLoss / $propertyValue) * 100) : 0;

        page_title()->setTitle('Select Winner: ' . $draw->name);

        return view('plugins/real-estate::lucky-draws.select-winner', compact(
            'draw', 
            'participants',
            'dummyWinners',
            'totalPool', 
            'propertyValue', 
            'profitLoss', 
            'profitPercentage'
        ));
    }

    /**
     * Process manual winner selection (Real or Dummy)
     */
    public function setWinner(Request $request, $id, BaseHttpResponse $response)
    {
        $request->validate([
            'winner_id' => 'required',
            'winner_type' => 'required|in:real,dummy',
        ]);

        $draw = LuckyDraw::with(['participants'])->findOrFail($id);

        if ($draw->status !== 'active') {
            return $response
                ->setError()
                ->setMessage('Only active draws can have winners selected');
        }

        try {
            DB::beginTransaction();

            $winnerType = $request->input('winner_type');
            $winnerId = $request->input('winner_id');
            
            // Get all paid participants
            $participants = $draw->participants()
                ->where('payment_status', 'paid')
                ->with('account')
                ->get();

            if ($participants->isEmpty()) {
                return $response
                    ->setError()
                    ->setMessage('No paid participants found');
            }

            // Validate winner based on type
            if ($winnerType === 'real') {
                $winnerParticipant = $participants->firstWhere('account_id', $winnerId);
                if (!$winnerParticipant) {
                    return $response
                        ->setError()
                        ->setMessage('Selected winner is not a participant in this draw');
                }
            } else {
                // Validate dummy winner exists
                $dummyWinner = \Botble\RealEstate\Models\DummyWinner::find($winnerId);
                if (!$dummyWinner) {
                    return $response
                        ->setError()
                        ->setMessage('Selected dummy winner not found');
                }
            }

            // Calculate totals
            $totalPool = $participants->sum('entry_fee_paid');
            $profitLoss = $totalPool - $draw->property_value;
            $isProfit = $profitLoss > 0;

            // Update draw
            $draw->update([
                'status' => 'completed',
                'winner_id' => $winnerId,
                'winner_type' => $winnerType,
                'is_profit' => $isProfit,
                'total_pool' => $totalPool,
                'profit_loss_amount' => $profitLoss,
            ]);

            // Process all participants
            foreach ($participants as $participant) {
                $entryFee = $participant->entry_fee_paid;
                
                // Check if this participant is the winner (only possible if winner_type is 'real')
                $isWinner = ($winnerType === 'real' && $participant->account_id == $winnerId);
                
                if ($isWinner) {
                    // Mark as winner
                    $participant->update(['is_winner' => true]);

                    // Update wallet: move from on_hold
                    DB::table('re_accounts')
                        ->where('id', $participant->account_id)
                        ->update([
                            'wallet_on_hold' => DB::raw('wallet_on_hold - ' . $entryFee),
                            'updated_at' => now(),
                        ]);

                    // Update discount for winner (winner gets 0 discount, membership expires)
                    if ($participant->account) {
                        $participant->account->refresh();
                        if (method_exists($participant->account, 'updateDiscountAfterDraw')) {
                            $participant->account->updateDiscountAfterDraw($draw, true);
                        }
                    }

                } else {
                    // Mark as loser
                    $participant->update(['is_winner' => false]);
                    
                    // Update wallet: move from on_hold
                    DB::table('re_accounts')
                        ->where('id', $participant->account_id)
                        ->update([
                            'wallet_on_hold' => DB::raw('wallet_on_hold - ' . $entryFee),
                            'updated_at' => now(),
                        ]);
                    
                    // Update discount for loser (losers get discount)
                    if ($participant->account) {
                        $participant->account->refresh();
                        if (method_exists($participant->account, 'updateDiscountAfterDraw')) {
                            $participant->account->updateDiscountAfterDraw($draw, false);
                        }
                    }
                }
            }

            // Send winner notification (only for real winners)
            if ($winnerType === 'real' && isset($winnerParticipant)) {
                $this->sendWinnerNotification($draw, $winnerParticipant);
            }

            DB::commit();

            // Get winner name for success message
            $winnerName = 'Winner';
            if ($winnerType === 'real' && isset($winnerParticipant) && $winnerParticipant->account) {
                $winnerName = trim(($winnerParticipant->account->first_name ?? '') . ' ' . ($winnerParticipant->account->last_name ?? ''));
            } elseif ($winnerType === 'dummy') {
                $dummyWinner = \Botble\RealEstate\Models\DummyWinner::find($winnerId);
                if ($dummyWinner) {
                    $winnerName = $dummyWinner->name;
                }
            }

            $typeLabel = $winnerType === 'real' ? 'Real Winner' : 'Dummy Winner';

            return $response
                ->setPreviousUrl(route('lucky-draws.show', $id))
                ->setMessage("Winner selected successfully! {$winnerName} ({$typeLabel}) has won the draw.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return $response
                ->setError()
                ->setMessage('Failed to set winner: ' . $e->getMessage());
        }
    }

    /**
     * Send winner notification
     */
    private function sendWinnerNotification($draw, $winnerParticipant)
    {
        try {
            // Reload account relationship if needed
            if (!$winnerParticipant->account) {
                $winnerParticipant->load('account');
            }
            
            if (!$winnerParticipant->account || !$winnerParticipant->account->email) {
                \Log::warning('Cannot send winner notification - account or email missing', [
                    'participant_id' => $winnerParticipant->id,
                    'account_id' => $winnerParticipant->account_id
                ]);
                return;
            }

            $siteName = setting('site_title', 'AADS Property Portal');
            $winnerEmail = $winnerParticipant->account->email;
            $winnerName = trim(($winnerParticipant->account->first_name ?? '') . ' ' . ($winnerParticipant->account->last_name ?? ''));
            
            $subject = "🎉 Congratulations! You Won the Reward Draw - " . $siteName;
            
            $message = "
            <html>
            <head><title>Reward Draw Winner!</title></head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 10px;'>
                        <h1 style='margin: 0;'>� CONGRATULATIONS " . htmlspecialchars($winnerName) . "!</h1>
                        <h2 style='margin: 10px 0 0 0;'>You Won the Reward Draw!</h2>
                    </div>
                    
                    <div style='padding: 20px; background: #f9f9f9; margin: 20px 0; border-radius: 10px;'>
                        <h3 style='color: #28a745;'>Draw Details:</h3>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr><td><strong>Draw Name:</strong></td><td>" . htmlspecialchars($draw->name) . "</td></tr>
                            <tr><td><strong>Property:</strong></td><td>" . htmlspecialchars($draw->property->name ?? 'Premium Property') . "</td></tr>
                            <tr><td><strong>Property Value:</strong></td><td>₹" . number_format($draw->property_value, 2) . "</td></tr>
                            <tr><td><strong>Your Entry Fee:</strong></td><td>₹" . number_format($winnerParticipant->entry_fee_paid, 2) . "</td></tr>
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
                        <p><strong>Contact:</strong> 9876543210 | support@sspl20.com</p>
                    </div>
                    
                    <p style='text-align: center;'>
                        <strong>Thank you for choosing " . htmlspecialchars($siteName) . "!</strong><br>
                        <em>Making dreams come true, one draw at a time.</em>
                    </p>
                </div>
            </body>
            </html>";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $siteName . " <" . setting('email_from_address', 'noreply@sspl20.com') . ">\r\n";

            $mailSent = mail($winnerEmail, $subject, $message, $headers);
            
            \Log::info('Winner notification email sent', [
                'draw_id' => $draw->id,
                'winner_email' => $winnerEmail,
                'winner_name' => $winnerName,
                'mail_sent' => $mailSent
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send winner notification: ' . $e->getMessage(), [
                'draw_id' => $draw->id,
                'participant_id' => $winnerParticipant->id ?? null
            ]);
        }
    }

    /**
     * Show draw details
     */
    public function showOld($id)
    {
        $draw = LuckyDraw::with(['property', 'participants.account'])->findOrFail($id);
        
        page_title()->setTitle('Draw Details: ' . $draw->name);

        // Get statistics
        $stats = [
            'total_participants' => $draw->participants()->where('payment_status', 'paid')->count(),
            'total_pool' => $draw->participants()->where('payment_status', 'paid')->sum('entry_fee_paid'),
            'pending_payments' => $draw->participants()->where('payment_status', 'pending')->count(),
            'profit_loss' => 0,
        ];
        
        $stats['profit_loss'] = $stats['total_pool'] - $draw->property_value;
        $stats['is_profitable'] = $stats['profit_loss'] >= 0;

        return view('plugins/real-estate::lucky-draws.show', compact('draw', 'stats'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $draw = LuckyDraw::findOrFail($id);
        
        // Can't edit completed draws
        if ($draw->status === 'completed') {
            return redirect()->route('lucky-draws.index')
                ->with('error', 'Cannot edit completed draw');
        }

        page_title()->setTitle('Edit Draw: ' . $draw->name);

        $properties = \Botble\RealEstate\Models\Property::query()
            ->select('id', 'name', 'price', 'location')
            ->where('moderation_status', 'approved')
            ->orderBy('name')
            ->get();

        return view('plugins/real-estate::lucky-draws.edit', compact('draw', 'properties'));
    }

    /**
     * Update draw
     */
    public function update($id, Request $request, BaseHttpResponse $response)
    {
        $draw = LuckyDraw::findOrFail($id);

        if ($draw->status === 'completed') {
            return $response->setError()->setMessage('Cannot edit completed draw');
        }

        $request->validate([
            'name' => 'required|max:255',
            'draw_type' => 'required|in:weekly,monthly',
            'property_id' => 'required|exists:re_properties,id',
            'property_value' => 'required|numeric|min:1',
            'entry_fee' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'draw_date' => 'required|date|after:end_date',
            'description' => 'nullable|max:1000',
        ]);

        $draw->update($request->only([
            'name', 'draw_type', 'property_id', 'property_value', 
            'entry_fee', 'start_date', 'end_date', 'draw_date', 'description'
        ]));

        return $response
            ->setPreviousUrl(route('lucky-draws.index'))
            ->setMessage('Reward Draw updated successfully!');
    }

    /**
     * Delete draw
     */
    public function destroy($id, BaseHttpResponse $response)
    {
        $draw = LuckyDraw::findOrFail($id);

        // Can't delete if has participants
        if ($draw->participants()->exists()) {
            return $response->setError()
                ->setMessage('Cannot delete draw with participants');
        }

        $draw->delete();

        return $response->setMessage('Reward Draw deleted successfully!');
    }

    /**
     * Activate draw
     */
    public function activate($id, BaseHttpResponse $response)
    {
        $draw = LuckyDraw::findOrFail($id);
        
        if ($draw->status !== 'upcoming') {
            return $response->setError()->setMessage('Can only activate upcoming draws');
        }

        $draw->update(['status' => 'active']);

        return $response->setMessage('Draw activated successfully!');
    }

    /**
     * Execute draw (select winner)
     */
    public function executeDraw($id, BaseHttpResponse $response)
    {
        $draw = LuckyDraw::findOrFail($id);

        if ($draw->status !== 'active') {
            return $response->setError()->setMessage('Can only execute active draws');
        }

        // Use the service to execute the draw
        $result = $this->luckyDrawService->executeDraw($draw);

        if ($result['success']) {
            $message = "Draw executed successfully! ";
            $message .= "Winner: " . $result['winner_name'] . " ";
            $message .= "(" . ucfirst($result['winner_type']) . " winner)";
            
            return $response->setMessage($message);
        } else {
            return $response->setError()->setMessage($result['message']);
        }
    }

    /**
     * Get draw statistics for dashboard
     */
    public function getStats()
    {
        $stats = $this->luckyDrawService->getDrawStatistics();
        return response()->json($stats);
    }
}
