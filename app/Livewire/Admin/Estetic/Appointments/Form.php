<?php

namespace App\Livewire\Admin\Estetic\Appointments;

use App\Models\Estetic\Appointment;
use App\Models\Estetic\Treatment;
use App\Models\EsteticProfile;
use App\Models\Professional;
use Carbon\Carbon;
use Livewire\Component;

class Form extends Component
{
    public ?int $appointmentId = null;

    public ?int $estetic_profile_id = null;
    public ?int $tratamiento_id = null;
    public ?int $professional_id = null;
    public ?string $fecha = null;
    public string $hora_inicio = '10:00';
    public int $duracion_min = 60;
    public string $estado = 'pendiente';
    public ?string $motivo = null;
    public ?string $notas = null;

    public function mount(): void
    {
        $appointment = request()->route('appointment');
        if (is_string($appointment) || is_numeric($appointment)) {
            $appointment = Appointment::find($appointment);
        }
        if ($appointment instanceof Appointment && $appointment->exists) {
            $this->appointmentId = $appointment->id;
            $this->estetic_profile_id = $appointment->estetic_profile_id;
            $this->tratamiento_id = $appointment->tratamiento_id;
            $this->professional_id = $appointment->professional_id;
            $this->fecha = $appointment->inicio->format('Y-m-d');
            $this->hora_inicio = $appointment->inicio->format('H:i');
            $this->duracion_min = $appointment->inicio->diffInMinutes($appointment->fin);
            $this->estado = $appointment->estado;
            $this->motivo = $appointment->motivo;
            $this->notas = $appointment->notas;
        } else {
            $this->fecha = now()->format('Y-m-d');
        }
    }

    public function save()
    {
        $data = $this->validate([
            'estetic_profile_id' => ['required', 'exists:estetic_profiles,id'],
            'tratamiento_id' => ['nullable', 'exists:este_tratamientos,id'],
            'professional_id' => ['nullable', 'exists:professionals,id'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required'],
            'duracion_min' => ['required', 'integer', 'min:5'],
            'estado' => ['required', 'in:pendiente,confirmado,atendido,cancelado,ausente'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
        ]);

        $inicio = Carbon::parse("{$data['fecha']} {$data['hora_inicio']}");
        $fin = (clone $inicio)->addMinutes($data['duracion_min']);

        $payload = [
            'estetic_profile_id' => $data['estetic_profile_id'],
            'tratamiento_id' => $data['tratamiento_id'],
            'professional_id' => $data['professional_id'],
            'inicio' => $inicio,
            'fin' => $fin,
            'estado' => $data['estado'],
            'motivo' => $data['motivo'],
            'notas' => $data['notas'],
        ];

        $existing = $this->appointmentId ? Appointment::find($this->appointmentId) : null;
        if ($existing) {
            $existing->update($payload);
        } else {
            $created = Appointment::create($payload);
            $this->appointmentId = $created->id;
        }

        session()->flash('success', 'Cita guardada.');
        return $this->redirectRoute('admin.estetic.appointments.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.estetic.appointments.form', [
            'appointment' => $this->appointmentId ? Appointment::find($this->appointmentId) : null,
            'profiles' => EsteticProfile::with('person')->get(),
            'treatments' => $this->estetic_profile_id
                ? Treatment::where('estetic_profile_id', $this->estetic_profile_id)->get()
                : collect(),
            'professionals' => Professional::estetic()->where('active', true)->get(),
        ]);
    }
}
