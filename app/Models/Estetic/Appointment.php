<?php

namespace App\Models\Estetic;

use App\Models\EsteticProfile;
use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'este_turnos';

    protected $fillable = [
        'estetic_profile_id', 'tratamiento_id', 'professional_id',
        'inicio', 'fin', 'estado', 'motivo', 'notas', 'recordatorio_enviado',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin'    => 'datetime',
        'recordatorio_enviado' => 'boolean',
    ];

    public function esteticProfile(): BelongsTo
    {
        return $this->belongsTo(EsteticProfile::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'tratamiento_id');
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function scopeOfDay($query, $date)
    {
        return $query->whereDate('inicio', $date);
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('inicio', [$from, $to]);
    }

    public function getColorAttribute(): string
    {
        return match ($this->estado) {
            'pendiente'  => 'zinc',
            'confirmado' => 'blue',
            'atendido'   => 'green',
            'cancelado'  => 'red',
            'ausente'    => 'amber',
            default      => 'zinc',
        };
    }
}
