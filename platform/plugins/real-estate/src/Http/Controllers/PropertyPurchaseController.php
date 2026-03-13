<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\RealEstate\Models\PropertyPurchase;
use Botble\RealEstate\Models\Property;
use Botble\RealEstate\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyPurchaseController extends BaseController
{
    /**
     * Show property purchase page
     */
    public function show($propertyId)
    {
        $user = auth('account')->user();
        if (!$user) {
            return redirect()->route('public.account.login');
        }

        $property = Property::findOrFail($propertyId);
        
        // Calculate pricing
        $propertyPrice = $property->price;
        $gstRate = 0.18; // 18% GST
        $gstAmount = $propertyPrice * $gstRate;
        $subtotal = $propertyPrice + $gstAmount;
        
        // Calculate discounts
        $lostDrawDiscount = $user->available_discount ?? 0;
        $walletBalance = $user->wallet_balance ?? 0;
        
        return view('plugins/real-estate::property-purchase.checkout', compact(
            'property', 
            'user', 
            'propertyPrice', 
            'gstAmount', 
            'subtotal', 
            'lostDrawDiscount', 
            'walletBalance'
        ));
    }

    /**
     * Submit property purchase request
     */
    public function submit(Request $request, BaseHttpResponse $response)
    {
        $user = auth('account')->user();
        if (!$user) {
            return redirect()->route('public.account.login');
        }

        $request->validate([
            'property_id' => 'required|exists:re_properties,id',
            'wallet_discount' => 'numeric|min:0|max:' . ($user->wallet_balance ?? 0),
        ]);

        try {
            $property = Property::findOrFail($request->property_id);
            
            // Calculate amounts
            $propertyPrice = $property->price;
            $gstAmount = $propertyPrice * 0.18; // 18% GST
            $subtotal = $propertyPrice + $gstAmount;
            
            $lostDrawDiscount = $user->available_discount ?? 0;
            $walletDiscount = (float) $request->input('wallet_discount', 0);
            $totalDiscount = $lostDrawDiscount + $walletDiscount;
            $finalAmount = $subtotal - $totalDiscount;

            DB::transaction(function () use ($user, $property, $propertyPrice, $gstAmount, $subtotal, $lostDrawDiscount, $walletDiscount, $totalDiscount, $finalAmount) {
                // Create purchase request
                PropertyPurchase::create([
                    'account_id' => $user->id,
                    'property_id' => $property->id,
                    'property_name' => $property->name,
                    'property_location' => $property->location,
                    'property_price' => $propertyPrice,
                    'gst_amount' => $gstAmount,
                    'subtotal' => $subtotal,
                    'lost_draw_discount' => $lostDrawDiscount,
                    'wallet_discount' => $walletDiscount,
                    'total_discount' => $totalDiscount,
                    'final_amount' => $finalAmount,
                    'status' => 'pending',
                ]);

                // Deduct wallet discount from user's wallet
                if ($walletDiscount > 0) {
                    $user->wallet_balance -= $walletDiscount;
                    $user->save();
                }

                // Mark lost draw discount as used
                if ($lostDrawDiscount > 0) {
                    $user->available_discount = 0;
                    $user->save();
                }
            });

            return $response
                ->setNextUrl(route('public.account.property-purchases'))
                ->setMessage('Property purchase request submitted successfully! Admin will review and approve.');

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to submit purchase request: ' . $e->getMessage());
        }
    }

    /**
     * User's property purchases list
     */
    public function userPurchases()
    {
        $user = auth('account')->user();
        if (!$user) {
            return redirect()->route('public.account.login');
        }

        $purchases = PropertyPurchase::where('account_id', $user->id)
            ->with('property')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('plugins/real-estate::account.property-purchases.index', compact('purchases'));
    }

    /**
     * Admin: List all property purchases
     */
    public function adminIndex()
    {
        page_title()->setTitle('Property Purchases');

        $purchases = PropertyPurchase::with(['account', 'property'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('plugins/real-estate::property-purchases.index', compact('purchases'));
    }

    /**
     * Admin: Show purchase details
     */
    public function adminShow($id)
    {
        $purchase = PropertyPurchase::with(['account', 'property', 'approvedBy'])->findOrFail($id);
        
        page_title()->setTitle('Property Purchase #' . $purchase->id);
        
        return view('plugins/real-estate::property-purchases.show', compact('purchase'));
    }

    /**
     * Admin: Approve purchase
     */
    public function approve($id, Request $request, BaseHttpResponse $response)
    {
        try {
            $purchase = PropertyPurchase::findOrFail($id);

            if (!$purchase->isPending()) {
                return $response
                    ->setError()
                    ->setMessage('This purchase request has already been processed.');
            }

            $purchase->status = 'approved';
            $purchase->approved_at = now();
            $purchase->approved_by = auth()->id();
            $purchase->admin_notes = $request->input('notes');
            $purchase->save();

            return $response
                ->setMessage('Property purchase approved successfully!');

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to approve purchase: ' . $e->getMessage());
        }
    }

    /**
     * Admin: Reject purchase
     */
    public function reject($id, Request $request, BaseHttpResponse $response)
    {
        try {
            $purchase = PropertyPurchase::findOrFail($id);

            if (!$purchase->isPending()) {
                return $response
                    ->setError()
                    ->setMessage('This purchase request has already been processed.');
            }

            DB::transaction(function () use ($purchase, $request) {
                // Refund wallet discount if any
                if ($purchase->wallet_discount > 0) {
                    $user = $purchase->account;
                    $user->wallet_balance += $purchase->wallet_discount;
                    $user->save();
                }

                // Refund lost draw discount if any
                if ($purchase->lost_draw_discount > 0) {
                    $user = $purchase->account;
                    $user->available_discount += $purchase->lost_draw_discount;
                    $user->save();
                }

                $purchase->status = 'rejected';
                $purchase->rejected_at = now();
                $purchase->admin_notes = $request->input('reason', 'Rejected by admin');
                $purchase->save();
            });

            return $response
                ->setMessage('Property purchase rejected and discounts refunded.');

        } catch (\Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to reject purchase: ' . $e->getMessage());
        }
    }
}
