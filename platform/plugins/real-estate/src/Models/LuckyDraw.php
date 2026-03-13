<?php

namespace Botble\RealEstate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LuckyDraw extends Model
{
    protected $table = 'lucky_draws';

    protected $fillable = [
        'name',
        'draw_type',
        'property_id',
        'property_value',
        'entry_fee',
        'start_date',
        'end_date',
        'draw_date',
        'status',
        'total_pool',
        'winner_id',
        'winner_type',
        'is_profit',
        'profit_loss_amount',
        'description',
        'settings',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'draw_date' => 'datetime',
        'property_value' => 'decimal:2',
        'entry_fee' => 'decimal:2',
        'total_pool' => 'decimal:2',
        'profit_loss_amount' => 'decimal:2',
        'is_profit' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the property for this draw
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Get all participants
     */
    public function participants(): HasMany
    {
        return $this->hasMany(LuckyDrawParticipant::class, 'draw_id');
    }

    /**
     * Get paid participants only
     */
    public function paidParticipants(): HasMany
    {
        return $this->hasMany(LuckyDrawParticipant::class, 'draw_id')
                    ->where('payment_status', 'paid');
    }

    /**
     * Get winner (real account)
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'winner_id')
                    ->where('winner_type', 'real');
    }

    /**
     * Get dummy winner
     */
    public function dummyWinner(): BelongsTo
    {
        return $this->belongsTo(DummyWinner::class, 'winner_id')
                    ->where('winner_type', 'dummy');
    }

    /**
     * Check if draw is active for participation
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               now()->between($this->start_date, $this->end_date);
    }

    /**
     * Check if draw is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get total participants count
     */
    public function getTotalParticipantsAttribute(): int
    {
        return $this->paidParticipants()->count();
    }

    /**
     * Calculate if this draw will be profitable
     */
    public function isProfitable(): bool
    {
        return $this->total_pool >= $this->property_value;
    }

    /**
     * Get profit/loss amount
     */
    public function getProfitLossAttribute(): float
    {
        return $this->total_pool - $this->property_value;
    }

    /**
     * Update total pool from participants
     */
    public function updateTotalPool(): void
    {
        $this->total_pool = $this->paidParticipants()->sum('entry_fee_paid');
        $this->save();
    }
}