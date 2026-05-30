<?php

namespace App\Models\Kine;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoTratamiento extends Model
{
    protected $table = 'kine_tipos_tratamientos';

    protected $fillable = [
        'nombre', 'descripcion', 'duracion_minutos', 'precio_base',
        'sesiones_recomendadas', 'intervalo_dias', 'protocolo', 'color',
        'categoria', 'materiales_requeridos', 'contraindicaciones', 'activo',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
        'sesiones_recomendadas' => 'integer',
        'intervalo_dias' => 'integer',
    ];

    public function tratamientos(): HasMany
    {
        return $this->hasMany(Treatment::class, 'tipo_tratamiento_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function getNombreConPrecioAttribute(): string
    {
        return "{$this->nombre} - \${$this->precio_base}";
    }
}
