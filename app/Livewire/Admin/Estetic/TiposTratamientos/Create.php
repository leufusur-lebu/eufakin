<?php

namespace App\Livewire\Admin\Estetic\TiposTratamientos;

use App\Models\Estetic\TipoTratamiento;
use Livewire\Component;

class Create extends Component
{
    public $nombre;
    public $descripcion;
    public $duracion_minutos = 60;
    public $precio_base;
    public $categoria = 'facial';
    public $materiales_requeridos;
    public $contraindicaciones;
    public $sesiones_recomendadas = 1;
    public $intervalo_dias = 7;
    public $protocolo;
    public $color = '#ec4899';
    public bool $activo = true;

    public function save()
    {
        $validated = $this->validate([
            'nombre'                 => 'required|string|max:100|unique:este_tipos_tratamientos,nombre',
            'descripcion'            => 'nullable|string',
            'duracion_minutos'       => 'required|integer|min:1',
            'precio_base'            => 'required|numeric|min:0',
            'categoria'              => 'nullable|string|max:100',
            'materiales_requeridos'  => 'nullable|string',
            'contraindicaciones'     => 'nullable|string',
            'sesiones_recomendadas'  => 'required|integer|min:1|max:50',
            'intervalo_dias'         => 'required|integer|min:1|max:365',
            'protocolo'              => 'nullable|string',
            'color'                  => 'nullable|string|max:16',
            'activo'                 => 'boolean',
        ]);

        TipoTratamiento::create($validated);

        session()->flash('success', 'Tipo de tratamiento creado exitosamente.');
        return redirect()->route('admin.estetic.tipos-tratamientos.index');
    }

    public function render()
    {
        return view('livewire.admin.estetic.tipos-tratamientos.create', [
            'categorias' => \App\Models\TreatmentCategory::where('module', 'estetic')
                ->where('activo', true)
                ->orderBy('sort_order')->get(),
        ]);
    }
}
