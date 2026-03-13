<?php

namespace Botble\RealEstate\Models;

use Botble\Base\Models\BaseModel;

class CreditRecharge extends BaseModel
{
    protected $table = 're_credit_recharges';

    protected $fillable = [
        'account_id',
        'membership_plan_id',
        'amount',
        'payment_qr_code',
        'payment_utr_number',
        'payment_screenshot',
        'status',
        'admin_notes',
        'approved_at',
        'approved_by',
        'rejected_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the account that owns the recharge
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Get the membership plan
     */
    public function membershipPlan()
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    /**
     * Get the admin who approved
     */
    public function approvedBy()
    {
        return $this->belongsTo('Botble\ACL\Models\User', 'approved_by');
    }

    /**
     * Check if pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Scope for pending recharges
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved recharges
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
