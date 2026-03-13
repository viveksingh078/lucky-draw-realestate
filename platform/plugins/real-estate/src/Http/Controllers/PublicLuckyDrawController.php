<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\RealEstate\Models\LuckyDraw;
use Botble\RealEstate\Models\LuckyDrawParticipant;
use Botble\RealEstate\Models\DummyWinner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Theme;

class PublicLuckyDrawController extends BaseController
{
    public function index()
    {
        $activeDraws = LuckyDraw::with(['property', 'participants'])
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->orderBy('draw_date', 'asc')
            ->get();

        $upcomingDraws = LuckyDraw::with(['property'])
            ->where('status', 'upcoming')
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        $recentWinners = $this->getRecentWinners();

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add('Reward Draws', route('public.lucky-draws.index'));

        return Theme::scope('real-estate.public.lucky-draws.index', compact('activeDraws', 'upcomingDraws', 'recentWinners'))->render();
    }

    public function show($id)
    {
        $draw = LuckyDraw::with(['property', 'participants.account'])->findOrFail($id);
        
        $userParticipation = null;
        if (auth('account')->check()) {
            $userParticipation = $draw->participants()
                ->where('account_id', auth('account')->id())
                ->first();
        }

        $stats = [
            'total_participants' => $draw->participants()->where('payment_status', 'paid')->count(),
            'total_pool' => $draw->participants()->where('payment_status', 'paid')->sum('entry_fee_paid'),
            'time_left' => $draw->end_date->diffForHumans(),
            'days_left' => now()->diffInDays($draw->end_date, false),
        ];

        return Theme::scope('real-estate.public.lucky-draws.show', compact('draw', 'userParticipation', 'stats'))->render();
    }

    public function join(Request $request, $id)
    {
        // Debug: Force show what's happening
        $debugInfo = [];
        
        try {
            $debugInfo[] = "Step 1: Checking authentication";
            
            if (!auth('account')->check()) {
                $debugInfo[] = "FAILED: User not authenticated";
                return redirect()->route('public.account.login')
                    ->with('error', 'Please login to join reward draws')
                    ->with('debug', implode(' | ', $debugInfo));
            }
            
            $debugInfo[] = "PASSED: User authenticated";

            $draw = LuckyDraw::findOrFail($id);
            $user = auth('account')->user();
            
            $debugInfo[] = "Step 2: User ID: {$user->id}, Draw ID: {$draw->id}";
            $debugInfo[] = "Step 3: Draws Remaining: {$user->draws_remaining}";

            if (!$draw->isActive()) {
                $now = now()->format('Y-m-d H:i:s');
                $start = $draw->start_date->format('Y-m-d H:i:s');
                $end = $draw->end_date->format('Y-m-d H:i:s');
                $debugInfo[] = "FAILED: Draw not active - Status: {$draw->status}, Now: {$now}, Start: {$start}, End: {$end}";
                return redirect()->route('public.lucky-draws.index')
                    ->with('error', "This draw is not active yet. Start: {$start}, End: {$end}")
                    ->with('debug', implode(' | ', $debugInfo));
            }
            $debugInfo[] = "PASSED: Draw is active";

            if (!$user->membershipPlan) {
                $debugInfo[] = "FAILED: No membership plan";
                return redirect()->route('public.lucky-draws.index')
                    ->with('error', 'Please purchase a membership plan first')
                    ->with('debug', implode(' | ', $debugInfo));
            }
            $debugInfo[] = "PASSED: Has membership plan";

            if (!$user->isApproved()) {
                $debugInfo[] = "FAILED: Account not approved (Status: {$user->account_status})";
                return redirect()->route('public.lucky-draws.index')
                    ->with('error', 'Your account is not approved yet')
                    ->with('debug', implode(' | ', $debugInfo));
            }
            $debugInfo[] = "PASSED: Account approved";

            if ($user->membership_status !== 'active') {
                $debugInfo[] = "FAILED: Membership not active (Status: {$user->membership_status})";
                return redirect()->route('public.lucky-draws.index')
                    ->with('error', 'Your membership is not active')
                    ->with('debug', implode(' | ', $debugInfo));
            }
            $debugInfo[] = "PASSED: Membership active";

            if ($user->hasActiveDraw()) {
                $activeDraws = $user->activeDraws();
                $maxConcurrent = $user->membershipPlan ? $user->membershipPlan->max_concurrent_draws : 1;
                $debugInfo[] = "FAILED: Max concurrent draws reached ({$activeDraws->count()}/{$maxConcurrent})";
                return redirect()->route('public.lucky-draws.index')
                    ->with('error', "You have reached your maximum concurrent draws limit ({$maxConcurrent}). Please wait for a draw to complete or upgrade your plan.")
                    ->with('debug', implode(' | ', $debugInfo));
            }
            $debugInfo[] = "PASSED: Can join more draws";

            if ($user->draws_remaining <= 0) {
                $debugInfo[] = "FAILED: No draws remaining ({$user->draws_remaining})";
                return redirect()->route('public.lucky-draws.index')
                    ->with('error', 'You have used all your draw credits')
                    ->with('debug', implode(' | ', $debugInfo));
            }
            $debugInfo[] = "PASSED: Has draws remaining";

            if (!$user->canJoinDraw($draw)) {
                $debugInfo[] = "FAILED: canJoinDraw() returned false";
                return redirect()->route('public.lucky-draws.index')
                    ->with('error', 'You cannot join this draw')
                    ->with('debug', implode(' | ', $debugInfo));
            }
            $debugInfo[] = "PASSED: canJoinDraw() check";

            $debugInfo[] = "Step 4: Calling joinDraw()";
            $participant = $user->joinDraw($draw);
            $debugInfo[] = "SUCCESS: Participant created (ID: {$participant->id})";
            
            $user->refresh();
            $debugInfo[] = "Final: Draws Remaining: {$user->draws_remaining}, Active Draw: {$user->current_active_draw_id}";

            return redirect()->route('public.lucky-draws.index')
                ->with('success', '✅ Successfully joined the draw: ' . $draw->name)
                ->with('debug', implode(' | ', $debugInfo));
                
        } catch (\Exception $e) {
            $debugInfo[] = "ERROR: " . $e->getMessage();
            return redirect()->route('public.lucky-draws.index')
                ->with('error', 'Error: ' . $e->getMessage())
                ->with('debug', implode(' | ', $debugInfo));
        }
    }

    public function leave(Request $request, $id)
    {
        try {
            if (!auth('account')->check()) {
                return redirect()->route('public.account.login')
                    ->with('error', 'Please login first');
            }

            $draw = LuckyDraw::findOrFail($id);
            $user = auth('account')->user();

            if (!$user->canLeaveDraw($draw)) {
                $endDate = $draw->end_date->format('M d, Y h:i A');
                return redirect()->route('public.lucky-draws.index')
                    ->with('error', 'You can only leave the draw before it ends (' . $endDate . ')');
            }

            $success = $user->leaveDraw($draw);

            if ($success) {
                return redirect()->route('public.lucky-draws.index')
                    ->with('success', '✅ Successfully left the draw. Your credit has been refunded.');
            } else {
                return redirect()->route('public.lucky-draws.index')
                    ->with('error', 'Failed to leave the draw. Please try again.');
            }

        } catch (\Exception $e) {
            return redirect()->route('public.lucky-draws.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function payment($participantId)
    {
        $participant = LuckyDrawParticipant::with(['draw.property', 'account'])
            ->where('account_id', auth('account')->id())
            ->findOrFail($participantId);

        if ($participant->payment_status === 'paid') {
            return redirect()->route('public.account.lucky-draws')
                ->with('info', 'Payment already completed');
        }

        $upiId = setting('payment_upi_id', '9572923168-4@ybl');
        $siteName = setting('site_title', 'AADS Property Portal');
        $upiString = "upi://pay?pa={$upiId}&pn=" . urlencode($siteName) . "&am={$participant->entry_fee_paid}&cu=INR&tn=" . urlencode('Reward Draw: ' . $participant->draw->name);
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($upiString);

        return Theme::scope('real-estate.public.lucky-draws.payment', compact('participant', 'qrCodeUrl', 'upiId'))->render();
    }

    public function submitPayment(Request $request, $participantId)
    {
        $request->validate([
            'payment_utr' => 'required|string|max:255',
            'payment_screenshot' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $participant = LuckyDrawParticipant::where('account_id', auth('account')->id())
            ->findOrFail($participantId);

        if ($participant->payment_status === 'paid') {
            return back()->with('error', 'Payment already submitted');
        }

        $screenshotPath = null;
        if ($request->hasFile('payment_screenshot')) {
            $file = $request->file('payment_screenshot');
            $filename = 'payment_' . $participant->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $screenshotPath = $file->storeAs('lucky-draw-payments', $filename, 'public');
        }

        $participant->update([
            'payment_utr' => $request->input('payment_utr'),
            'payment_screenshot' => $screenshotPath,
            'payment_status' => 'paid',
        ]);

        $participant->draw->updateTotalPool();

        return redirect()->route('public.account.lucky-draws')
            ->with('success', 'Payment submitted successfully');
    }

    public function userDashboard()
    {
        if (!auth('account')->check()) {
            return redirect()->route('public.account.login');
        }

        $user = auth('account')->user();

        $activeParticipations = $user->luckyDrawParticipations()
            ->with(['draw.property'])
            ->whereHas('draw', function($query) {
                $query->where('status', 'active');
            })
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        $completedParticipations = $user->luckyDrawParticipations()
            ->with(['draw.property'])
            ->whereHas('draw', function($query) {
                $query->where('status', 'completed');
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $wonDraws = $user->luckyDrawParticipations()
            ->with(['draw.property'])
            ->whereHas('draw', function($query) use ($user) {
                $query->where('status', 'completed')
                      ->where('winner_type', 'real')
                      ->where('winner_id', $user->id);
            })
            ->get();

        $totalWon = $wonDraws->count();

        $stats = [
            'total_joined' => $user->total_draws_joined,
            'total_won' => $totalWon,
            'available_credits' => $user->available_discount,
            'active_participations' => $activeParticipations->count(),
        ];

        return Theme::scope('real-estate.public.lucky-draws.dashboard', compact('activeParticipations', 'completedParticipations', 'wonDraws', 'stats'))->render();
    }

    public function winners()
    {
        $recentWinners = $this->getRecentWinners(20);
        
        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add('Reward Draws', route('public.lucky-draws.index'))
            ->add('Winners', route('public.lucky-draws.winners'));
        
        return Theme::scope('real-estate.public.lucky-draws.winners', compact('recentWinners'))->render();
    }

    private function getRecentWinners($limit = 10)
    {
        $completedDraws = LuckyDraw::where('status', 'completed')
            ->with(['property'])
            ->orderBy('draw_date', 'desc')
            ->limit($limit)
            ->get();

        $winners = [];
        foreach ($completedDraws as $draw) {
            if ($draw->winner_type === 'real') {
                $winner = DB::table('re_accounts')
                    ->select('first_name', 'last_name', 'avatar_id')
                    ->where('id', $draw->winner_id)
                    ->first();
                
                if ($winner) {
                    $winners[] = [
                        'name' => $winner->first_name . ' ' . $winner->last_name,
                        'draw_name' => $draw->name,
                        'property_name' => $draw->property->name ?? 'N/A',
                        'property_value' => $draw->property_value,
                        'draw_date' => $draw->draw_date,
                        'avatar' => $winner->avatar_id ? asset('storage/avatars/' . $winner->avatar_id) : null,
                        'type' => 'real'
                    ];
                }
            } else {
                $dummyWinner = DummyWinner::find($draw->winner_id);
                if ($dummyWinner) {
                    $winners[] = [
                        'name' => $dummyWinner->name,
                        'draw_name' => $draw->name,
                        'property_name' => $draw->property->name ?? 'N/A',
                        'property_value' => $draw->property_value,
                        'draw_date' => $draw->draw_date,
                        'avatar' => $dummyWinner->avatar_url,
                        'city' => $dummyWinner->city,
                        'bio' => $dummyWinner->bio,
                        'type' => 'dummy'
                    ];
                }
            }
        }

        return collect($winners);
    }

    public function getDrawStats($id)
    {
        $draw = LuckyDraw::findOrFail($id);
        
        $stats = [
            'total_participants' => $draw->participants()->where('payment_status', 'paid')->count(),
            'total_pool' => $draw->participants()->where('payment_status', 'paid')->sum('entry_fee_paid'),
            'time_left' => $draw->end_date->diffForHumans(),
            'days_left' => now()->diffInDays($draw->end_date, false),
            'hours_left' => now()->diffInHours($draw->end_date, false),
            'is_profitable' => false,
        ];
        
        $stats['is_profitable'] = $stats['total_pool'] >= $draw->property_value;
        $stats['profit_loss'] = $stats['total_pool'] - $draw->property_value;

        return response()->json($stats);
    }
}
