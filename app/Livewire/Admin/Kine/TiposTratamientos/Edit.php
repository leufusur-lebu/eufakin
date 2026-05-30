<?php

namespace App\Livewire\Admin\Kine\TiposTratamientos;

use App\Models\Kine\TipoTratamiento;
use Livewire\Component;

class Edit extends Component
{
    public ?int $tipoId = null;

    public $nombre;
    public $descripcion;
    public $duracion_minutos;
    public $precio_base;
    public $categoria;
    public $materiales_requeridos;
    public $contraindicaciones;
    public $sesiones_recomendadas = 1;
    public $intervalo_dias = 3;
    public $protocolo;
    public $color = '#0ea5e9';
    public $activo = true;

    public function mount(): void
    {
        $param = request()->route('tipoTratamiento');
        $tipo = $param instanceof TipoTratamiento
            ? $param
            : TipoTratamiento::findOrFail($param);

        $this->tipoId = $tipo->id;
        $this->nombre = $tipo->nombre;
        $this->descripcion = $tipo->descripcion;
        $this->duracion_minutos = $tipo->duracion_minutos;
        $this->precio_base = $tipo->precio_base;
        $this->categoria = $tipo->categoria;
        $this->materiales_requeridos = $tipo->materiales_requeridos;
        $this->contraindicaciones = $tipo->contraindicaciones;
        $this->sesiones_recomendadas = $tipo->sesiones_recomendadas ?: 1;
        $this->intervalo_dias = $tipo->intervalo_dias ?: 3;
        $this->protocolo = $tipo->protocolo;
        $this->color = $tipo->color ?: '#0ea5e9';
        $this->activo = $tipo->activo;
    }

    public function save()
    {
        $validated = $this->validate([
            'nombre'                 => 'required|string|max:100|unique:kine_tipos_tratamientos,nombre,' . $this->tipoId,
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

        TipoTratamiento::findOrFail($this->tipoId)->update($validated);

        session()->flash('success', 'Protocolo actualizado.');
        return redirect()->route('admin.kine.tipos-tratamientos.index');
    }

    public function render()
    {
        return view('livewire.admin.kine.tipos-tratamientos.edit', [
            'tipo' => TipoTratamiento::findOrFail($this->tipoId),
            'categorias' => \App\Models\TreatmentCategory::where('module', 'kine')
                ->where('activo', true)
                ->orderBy('sort_order')->get(),
        ]);
    }
}
