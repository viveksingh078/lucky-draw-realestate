<?php

namespace Botble\RealEstate\Models;

use Botble\Base\Models\BaseModel;

class MembershipPlan extends BaseModel
{
    protected $table = 'membership_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'draws_allowed',
        'max_concurrent_draws',
        'credit_value',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'credit_value' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    /**
     * Get active plans only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->orderBy('sort_order');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '₹' . number_format($this->price, 2);
    }

    /**
     * Get duration in months
     */
    public function getDurationInMonthsAttribute()
    {
        return round($this->duration_days / 30);
    }

    /**
     * Get formatted credit value
     */
    public function getFormattedCreditValueAttribute()
    {
        return '₹' . number_format($this->credit_value, 0);
    }

    /**
     * Calculate credit value (price per draw)
     */
    public function calculateCreditValue()
    {
        if ($this->draws_allowed > 0) {
            return round($this->price / $this->draws_allowed, 2);
        }
        return 0;
    }
}
