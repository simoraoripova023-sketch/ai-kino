<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'season_id',
        'number',
        'title',
        'duration_sec',
        'video_url',
        'file_id',
        'file_unique_id',
        'storage_chat_id',
        'storage_message_id',
        'status',
        'publish_at',
        'is_preview',
        'price_stars',
    ];

    protected $appends = [
        'video_url',
    ];

    protected function casts(): array
    {
        return [
            'duration_sec' => 'integer',
            'storage_chat_id' => 'integer',
            'storage_message_id' => 'integer',
            'is_preview' => 'boolean',
            'price_stars' => 'integer',
            'publish_at' => 'datetime',
        ];
    }

    public function getVideoUrlAttribute(): ?string
    {
        // 1. Direct explicit video URL
        if (!empty($this->attributes['video_url'])) {
            return $this->attributes['video_url'];
        }

        // 2. Streamable proxy for Telegram video
        if (!empty($this->file_id) && !str_starts_with($this->file_id, 'sample_')) {
            return url("/api/v1/episodes/{$this->id}/stream");
        }

        // 3. Fallback sample video
        return 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4';
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
