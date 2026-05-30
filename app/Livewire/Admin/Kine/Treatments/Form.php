<?php

namespace App\Livewire\Admin\Kine\Treatments;

use App\Models\Kine\Treatment;
use App\Models\KineProfile;
use App\Models\Professional;
use Livewire\Component;

class Form extends Component
{
    public ?int $treatmentId = null;

    public ?int $kine_profile_id = null;
    public ?int $professional_id = null;
    public string $diagnostico = '';
    public ?string $plan = null;
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;
    public int $sesiones_totales = 10;
    public int $sesiones_realizadas = 0;
    public float $costo_sesion = 8000;
    public float $costo_total = 80000;
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
                'kine_profile_id', 'professional_id', 'diagnostico', 'plan',
                'sesiones_totales', 'sesiones_realizadas', 'costo_sesion', 'costo_total',
                'estado', 'observaciones',
            ]));
            $this->fecha_inicio = $treatment->fecha_inicio?->format('Y-m-d');
            $this->fecha_fin = $treatment->fecha_fin?->format('Y-m-d');
        } else {
            $this->fecha_inicio = now()->format('Y-m-d');
        }
    }

    protected function getTreatment(): ?Treatment
    {
        return $this->treatmentId ? Treatment::find($this->treatmentId) : null;
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
            'kine_profile_id' => ['required', 'exists:kine_profiles,id'],
            'professional_id' => ['nullable', 'exists:professionals,id'],
            'diagnostico' => ['required', 'string', 'max:255'],
            'plan' => ['nullable', 'string'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'sesiones_totales' => ['required', 'integer', 'min:1'],
            'sesiones_realizadas' => ['required', 'integer', 'min:0'],
            'costo_sesion' => ['required', 'numeric', 'min:0'],
            'costo_total' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'in:activo,finalizado,suspendido,cancelado'],
            'observaciones' => ['nullable', 'string'],
        ]);

        if ($existing = $this->getTreatment()) {
            $existing->update($data);
        } else {
            $created = Treatment::create($data);
            $this->treatmentId = $created->id;
        }

        session()->flash('success', 'Tratamiento guardado.');
        return $this->redirectRoute('admin.kine.treatments.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.kine.treatments.form', [
            'treatment' => $this->getTreatment(),
            'profiles' => KineProfile::with('person')->get(),
            'professionals' => Professional::kine()->where('active', true)->get(),
        ]);
    }
}
