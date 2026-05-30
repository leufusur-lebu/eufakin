<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use Carbon\Carbon;
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

    // Registro de pago pendiente
    public bool $payOpen = false;
    public ?int $payingId = null;
    public ?string $pay_person = null;
    public ?float $pay_amount = null;
    public ?string $pay_date = null;
    public string $pay_method = 'efectivo';
    public ?string $pay_notes = null;

    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }

    public function setStatus(string $s): void
    {
        $this->status = $s;
        $this->resetPage();
    }

    public function openPay(int $id): void
    {
        $p = Payment::with('person')->findOrFail($id);
        $this->payingId    = $p->id;
        $this->pay_person  = $p->person?->full_name;
        $this->pay_amount  = (float) $p->amount;
        $this->pay_date    = now()->format('Y-m-d');
        $this->pay_method  = 'efectivo';
        $this->pay_notes   = $p->notes;
        $this->resetErrorBag();
        $this->payOpen     = true;
    }

    public function closePay(): void
    {
        $this->payOpen = false;
        $this->payingId = null;
    }

    public function confirmPay(): void
    {
        $this->validate([
            'pay_amount' => ['required', 'numeric', 'min:0'],
            'pay_date'   => ['required', 'date'],
            'pay_method' => ['required', 'string', 'max:50'],
            'pay_notes'  => ['nullable', 'string', 'max:500'],
        ], [], [
            'pay_amount' => 'monto', 'pay_date' => 'fecha',
            'pay_method' => 'método', 'pay_notes' => 'observaciones',
        ]);

        $p = Payment::findOrFail($this->payingId);
        $p->update([
            'amount'       => $this->pay_amount,
            'payment_date' => $this->pay_date,
            'payment_type' => $this->pay_method,
            'status'       => 'pagado',
            'notes'        => $this->pay_notes,
        ]);

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
