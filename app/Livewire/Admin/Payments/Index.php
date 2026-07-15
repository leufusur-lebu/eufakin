<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $month = '';

    // Registro de pago pendiente
    public bool    $payOpen    = false;
    public ?int    $payingId   = null;
    public ?string $pay_person = null;
    public ?float  $pay_total  = null;
    public ?string $pay_date   = null;
    public ?string $pay_notes  = null;
    public array   $pay_splits = [['monto' => null, 'metodo' => 'efectivo']];

    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingMonth(): void  { $this->resetPage(); }

    public function setStatus(string $s): void
    {
        $this->status = $s;
        $this->resetPage();
    }

    public function openPay(int $id): void
    {
        $p = Payment::with('person')->findOrFail($id);
        $this->payingId   = $p->id;
        $this->pay_person = $p->person?->full_name;
        $this->pay_total  = (float) $p->amount;
        $this->pay_date   = now()->format('Y-m-d');
        $this->pay_notes  = $p->notes;
        $this->pay_splits = [['monto' => (float) $p->amount, 'metodo' => 'efectivo']];
        $this->resetErrorBag();
        $this->payOpen    = true;
    }

    public function closePay(): void
    {
        $this->payOpen  = false;
        $this->payingId = null;
    }

    public function addPaySplit(): void
    {
        if (count($this->pay_splits) < 4) {
            $this->pay_splits[] = ['monto' => null, 'metodo' => 'efectivo'];
        }
    }

    public function removePaySplit(int $index): void
    {
        if (count($this->pay_splits) > 1) {
            array_splice($this->pay_splits, $index, 1);
            $this->pay_splits = array_values($this->pay_splits);
        }
    }

    public function splitsTotal(): float
    {
        return collect($this->pay_splits)->sum(fn($s) => (float) ($s['monto'] ?? 0));
    }

    public function confirmPay(): void
    {
        $this->validate([
            'pay_date'            => ['required', 'date'],
            'pay_splits'          => ['required', 'array', 'min:1'],
            'pay_splits.*.monto'  => ['required', 'numeric', 'min:1'],
            'pay_splits.*.metodo' => ['required', 'string'],
            'pay_notes'           => ['nullable', 'string', 'max:500'],
        ], [], [
            'pay_date'            => 'fecha',
            'pay_splits.*.monto'  => 'monto',
            'pay_splits.*.metodo' => 'método',
            'pay_notes'           => 'observaciones',
        ]);

        $original = Payment::findOrFail($this->payingId);
        $splits     = collect($this->pay_splits)->filter(fn($s) => ($s['monto'] ?? 0) > 0)->values();
        $splitCount = $splits->count();

        DB::transaction(function () use ($original, $splits, $splitCount) {
            foreach ($splits as $i => $split) {
                $obs = $splitCount > 1
                    ? ($this->pay_notes ? $this->pay_notes . " (parte " . ($i+1) . "/$splitCount)" : "Pago mixto parte " . ($i+1) . "/$splitCount")
                    : $this->pay_notes;

                if ($i === 0) {
                    // Actualiza el registro pendiente original con el primer split
                    $original->update([
                        'amount'       => (float) $split['monto'],
                        'payment_date' => $this->pay_date,
                        'payment_type' => $split['metodo'],
                        'status'       => 'pagado',
                        'notes'        => $obs,
                    ]);
                } else {
                    // Crea registros adicionales vinculados a la misma suscripción
                    Payment::create([
                        'person_id'       => $original->person_id,
                        'subscription_id' => $original->subscription_id,
                        'amount'          => (float) $split['monto'],
                        'payment_date'    => $this->pay_date,
                        'payment_type'    => $split['metodo'],
                        'status'          => 'pagado',
                        'notes'           => $obs,
                    ]);
                }
            }
        });

        session()->flash('success', 'Pago registrado correctamente.');
        $this->closePay();
    }

    public function render()
    {
        $base = Payment::query()->with(['person', 'subscription.plan']);

        $query = (clone $base)
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->whereHas('person', fn ($p) =>
                $p->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%")
                  ->orWhere('rut', 'like', "%{$this->search}%")
            ))
            ->when($this->month, function ($q) {
                [$y, $m] = explode('-', $this->month);
                $q->whereYear('payment_date', $y)->whereMonth('payment_date', $m);
            })
            ->orderBy('payment_date', 'desc');

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $counts = [
            'all' => (clone $base)->count(),
            'pagado' => (clone $base)->where('status', 'pagado')->count(),
            'pendiente' => (clone $base)->where('status', 'pendiente')->count(),
            'anulado' => (clone $base)->where('status', 'anulado')->count(),
        ];

        return view('livewire.admin.payments.index', [
            'payments' => $query->paginate(15),
            'totalHistorico' => (float) (clone $base)->where('status', 'pagado')->sum('amount'),
            'totalMes' => (float) (clone $base)->where('status', 'pagado')
                ->whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount'),
            'pendienteMonto' => (float) (clone $base)->where('status', 'pendiente')->sum('amount'),
            'counts' => $counts,
        ]);
    }

    public function delete(int $id): void
    {
        Payment::findOrFail($id)->delete();
        session()->flash('success', 'Pago eliminado.');
    }
}
