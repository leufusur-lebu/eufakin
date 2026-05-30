<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionDateChange extends Model
{
    protected $fillable = [
        'subscription_id', 'user_id',
        'previous_start_date', 'previous_end_date',
        'new_start_date', 'new_end_date',
        'glosa',
    ];

    protected $casts = [
        'previous_start_date' => 'date',
        'previous_end_date'   => 'date',
        'new_start_date'      => 'date',
        'new_end_date'        => 'date',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
