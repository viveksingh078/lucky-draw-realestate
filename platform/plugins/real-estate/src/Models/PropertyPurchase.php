<?php

namespace Botble\RealEstate\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyPurchase extends BaseModel
{
    protected $table = 're_property_purchases';

    protected $fillable = [
        'account_id',
        'property_id',
        'property_name',
        'property_location',
        'property_price',
        'gst_amount',
        'subtotal',
        'lost_draw_discount',
        'wallet_discount',
        'total_discount',
        'final_amount',
        'status',
        'admin_notes',
        'approved_at',
        'approved_by',
        'rejected_at',
    ];

    protected $casts = [
        'property_price' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'lost_draw_discount' => 'decimal:2',
        'wallet_discount' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the account that owns the purchase
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Get the property being purchased
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Get the admin who approved/rejected
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\Botble\ACL\Models\User::class, 'approved_by');
    }

    /**
     * Check if purchase is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if purchase is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if purchase is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Pending</span>',
            'approved' => '<span class="badge badge-success">Approved</span>',
            'rejected' => '<span class="badge badge-danger">Rejected</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }
}