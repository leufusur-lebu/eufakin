<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentCategory extends Model
{
    protected $fillable = [
        'module', 'key', 'label', 'icon', 'color', 'sort_order', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeKine($query)
    {
        return $query->where('module', 'kine');
    }

    public function scopeEstetic($query)
    {
        return $query->where('module', 'estetic');
    }

    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Cuenta cuántos protocolos usan esta categoría (para validar borrado).
     */
    public function countUsages(): int
    {
        if ($this->module === 'estetic') {
            return \App\Models\Estetic\TipoTratamiento::where('categoria', $this->key)->count();
        }
        if ($this->module === 'kine') {
            return \App\Models\Kine\TipoTratamiento::where('categoria', $this->key)->count();
        }
        return 0;
    }

    /**
     * Helper: lista de categorías de un módulo como array indexado por key
     * con formato [key => [label, icon, color]].
     */
    public static function defFor(string $module): array
    {
        return static::where('module', $module)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->key => [$c->label, $c->icon, $c->color]])
            ->toArray();
    }
}
