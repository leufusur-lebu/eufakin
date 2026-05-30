<?php

namespace App\Livewire\Admin\Estetic\Sessions;

use App\Models\ClinicalMeasurement;
use App\Models\Estetic\Appointment;
use App\Models\Estetic\Sesion;
use App\Models\Estetic\SessionPhoto;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Attend extends Component
{
    use WithFileUploads;

    public Appointment $appointment;

    // Datos de la sesión
    public ?string $zona_especifica = null;
    public string $intensidad = 'media'; // baja|media|alta
    public ?int $duracion_real_minutos = null;
    public ?string $productos_utilizados = null;
    public ?string $resultados_observados = null;
    public ?string $notas_clinicas = null;

    // Fotos a subir
    public array $photos_antes = [];
    public array $photos_durante = [];
    public array $photos_despues = [];

    // Medición rápida (alimenta ficha clínica)
    public bool $record_measurement = false;
    public ?float $m_weight_kg = null;
    public ?float $m_waist_cm = null;
    public ?float $m_hip_cm = null;
    public ?int $m_bp_sys = null;
    public ?int $m_bp_dia = null;

    public function mount(Appointment $appointment): void
    {
        $this->appointment = $appointment->load(['esteticProfile.person.clinicalProfile', 'treatment.tipoTratamiento', 'professional']);
        $this->zona_especifica = $this->appointment->treatment?->zona_tratada;
        $duracion = $this->appointment->inicio && $this->appointment->fin
            ? $this->appointment->inicio->diffInMinutes($this->appointment->fin)
            : 60;
        $this->duracion_real_minutos = (int) $duracion;
    }

    public function save()
    {
        $this->validate([
            'intensidad' => ['required', 'in:baja,media,alta'],
            'duracion_real_minutos' => ['nullable', 'integer', 'min:1'],
            'photos_antes.*'   => ['nullable', 'image', 'max:5120'],
            'photos_durante.*' => ['nullable', 'image', 'max:5120'],
            'photos_despues.*' => ['nullable', 'image', 'max:5120'],
            'm_weight_kg' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'm_waist_cm'  => ['nullable', 'numeric', 'min:30', 'max:250'],
            'm_hip_cm'    => ['nullable', 'numeric', 'min:30', 'max:250'],
            'm_bp_sys'    => ['nullable', 'integer', 'min:50', 'max:300'],
            'm_bp_dia'    => ['nullable', 'integer', 'min:30', 'max:200'],
        ]);

        DB::transaction(function () {
            $treatment = $this->appointment->treatment;

            // 1. Marcar appointment como atendido
            $this->appointment->update(['estado' => 'atendido']);

            // 2. Determinar número de sesión
            $numero = 1;
            if ($treatment) {
                $numero = ($treatment->sesiones_realizadas ?? 0) + 1;
            }

            // 3. Crear registro de sesión
            $sesion = Sesion::create([
                'tratamiento_id'        => $treatment?->id,
                'turno_id'              => $this->appointment->id,
                'numero_sesion'         => $numero,
                'fecha'                 => $this->appointment->inicio?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'productos_utilizados'  => $this->productos_utilizados,
                'resultados_observados' => $this->resultados_observados,
                'notas_clinicas'        => $this->notas_clinicas,
                'intensidad'            => $this->intensidad,
                'zona_especifica'       => $this->zona_especifica,
                'duracion_real_minutos' => $this->duracion_real_minutos,
                'estado'                => 'realizada',
            ]);

            // 4. Incrementar sesiones_realizadas y eventualmente cerrar
            if ($treatment) {
                $treatment->increment('sesiones_realizadas');
                if ($treatment->sesiones_realizadas >= $treatment->sesiones_totales) {
                    $treatment->update(['estado' => 'finalizado', 'fecha_fin' => now()->format('Y-m-d')]);
                }
            }

            // 5. Subir fotos
            $this->saveSet($sesion, $this->photos_antes,   'antes');
            $this->saveSet($sesion, $this->photos_durante, 'durante');
            $this->saveSet($sesion, $this->photos_despues, 'despues');

            // 6. Medición rápida → alimenta ficha clínica
            if ($this->record_measurement && ($this->m_weight_kg || $this->m_waist_cm || $this->m_hip_cm || $this->m_bp_sys)) {
                $personId = $this->appointment->esteticProfile?->person_id;
                if ($personId) {
                    $lastH = ClinicalMeasurement::where('person_id', $personId)
                        ->whereNotNull('height_cm')->latest('measured_at')->value('height_cm');
                    ClinicalMeasurement::create([
                        'person_id'              => $personId,
                        'measured_at'            => now(),
                        'weight_kg'              => $this->m_weight_kg,
                        'height_cm'              => $lastH,
                        'bmi'                    => ClinicalMeasurement::computeBmi($this->m_weight_kg ? (float) $this->m_weight_kg : null, $lastH ? (int) $lastH : null),
                        'waist_cm'               => $this->m_waist_cm,
                        'hip_cm'                 => $this->m_hip_cm,
                        'whr'                    => ClinicalMeasurement::computeWhr($this->m_waist_cm ? (float) $this->m_waist_cm : null, $this->m_hip_cm ? (float) $this->m_hip_cm : null),
                        'blood_pressure_systolic'=> $this->m_bp_sys,
                        'blood_pressure_diastolic'=> $this->m_bp_dia,
                        'source'                 => 'session_auto',
                        'notes'                  => 'Capturada en sesión estética #'.$sesion->numero_sesion,
                        'recorded_by'            => auth()->id(),
                    ]);
                }
            }
        });

        session()->flash('success', 'Sesión registrada correctamente.');
        return $this->redirectRoute('admin.estetic.patients.show', [
            'profile' => $this->appointment->estetic_profile_id,
            'tab' => 'sessions',
        ], navigate: true);
    }

    protected function saveSet(Sesion $sesion, array $files, string $tipo): void
    {
        foreach ($files as $file) {
            if (!$file) continue;
            $path = $file->store('estetic_photos/'.$sesion->tratamiento_id, 'public');
            SessionPhoto::create([
                'estetic_profile_id' => $this->appointment->estetic_profile_id,
                'sesion_id'          => $sesion->id,
                'tratamiento_id'     => $sesion->tratamiento_id,
                'tipo'               => $tipo,
                'path'               => $path,
                'tomada_at'          => now(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.estetic.sessions.attend');
    }
}
