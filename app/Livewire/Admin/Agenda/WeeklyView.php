<?php

namespace App\Livewire\Admin\Agenda;

use App\Models\Estetic\Appointment as EsteticAppointment;
use App\Models\Kine\Appointment as KineAppointment;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class WeeklyView extends Component
{
    #[Url]
    public string $weekStart = '';

    #[Url]
    public string $module = 'all'; // all, kine, estetic

    public function mount(): void
    {
        if ($this->weekStart === '') {
            $this->weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        }
    }

    public function prevWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->format('Y-m-d');
    }

    public function today(): void
    {
        $this->weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function render()
    {
        $start = Carbon::parse($this->weekStart)->startOfDay();
        $end = (clone $start)->addDays(6)->endOfDay();

        $days = collect(range(0, 6))->map(fn ($i) => (clone $start)->addDays($i));

        // Time slots 08:00 - 20:00 in 30-min increments
        $slots = [];
        for ($h = 8; $h < 20; $h++) {
            foreach ([0, 30] as $m) {
                $slots[] = sprintf('%02d:%02d', $h, $m);
            }
        }

        $kineAppts = ($this->module === 'estetic') ? collect() :
            KineAppointment::with(['kineProfile.person', 'professional'])
                ->between($start, $end)->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => 'kine',
                    'inicio' => $a->inicio,
                    'fin' => $a->fin,
                    'estado' => $a->estado,
                    'color' => $a->color,
                    'person' => $a->kineProfile?->person?->full_name ?? '—',
                    'professional' => $a->professional?->full_name,
                    'motivo' => $a->motivo,
                ]);

        $esteticAppts = ($this->module === 'kine') ? collect() :
            EsteticAppointment::with(['esteticProfile.person', 'professional'])
                ->between($start, $end)->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => 'estetic',
                    'inicio' => $a->inicio,
                    'fin' => $a->fin,
                    'estado' => $a->estado,
                    'color' => $a->color,
                    'person' => $a->esteticProfile?->person?->full_name ?? '—',
                    'professional' => $a->professional?->full_name,
                    'motivo' => $a->motivo,
                ]);

        $appointments = $kineAppts->concat($esteticAppts);

        // Group by day (Y-m-d) and time slot (H:i)
        $grid = [];
        foreach ($appointments as $a) {
            $day = $a['inicio']->format('Y-m-d');
            $slot = $a['inicio']->format('H:i');
            $grid[$day][$slot][] = $a;
        }

        return view('livewire.admin.agenda.weekly-view', compact('days', 'slots', 'grid', 'start'));
    }
}
