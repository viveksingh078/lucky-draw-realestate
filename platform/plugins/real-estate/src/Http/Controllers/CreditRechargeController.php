<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\RealEstate\Models\CreditRecharge;
use Botble\RealEstate\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use RvMedia;

class CreditRechargeController extends Controller
{
    /**
     * Show recharge page with plan selection
     */
    public function index()
    {
        $user = auth('account')->user();
        $plans = MembershipPlan::where('is_active', 1)->orderBy('sort_order')->get();
        
        return view('plugins/real-estate::account.recharge.index', compact('user', 'plans'));
    }

    /**
     * Submit recharge request
     */
    public function submit(Request $request, BaseHttpResponse $response)
    {
        $validator = \Validator::make($request->all(), [
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'payment_utr_number' => [
                'required',
                'string',
                'regex:/^[0-9]{12,22}$/',
                'unique:re_credit_recharges,payment_utr_number'
            ],
            'payment_screenshot' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'payment_utr_number.regex' => 'UTR number must be 12-22 digits and contain only numbers.',
            'payment_utr_number.unique' => 'This UTR number has already been used. Please enter a different UTR number.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = auth('account')->user();
            $plan = MembershipPlan::findOrFail($request->membership_plan_id);

            // Double check UTR uniqueness
            $existingUTR = CreditRecharge::where('payment_utr_number', $request->payment_utr_number)->first();
            if ($existingUTR) {
                return redirect()->back()
                    ->withErrors(['payment_utr_number' => 'This UTR number has already been used. Please check your transaction and enter a unique UTR number.'])
                    ->withInput();
            }

            // Upload payment screenshot
            $screenshotPath = null;
            if ($request->hasFile('payment_screenshot')) {
                $result = RvMedia::handleUpload($request->file('payment_screenshot'), 0, 'recharge-payments');
                if (!$result['error']) {
                    $screenshotPath = $result['data']->url;
                }
            }

            // Create recharge request
            $recharge = CreditRecharge::create([
                'account_id' => $user->id,
                'membership_plan_id' => $plan->id,
                'amount' => $plan->price,
                'payment_qr_code' => setting('payment_qr_code'),
                'payment_utr_number' => $request->payment_utr_number,
                'payment_screenshot' => $screenshotPath,
                'status' => 'pending',
            ]);

            return redirect()->route('public.account.recharge')
                ->with('success', 'Recharge request submitted successfully! Admin will review and approve.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to submit recharge request: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Get QR code for payment
     */
    public function getQRCode(Request $request)
    {
        $planId = $request->get('plan_id');
        $plan = MembershipPlan::find($planId);
        
        if (!$plan) {
            return response()->json(['error' => 'Plan not found'], 404);
        }

        $qrCode = setting('payment_qr_code');
        $upiId = setting('payment_upi_id', 'Not configured');

        return response()->json([
            'qr_code' => $qrCode ? url($qrCode) : null,
            'upi_id' => $upiId,
            'amount' => $plan->price,
            'plan_name' => $plan->name,
        ]);
    }

    /**
     * Admin: List all recharge requests
     */
    public function adminIndex()
    {
        $recharges = CreditRecharge::with(['account', 'membershipPlan'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('plugins/real-estate::credit-recharges.index', compact('recharges'));
    }

    /**
     * Admin: Show recharge details
     */
    public function show($id)
    {
        $recharge = CreditRecharge::with(['account', 'membershipPlan', 'approvedBy'])->findOrFail($id);
        
        return view('plugins/real-estate::credit-recharges.show', compact('recharge'));
    }

    /**
     * Admin: Approve recharge request
     */
    public function approve($id, Request $request, BaseHttpResponse $response)
    {
        try {
            $recharge = CreditRecharge::findOrFail($id);

            if (!$recharge->isPending()) {
                return $response
                    ->setError()
                    ->setMessage('This recharge request has already been processed.');
            }

            DB::transaction(function () use ($recharge, $request) {
                $account = $recharge->account;
                $plan = $recharge->membershipPlan;

                // Add credits to wallet
                $account->wallet_balance += $plan->price;
                $account->draws_remaining += $plan->draws_allowed;
                $account->save();

                // Update recharge status
                $recharge->status = 'approved';
                $recharge->approved_at = now();
                $recharge->approved_by = auth()->id();
                $recharge->admin_notes = $request->input('notes');
                $recharge->save();
            });

            return $response
                ->setMessage('Recharge approved successfully! Credits added to user wallet.');

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to approve recharge: ' . $e->getMessage());
        }
    }

    /**
     * Admin: Reject recharge request
     */
    public function reject($id, Request $request, BaseHttpResponse $response)
    {
        try {
            $recharge = CreditRecharge::findOrFail($id);

            if (!$recharge->isPending()) {
                return $response
                    ->setError()
                    ->setMessage('This recharge request has already been processed.');
            }

            $recharge->status = 'rejected';
            $recharge->rejected_at = now();
            $recharge->admin_notes = $request->input('reason', 'Rejected by admin');
            $recharge->save();

            return $response
                ->setMessage('Recharge request rejected.');

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to reject recharge: ' . $e->getMessage());
        }
    }
}
