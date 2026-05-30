<?php

namespace App\Models\Kine;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sesion extends Model
{
    use HasFactory;

    protected $table = 'kine_sesiones';

    protected $fillable = [
        'tratamiento_id', 'turno_id', 'numero_sesion', 'fecha',
        'evolucion', 'ejercicios', 'escala_dolor',
        'notas_clinicas', 'rom', 'fuerza_muscular', 'duracion_real_minutos',
        'estado',
    ];

    protected $casts = [
        'fecha'        => 'date',
        'escala_dolor' => 'integer',
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
