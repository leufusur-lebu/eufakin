<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use App\Models\Person;
use App\Models\Subscription;
use Livewire\Component;

class Create extends Component
{
    public ?int $person_id = null;
    public ?int $subscription_id = null;
    public ?string $payment_date = null;
    public float $amount = 0;
    public string $payment_type = 'efectivo';
    public string $status = 'pagado';
    public ?string $notes = null;

    public function mount(): void
    {
        $this->payment_date = now()->format('Y-m-d');
    }

    public function updatedSubscriptionId($id): void
    {
        if ($s = Subscription::with('plan')->find($id)) {
            $this->amount = (float) ($s->plan?->price ?? 0);
        }
    }

    public function save()
    {
        $data = $this->validate([
            'person_id' => ['required', 'exists:people,id'],
            'subscription_id' => ['nullable', 'exists:subscriptions,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_type' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:pendiente,pagado,anulado'],
            'notes' => ['nullable', 'string'],
        ]);

        Payment::create($data);
        session()->flash('success', 'Pago registrado.');
        return $this->redirectRoute('admin.payments.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.payments.create', [
            'people' => Person::whereHas('gymProfile')->orderBy('last_name')->get(),
            'subscriptions' => $this->person_id
                ? Subscription::with('plan')->where('person_id', $this->person_id)->get()
                : collect(),
        ]);
    }
}
