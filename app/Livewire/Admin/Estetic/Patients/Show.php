<?php

namespace App\Livewire\Admin\Estetic\Patients;

use App\Models\Estetic\SessionPhoto;
use App\Models\EsteticProfile;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    public EsteticProfile $profile;

    #[Url] public string $tab = 'overview'; // overview|treatments|sessions|gallery|finance|profile

    public function mount(EsteticProfile $profile): void
    {
        $this->profile = $profile->load('person.clinicalProfile');
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

        // Sesiones clínicas con notas y fotos
        $clinicalSessions = \App\Models\Estetic\Sesion::with(['photos', 'treatment.tipoTratamiento', 'appointment.professional'])
            ->whereHas('treatment', fn ($q) => $q->where('estetic_profile_id', $profile->id))
            ->orderByDesc('fecha')
            ->get();

        // Galería organizada
        $photos = SessionPhoto::with(['sesion', 'treatment.tipoTratamiento'])
            ->where('estetic_profile_id', $profile->id)
            ->orderByDesc('tomada_at')
            ->get();
        $photosByTipo = $photos->groupBy('tipo');

        $upcoming = $appointments->filter(fn ($a) => $a->inicio?->gte(now()) && in_array($a->estado, ['pendiente', 'confirmado']))->sortBy('inicio');
        $history  = $appointments->filter(fn ($a) => !$a->inicio?->gte(now()) || !in_array($a->estado, ['pendiente', 'confirmado']));

        $payments = $profile->payments()
            ->with(['treatment.tipoTratamiento'])
            ->orderByDesc('fecha')->get();

        // Finanzas
        $totalProtocols = (float) $treatments->sum('costo_total');
        $paid    = (float) $payments->where('estado', 'pagado')->sum('monto');
        $pending = (float) $payments->where('estado', 'pendiente')->sum('monto');
        $balance = $totalProtocols - $paid;

        // Stats rápidas
        $stats = [
            'treatments_active'   => $treatments->where('estado', 'activo')->count(),
            'treatments_finished' => $treatments->where('estado', 'finalizado')->count(),
            'sessions_done'       => $appointments->where('estado', 'atendido')->count(),
            'sessions_no_show'    => $appointments->whereIn('estado', ['ausente', 'cancelado'])->count(),
        ];

        return view('livewire.admin.estetic.patients.show', [
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
        ]);
    }
}
