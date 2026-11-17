<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    const CREATED_AT = null;

    protected $fillable = [
        'key',
        'value',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'updated_at' => 'datetime',
        ];
    }
}

