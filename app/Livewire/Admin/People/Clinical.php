<?php

namespace App\Livewire\Admin\People;

use App\Models\ClinicalAttachment;
use App\Models\ClinicalEvent;
use App\Models\ClinicalMeasurement;
use App\Models\ClinicalProfile;
use App\Models\Person;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Clinical extends Component
{
    use WithFileUploads;

    public Person $person;

    #[Url] public string $tab = 'overview'; // overview|baseline|measurements|events|attachments

    // Baseline
    public array $baseline = [
        'blood_type' => null,
        'donor' => false,
        'chronic_diseases' => null,
        'chronic_medications' => null,
        'allergies' => null,
        'family_history' => null,
        'surgical_history' => null,
        'is_pregnant' => false,
        'pregnancy_weeks' => null,
        'smoker' => null,
        'alcohol' => null,
        'exercise_frequency' => null,
        'notes' => null,
    ];
    public bool $baselineSaved = false;

    // Measurement form
    public bool $mOpen = false;
    public ?int $editingMeasurementId = null;
    public array $m = [];

    // Event form
    public bool $eOpen = false;
    public ?int $editingEventId = null;
    public array $e = [];

    // Attachment form
    public bool $aOpen = false;
    public string $attTitle = '';
    public string $attCategory = 'examen';
    public ?string $attDate = null;
    public ?string $attNotes = null;
    public $attFile = null;

    public function mount(Person $person): void
    {
        $this->person = $person->load(['clinicalProfile']);
        $this->loadBaseline();
        $this->resetMeasurementForm();
        $this->resetEventForm();
    }

    protected function loadBaseline(): void
    {
        $cp = $this->person->clinicalProfile;
        if ($cp) {
            foreach (array_keys($this->baseline) as $k) {
                $this->baseline[$k] = $cp->{$k};
            }
        }
    }

    public function saveBaseline(): void
    {
        $this->validate([
            'baseline.blood_type' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-,desconocido'],
            'baseline.pregnancy_weeks' => ['nullable', 'integer', 'min:1', 'max:42'],
            'baseline.smoker' => ['nullable', 'in:no,ex,ocasional,habitual'],
            'baseline.alcohol' => ['nullable', 'in:no,ocasional,frecuente'],
            'baseline.exercise_frequency' => ['nullable', 'in:sedentario,ocasional,regular,intenso'],
        ]);

        ClinicalProfile::updateOrCreate(
            ['person_id' => $this->person->id],
            array_merge($this->baseline, ['updated_by' => auth()->id()])
        );

        $this->person->load('clinicalProfile');
        session()->flash('success', 'Antecedentes guardados.');
        $this->baselineSaved = true;
    }

    // ============ MEDICIONES ============
    protected function resetMeasurementForm(): void
    {
        $lastHeight = $this->person->measurements()->whereNotNull('height_cm')->latest('measured_at')->value('height_cm');
        $this->m = [
            'measured_at' => now()->format('Y-m-d\TH:i'),
            'source' => 'manual',
            'weight_kg' => null, 'height_cm' => $lastHeight,
            'body_fat_kg' => null, 'body_fat_percent' => null,
            'skeletal_muscle_kg' => null, 'soft_lean_mass_kg' => null, 'fat_free_mass_kg' => null,
            'protein_kg' => null, 'mineral_kg' => null,
            'total_body_water_l' => null, 'intracellular_water_l' => null, 'extracellular_water_l' => null, 'ecw_tbw_ratio' => null,
            'visceral_fat_area' => null, 'visceral_fat_level' => null, 'bmr_kcal' => null,
            'phase_angle' => null, 'inbody_score' => null,
            'waist_cm' => null, 'hip_cm' => null, 'chest_cm' => null,
            'arm_right_cm' => null, 'arm_left_cm' => null,
            'thigh_right_cm' => null, 'thigh_left_cm' => null,
            'blood_pressure_systolic' => null, 'blood_pressure_diastolic' => null,
            'heart_rate' => null, 'glucose_mg_dl' => null,
            'notes' => null,
        ];
        $this->editingMeasurementId = null;
    }

    public function openMeasurement(?int $id = null): void
    {
        if ($id) {
            $rec = ClinicalMeasurement::where('person_id', $this->person->id)->findOrFail($id);
            $this->editingMeasurementId = $id;
            $this->m = [
                'measured_at' => $rec->measured_at?->format('Y-m-d\TH:i'),
                'source' => $rec->source,
                'weight_kg' => $rec->weight_kg, 'height_cm' => $rec->height_cm,
                'body_fat_kg' => $rec->body_fat_kg, 'body_fat_percent' => $rec->body_fat_percent,
                'skeletal_muscle_kg' => $rec->skeletal_muscle_kg, 'soft_lean_mass_kg' => $rec->soft_lean_mass_kg,
                'fat_free_mass_kg' => $rec->fat_free_mass_kg,
                'protein_kg' => $rec->protein_kg, 'mineral_kg' => $rec->mineral_kg,
                'total_body_water_l' => $rec->total_body_water_l,
                'intracellular_water_l' => $rec->intracellular_water_l,
                'extracellular_water_l' => $rec->extracellular_water_l,
                'ecw_tbw_ratio' => $rec->ecw_tbw_ratio,
                'visceral_fat_area' => $rec->visceral_fat_area, 'visceral_fat_level' => $rec->visceral_fat_level,
                'bmr_kcal' => $rec->bmr_kcal, 'phase_angle' => $rec->phase_angle, 'inbody_score' => $rec->inbody_score,
                'waist_cm' => $rec->waist_cm, 'hip_cm' => $rec->hip_cm, 'chest_cm' => $rec->chest_cm,
                'arm_right_cm' => $rec->arm_right_cm, 'arm_left_cm' => $rec->arm_left_cm,
                'thigh_right_cm' => $rec->thigh_right_cm, 'thigh_left_cm' => $rec->thigh_left_cm,
                'blood_pressure_systolic' => $rec->blood_pressure_systolic,
                'blood_pressure_diastolic' => $rec->blood_pressure_diastolic,
                'heart_rate' => $rec->heart_rate, 'glucose_mg_dl' => $rec->glucose_mg_dl,
                'notes' => $rec->notes,
            ];
        } else {
            $this->resetMeasurementForm();
        }
        $this->resetErrorBag();
        $this->mOpen = true;
    }

    public function saveMeasurement(): void
    {
        $this->validate([
            'm.measured_at' => ['required', 'date'],
            'm.source' => ['required', 'in:manual,inbody,session_auto,admission'],
            'm.weight_kg' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'm.height_cm' => ['nullable', 'integer', 'min:100', 'max:250'],
            'm.body_fat_percent' => ['nullable', 'numeric', 'min:0', 'max:80'],
            'm.blood_pressure_systolic' => ['nullable', 'integer', 'min:50', 'max:300'],
            'm.blood_pressure_diastolic' => ['nullable', 'integer', 'min:30', 'max:200'],
            'm.heart_rate' => ['nullable', 'integer', 'min:20', 'max:250'],
            'm.arm_right_cm'   => ['nullable', 'numeric', 'min:5', 'max:80'],
            'm.arm_left_cm'    => ['nullable', 'numeric', 'min:5', 'max:80'],
            'm.thigh_right_cm' => ['nullable', 'numeric', 'min:10', 'max:120'],
            'm.thigh_left_cm'  => ['nullable', 'numeric', 'min:10', 'max:120'],
        ]);

        $data = $this->m;
        $data['bmi'] = ClinicalMeasurement::computeBmi(
            $data['weight_kg'] ? (float) $data['weight_kg'] : null,
            $data['height_cm'] ? (int) $data['height_cm'] : null
        );
        $data['whr'] = ClinicalMeasurement::computeWhr(
            $data['waist_cm'] ? (float) $data['waist_cm'] : null,
            $data['hip_cm'] ? (float) $data['hip_cm'] : null
        );
        $data['person_id'] = $this->person->id;
        $data['recorded_by'] = auth()->id();

        if ($this->editingMeasurementId) {
            ClinicalMeasurement::findOrFail($this->editingMeasurementId)->update($data);
            session()->flash('success', 'Medición actualizada.');
        } else {
            ClinicalMeasurement::create($data);
            session()->flash('success', 'Medición registrada.');
        }
        $this->mOpen = false;
    }

    public function deleteMeasurement(int $id): void
    {
        ClinicalMeasurement::where('person_id', $this->person->id)->where('id', $id)->delete();
        session()->flash('success', 'Medición eliminada.');
    }

    // ============ EVENTOS ============
    protected function resetEventForm(): void
    {
        $this->e = [
            'type' => 'lesion',
            'event_date' => now()->format('Y-m-d'),
            'description' => '',
            'severity' => null,
            'status' => 'activo',
            'body_region' => null,
            'notes' => null,
        ];
        $this->editingEventId = null;
    }

    public function openEvent(?int $id = null): void
    {
        if ($id) {
            $rec = ClinicalEvent::where('person_id', $this->person->id)->findOrFail($id);
            $this->editingEventId = $id;
            $this->e = [
                'type' => $rec->type,
                'event_date' => $rec->event_date?->format('Y-m-d'),
                'description' => $rec->description,
                'severity' => $rec->severity,
                'status' => $rec->status,
                'body_region' => $rec->body_region,
                'notes' => $rec->notes,
            ];
        } else {
            $this->resetEventForm();
        }
        $this->resetErrorBag();
        $this->eOpen = true;
    }

    public function saveEvent(): void
    {
        $this->validate([
            'e.type' => ['required', 'in:lesion,cirugia,hospitalizacion,alergia_grave,vacuna,enfermedad,otro'],
            'e.event_date' => ['required', 'date'],
            'e.description' => ['required', 'string', 'max:255'],
            'e.severity' => ['nullable', 'in:leve,moderada,grave'],
            'e.status' => ['required', 'in:activo,en_tratamiento,resuelto'],
            'e.body_region' => ['nullable', 'string', 'max:150'],
        ]);

        $data = $this->e;
        $data['person_id'] = $this->person->id;
        $data['recorded_by'] = auth()->id();

        if ($this->editingEventId) {
            ClinicalEvent::findOrFail($this->editingEventId)->update($data);
            session()->flash('success', 'Evento actualizado.');
        } else {
            ClinicalEvent::create($data);
            session()->flash('success', 'Evento registrado.');
        }
        $this->eOpen = false;
    }

    public function deleteEvent(int $id): void
    {
        ClinicalEvent::where('person_id', $this->person->id)->where('id', $id)->delete();
        session()->flash('success', 'Evento eliminado.');
    }

    // ============ ADJUNTOS ============
    public function openAttachment(): void
    {
        $this->attTitle = '';
        $this->attCategory = 'examen';
        $this->attDate = now()->format('Y-m-d');
        $this->attNotes = null;
        $this->attFile = null;
        $this->resetErrorBag();
        $this->aOpen = true;
    }

    public function saveAttachment(): void
    {
        $this->validate([
            'attTitle' => ['required', 'string', 'max:200'],
            'attCategory' => ['required', 'in:examen,imagen,informe,receta,inbody,otro'],
            'attDate' => ['nullable', 'date'],
            'attFile' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
            'attNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $path = $this->attFile->store('clinical/'.$this->person->id, 'public');

        ClinicalAttachment::create([
            'person_id'   => $this->person->id,
            'category'    => $this->attCategory,
            'title'       => $this->attTitle,
            'path'        => $path,
            'mime_type'   => $this->attFile->getMimeType(),
            'size_kb'     => round($this->attFile->getSize() / 1024),
            'document_date' => $this->attDate,
            'notes'       => $this->attNotes,
            'uploaded_by' => auth()->id(),
        ]);

        session()->flash('success', 'Adjunto subido.');
        $this->aOpen = false;
    }

    public function deleteAttachment(int $id): void
    {
        $a = ClinicalAttachment::where('person_id', $this->person->id)->findOrFail($id);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($a->path);
        $a->delete();
        session()->flash('success', 'Adjunto eliminado.');
    }

    public function render()
    {
        $person = $this->person;

        $measurements = $person->measurements()->limit(50)->get();
        $events       = $person->clinicalEvents()->get();
        $attachments  = $person->attachments()->get();
        $latest       = $measurements->first();

        // Series para gráficas (incluye circunferencias)
        $series = $person->measurements()
            ->orderBy('measured_at')
            ->get([
                'measured_at',
                'weight_kg', 'bmi', 'body_fat_percent', 'skeletal_muscle_kg',
                'waist_cm', 'hip_cm', 'chest_cm',
                'arm_right_cm', 'arm_left_cm',
                'thigh_right_cm', 'thigh_left_cm',
            ]);

        $stats = [
            'meas_count'  => $person->measurements()->count(),
            'events_active' => $person->clinicalEvents()->whereIn('status', ['activo', 'en_tratamiento'])->count(),
            'attachments' => $attachments->count(),
        ];

        return view('livewire.admin.people.clinical', [
            'measurements' => $measurements,
            'events'       => $events,
            'attachments'  => $attachments,
            'latest'       => $latest,
            'series'       => $series,
            'stats'        => $stats,
        ]);
    }
}
