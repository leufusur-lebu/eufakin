<?php

namespace App\Livewire\Admin\Kine\Sessions;

use App\Models\ClinicalMeasurement;
use App\Models\Kine\Appointment;
use App\Models\Kine\Sesion;
use App\Models\Kine\SessionPhoto;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Attend extends Component
{
    use WithFileUploads;

    public Appointment $appointment;

    public ?int $escala_dolor = 5;
    public ?string $rom = null;
    public ?string $fuerza_muscular = null;
    public ?int $duracion_real_minutos = null;
    public ?string $evolucion = null;
    public ?string $ejercicios = null;
    public ?string $notas_clinicas = null;

    public array $photos_inicial = [];
    public array $photos_evolucion = [];
    public array $photos_final = [];

    // Medición rápida (alimenta ficha clínica)
    public bool $record_measurement = false;
    public ?float $m_weight_kg = null;
    public ?int $m_bp_sys = null;
    public ?int $m_bp_dia = null;
    public ?int $m_heart_rate = null;

    public function mount(Appointment $appointment): void
    {
        $this->appointment = $appointment->load(['kineProfile.person.clinicalProfile', 'treatment.tipoTratamiento', 'professional']);
        $duracion = $this->appointment->inicio && $this->appointment->fin
            ? $this->appointment->inicio->diffInMinutes($this->appointment->fin)
            : 45;
        $this->duracion_real_minutos = (int) $duracion;
    }

    public function save()
    {
        $this->validate([
            'escala_dolor' => ['nullable', 'integer', 'min:0', 'max:10'],
            'duracion_real_minutos' => ['nullable', 'integer', 'min:1'],
            'photos_inicial.*'   => ['nullable', 'image', 'max:5120'],
            'photos_evolucion.*' => ['nullable', 'image', 'max:5120'],
            'photos_final.*'     => ['nullable', 'image', 'max:5120'],
            'm_weight_kg' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'm_bp_sys'    => ['nullable', 'integer', 'min:50', 'max:300'],
            'm_bp_dia'    => ['nullable', 'integer', 'min:30', 'max:200'],
            'm_heart_rate'=> ['nullable', 'integer', 'min:20', 'max:250'],
        ]);

        DB::transaction(function () {
            $treatment = $this->appointment->treatment;

            $this->appointment->update(['estado' => 'atendido']);

            $numero = $treatment ? ($treatment->sesiones_realizadas ?? 0) + 1 : 1;

            $sesion = Sesion::create([
                'tratamiento_id'        => $treatment?->id,
                'turno_id'              => $this->appointment->id,
                'numero_sesion'         => $numero,
                'fecha'                 => $this->appointment->inicio?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'evolucion'             => $this->evolucion,
                'ejercicios'            => $this->ejercicios,
                'escala_dolor'          => $this->escala_dolor,
                'notas_clinicas'        => $this->notas_clinicas,
                'rom'                   => $this->rom,
                'fuerza_muscular'       => $this->fuerza_muscular,
                'duracion_real_minutos' => $this->duracion_real_minutos,
                'estado'                => 'realizada',
            ]);

            if ($treatment) {
                $treatment->increment('sesiones_realizadas');
                if ($treatment->sesiones_realizadas >= $treatment->sesiones_totales) {
                    $treatment->update(['estado' => 'finalizado', 'fecha_fin' => now()->format('Y-m-d')]);
                }
            }

            $this->saveSet($sesion, $this->photos_inicial,   'inicial');
            $this->saveSet($sesion, $this->photos_evolucion, 'evolucion');
            $this->saveSet($sesion, $this->photos_final,     'final');

            // Medición rápida → alimenta ficha clínica
            if ($this->record_measurement && ($this->m_weight_kg || $this->m_bp_sys || $this->m_heart_rate)) {
                $personId = $this->appointment->kineProfile?->person_id;
                if ($personId) {
                    $lastH = ClinicalMeasurement::where('person_id', $personId)
                        ->whereNotNull('height_cm')->latest('measured_at')->value('height_cm');
                    ClinicalMeasurement::create([
                        'person_id'              => $personId,
                        'measured_at'            => now(),
                        'weight_kg'              => $this->m_weight_kg,
                        'height_cm'              => $lastH,
                        'bmi'                    => ClinicalMeasurement::computeBmi($this->m_weight_kg ? (float) $this->m_weight_kg : null, $lastH ? (int) $lastH : null),
                        'blood_pressure_systolic'=> $this->m_bp_sys,
                        'blood_pressure_diastolic'=> $this->m_bp_dia,
                        'heart_rate'             => $this->m_heart_rate,
                        'source'                 => 'session_auto',
                        'notes'                  => 'Capturada en sesión kine #'.$sesion->numero_sesion,
                        'recorded_by'            => auth()->id(),
                    ]);
                }
            }
        });

        session()->flash('success', 'Sesión registrada correctamente.');
        return $this->redirectRoute('admin.kine.patients.show', [
            'profile' => $this->appointment->kine_profile_id,
            'tab' => 'sessions',
        ], navigate: true);
    }

    protected function saveSet(Sesion $sesion, array $files, string $tipo): void
    {
        foreach ($files as $file) {
            if (!$file) continue;
            $path = $file->store('kine_photos/'.$sesion->tratamiento_id, 'public');
            SessionPhoto::create([
                'kine_profile_id' => $this->appointment->kine_profile_id,
                'sesion_id'       => $sesion->id,
                'tratamiento_id'  => $sesion->tratamiento_id,
                'tipo'            => $tipo,
                'path'            => $path,
                'tomada_at'       => now(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.kine.sessions.attend');
    }
}
