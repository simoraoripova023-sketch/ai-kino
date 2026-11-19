<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'telegram_id',
        'first_name',
        'username',
        'language',
        'is_blocked',
        'is_admin',
        'last_seen_at',
        'name',
        'email',
        'password',
        'referral_code',
        'referrer_id',
        'bonus_stars',
        'referrals_count',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }

    public function ensureReferralCode(): string
    {
        if (empty($this->referral_code)) {
            $this->referral_code = strtoupper(\Illuminate\Support\Str::random(8));
            $this->save();
        }
        return $this->referral_code;
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    public function referralRewards()
    {
        return $this->hasMany(ReferralLog::class, 'referrer_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'telegram_id' => 'integer',
            'is_blocked' => 'boolean',
            'is_admin' => 'boolean',
            'last_seen_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function accessGrants()
    {
        return $this->hasMany(AccessGrant::class);
    }

    public function watchProgresses()
    {
        return $this->hasMany(WatchProgress::class);
    }

    public function purchaseIntents()
    {
        return $this->hasMany(PurchaseIntent::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
