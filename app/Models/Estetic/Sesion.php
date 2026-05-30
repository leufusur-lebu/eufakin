<?php

namespace App\Models\Estetic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sesion extends Model
{
    protected $table = 'este_sesiones';

    protected $fillable = [
        'tratamiento_id', 'turno_id', 'numero_sesion', 'fecha',
        'productos_utilizados', 'resultados_observados', 'notas_clinicas',
        'intensidad', 'zona_especifica',
        'duracion_real_minutos', 'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'duracion_real_minutos' => 'integer',
    ];

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'tratamiento_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'turno_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SessionPhoto::class, 'sesion_id')->orderBy('tomada_at');
    }
}
