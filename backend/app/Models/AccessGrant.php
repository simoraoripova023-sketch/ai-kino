<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'source_type',
        'source_id',
        'status',
        'granted_at',
        'revoked_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

