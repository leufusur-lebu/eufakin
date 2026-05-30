<?php

namespace App\Models\Estetic;

use App\Models\EsteticProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'este_pagos';

    protected $fillable = [
        'estetic_profile_id', 'tratamiento_id', 'sesion_id',
        'fecha', 'monto', 'metodo', 'estado',
        'comprobante', 'observaciones', 'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function esteticProfile(): BelongsTo
    {
        return $this->belongsTo(EsteticProfile::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'tratamiento_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Sesion::class, 'sesion_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function scopePaid($query)
    {
        return $query->where('estado', 'pagado');
    }
}
