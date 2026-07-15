<?php

namespace App\Livewire\Admin\Estetic\Treatments;

use App\Models\Estetic\Treatment;
use App\Models\Professional;
use Livewire\Component;

class Form extends Component
{
    public Treatment $treatment;

    public ?int    $professional_id  = null;
    public string  $zona_tratada     = '';
    public ?string $fecha_inicio     = null;
    public ?string $fecha_fin        = null;
    public float   $costo_total      = 0;
    public string  $estado           = 'activo';
    public ?string $observaciones    = null;

    public function mount(Treatment $treatment): void
    {
        $this->treatment     = $treatment->load(['esteticProfile.person', 'tipoTratamiento', 'professional', 'appointments']);
        $this->professional_id = $treatment->professional_id;
        $this->zona_tratada    = $treatment->zona_tratada ?? '';
        $this->fecha_inicio    = $treatment->fecha_inicio?->format('Y-m-d');
        $this->fecha_fin       = $treatment->fecha_fin?->format('Y-m-d');
        $this->costo_total     = (float) $treatment->costo_total;
        $this->estado          = $treatment->estado;
        $this->observaciones   = $treatment->observaciones;
    }

    public function getCostoSesionProperty(): float
    {
        $sesiones = $this->treatment->sesiones_totales;
        return $sesiones > 0 ? round($this->costo_total / $sesiones, 0) : 0;
    }

    public function save(): void
    {
        $this->validate([
            'professional_id' => ['nullable', 'exists:professionals,id'],
            'zona_tratada'    => ['required', 'string', 'max:255'],
            'fecha_inicio'    => ['required', 'date'],
            'fecha_fin'       => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'costo_total'     => ['required', 'numeric', 'min:0'],
            'estado'          => ['required', 'in:activo,finalizado,suspendido,cancelado'],
            'observaciones'   => ['nullable', 'string', 'max:2000'],
        ], [], [
            'zona_tratada' => 'zona tratada',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin'    => 'fecha de fin',
            'costo_total'  => 'costo total',
        ]);

        $this->treatment->update([
            'professional_id' => $this->professional_id,
            'zona_tratada'    => $this->zona_tratada,
            'fecha_inicio'    => $this->fecha_inicio,
            'fecha_fin'       => $this->fecha_fin,
            'costo_sesion'    => $this->costoSesion,
            'costo_total'     => $this->costo_total,
            'estado'          => $this->estado,
            'observaciones'   => $this->observaciones,
        ]);

        session()->flash('success', 'Tratamiento actualizado.');
        $this->redirectRoute('admin.estetic.patients.show', $this->treatment->esteticProfile, navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.estetic.treatments.form', [
            'professionals' => Professional::estetic()->where('active', true)->orderBy('name')->get(),
        ]);
    }
}
