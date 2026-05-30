<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Estetic\Appointment as EsteticAppointment;
use App\Models\Kine\Appointment as KineAppointment;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Attendance extends Component
{
    #[Url] public string $from = '';
    #[Url] public string $to = '';
    #[Url] public string $module = 'all'; // all|kine|estetic

    public function mount(): void
    {
        if ($this->from === '') $this->from = Carbon::now()->startOfMonth()->format('Y-m-d');
        if ($this->to === '')   $this->to   = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function setRange(string $range): void
    {
        $today = Carbon::today();
        [$from, $to] = match ($range) {
            'today' => [$today, $today],
            'week'  => [$today->copy()->startOfWeek(Carbon::MONDAY), $today->copy()->endOfWeek(Carbon::SUNDAY)],
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'year'  => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            default => [$today, $today],
        };
        $this->from = $from->format('Y-m-d');
        $this->to   = $to->format('Y-m-d');
    }

    public function export(): StreamedResponse
    {
        $rows = $this->buildRows();
        $filename = 'reporte_asistencias_'.$this->from.'_'.$this->to.'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fecha', 'Hora', 'Módulo', 'Paciente', 'Profesional', 'Motivo', 'Estado']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['inicio']?->format('Y-m-d'), $r['inicio']?->format('H:i'),
                    strtoupper($r['module']), $r['person'], $r['professional'],
                    $r['motivo'], $r['estado'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function buildRows(): \Illuminate\Support\Collection
    {
        $from = Carbon::parse($this->from)->startOfDay();
        $to   = Carbon::parse($this->to)->endOfDay();
        $rows = collect();

        if (in_array($this->module, ['all', 'kine'])) {
            $q = KineAppointment::with(['kineProfile.person', 'professional'])
                ->whereBetween('inicio', [$from, $to])->get();
            foreach ($q as $a) {
                $rows->push([
                    'module' => 'kine',
                    'inicio' => $a->inicio,
                    'person' => $a->kineProfile?->person?->full_name ?? '—',
                    'professional' => $a->professional?->full_name ?? '—',
                    'motivo' => $a->motivo ?? '—',
                    'estado' => $a->estado,
                ]);
            }
        }

        if (in_array($this->module, ['all', 'estetic'])) {
            $q = EsteticAppointment::with(['esteticProfile.person', 'professional'])
                ->whereBetween('inicio', [$from, $to])->get();
            foreach ($q as $a) {
                $rows->push([
                    'module' => 'estetic',
                    'inicio' => $a->inicio,
                    'person' => $a->esteticProfile?->person?->full_name ?? '—',
                    'professional' => $a->professional?->full_name ?? '—',
                    'motivo' => $a->motivo ?? '—',
                    'estado' => $a->estado,
                ]);
            }
        }

        return $rows->sortByDesc('inicio')->values();
    }

    public function render()
    {
        $rows = $this->buildRows();

        $estados = ['pendiente' => 0, 'confirmado' => 0, 'atendido' => 0, 'cancelado' => 0, 'ausente' => 0];
        foreach ($rows as $r) {
            if (isset($estados[$r['estado']])) $estados[$r['estado']]++;
        }
        $total = $rows->count();
        $atendidos = $estados['atendido'];
        $tasa = $total ? round(($atendidos / $total) * 100) : 0;
        $ausencias = $estados['ausente'] + $estados['cancelado'];
        $tasaAusencia = $total ? round(($ausencias / $total) * 100) : 0;

        // Top pacientes y profesionales
        $topPacientes = $rows->groupBy('person')
            ->map(fn ($g) => ['count' => $g->count(), 'atendidos' => $g->where('estado', 'atendido')->count()])
            ->sortByDesc('count')->take(10);

        $topProfesionales = $rows->where('professional', '!=', '—')
            ->groupBy('professional')
            ->map(fn ($g) => ['count' => $g->count(), 'atendidos' => $g->where('estado', 'atendido')->count()])
            ->sortByDesc('count')->take(10);

        return view('livewire.admin.reports.attendance', [
            'rows' => $rows->take(200),
            'estados' => $estados,
            'total' => $total,
            'tasa' => $tasa,
            'tasaAusencia' => $tasaAusencia,
            'atendidos' => $atendidos,
            'topPacientes' => $topPacientes,
            'topProfesionales' => $topProfesionales,
        ]);
    }
}
