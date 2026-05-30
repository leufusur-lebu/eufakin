<?php

namespace App\Livewire\Admin\Kine\Appointments;

use App\Models\Kine\Appointment;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public string $weekStart = '';

    #[Url]
    public string $estado = '';

    public function mount(): void
    {
        if ($this->weekStart === '') {
            $this->weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        }
    }

    public function prevWeek(): void { $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d'); }
    public function nextWeek(): void { $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->format('Y-m-d'); }
    public function today(): void    { $this->weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'); }

    public function delete(int $id): void
    {
        Appointment::findOrFail($id)->delete();
        session()->flash('success', 'Cita eliminada.');
    }

    public function render()
    {
        $start = Carbon::parse($this->weekStart)->startOfDay();
        $end = (clone $start)->addDays(6)->endOfDay();

        $days = collect(range(0, 6))->map(fn ($i) => (clone $start)->addDays($i));

        $slots = [];
        for ($h = 8; $h < 20; $h++) {
            foreach ([0, 30] as $m) {
                $slots[] = sprintf('%02d:%02d', $h, $m);
            }
        }

        $appointments = Appointment::with(['kineProfile.person', 'professional'])
            ->between($start, $end)
            ->when($this->estado, fn ($q) => $q->where('estado', $this->estado))
            ->get();

        $grid = [];
        foreach ($appointments as $a) {
            $day = $a->inicio->format('Y-m-d');
            $slot = $a->inicio->format('H:i');
            $grid[$day][$slot][] = $a;
        }

        return view('livewire.admin.kine.appointments.index', compact('days', 'slots', 'grid', 'start'));
    }
}
