<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'amount',
        'due_date',
        'status', // 'pending', 'paid', etc.
    ];

    // Relación: una factura pertenece a una suscripción
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // Relación: muchas a muchos con pagos a través de la tabla pivot
    public function payments()
    {
        return $this->belongsToMany(
            Payment::class,
            'payment_subscription_invoice', // tabla pivot
            'subscription_invoice_id',      // FK en pivot a esta tabla
            'payment_id'                    // FK en pivot a payments
        );
    }
}
