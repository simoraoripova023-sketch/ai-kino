<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchProgress extends Model
{
    protected $table = 'watch_progress';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = ['user_id', 'work_id'];

    protected $fillable = [
        'user_id',
        'work_id',
        'last_episode_id',
        'player_message_id',
        'opened_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function lastEpisode()
    {
        return $this->belongsTo(Episode::class, 'last_episode_id');
    }
}

