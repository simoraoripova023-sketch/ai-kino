<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramUpdate extends Model
{
    protected $primaryKey = 'update_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'update_id',
        'kind',
        'received_at',
        'processed_at',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}

