<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembershipController extends BaseController
{
    /**
     * Get QR code for membership plan
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQRCode(Request $request)
    {
        try {
            $planId = $request->input('plan_id');
            
            if (!$planId) {
                return response()->json([
                    'error' => true,
                    'message' => 'Plan ID is required'
                ], 400);
            }
            
            // Get plan using direct DB query
            $plan = DB::table('membership_plans')->where('id', $planId)->first();
            
            if (!$plan) {
                return response()->json([
                    'error' => true,
                    'message' => 'Plan not found'
                ], 404);
            }
            
            // Get UPI ID from settings
            $upiId = setting('payment_upi_id', '9572923168-4@ybl');
            
            // Generate QR code URL directly (no helper needed)
            $upiString = "upi://pay?pa={$upiId}&pn=" . urlencode(setting('site_title', 'AADS Property Portal')) . "&am={$plan->price}&cu=INR&tn=" . urlencode('Membership: ' . $plan->name);
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($upiString);
            
            return response()->json([
                'error' => false,
                'data' => [
                    'qr_code_url' => $qrCodeUrl,
                    'amount' => $plan->price,
                    'plan_name' => $plan->name,
                    'upi_id' => $upiId
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error generating QR code: ' . $e->getMessage()
            ], 500);
        }
    }
}
