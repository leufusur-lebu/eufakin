<?php

namespace App\Livewire\Admin\Estetic\Appointments;

use App\Models\Estetic\Appointment;
use App\Models\Estetic\Payment;
use App\Models\Estetic\Treatment;
use App\Models\EsteticProfile;
use App\Models\Person;
use App\Models\Professional;
use App\Support\RutHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Form extends Component
{
    // Modo paciente: search | selected | creating
    public string  $patientMode  = 'search';
    public string  $personSearch = '';
    public ?int    $person_id    = null;

    // Creación rápida
    public string  $new_first_name = '';
    public string  $new_last_name  = '';
    public string  $new_phone      = '';
    public string  $new_rut        = '';

    // Cita
    public ?int    $appointmentId      = null;
    public ?int    $estetic_profile_id = null;
    public ?int    $tratamiento_id     = null;
    public ?int    $professional_id    = null;
    public ?string $fecha              = null;
    public string  $hora_inicio        = '10:00';
    public int     $duracion_min       = 60;
    public string  $estado             = 'pendiente';
    public ?string $motivo             = null;
    public ?string $notas              = null;

    // Pago
    public bool    $register_payment  = false;
    public float   $payment_amount    = 0;
    public string  $payment_method    = 'efectivo';
    public ?string $payment_date      = null;
    public ?string $payment_notes     = null;

    public function mount(): void
    {
        $this->fecha        = now()->format('Y-m-d');
        $this->payment_date = now()->format('Y-m-d');

        $appointment = request()->route('appointment');
        if (is_string($appointment) || is_numeric($appointment)) {
            $appointment = Appointment::find($appointment);
        }

        if ($appointment instanceof Appointment && $appointment->exists) {
            $this->appointmentId      = $appointment->id;
            $this->estetic_profile_id = $appointment->estetic_profile_id;
            $this->tratamiento_id     = $appointment->tratamiento_id;
            $this->professional_id    = $appointment->professional_id;
            $this->fecha              = $appointment->inicio->format('Y-m-d');
            $this->hora_inicio        = $appointment->inicio->format('H:i');
            $this->duracion_min       = $appointment->inicio->diffInMinutes($appointment->fin);
            $this->estado             = $appointment->estado;
            $this->motivo             = $appointment->motivo;
            $this->notas              = $appointment->notas;

            // Cargar datos del paciente
            $profile = EsteticProfile::with('person')->find($this->estetic_profile_id);
            if ($profile?->person) {
                $this->person_id    = $profile->person->id;
                $this->patientMode  = 'selected';
            }
        }
    }

    #[Computed]
    public function searchResults()
    {
        if (strlen(trim($this->personSearch)) < 2) {
            return Person::orderByDesc('updated_at')->limit(8)->get();
        }
        $term = trim($this->personSearch);
        return Person::where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name',  'like', "%{$term}%")
              ->orWhere('rut',        'like', "%{$term}%")
              ->orWhere('phone',      'like', "%{$term}%");
        })->limit(12)->get();
    }

    #[Computed]
    public function selectedPerson(): ?Person
    {
        return $this->person_id ? Person::find($this->person_id) : null;
    }

    #[Computed]
    public function treatments()
    {
        if (!$this->estetic_profile_id) return collect();
        return Treatment::where('estetic_profile_id', $this->estetic_profile_id)
            ->where('estado', 'activo')
            ->get();
    }

    public function selectPerson(int $id): void
    {
        $this->person_id     = $id;
        $this->patientMode   = 'selected';
        $this->personSearch  = '';
        $this->tratamiento_id = null;

        // Resolver o crear EsteticProfile
        $profile = EsteticProfile::firstOrCreate(
            ['person_id' => $id],
            ['active' => true]
        );
        $this->estetic_profile_id = $profile->id;
    }

    public function clearPatient(): void
    {
        $this->person_id          = null;
        $this->estetic_profile_id = null;
        $this->tratamiento_id     = null;
        $this->patientMode        = 'search';
        $this->personSearch       = '';
    }

    public function startCreating(): void
    {
        $this->patientMode     = 'creating';
        $this->new_first_name  = '';
        $this->new_last_name   = '';
        $this->new_phone       = '';
        $this->new_rut         = '';
    }

    public function cancelCreating(): void
    {
        $this->patientMode = 'search';
    }

    public function createAndSelect(): void
    {
        $this->validate([
            'new_first_name' => ['required', 'string', 'max:100'],
            'new_last_name'  => ['required', 'string', 'max:100'],
            'new_phone'      => ['nullable', 'string', 'max:20'],
            'new_rut'        => ['nullable', 'string', 'max:20'],
        ], [], [
            'new_first_name' => 'nombre',
            'new_last_name'  => 'apellido',
        ]);

        $rut = $this->new_rut ? RutHelper::format(RutHelper::clean($this->new_rut)) : null;

        $person = DB::transaction(function () use ($rut) {
            $person = Person::create([
                'first_name' => $this->new_first_name,
                'last_name'  => $this->new_last_name,
                'phone'      => $this->new_phone ?: null,
                'rut'        => $rut,
                'gender'     => 'F',
            ]);
            EsteticProfile::create(['person_id' => $person->id, 'active' => true]);
            return $person;
        });

        $this->selectPerson($person->id);
    }

    public function save(): void
    {
        $this->validate([
            'estetic_profile_id' => ['required', 'exists:estetic_profiles,id'],
            'tratamiento_id'     => ['nullable', 'exists:este_tratamientos,id'],
            'professional_id'    => ['nullable', 'exists:professionals,id'],
            'fecha'              => ['required', 'date'],
            'hora_inicio'        => ['required'],
            'duracion_min'       => ['required', 'integer', 'min:5'],
            'estado'             => ['required', 'in:pendiente,confirmado,atendido,cancelado,ausente'],
            'motivo'             => ['nullable', 'string', 'max:255'],
            'notas'              => ['nullable', 'string'],
        ]);

        if ($this->register_payment) {
            $this->validate([
                'payment_amount' => ['required', 'numeric', 'min:1'],
                'payment_method' => ['required', 'string'],
                'payment_date'   => ['required', 'date'],
            ], [], [
                'payment_amount' => 'monto',
                'payment_method' => 'método de pago',
                'payment_date'   => 'fecha de pago',
            ]);
        }

        $inicio = Carbon::parse("{$this->fecha} {$this->hora_inicio}");
        $fin    = (clone $inicio)->addMinutes($this->duracion_min);

        $payload = [
            'estetic_profile_id' => $this->estetic_profile_id,
            'tratamiento_id'     => $this->tratamiento_id,
            'professional_id'    => $this->professional_id,
            'inicio'             => $inicio,
            'fin'                => $fin,
            'estado'             => $this->estado,
            'motivo'             => $this->motivo,
            'notas'              => $this->notas,
        ];

        DB::transaction(function () use ($payload) {
            $existing = $this->appointmentId ? Appointment::find($this->appointmentId) : null;
            if ($existing) {
                $existing->update($payload);
                $appointment = $existing;
            } else {
                $appointment = Appointment::create($payload);
                $this->appointmentId = $appointment->id;
            }

            if ($this->register_payment) {
                Payment::create([
                    'estetic_profile_id' => $this->estetic_profile_id,
                    'tratamiento_id'     => $this->tratamiento_id,
                    'sesion_id'          => $appointment->id,
                    'fecha'              => $this->payment_date,
                    'monto'              => $this->payment_amount,
                    'metodo'             => $this->payment_method,
                    'estado'             => 'pagado',
                    'observaciones'      => $this->payment_notes,
                    'registrado_por'     => auth()->id(),
                ]);
            }
        });

        session()->flash('success', 'Cita guardada correctamente.');
        $this->redirectRoute('admin.estetic.appointments.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.estetic.appointments.form', [
            'appointment' => $this->appointmentId ? Appointment::find($this->appointmentId) : null,
            'professionals' => Professional::estetic()->where('active', true)->orderBy('name')->get(),
        ]);
    }
}
