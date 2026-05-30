<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Estetic\Payment as EsteticPayment;
use App\Models\Kine\Payment as KinePayment;
use App\Models\Payment as GymPayment;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Payments extends Component
{
    #[Url] public string $from = '';
    #[Url] public string $to = '';
    #[Url] public string $module = 'all'; // all|gym|kine|estetic
    #[Url] public string $status = 'all'; // all|pagado|pendiente|anulado

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
        $filename = 'reporte_pagos_'.$this->from.'_'.$this->to.'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fecha', 'Módulo', 'Persona', 'Concepto', 'Método', 'Estado', 'Monto']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['date']?->format('Y-m-d'), strtoupper($r['module']),
                    $r['person'], $r['concept'], $r['method'], $r['status'], $r['amount'],
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

        if (in_array($this->module, ['all', 'gym'])) {
            $q = GymPayment::with(['person', 'subscription.plan'])
                ->whereBetween('payment_date', [$from, $to])
                ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status));
            foreach ($q->get() as $p) {
                $rows->push([
                    'module' => 'gym',
                    'date' => $p->payment_date,
                    'person' => $p->person?->full_name ?? '—',
                    'concept' => $p->subscription?->plan?->name ?? 'Pago general',
                    'method' => ucfirst($p->payment_type ?? '—'),
                    'status' => ucfirst($p->status ?? '—'),
                    'amount' => (float) $p->amount,
                ]);
            }
        }

        if (in_array($this->module, ['all', 'kine'])) {
            $q = KinePayment::with(['kineProfile.person', 'treatment'])
                ->whereBetween('fecha', [$from, $to])
                ->when($this->status !== 'all', fn ($q) => $q->where('estado', $this->status));
            foreach ($q->get() as $p) {
                $rows->push([
                    'module' => 'kine',
                    'date' => $p->fecha,
                    'person' => $p->kineProfile?->person?->full_name ?? '—',
                    'concept' => $p->treatment?->diagnostico ?? 'Sesión kine',
                    'method' => ucfirst($p->metodo ?? '—'),
                    'status' => ucfirst($p->estado ?? '—'),
                    'amount' => (float) $p->monto,
                ]);
            }
        }

        if (in_array($this->module, ['all', 'estetic'])) {
            $q = EsteticPayment::with(['esteticProfile.person', 'treatment'])
                ->whereBetween('fecha', [$from, $to])
                ->when($this->status !== 'all', fn ($q) => $q->where('estado', $this->status));
            foreach ($q->get() as $p) {
                $rows->push([
                    'module' => 'estetic',
                    'date' => $p->fecha,
                    'person' => $p->esteticProfile?->person?->full_name ?? '—',
                    'concept' => $p->treatment?->zona_tratada ?? 'Sesión estética',
                    'method' => ucfirst($p->metodo ?? '—'),
                    'status' => ucfirst($p->estado ?? '—'),
                    'amount' => (float) $p->monto,
                ]);
            }
        }

        return $rows->sortByDesc('date')->values();
    }

    public function render()
    {
        $rows = $this->buildRows();

        $totals = [
            'count' => $rows->count(),
            'total' => $rows->sum('amount'),
            'gym' => $rows->where('module', 'gym')->sum('amount'),
            'kine' => $rows->where('module', 'kine')->sum('amount'),
            'estetic' => $rows->where('module', 'estetic')->sum('amount'),
            'pagado' => $rows->filter(fn ($r) => strtolower($r['status']) === 'pagado')->sum('amount'),
            'pendiente' => $rows->filter(fn ($r) => strtolower($r['status']) === 'pendiente')->sum('amount'),
        ];

        // Serie por día
        $days = [];
        $cursor = Carbon::parse($this->from);
        $end = Carbon::parse($this->to);
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $days[$key] = ['label' => $cursor->format('d/m'), 'total' => 0];
            $cursor->addDay();
            if (count($days) > 60) break; // limita visualización a 60 días
        }
        foreach ($rows as $r) {
            $k = $r['date']?->format('Y-m-d');
            if ($k && isset($days[$k])) $days[$k]['total'] += $r['amount'];
        }
        $maxDay = max(array_column($days, 'total')) ?: 1;

        return view('livewire.admin.reports.payments', [
            'rows' => $rows->take(200), // tabla acotada
            'totals' => $totals,
            'days' => $days,
            'maxDay' => $maxDay,
        ]);
    }
}
