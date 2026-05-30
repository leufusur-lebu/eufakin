<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GymProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id', 'registered_at', 'active',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'person_id', 'person_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'person_id', 'person_id');
    }
}
