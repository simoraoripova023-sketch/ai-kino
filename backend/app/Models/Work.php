<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'format',
        'style',
        'language',
        'age_rating',
        'poster_path',
        'cover_path',
        'trailer_file_id',
        'status',
        'pricing_mode',
        'price_stars',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price_stars' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'work_genre');
    }

    public function seasons()
    {
        return $this->hasMany(Season::class);
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }

    public function publishedEpisodes()
    {
        return $this->hasMany(Episode::class)
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            })
            ->orderBy('number');
    }
}
