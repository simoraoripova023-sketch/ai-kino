<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event_name',
        'work_id',
        'episode_id',
        'properties',
        'occurred_at',
        'request_id',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

