<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Professional extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'last_name', 'rut', 'module', 'specialty', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->last_name}");
    }

    public function scopeKine($query)
    {
        return $query->whereIn('module', ['kine', 'both']);
    }

    public function scopeEstetic($query)
    {
        return $query->whereIn('module', ['estetic', 'both']);
    }
}
