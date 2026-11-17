<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UploadSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'admin_user_id',
        'work_id',
        'season_id',
        'status',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}

