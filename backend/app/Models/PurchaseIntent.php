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

    protected $appends = [
        'target_title',
        'target_poster',
        'work_id',
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

    public function getTargetTitleAttribute(): string
    {
        if ($this->target_type === 'work') {
            return Work::find($this->target_id)?->title ?? 'AI Kino';
        }
        $ep = Episode::with('work')->find($this->target_id);
        return $ep ? (($ep->work?->title ?? 'AI Kino') . " — {$ep->number}-qism") : 'AI Epizod';
    }

    public function getTargetPosterAttribute(): ?string
    {
        if ($this->target_type === 'work') {
            return Work::find($this->target_id)?->poster_path;
        }
        $ep = Episode::with('work')->find($this->target_id);
        return $ep?->work?->poster_path;
    }

    public function getWorkIdAttribute(): ?int
    {
        if ($this->target_type === 'work') {
            return (int) $this->target_id;
        }
        $ep = Episode::find($this->target_id);
        return $ep?->work_id;
    }
}

