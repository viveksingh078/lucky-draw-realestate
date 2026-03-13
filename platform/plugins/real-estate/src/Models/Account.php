<?php

namespace Botble\RealEstate\Models;

use Botble\Base\Supports\Avatar;
use Botble\Media\Models\MediaFile;
use Botble\RealEstate\Notifications\ResetPasswordNotification;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use RealEstateHelper;
use RvMedia;

/**
 * @mixin \Eloquent
 */
class Account extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;

    /**
     * @var string
     */
    protected $table = 're_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'avatar_id',
        'dob',
        'phone',
        'description',
        'gender',
        'company',
        'account_type',
        'membership_plan_id',
        'membership_start_date',
        'membership_end_date',
        'draws_used',
        'draws_remaining',
        'current_active_draw_id',
        'membership_status',
        'pan_card_number',
        'pan_card_file',
        'aadhaar_number',
        'aadhaar_front_image',
        'aadhaar_back_image',
        'payment_qr_code',
        'payment_utr_number',
        'payment_screenshot',
        'account_status',
        'admin_notes',
        'approved_at',
        'approved_by',
        'available_credits',
        'total_draws_joined',
        'total_draws_won',
        'last_draw_joined',
        'available_discount',
        'discount_used',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'dob',
        'package_start_date',
        'package_end_date',
        'membership_start_date',
        'membership_end_date',
        'approved_at',
        'last_draw_joined',
    ];

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function avatar()
    {
        return $this->belongsTo(MediaFile::class)->withDefault();
    }

    /**
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar->url) {
            return RvMedia::url($this->avatar->url);
        }

        try {
            return (new Avatar)->create($this->name)->toBase64();
        } catch (Exception $exception) {
            return RvMedia::getDefaultImage();
        }
    }

    /**
     * Always capitalize the first name when we retrieve it
     * @param string $value
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Always capitalize the last name when we retrieve it
     * @param string $value
     * @return string
     */
    public function getLastNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * @return string
     * @deprecated since v2.22
     */
    public function getFullName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function properties()
    {
        return $this->morphMany(Property::class, 'author');
    }

    /**
     * @return bool
     */
    public function canPost(): bool
    {
        return !RealEstateHelper::isEnabledCreditsSystem() || $this->credits > 0;
    }

    /**
     * @param int $value
     * @return int
     */
    public function getCreditsAttribute($value)
    {
        if (!RealEstateHelper::isEnabledCreditsSystem()) {
            return 0;
        }

        return $value ?: 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    /**
     * @return BelongsToMany
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 're_account_packages', 'account_id', 'package_id');
    }

    /**
     * Get membership plan
     */
    public function membershipPlan()
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    /**
     * Check if account is approved
     */
    public function isApproved(): bool
    {
        return $this->account_status === 'approved';
    }

    /**
     * Check if account is pending
     */
    public function isPending(): bool
    {
        return $this->account_status === 'pending';
    }

    /**
     * Check if account is rejected
     */
    public function isRejected(): bool
    {
        return $this->account_status === 'rejected';
    }

    /**
     * Get reward draw participations
     */
    public function luckyDrawParticipations()
    {
        return $this->hasMany(LuckyDrawParticipant::class, 'account_id');
    }

    /**
     * Get active draw participations
     */
    public function activeDrawParticipations()
    {
        return $this->hasMany(LuckyDrawParticipant::class, 'account_id')
                    ->whereHas('draw', function($query) {
                        $query->where('status', 'active');
                    });
    }

    /**
     * Get won draws
     */
    public function wonDraws()
    {
        return $this->hasMany(LuckyDrawParticipant::class, 'account_id')
                    ->where('is_winner', true);
    }

    /**
     * Check if user can join a draw based on membership plan
     */
    public function canJoinDraw(LuckyDraw $draw): bool
    {
        // Get entry fee from membership plan's credit value
        $entryFee = $this->membershipPlan ? $this->membershipPlan->credit_value : 10000;
        
        // Check if already joined this specific draw
        $alreadyJoined = $this->luckyDrawParticipations()
            ->where('draw_id', $draw->id)
            ->exists();

        if ($alreadyJoined) {
            return false;
        }

        // Check if account is approved and membership is active
        if (!$this->isApproved() || $this->membership_status !== 'active') {
            return false;
        }

        // Check if user has sufficient wallet balance
        if ($this->wallet_balance < $entryFee) {
            return false;
        }

        // Check if user has reached max concurrent draws limit
        $currentActiveDraws = $this->luckyDrawParticipations()
            ->whereHas('draw', function($query) {
                $query->where('status', 'active');
            })
            ->where('payment_status', 'paid')
            ->count();

        $maxConcurrent = $this->membershipPlan ? $this->membershipPlan->max_concurrent_draws : 1;

        if ($currentActiveDraws >= $maxConcurrent) {
            return false;
        }

        return true;
    }

    /**
     * Join a reward draw using wallet balance
     */
    public function joinDraw(LuckyDraw $draw): LuckyDrawParticipant
    {
        // Get entry fee from membership plan's credit value
        $entryFee = $this->membershipPlan ? $this->membershipPlan->credit_value : 10000;
        
        return DB::transaction(function () use ($draw, $entryFee) {
            // Create participation record with entry fee
            $participant = $this->luckyDrawParticipations()->create([
                'draw_id' => $draw->id,
                'entry_fee_paid' => $entryFee,
                'payment_status' => 'paid',
                'joined_at' => now(),
            ]);

            // Update account wallet using direct update
            DB::table('re_accounts')
                ->where('id', $this->id)
                ->update([
                    'total_draws_joined' => DB::raw('total_draws_joined + 1'),
                    'draws_used' => DB::raw('draws_used + 1'),
                    'draws_remaining' => DB::raw('draws_remaining - 1'),
                    'wallet_balance' => DB::raw('wallet_balance - ' . $entryFee),
                    'wallet_on_hold' => DB::raw('wallet_on_hold + ' . $entryFee),
                    'wallet_used' => DB::raw('wallet_used + ' . $entryFee),
                    'last_draw_joined' => now(),
                    'updated_at' => now(),
                ]);

            // Refresh model
            $this->refresh();

            return $participant;
        });
    }

    /**
     * Complete a draw (win or lose) and free up slot for next draw
     */
    public function completeDraw(LuckyDraw $draw, bool $isWinner = false): void
    {
        // No need to clear current_active_draw_id anymore
        // Just update winner stats if applicable
        if ($isWinner) {
            $this->increment('total_draws_won');
        }
    }

    /**
     * Leave a draw (allowed anytime before draw end date)
     */
    public function leaveDraw(LuckyDraw $draw): bool
    {
        // Get entry fee from membership plan's credit value
        $entryFee = $this->membershipPlan ? $this->membershipPlan->credit_value : 10000;
        
        // Check if user can leave (before draw end date)
        if (now()->greaterThanOrEqualTo($draw->end_date)) {
            return false; // Cannot leave, draw has ended
        }

        return DB::transaction(function () use ($draw, $entryFee) {
            // Delete participation record
            $deleted = $this->luckyDrawParticipations()
                ->where('draw_id', $draw->id)
                ->delete();

            if ($deleted) {
                // Refund wallet balance
                DB::table('re_accounts')
                    ->where('id', $this->id)
                    ->update([
                        'draws_used' => DB::raw('draws_used - 1'),
                        'draws_remaining' => DB::raw('draws_remaining + 1'),
                        'total_draws_joined' => DB::raw('total_draws_joined - 1'),
                        'wallet_balance' => DB::raw('wallet_balance + ' . $entryFee),
                        'wallet_on_hold' => DB::raw('wallet_on_hold - ' . $entryFee),
                        'wallet_used' => DB::raw('wallet_used - ' . $entryFee),
                        'updated_at' => now(),
                    ]);

                $this->refresh();
                return true;
            }

            return false;
        });
    }

    /**
     * Check if user can leave a draw
     */
    public function canLeaveDraw(LuckyDraw $draw): bool
    {
        // Check if user has joined this draw
        $participation = $this->luckyDrawParticipations()
            ->where('draw_id', $draw->id)
            ->first();

        if (!$participation) {
            return false;
        }

        // Check if draw has not ended yet
        return now()->lessThan($draw->end_date);
    }

    /**
     * Initialize draws remaining when membership is activated
     */
    public function initializeDrawCredits(): void
    {
        if ($this->membershipPlan) {
            $this->draws_remaining = $this->membershipPlan->draws_allowed - $this->draws_used;
            $this->save();
        }
    }

    /**
     * Check if user has active draw
     */
    public function hasActiveDraw(): bool
    {
        $activeCount = $this->luckyDrawParticipations()
            ->whereHas('draw', function($query) {
                $query->where('status', 'active');
            })
            ->where('payment_status', 'paid')
            ->count();

        $maxConcurrent = $this->membershipPlan ? $this->membershipPlan->max_concurrent_draws : 1;

        return $activeCount >= $maxConcurrent;
    }

    /**
     * Get current active draw
     */
    public function currentActiveDraw()
    {
        return $this->luckyDrawParticipations()
            ->with('draw')
            ->whereHas('draw', function($query) {
                $query->where('status', 'active');
            })
            ->where('payment_status', 'paid')
            ->first();
    }

    /**
     * Get all active draws
     */
    public function activeDraws()
    {
        return $this->luckyDrawParticipations()
            ->with('draw')
            ->whereHas('draw', function($query) {
                $query->where('status', 'active');
            })
            ->where('payment_status', 'paid')
            ->get();
    }

    /**
     * Calculate available discount based on lost draws
     */
    public function calculateAvailableDiscount(): float
    {
        if (!$this->membershipPlan) {
            return 0;
        }

        // Count lost draws (completed draws where user didn't win)
        $lostDraws = $this->luckyDrawParticipations()
            ->whereHas('draw', function($query) {
                $query->where('status', 'completed');
            })
            ->where('is_winner', false)
            ->count();

        // Calculate discount: lost_draws × credit_value
        $discount = $lostDraws * $this->membershipPlan->credit_value;

        return round($discount, 2);
    }

    /**
     * Update available discount after draw completes
     */
    public function updateDiscountAfterDraw(LuckyDraw $draw, bool $isWinner): void
    {
        if ($isWinner) {
            // User won - membership expires, no discount
            $this->membership_status = 'expired';
            $this->available_discount = 0;
            $this->save();
        } else {
            // User lost - add credit value to discount
            if ($this->membershipPlan) {
                $this->available_discount += $this->membershipPlan->credit_value;
                $this->save();
            }
        }
    }

    /**
     * Use discount on property purchase
     */
    public function useDiscount(float $amount): bool
    {
        if ($amount > $this->available_discount) {
            return false; // Not enough discount
        }

        DB::transaction(function () use ($amount) {
            $this->discount_used += $amount;
            $this->available_discount -= $amount;
            
            // If all discount used, expire membership
            if ($this->available_discount <= 0) {
                $this->membership_status = 'expired';
            }
            
            $this->save();
        });

        return true;
    }

    /**
     * Get formatted available discount
     */
    public function getFormattedDiscountAttribute()
    {
        return '₹' . number_format($this->available_discount, 0);
    }

    /**
     * Check if user has available discount
     */
    public function hasDiscount(): bool
    {
        return $this->available_discount > 0;
    }

    /**
     * Check if account is a vendor
     */
    public function isVendor(): bool
    {
        return $this->account_type === 'vendor';
    }

    /**
     * Check if account is a user
     */
    public function isUser(): bool
    {
        return $this->account_type === 'user';
    }
}
