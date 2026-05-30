<?php

namespace App\Livewire\Admin\Cash;

use App\Models\CashClose;
use App\Models\Estetic\Payment as EsteticPayment;
use App\Models\Kine\Payment as KinePayment;
use App\Models\Payment as GymPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Daily extends Component
{
    #[Url] public string $date = '';

    public bool $closeOpen = false;
    public ?float $counted_cash = null;
    public ?string $close_notes = null;

    public function mount(): void
    {
        if ($this->date === '') $this->date = now()->format('Y-m-d');
    }

    public function setDate(string $offset): void
    {
        $this->date = match($offset) {
            'today'     => now()->format('Y-m-d'),
            'yesterday' => now()->subDay()->format('Y-m-d'),
            default     => $this->date,
        };
    }

    public function shiftDate(int $days): void
    {
        $this->date = Carbon::parse($this->date)->addDays($days)->format('Y-m-d');
    }

    // ===== Datos consolidados =====
    protected function loadPayments(string $date): Collection
    {
        $start = Carbon::parse($date)->startOfDay();
        $end   = Carbon::parse($date)->endOfDay();
        $rows = collect();

        // GYM
        GymPayment::with(['person', 'subscription.plan'])
            ->whereBetween('payment_date', [$start, $end])
            ->get()
            ->each(function ($p) use ($rows) {
                $rows->push((object) [
                    'id'        => 'g_'.$p->id,
                    'modulo'    => 'gym',
                    'fecha'     => $p->payment_date,
                    'persona'   => $p->person?->full_name ?? '—',
                    'concepto'  => $p->subscription?->plan?->name ?? 'Pago general',
                    'metodo'    => strtolower($p->payment_type ?? 'otro'),
                    'estado'    => strtolower($p->status ?? '—'),
                    'monto'     => (float) $p->amount,
                ]);
            });

        // KINE
        KinePayment::with(['kineProfile.person', 'treatment.tipoTratamiento'])
            ->whereBetween('fecha', [$start, $end])
            ->get()
            ->each(function ($p) use ($rows) {
                $rows->push((object) [
                    'id'        => 'k_'.$p->id,
                    'modulo'    => 'kine',
                    'fecha'     => $p->fecha,
                    'persona'   => $p->kineProfile?->person?->full_name ?? '—',
                    'concepto'  => $p->treatment?->tipoTratamiento?->nombre ?? $p->treatment?->diagnostico ?? 'Sesión kine',
                    'metodo'    => strtolower($p->metodo ?? 'otro'),
                    'estado'    => strtolower($p->estado ?? '—'),
                    'monto'     => (float) $p->monto,
                ]);
            });

        // ESTÉTICA
        EsteticPayment::with(['esteticProfile.person', 'treatment.tipoTratamiento'])
            ->whereBetween('fecha', [$start, $end])
            ->get()
            ->each(function ($p) use ($rows) {
                $rows->push((object) [
                    'id'        => 'e_'.$p->id,
                    'modulo'    => 'estetic',
                    'fecha'     => $p->fecha,
                    'persona'   => $p->esteticProfile?->person?->full_name ?? '—',
                    'concepto'  => $p->treatment?->tipoTratamiento?->nombre ?? $p->treatment?->zona_tratada ?? 'Sesión estética',
                    'metodo'    => strtolower($p->metodo ?? 'otro'),
                    'estado'    => strtolower($p->estado ?? '—'),
                    'monto'     => (float) $p->monto,
                ]);
            });

        return $rows->sortByDesc('fecha')->values();
    }

    protected function pendingPayments(string $date): Collection
    {
        // Cuotas pendientes que vencen hasta hoy (incluye atrasadas)
        $end = Carbon::parse($date)->endOfDay();
        $rows = collect();

        GymPayment::with(['person', 'subscription.plan'])
            ->where('status', 'pendiente')
            ->whereDate('payment_date', '<=', $end)
            ->get()->each(function ($p) use ($rows, $end) {
                $rows->push((object) [
                    'id'       => 'g_'.$p->id,
                    'pid'      => $p->id,
                    'modulo'   => 'gym',
                    'persona'  => $p->person?->full_name,
                    'concepto' => $p->subscription?->plan?->name ?? 'Pago general',
                    'fecha'    => $p->payment_date,
                    'monto'    => (float) $p->amount,
                    'dias_mora' => Carbon::parse($p->payment_date)->diffInDays($end),
                ]);
            });

        KinePayment::with(['kineProfile.person', 'treatment.tipoTratamiento'])
            ->where('estado', 'pendiente')
            ->whereDate('fecha', '<=', $end)
            ->get()->each(function ($p) use ($rows, $end) {
                $rows->push((object) [
                    'id'       => 'k_'.$p->id,
                    'pid'      => $p->id,
                    'modulo'   => 'kine',
                    'persona'  => $p->kineProfile?->person?->full_name,
                    'concepto' => $p->treatment?->tipoTratamiento?->nombre ?? 'Tratamiento',
                    'fecha'    => $p->fecha,
                    'monto'    => (float) $p->monto,
                    'dias_mora' => Carbon::parse($p->fecha)->diffInDays($end),
                ]);
            });

        EsteticPayment::with(['esteticProfile.person', 'treatment.tipoTratamiento'])
            ->where('estado', 'pendiente')
            ->whereDate('fecha', '<=', $end)
            ->get()->each(function ($p) use ($rows, $end) {
                $rows->push((object) [
                    'id'       => 'e_'.$p->id,
                    'pid'      => $p->id,
                    'modulo'   => 'estetic',
                    'persona'  => $p->esteticProfile?->person?->full_name,
                    'concepto' => $p->treatment?->tipoTratamiento?->nombre ?? 'Tratamiento',
                    'fecha'    => $p->fecha,
                    'monto'    => (float) $p->monto,
                    'dias_mora' => Carbon::parse($p->fecha)->diffInDays($end),
                ]);
            });

        return $rows->sortByDesc('dias_mora')->values();
    }

    // ===== Cierre =====
    public function openClose(): void
    {
        $payments = $this->loadPayments($this->date)->where('estado', 'pagado');
        $this->counted_cash = (float) $payments->where('metodo', 'efectivo')->sum('monto');
        $this->close_notes = null;
        $this->resetErrorBag();
        $this->closeOpen = true;
    }

    public function confirmClose(): void
    {
        $this->validate([
            'counted_cash' => ['required', 'numeric', 'min:0'],
            'close_notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $payments = $this->loadPayments($this->date)->where('estado', 'pagado');
        $totalSistema = (float) $payments->sum('monto');
        $totalEfectivo = (float) $payments->where('metodo', 'efectivo')->sum('monto');
        $diferencia = (float) $this->counted_cash - $totalEfectivo;

        $byMethod = $payments->groupBy('metodo')->map(fn ($g) => (float) $g->sum('monto'))->all();
        $byModule = $payments->groupBy('modulo')->map(fn ($g) => (float) $g->sum('monto'))->all();

        CashClose::updateOrCreate(
            ['fecha' => $this->date],
            [
                'total_sistema'         => $totalSistema,
                'total_efectivo_sistema'=> $totalEfectivo,
                'total_efectivo_contado'=> $this->counted_cash,
                'diferencia'            => $diferencia,
                'breakdown_metodos'     => $byMethod,
                'breakdown_modulos'     => $byModule,
                'total_transacciones'   => $payments->count(),
                'observaciones'         => $this->close_notes,
                'closed_by'             => auth()->id(),
                'closed_at'             => now(),
            ]
        );

        session()->flash('success', 'Caja cerrada para '.Carbon::parse($this->date)->format('d/m/Y').'.');
        $this->closeOpen = false;
    }

    public function reopenClose(): void
    {
        CashClose::where('fecha', $this->date)->delete();
        session()->flash('success', 'Cierre revertido. Puedes seguir editando movimientos.');
    }

    public function render()
    {
        $payments = $this->loadPayments($this->date);
        $paid     = $payments->where('estado', 'pagado');
        $pending  = $payments->where('estado', 'pendiente');
        $overdue  = $this->pendingPayments($this->date);

        $byMethod = $paid->groupBy('metodo')->map(fn ($g) => [
            'total' => (float) $g->sum('monto'),
            'count' => $g->count(),
        ])->sortByDesc('total');

        $byModule = $paid->groupBy('modulo')->map(fn ($g) => [
            'total' => (float) $g->sum('monto'),
            'count' => $g->count(),
        ]);

        $totalIn  = (float) $paid->sum('monto');
        $totalCash = (float) $paid->where('metodo', 'efectivo')->sum('monto');

        // Cierres recientes (últimos 7 días)
        $recentCloses = CashClose::orderByDesc('fecha')->limit(7)->get();
        $thisClose = CashClose::where('fecha', $this->date)->first();

        return view('livewire.admin.cash.daily', [
            'paid'         => $paid,
            'pending'      => $pending,
            'overdue'      => $overdue,
            'totalIn'      => $totalIn,
            'totalCash'    => $totalCash,
            'totalCount'   => $paid->count(),
            'pendingTotal' => (float) $pending->sum('monto'),
            'overdueTotal' => (float) $overdue->sum('monto'),
            'byMethod'     => $byMethod,
            'byModule'     => $byModule,
            'recentCloses' => $recentCloses,
            'thisClose'    => $thisClose,
        ]);
    }
}
