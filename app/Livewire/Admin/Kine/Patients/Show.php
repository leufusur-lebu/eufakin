<?php

namespace App\Livewire\Admin\Kine\Patients;

use App\Models\Kine\Appointment;
use App\Models\Kine\SessionPhoto;
use App\Models\Kine\Sesion;
use App\Models\Kine\Treatment;
use App\Models\KineProfile;
use App\Models\Professional;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    public KineProfile $profile;

    #[Url] public string $tab = 'overview';

    // Filtro de la pestaña Citas
    public string $apptFilter = 'all'; // all|upcoming|past|active

    // Modal de cita (crear/editar)
    public bool $apptOpen = false;
    public ?int $editingApptId = null;
    public ?string $appt_date = null;
    public ?string $appt_time = '10:00';
    public int $appt_duration = 45;
    public ?int $appt_professional_id = null;
    public ?int $appt_tratamiento_id = null;
    public string $appt_estado = 'pendiente';
    public ?string $appt_motivo = null;
    public ?string $appt_notas = null;

    public function mount(KineProfile $profile): void
    {
        $this->profile = $profile->load('person.clinicalProfile');
    }

    // ============ GESTIÓN DE CITAS ============
    public function openAppointment(?int $id = null): void
    {
        if ($id) {
            $a = Appointment::where('kine_profile_id', $this->profile->id)->findOrFail($id);
            $this->editingApptId = $id;
            $this->appt_date = $a->inicio?->format('Y-m-d');
            $this->appt_time = $a->inicio?->format('H:i');
            $this->appt_duration = (int) $a->inicio?->diffInMinutes($a->fin) ?: 45;
            $this->appt_professional_id = $a->professional_id;
            $this->appt_tratamiento_id  = $a->tratamiento_id;
            $this->appt_estado = $a->estado;
            $this->appt_motivo = $a->motivo;
            $this->appt_notas  = $a->notas;
        } else {
            $this->editingApptId = null;
            $this->appt_date = now()->addDay()->format('Y-m-d');
            $this->appt_time = '10:00';
            $this->appt_duration = 45;
            // Pre-cargar profesional/tratamiento del activo si existe
            $active = $this->profile->treatments()->where('estado','activo')->latest('id')->first();
            $this->appt_professional_id = $active?->professional_id;
            $this->appt_tratamiento_id  = $active?->id;
            $this->appt_estado = 'pendiente';
            $this->appt_motivo = $active ? 'Sesión kinésica' : null;
            $this->appt_notas = null;
        }
        $this->resetErrorBag();
        $this->apptOpen = true;
    }

    /**
     * Capacidad de sesiones del tratamiento seleccionado.
     * Devuelve null si no hay tratamiento asociado (cita libre).
     */
    #[Computed]
    public function slotInfo(): ?array
    {
        if (!$this->appt_tratamiento_id) return null;

        $t = Treatment::find($this->appt_tratamiento_id);
        if (!$t || $t->kine_profile_id !== $this->profile->id) return null;

        $consumed = $t->appointments()
            ->where('estado', '!=', 'cancelado')
            ->when($this->editingApptId, fn ($q) => $q->where('id', '!=', $this->editingApptId))
            ->count();

        $total     = (int) $t->sesiones_totales;
        $available = max(0, $total - $consumed);
        $position  = $consumed + 1;

        return [
            'treatment'       => $t,
            'total'           => $total,
            'consumed'        => $consumed,
            'available'       => $available,
            'position'        => $position,
            'is_full'         => $available <= 0,
            'treatment_state' => $t->estado,
        ];
    }

    public function saveAppointment(): void
    {
        $this->validate([
            'appt_date'           => ['required', 'date'],
            'appt_time'           => ['required'],
            'appt_duration'       => ['required', 'integer', 'min:5', 'max:480'],
            'appt_professional_id'=> ['nullable', 'exists:professionals,id'],
            'appt_tratamiento_id' => ['nullable', 'integer'],
            'appt_estado'         => ['required', 'in:pendiente,confirmado,atendido,cancelado,ausente'],
            'appt_motivo'         => ['nullable', 'string', 'max:255'],
            'appt_notas'          => ['nullable', 'string', 'max:1000'],
        ], [], [
            'appt_date' => 'fecha', 'appt_time' => 'hora', 'appt_duration' => 'duración',
        ]);

        // Validar cupo del tratamiento (no aplica si la cita es libre o se está cancelando)
        if ($this->appt_tratamiento_id && $this->appt_estado !== 'cancelado') {
            // Si estamos editando una cita que YA pertenecía al mismo tratamiento, el cupo no cambia
            $skipCupoCheck = false;
            if ($this->editingApptId) {
                $originalTratamientoId = Appointment::where('id', $this->editingApptId)->value('tratamiento_id');
                $skipCupoCheck = ((int) $originalTratamientoId === (int) $this->appt_tratamiento_id);
            }

            if (!$skipCupoCheck) {
                $info = $this->slotInfo;
                if ($info && $info['is_full']) {
                    $this->addError('appt_tratamiento_id',
                        "El tratamiento ya tiene {$info['consumed']} de {$info['total']} sesiones agendadas. ".
                        "Cancela una existente o aumenta el total de sesiones del tratamiento.");
                    return;
                }
                if ($info && in_array($info['treatment_state'], ['cancelado', 'suspendido'])) {
                    $this->addError('appt_tratamiento_id',
                        "No se pueden agendar citas: el tratamiento está {$info['treatment_state']}.");
                    return;
                }
            }
        }

        $inicio = Carbon::parse($this->appt_date.' '.$this->appt_time);
        $fin    = $inicio->copy()->addMinutes($this->appt_duration);

        $data = [
            'kine_profile_id' => $this->profile->id,
            'tratamiento_id'  => $this->appt_tratamiento_id ?: null,
            'professional_id' => $this->appt_professional_id ?: null,
            'inicio'          => $inicio,
            'fin'             => $fin,
            'estado'          => $this->appt_estado,
            'motivo'          => $this->appt_motivo,
            'notas'           => $this->appt_notas,
        ];

        if ($this->editingApptId) {
            Appointment::findOrFail($this->editingApptId)->update($data);
            session()->flash('success', 'Cita actualizada.');
        } else {
            Appointment::create($data);
            session()->flash('success', 'Cita agendada.');
        }
        $this->apptOpen = false;
    }

    public function confirmAppointment(int $id): void
    {
        Appointment::where('kine_profile_id', $this->profile->id)
            ->where('id', $id)->update(['estado' => 'confirmado']);
        session()->flash('success', 'Cita confirmada.');
    }

    public function cancelAppointment(int $id): void
    {
        Appointment::where('kine_profile_id', $this->profile->id)
            ->where('id', $id)->update(['estado' => 'cancelado']);
        session()->flash('success', 'Cita cancelada.');
    }

    public function deleteAppointment(int $id): void
    {
        Appointment::where('kine_profile_id', $this->profile->id)
            ->where('id', $id)->delete();
        session()->flash('success', 'Cita eliminada.');
    }

    public function render()
    {
        $profile = $this->profile;

        $treatments = $profile->treatments()
            ->with(['tipoTratamiento', 'professional'])
            ->orderByDesc('id')->get();

        $activeTreatment = $treatments->firstWhere('estado', 'activo');

        $appointments = $profile->appointments()
            ->with(['professional', 'treatment.tipoTratamiento'])
            ->orderByDesc('inicio')->get();

        $upcoming = $appointments->filter(fn ($a) => $a->inicio?->gte(now()) && in_array($a->estado, ['pendiente', 'confirmado']))->sortBy('inicio');
        $history  = $appointments->filter(fn ($a) => !$a->inicio?->gte(now()) || !in_array($a->estado, ['pendiente', 'confirmado']));

        $payments = $profile->payments()
            ->with(['treatment.tipoTratamiento'])
            ->orderByDesc('fecha')->get();

        $clinicalSessions = Sesion::with(['photos', 'treatment.tipoTratamiento', 'appointment.professional'])
            ->whereHas('treatment', fn ($q) => $q->where('kine_profile_id', $profile->id))
            ->orderByDesc('fecha')
            ->get();

        $photos = SessionPhoto::with(['sesion', 'treatment.tipoTratamiento'])
            ->where('kine_profile_id', $profile->id)
            ->orderByDesc('tomada_at')
            ->get();
        $photosByTipo = $photos->groupBy('tipo');

        $totalProtocols = (float) $treatments->sum('costo_total');
        $paid    = (float) $payments->where('estado', 'pagado')->sum('monto');
        $pending = (float) $payments->where('estado', 'pendiente')->sum('monto');
        $balance = $totalProtocols - $paid;

        $stats = [
            'treatments_active'   => $treatments->where('estado', 'activo')->count(),
            'treatments_finished' => $treatments->where('estado', 'finalizado')->count(),
            'sessions_done'       => $appointments->where('estado', 'atendido')->count(),
            'sessions_no_show'    => $appointments->whereIn('estado', ['ausente', 'cancelado'])->count(),
        ];

        // Evolución de dolor (escala EVA) en las sesiones
        $painSeries = $clinicalSessions
            ->filter(fn ($s) => $s->escala_dolor !== null)
            ->sortBy('fecha')
            ->map(fn ($s) => ['fecha' => $s->fecha?->format('d/m'), 'eva' => $s->escala_dolor])
            ->values();

        // Citas filtradas para la pestaña "Citas"
        $allAppts = $appointments->sortByDesc('inicio')->values();
        $apptFiltered = match ($this->apptFilter) {
            'upcoming' => $allAppts->filter(fn ($a) => $a->inicio?->gte(now())),
            'past'     => $allAppts->filter(fn ($a) => $a->inicio?->lt(now())),
            'active'   => $allAppts->filter(fn ($a) => in_array($a->estado, ['pendiente', 'confirmado'])),
            default    => $allAppts,
        };

        return view('livewire.admin.kine.patients.show', [
            'profile'         => $profile,
            'person'          => $profile->person,
            'treatments'      => $treatments,
            'activeTreatment' => $activeTreatment,
            'upcoming'        => $upcoming,
            'history'         => $history,
            'payments'        => $payments,
            'totalProtocols'  => $totalProtocols,
            'paid'            => $paid,
            'pending'         => $pending,
            'balance'         => $balance,
            'stats'           => $stats,
            'clinicalSessions' => $clinicalSessions,
            'photos'          => $photos,
            'photosByTipo'    => $photosByTipo,
            'painSeries'      => $painSeries,
            'apptList'        => $apptFiltered->values(),
            'apptCounts'      => [
                'all'      => $allAppts->count(),
                'upcoming' => $allAppts->filter(fn ($a) => $a->inicio?->gte(now()))->count(),
                'past'     => $allAppts->filter(fn ($a) => $a->inicio?->lt(now()))->count(),
                'active'   => $allAppts->filter(fn ($a) => in_array($a->estado, ['pendiente','confirmado']))->count(),
            ],
            'professionals'   => Professional::kine()->orderBy('name')->get(),
            'patientTreatments' => $treatments,
        ]);
    }
}
