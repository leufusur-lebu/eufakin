<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'subscription_id',
        'amount',
        'payment_date',
        'payment_type',
        'comprobante',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount'       => 'decimal:2',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function subscriptionInvoices(): BelongsToMany
    {
        return $this->belongsToMany(
            SubscriptionInvoice::class,
            'payment_subscription_invoice',
            'payment_id',
            'subscription_invoice_id'
        );
    }
}
