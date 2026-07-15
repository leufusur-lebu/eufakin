<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\Subscription;
use App\Models\SubscriptionDateChange;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'active';

    #[Url]
    public string $search = '';

    // --- Edición ---
    public bool $editOpen = false;
    public ?int $editingId = null;
    public ?string $edit_start_date = null;
    public ?string $edit_end_date = null;
    public string $edit_glosa = '';
    public ?string $edit_person_name = null;
    public ?string $edit_plan_name = null;
    public array $edit_history = [];

    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }

    public function setStatus(string $s): void
    {
        $this->status = $s;
        $this->resetPage();
    }

    public function openEdit(int $id): void
    {
        $s = Subscription::with(['person', 'plan', 'dateChanges.user'])->findOrFail($id);

        $this->editingId       = $s->id;
        $this->edit_start_date = $s->start_date?->format('Y-m-d');
        $this->edit_end_date   = $s->end_date?->format('Y-m-d');
        $this->edit_glosa      = '';
        $this->edit_person_name = $s->person?->full_name;
        $this->edit_plan_name   = $s->plan?->name;

        $this->edit_history = $s->dateChanges->map(fn ($c) => [
            'fecha' => $c->created_at?->format('d/m/Y H:i'),
            'usuario' => $c->user?->name ?? '—',
            'prev' => ($c->previous_start_date?->format('d/m/Y') ?? '—').' → '.($c->previous_end_date?->format('d/m/Y') ?? '—'),
            'new'  => ($c->new_start_date?->format('d/m/Y') ?? '—').' → '.($c->new_end_date?->format('d/m/Y') ?? '—'),
            'glosa' => $c->glosa,
        ])->toArray();

        $this->resetErrorBag();
        $this->editOpen = true;
    }

    public function closeEdit(): void
    {
        $this->editOpen = false;
        $this->editingId = null;
        $this->edit_glosa = '';
    }

    public function saveEdit(): void
    {
        $this->validate([
            'edit_start_date' => ['required', 'date'],
            'edit_end_date'   => ['nullable', 'date', 'after_or_equal:edit_start_date'],
            'edit_glosa'      => ['required', 'string', 'min:5', 'max:1000'],
        ], [], [
            'edit_start_date' => 'fecha de inicio',
            'edit_end_date'   => 'fecha de término',
            'edit_glosa'      => 'glosa',
        ]);

        $s = Subscription::findOrFail($this->editingId);
        $prevStart = $s->start_date?->format('Y-m-d');
        $prevEnd   = $s->end_date?->format('Y-m-d');

        $newStart = $this->edit_start_date;
        $newEnd   = $this->edit_end_date ?: null;

        // Si no hubo cambios reales en fechas, exigir cambio
        if ($prevStart === $newStart && $prevEnd === $newEnd) {
            $this->addError('edit_start_date', 'Debes modificar al menos una de las fechas.');
            return;
        }

        $s->update([
            'start_date' => $newStart,
            'end_date'   => $newEnd,
        ]);

        SubscriptionDateChange::create([
            'subscription_id'     => $s->id,
            'user_id'             => auth()->id(),
            'previous_start_date' => $prevStart,
            'previous_end_date'   => $prevEnd,
            'new_start_date'      => $newStart,
            'new_end_date'        => $newEnd,
            'glosa'               => $this->edit_glosa,
        ]);

        session()->flash('success', 'Suscripción actualizada y registrada en el historial.');
        $this->closeEdit();
    }

    public function render()
    {
        $base = Subscription::query()->with(['person', 'plan']);

        $query = (clone $base)
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->whereHas('person', fn ($p) =>
                $p->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%")
                  ->orWhere('rut', 'like', "%{$this->search}%")
            ))
            ->latest('start_date');

        $counts = [
            'all' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'paused' => (clone $base)->where('status', 'paused')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
            'expired' => (clone $base)->where('status', 'expired')->count(),
        ];

        $porVencer = (clone $base)
            ->where('status', 'active')
            ->whereBetween('end_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->count();

        return view('livewire.admin.subscriptions.index', [
            'subscriptions' => $query->paginate(15),
            'counts' => $counts,
            'porVencer' => $porVencer,
        ]);
    }

    public function delete(int $id): void
    {
        Subscription::findOrFail($id)->delete();
        session()->flash('success', 'Suscripción eliminada.');
    }
}
