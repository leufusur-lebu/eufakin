<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\GymProfile;
use App\Models\Payment;
use App\Models\Person;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    // Persona seleccionada
    public ?int $person_id = null;

    // Búsqueda
    public string $personSearch = '';

    // Suscripción
    public ?int $plan_id = null;
    public ?string $start_date = null;
    public ?string $end_date = null;
    public string $status = 'active';

    // Pago
    public string $payment_choice = 'pending'; // pending | now
    public ?string $payment_date = null;
    public ?string $payment_notes = null;
    public array $payment_splits = [
        ['monto' => null, 'metodo' => 'efectivo'],
    ];

    public function mount(): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->payment_date = now()->format('Y-m-d');
    }

    public function selectPerson(int $id): void
    {
        $this->person_id = $id;
        $this->personSearch = '';
    }

    public function clearPerson(): void
    {
        $this->person_id = null;
    }

    public function selectPlan(int $id): void
    {
        $this->plan_id = $id;
        $this->recalculateEndDate();
        $plan = Plan::find($id);
        if ($plan) {
            $this->payment_splits = [
                ['monto' => (float) $plan->price, 'metodo' => 'efectivo'],
            ];
        }
    }

    public function addPaymentSplit(): void
    {
        $this->payment_splits[] = ['monto' => null, 'metodo' => 'efectivo'];
    }

    public function removePaymentSplit(int $index): void
    {
        if (count($this->payment_splits) > 1) {
            array_splice($this->payment_splits, $index, 1);
            $this->payment_splits = array_values($this->payment_splits);
        }
    }

    public function splitsTotal(): float
    {
        return collect($this->payment_splits)->sum(fn($s) => (float) ($s['monto'] ?? 0));
    }

    public function updatedStartDate(): void
    {
        $this->recalculateEndDate();
    }

    protected function recalculateEndDate(): void
    {
        if ($this->plan_id && $this->start_date) {
            $plan = Plan::find($this->plan_id);
            if ($plan && $plan->duration_days) {
                $this->end_date = Carbon::parse($this->start_date)
                    ->addDays($plan->duration_days)
                    ->format('Y-m-d');
            }
        }
    }

    #[Computed]
    public function searchResults()
    {
        if (strlen(trim($this->personSearch)) < 2) {
            return Person::query()
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
        }

        $term = trim($this->personSearch);
        return Person::query()
            ->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('rut', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            })
            ->limit(15)
            ->get();
    }

    #[Computed]
    public function selectedPerson(): ?Person
    {
        return $this->person_id ? Person::find($this->person_id) : null;
    }

    #[Computed]
    public function activeSubscription(): ?Subscription
    {
        if (!$this->person_id) return null;
        return Subscription::with('plan')
            ->where('person_id', $this->person_id)
            ->where('status', 'active')
            ->latest('start_date')
            ->first();
    }

    #[Computed]
    public function selectedPlan(): ?Plan
    {
        return $this->plan_id ? Plan::find($this->plan_id) : null;
    }

    public function save()
    {
        $this->validate([
            'person_id'  => ['required', 'exists:people,id'],
            'plan_id'    => ['required', 'exists:plans,id'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'     => ['required', 'in:active,paused,cancelled,expired'],
            'payment_choice' => ['required', 'in:pending,now'],
        ], [], ['person_id' => 'persona', 'plan_id' => 'plan']);

        if ($this->payment_choice === 'now') {
            $this->validate([
                'payment_date'            => ['required', 'date'],
                'payment_splits'          => ['required', 'array', 'min:1'],
                'payment_splits.*.monto'  => ['required', 'numeric', 'min:1'],
                'payment_splits.*.metodo' => ['required', 'string'],
                'payment_notes'           => ['nullable', 'string', 'max:500'],
            ], [], [
                'payment_date'            => 'fecha de pago',
                'payment_splits.*.monto'  => 'monto',
                'payment_splits.*.metodo' => 'método',
                'payment_notes'           => 'observaciones',
            ]);
            $plan = Plan::find($this->plan_id);
            if ($plan && abs($this->splitsTotal() - (float) $plan->price) > 0.01) {
                $this->addError('payment_splits', 'La suma de los montos ($'.number_format($this->splitsTotal(), 0, ',', '.').' ) debe ser igual al precio del plan ($'.number_format($plan->price, 0, ',', '.').') .');
                return;
            }
        }

        DB::transaction(function () {
            // Asegurar GymProfile
            GymProfile::firstOrCreate(
                ['person_id' => $this->person_id],
                ['registered_at' => now(), 'active' => true]
            );

            // Crear suscripción
            $subscription = Subscription::create([
                'person_id'  => $this->person_id,
                'plan_id'    => $this->plan_id,
                'start_date' => $this->start_date,
                'end_date'   => $this->end_date,
                'status'     => $this->status,
            ]);

            // Crear pago (pendiente o pagado)
            $plan = Plan::find($this->plan_id);
            if ($this->payment_choice === 'now') {
                $splits = collect($this->payment_splits)->filter(fn($s) => ($s['monto'] ?? 0) > 0);
                $splitCount = $splits->count();
                foreach ($splits as $i => $split) {
                    $noteSplit = $splitCount > 1
                        ? ($this->payment_notes ? $this->payment_notes . " (parte " . ($i+1) . "/$splitCount)" : "Pago mixto parte " . ($i+1) . "/$splitCount")
                        : $this->payment_notes;
                    Payment::create([
                        'person_id'       => $this->person_id,
                        'subscription_id' => $subscription->id,
                        'amount'          => (float) $split['monto'],
                        'payment_date'    => $this->payment_date,
                        'payment_type'    => $split['metodo'],
                        'status'          => 'pagado',
                        'notes'           => $noteSplit,
                    ]);
                }
            } else {
                Payment::create([
                    'person_id'       => $this->person_id,
                    'subscription_id' => $subscription->id,
                    'amount'          => $plan?->price ?? 0,
                    'payment_date'    => $this->start_date,
                    'payment_type'    => 'pendiente',
                    'status'          => 'pendiente',
                    'notes'           => 'Pago generado automáticamente con la suscripción',
                ]);
            }
        });

        session()->flash('success', $this->payment_choice === 'now'
            ? 'Suscripción y pago registrados correctamente.'
            : 'Suscripción creada. El pago quedó como pendiente.');
        return $this->redirectRoute('admin.subscriptions.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.subscriptions.create', [
            'plans' => Plan::orderBy('price')->get(),
        ]);
    }
}
