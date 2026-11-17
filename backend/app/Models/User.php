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
    ];

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
