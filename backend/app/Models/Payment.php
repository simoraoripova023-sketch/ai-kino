<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'intent_id',
        'user_id',
        'telegram_payment_charge_id',
        'amount_stars',
        'currency',
        'paid_at',
        'status',
        'raw_event_reference',
    ];

    protected function casts(): array
    {
        return [
            'amount_stars' => 'integer',
            'paid_at' => 'datetime',
            'raw_event_reference' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function intent()
    {
        return $this->belongsTo(PurchaseIntent::class, 'intent_id');
    }
}

