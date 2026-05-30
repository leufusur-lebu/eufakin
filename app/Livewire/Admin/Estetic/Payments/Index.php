<?php

namespace App\Livewire\Admin\Estetic\Payments;

use App\Models\Estetic\Payment;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    #[Url] public string $tab = 'receivables'; // receivables | movements
    #[Url] public string $search = '';
    #[Url] public string $from = '';
    #[Url] public string $to = '';
    #[Url] public string $method = ''; // filtro método en movements

    // Modal de cobro
    public bool $payOpen = false;
    public ?int $payingId = null;
    public ?string $payPerson = null;
    public ?string $payProtocol = null;
    public ?float $payAmount = null;
    public ?string $payDate = null;
    public string $payMethod = 'efectivo';
    public ?string $payNotes = null;

    // Cobro masivo de un paciente
    public bool $bulkOpen = false;
    public ?int $bulkProfileId = null;
    public ?string $bulkPerson = null;
    public ?float $bulkTotal = null;
    public ?float $bulkAmount = null;
    public ?string $bulkDate = null;
    public string $bulkMethod = 'efectivo';

    public function mount(): void
    {
        if ($this->from === '') $this->from = Carbon::now()->startOfMonth()->format('Y-m-d');
        if ($this->to === '')   $this->to   = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function setRange(string $r): void
    {
        $today = Carbon::today();
        [$f, $t] = match($r) {
            'today' => [$today, $today],
            'week'  => [$today->copy()->startOfWeek(Carbon::MONDAY), $today->copy()->endOfWeek(Carbon::SUNDAY)],
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'year'  => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            default => [$today, $today],
        };
        $this->from = $f->format('Y-m-d');
        $this->to   = $t->format('Y-m-d');
    }

    // ===== COBRO INDIVIDUAL =====
    public function openPay(int $id): void
    {
        $p = Payment::with(['esteticProfile.person', 'treatment.tipoTratamiento'])->findOrFail($id);
        $this->payingId    = $p->id;
        $this->payPerson   = $p->esteticProfile?->person?->full_name;
        $this->payProtocol = $p->treatment?->tipoTratamiento?->nombre ?? $p->treatment?->zona_tratada;
        $this->payAmount   = (float) $p->monto;
        $this->payDate     = now()->format('Y-m-d');
        $this->payMethod   = 'efectivo';
        $this->payNotes    = $p->observaciones;
        $this->resetErrorBag();
        $this->payOpen     = true;
    }

    public function confirmPay(): void
    {
        $this->validate([
            'payAmount' => ['required', 'numeric', 'min:0'],
            'payDate'   => ['required', 'date'],
            'payMethod' => ['required', 'string'],
        ]);
        $p = Payment::findOrFail($this->payingId);
        $p->update([
            'monto'          => $this->payAmount,
            'fecha'          => $this->payDate,
            'metodo'         => $this->payMethod,
            'estado'         => 'pagado',
            'observaciones'  => $this->payNotes,
        ]);
        session()->flash('success', 'Pago registrado correctamente.');
        $this->payOpen = false;
    }

    // ===== COBRO MASIVO POR PACIENTE =====
    public function openBulk(int $profileId): void
    {
        $rows = Payment::with('esteticProfile.person')
            ->where('estetic_profile_id', $profileId)
            ->where('estado', 'pendiente')
            ->orderBy('fecha')->get();

        $this->bulkProfileId = $profileId;
        $this->bulkPerson    = $rows->first()?->esteticProfile?->person?->full_name;
        $this->bulkTotal     = (float) $rows->sum('monto');
        $this->bulkAmount    = $this->bulkTotal;
        $this->bulkDate      = now()->format('Y-m-d');
        $this->bulkMethod    = 'efectivo';
        $this->resetErrorBag();
        $this->bulkOpen      = true;
    }

    public function confirmBulk(): void
    {
        $this->validate([
            'bulkAmount' => ['required', 'numeric', 'min:0'],
            'bulkDate'   => ['required', 'date'],
            'bulkMethod' => ['required', 'string'],
        ]);

        $remaining = (float) $this->bulkAmount;
        $rows = Payment::where('estetic_profile_id', $this->bulkProfileId)
            ->where('estado', 'pendiente')
            ->orderBy('fecha')->get();

        foreach ($rows as $p) {
            if ($remaining <= 0) break;
            $monto = (float) $p->monto;
            if ($remaining >= $monto) {
                $p->update([
                    'monto'  => $monto,
                    'fecha'  => $this->bulkDate,
                    'metodo' => $this->bulkMethod,
                    'estado' => 'pagado',
                ]);
                $remaining -= $monto;
            } else {
                // Pago parcial: registramos el cobro del abono y dejamos saldo en una cuota nueva
                $partial = $remaining;
                $p->update([
                    'monto'  => $partial,
                    'fecha'  => $this->bulkDate,
                    'metodo' => $this->bulkMethod,
                    'estado' => 'pagado',
                    'observaciones' => trim(($p->observaciones ?? '').' (abono)'),
                ]);
                Payment::create([
                    'estetic_profile_id' => $p->estetic_profile_id,
                    'tratamiento_id'     => $p->tratamiento_id,
                    'fecha'              => $p->fecha,
                    'monto'              => $monto - $partial,
                    'metodo'             => 'efectivo',
                    'estado'             => 'pendiente',
                    'observaciones'      => 'Saldo pendiente tras abono',
                ]);
                $remaining = 0;
            }
        }

        session()->flash('success', 'Cobro registrado correctamente.');
        $this->bulkOpen = false;
    }

    // ===== EXPORT MOVIMIENTOS =====
    public function export(): StreamedResponse
    {
        $rows = $this->movements()['rows'];
        $filename = "caja_{$this->from}_{$this->to}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fecha', 'Paciente', 'Protocolo', 'Método', 'Estado', 'Monto']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->fecha?->format('Y-m-d'),
                    $r->esteticProfile?->person?->full_name,
                    $r->treatment?->tipoTratamiento?->nombre ?? $r->treatment?->zona_tratada,
                    $r->metodo, $r->estado, $r->monto,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function receivables(): array
    {
        $base = Payment::query()
            ->where('estado', 'pendiente')
            ->with(['esteticProfile.person', 'treatment.tipoTratamiento']);

        if ($this->search !== '') {
            $term = $this->search;
            $base->whereHas('esteticProfile.person', fn ($q) =>
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('rut', 'like', "%{$term}%"));
        }

        $rows = $base->orderBy('fecha')->get();

        // Agrupado por paciente
        $byPatient = $rows->groupBy('estetic_profile_id')->map(function ($g) {
            $first = $g->first();
            $oldest = $g->min('fecha');
            return [
                'profile_id' => $first->estetic_profile_id,
                'person'     => $first->esteticProfile?->person,
                'pending_count' => $g->count(),
                'total_due'  => (float) $g->sum('monto'),
                'oldest_date' => $oldest,
                'days_overdue' => Carbon::parse($oldest)->diffInDays(now()),
                'protocols'  => $g->map(fn ($p) => $p->treatment?->tipoTratamiento?->nombre ?? $p->treatment?->zona_tratada)->filter()->unique()->values(),
            ];
        })->sortByDesc('days_overdue')->values();

        return [
            'rows'  => $rows,
            'groups'=> $byPatient,
            'totalDue' => (float) $rows->sum('monto'),
            'count' => $rows->count(),
        ];
    }

    protected function movements(): array
    {
        $from = Carbon::parse($this->from)->startOfDay();
        $to   = Carbon::parse($this->to)->endOfDay();

        $base = Payment::query()
            ->whereBetween('fecha', [$from, $to])
            ->where('estado', 'pagado')
            ->with(['esteticProfile.person', 'treatment.tipoTratamiento'])
            ->when($this->method, fn ($q) => $q->where('metodo', $this->method))
            ->orderByDesc('fecha');

        $rows = $base->get();

        $totalsByMethod = $rows->groupBy('metodo')->map(fn ($g) => (float) $g->sum('monto'));

        // Serie diaria
        $days = [];
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $key = $cursor->format('Y-m-d');
            $days[$key] = ['label' => $cursor->format('d/m'), 'total' => 0];
            $cursor->addDay();
            if (count($days) > 92) break;
        }
        foreach ($rows as $r) {
            $k = $r->fecha?->format('Y-m-d');
            if ($k && isset($days[$k])) $days[$k]['total'] += (float) $r->monto;
        }

        return [
            'rows' => $rows,
            'total' => (float) $rows->sum('monto'),
            'count' => $rows->count(),
            'totalsByMethod' => $totalsByMethod,
            'days' => $days,
            'maxDay' => max(array_column($days, 'total')) ?: 1,
        ];
    }

    public function render()
    {
        $receivables = $this->receivables();
        $movements   = $this->movements();

        // Stats globales
        $totalPendiente = (float) Payment::where('estado', 'pendiente')->sum('monto');
        $totalMes = (float) Payment::where('estado', 'pagado')
            ->whereBetween('fecha', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('monto');

        return view('livewire.admin.estetic.payments.index', [
            'receivables' => $receivables,
            'movements'   => $movements,
            'totalPendiente' => $totalPendiente,
            'totalMes'    => $totalMes,
        ]);
    }
}
