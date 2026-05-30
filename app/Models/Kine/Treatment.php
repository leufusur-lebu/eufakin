<?php

namespace App\Models\Kine;

use App\Models\KineProfile;
use App\Models\Professional;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kine_tratamientos';

    protected $fillable = [
        'kine_profile_id', 'professional_id', 'tipo_tratamiento_id',
        'diagnostico', 'zona_tratada', 'plan',
        'fecha_inicio', 'fecha_fin', 'sesiones_totales', 'sesiones_realizadas',
        'costo_sesion', 'costo_total', 'estado', 'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'costo_sesion' => 'decimal:2',
        'costo_total'  => 'decimal:2',
    ];

    public function kineProfile(): BelongsTo
    {
        return $this->belongsTo(KineProfile::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function tipoTratamiento(): BelongsTo
    {
        return $this->belongsTo(TipoTratamiento::class, 'tipo_tratamiento_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Sesion::class, 'tratamiento_id')->orderBy('numero_sesion');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'tratamiento_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tratamiento_id');
    }

    public function getProgressPercentAttribute(): float
    {
        if (!$this->sesiones_totales) return 0;
        return round(($this->sesiones_realizadas / $this->sesiones_totales) * 100, 2);
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->where('estado', 'pagado')->sum('monto');
    }

    public function pendingBalance(): float
    {
        return (float) $this->costo_total - $this->paidAmount();
    }

    public function scopeActive($query)
    {
        return $query->where('estado', 'activo');
    }
}
