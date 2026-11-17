<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_uz',
        'name_ru',
        'sort_order',
    ];

    public function works()
    {
        return $this->belongsToMany(Work::class, 'work_genre');
    }
}
