<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    protected $fillable = ['key', 'name', 'body', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public static function get(string $key): ?self
    {
        return static::where('key', $key)->where('active', true)->first();
    }
}
