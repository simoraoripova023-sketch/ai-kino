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
        'file_id',
        'file_unique_id',
        'storage_chat_id',
        'storage_message_id',
        'status',
        'publish_at',
        'is_preview',
        'price_stars',
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

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
