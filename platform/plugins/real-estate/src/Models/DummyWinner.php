<?php

namespace Botble\RealEstate\Models;

use Illuminate\Database\Eloquent\Model;

class DummyWinner extends Model
{
    protected $table = 'dummy_winners';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'city',
        'bio',
        'avatar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get active dummy winners
     */
    public static function getActive()
    {
        return self::where('is_active', true)->get();
    }

    /**
     * Get random dummy winner
     */
    public static function getRandomWinner()
    {
        return self::where('is_active', true)->inRandomOrder()->first();
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // Generate avatar based on name
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&size=200&background=1d5f6f&color=fff";
    }
}