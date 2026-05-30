<?php

namespace App\Models\Kine;

use App\Models\KineProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kine_pagos';

    protected $fillable = [
        'kine_profile_id', 'tratamiento_id', 'sesion_id',
        'fecha', 'monto', 'metodo', 'estado',
        'comprobante', 'observaciones', 'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function kineProfile(): BelongsTo
    {
        return $this->belongsTo(KineProfile::class);
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
