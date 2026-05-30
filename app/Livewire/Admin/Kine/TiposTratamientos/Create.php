<?php

namespace App\Livewire\Admin\Kine\TiposTratamientos;

use App\Models\Kine\TipoTratamiento;
use Livewire\Component;

class Create extends Component
{
    public $nombre;
    public $descripcion;
    public $duracion_minutos = 45;
    public $precio_base;
    public $categoria = 'traumatologia';
    public $materiales_requeridos;
    public $contraindicaciones;
    public $sesiones_recomendadas = 10;
    public $intervalo_dias = 3;
    public $protocolo;
    public $color = '#0ea5e9';
    public $activo = true;

    public function save()
    {
        $validated = $this->validate([
            'nombre'                 => 'required|string|max:100|unique:kine_tipos_tratamientos,nombre',
            'descripcion'            => 'nullable|string',
            'duracion_minutos'       => 'required|integer|min:1',
            'precio_base'            => 'required|numeric|min:0',
            'categoria'              => 'nullable|string|max:100',
            'materiales_requeridos'  => 'nullable|string',
            'contraindicaciones'     => 'nullable|string',
            'sesiones_recomendadas'  => 'required|integer|min:1|max:60',
            'intervalo_dias'         => 'required|integer|min:1|max:365',
            'protocolo'              => 'nullable|string',
            'color'                  => 'nullable|string|max:16',
            'activo'                 => 'boolean',
        ]);

        TipoTratamiento::create($validated);

        session()->flash('success', 'Protocolo creado.');
        return redirect()->route('admin.kine.tipos-tratamientos.index');
    }

    public function render()
    {
        return view('livewire.admin.kine.tipos-tratamientos.create', [
            'categorias' => \App\Models\TreatmentCategory::where('module', 'kine')
                ->where('activo', true)
                ->orderBy('sort_order')->get(),
        ]);
    }
}
