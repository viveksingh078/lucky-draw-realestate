<?php

namespace Botble\RealEstate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LuckyDrawParticipant extends Model
{
    protected $table = 'lucky_draw_participants';

    protected $fillable = [
        'draw_id',
        'account_id',
        'entry_fee_paid',
        'payment_status',
        'payment_utr',
        'payment_screenshot',
        'joined_at',
        'is_winner',
        'credit_given',
    ];

    protected $casts = [
        'entry_fee_paid' => 'decimal:2',
        'credit_given' => 'decimal:2',
        'joined_at' => 'datetime',
        'is_winner' => 'boolean',
    ];

    /**
     * Get the draw
     */
    public function draw(): BelongsTo
    {
        return $this->belongsTo(LuckyDraw::class, 'draw_id');
    }

    /**
     * Get the participant account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Check if payment is completed
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if this participant won
     */
    public function isWinner(): bool
    {
        return $this->is_winner;
    }

    /**
     * Mark as winner
     */
    public function markAsWinner(): void
    {
        $this->is_winner = true;
        $this->save();
    }

    /**
     * Give credit to losing participant
     */
    public function giveCredit(float $amount): void
    {
        $this->credit_given = $amount;
        $this->save();

        // Update account credits
        $this->account->available_credits += $amount;
        $this->account->save();
    }
}