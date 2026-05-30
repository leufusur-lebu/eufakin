<?php

namespace App\Models\Kine;

use App\Models\KineProfile;
use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'kine_turnos';

    protected $fillable = [
        'kine_profile_id', 'tratamiento_id', 'professional_id',
        'inicio', 'fin', 'estado', 'motivo', 'notas',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin'    => 'datetime',
    ];

    public function kineProfile(): BelongsTo
    {
        return $this->belongsTo(KineProfile::class);
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'tratamiento_id');
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function session(): HasOne
    {
        return $this->hasOne(Sesion::class, 'turno_id');
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
