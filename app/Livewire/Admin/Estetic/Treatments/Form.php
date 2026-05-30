<?php

namespace App\Livewire\Admin\Estetic\Treatments;

use App\Models\Estetic\TipoTratamiento;
use App\Models\Estetic\Treatment;
use App\Models\EsteticProfile;
use App\Models\Professional;
use Livewire\Component;

class Form extends Component
{
    public ?int $treatmentId = null;

    public ?int $estetic_profile_id = null;
    public ?int $tipo_tratamiento_id = null;
    public ?int $professional_id = null;
    public ?string $descripcion_plan = null;
    public string $zona_tratada = '';
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;
    public int $sesiones_totales = 6;
    public int $sesiones_realizadas = 0;
    public float $costo_sesion = 0;
    public float $costo_total = 0;
    public string $estado = 'activo';
    public ?string $observaciones = null;

    public function mount(): void
    {
        $treatment = request()->route('treatment');
        if (is_string($treatment) || is_numeric($treatment)) {
            $treatment = Treatment::find($treatment);
        }
        if ($treatment instanceof Treatment && $treatment->exists) {
            $this->treatmentId = $treatment->id;
            $this->fill($treatment->only([
                'estetic_profile_id', 'tipo_tratamiento_id', 'professional_id',
                'descripcion_plan', 'zona_tratada', 'sesiones_totales', 'sesiones_realizadas',
                'costo_sesion', 'costo_total', 'estado', 'observaciones',
            ]));
            $this->fecha_inicio = $treatment->fecha_inicio?->format('Y-m-d');
            $this->fecha_fin = $treatment->fecha_fin?->format('Y-m-d');
        } else {
            $this->fecha_inicio = now()->format('Y-m-d');
        }
    }

    public function updatedTipoTratamientoId($id): void
    {
        if ($tipo = TipoTratamiento::find($id)) {
            $this->costo_sesion = $tipo->precio_base;
            $this->costo_total = $this->sesiones_totales * $this->costo_sesion;
        }
    }

    public function updated($name): void
    {
        if (in_array($name, ['sesiones_totales', 'costo_sesion'])) {
            $this->costo_total = $this->sesiones_totales * $this->costo_sesion;
        }
    }

    public function save()
    {
        $data = $this->validate([
            'estetic_profile_id' => ['required', 'exists:estetic_profiles,id'],
            'tipo_tratamiento_id' => ['nullable', 'exists:este_tipos_tratamientos,id'],
            'professional_id' => ['nullable', 'exists:professionals,id'],
            'descripcion_plan' => ['nullable', 'string', 'max:255'],
            'zona_tratada' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'sesiones_totales' => ['required', 'integer', 'min:1'],
            'sesiones_realizadas' => ['required', 'integer', 'min:0'],
            'costo_sesion' => ['required', 'numeric', 'min:0'],
            'costo_total' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'in:activo,finalizado,suspendido,cancelado'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $existing = $this->treatmentId ? Treatment::find($this->treatmentId) : null;
        if ($existing) {
            $existing->update($data);
        } else {
            $created = Treatment::create($data);
            $this->treatmentId = $created->id;
        }

        session()->flash('success', 'Tratamiento guardado.');
        return $this->redirectRoute('admin.estetic.treatments.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.estetic.treatments.form', [
            'treatment' => $this->treatmentId ? Treatment::find($this->treatmentId) : null,
            'profiles' => EsteticProfile::with('person')->get(),
            'tipos' => TipoTratamiento::where('activo', true)->orderBy('nombre')->get(),
            'professionals' => Professional::estetic()->where('active', true)->get(),
        ]);
    }
}
