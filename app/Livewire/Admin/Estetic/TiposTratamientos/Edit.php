<?php

namespace App\Livewire\Admin\Estetic\TiposTratamientos;

use App\Models\Estetic\TipoTratamiento;
use Livewire\Component;

class Edit extends Component
{
    public ?TipoTratamiento $tipo = null;

    public $nombre;
    public $descripcion;
    public $duracion_minutos;
    public $precio_base;
    public $categoria;
    public $materiales_requeridos;
    public $contraindicaciones;
    public $sesiones_recomendadas = 1;
    public $intervalo_dias = 7;
    public $protocolo;
    public $color = '#ec4899';
    public $activo = true;

    public function mount(TipoTratamiento $tipoTratamiento): void
    {
        $this->tipo = $tipoTratamiento;
        $this->nombre = $tipoTratamiento->nombre;
        $this->descripcion = $tipoTratamiento->descripcion;
        $this->duracion_minutos = $tipoTratamiento->duracion_minutos;
        $this->precio_base = $tipoTratamiento->precio_base;
        $this->categoria = $tipoTratamiento->categoria;
        $this->materiales_requeridos = $tipoTratamiento->materiales_requeridos;
        $this->contraindicaciones = $tipoTratamiento->contraindicaciones;
        $this->sesiones_recomendadas = $tipoTratamiento->sesiones_recomendadas ?: 1;
        $this->intervalo_dias = $tipoTratamiento->intervalo_dias ?: 7;
        $this->protocolo = $tipoTratamiento->protocolo;
        $this->color = $tipoTratamiento->color ?: '#ec4899';
        $this->activo = $tipoTratamiento->activo;
    }

    public function save()
    {
        $validated = $this->validate([
            'nombre'                 => 'required|string|max:100|unique:este_tipos_tratamientos,nombre,' . $this->tipo->id,
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

        $this->tipo->update($validated);

        session()->flash('success', 'Tipo de tratamiento actualizado exitosamente.');
        return redirect()->route('admin.estetic.tipos-tratamientos.index');
    }

    public function render()
    {
        return view('livewire.admin.estetic.tipos-tratamientos.edit', [
            'categorias' => \App\Models\TreatmentCategory::where('module', 'estetic')
                ->where('activo', true)
                ->orderBy('sort_order')->get(),
        ]);
    }
}
