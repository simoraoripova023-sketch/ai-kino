<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseIntent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'amount_stars',
        'currency',
        'status',
        'invoice_payload',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_stars' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'intent_id');
    }
}

