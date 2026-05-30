<?php

namespace App\Livewire\Admin\Kine\Patients;

use App\Models\KineProfile;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $filter = 'all'; // all|active|no_next|with_balance|finished

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilter(): void { $this->resetPage(); }
    public function setFilter(string $f): void { $this->filter = $f; $this->resetPage(); }

    public function render()
    {
        $base = KineProfile::query()
            ->with(['person'])
            ->withCount([
                'treatments as treatments_active_count' => fn ($q) => $q->where('estado', 'activo'),
                'treatments as treatments_total_count',
            ]);

        if ($this->search !== '') {
            $term = $this->search;
            $base->whereHas('person', function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('rut', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        match ($this->filter) {
            'active'        => $base->whereHas('treatments', fn ($q) => $q->where('estado', 'activo')),
            'no_next'       => $base->whereHas('treatments', fn ($q) => $q->where('estado', 'activo'))
                                    ->whereDoesntHave('appointments', fn ($q) => $q->where('inicio', '>=', now())->whereIn('estado', ['pendiente', 'confirmado'])),
            'with_balance'  => $base->whereHas('treatments', fn ($q) => $q->where('estado', 'activo')),
            'finished'      => $base->whereHas('treatments', fn ($q) => $q->where('estado', 'finalizado'))
                                    ->whereDoesntHave('treatments', fn ($q) => $q->where('estado', 'activo')),
            default         => null,
        };

        $patients = $base->orderByDesc('updated_at')->paginate(15);

        $patients->getCollection()->transform(function ($p) {
            $next = $p->appointments()
                ->where('inicio', '>=', now())
                ->whereIn('estado', ['pendiente', 'confirmado'])
                ->orderBy('inicio')
                ->first();
            $activeTreatment = $p->treatments()->where('estado', 'activo')->orderByDesc('id')->first();
            $balance = 0;
            if ($activeTreatment) {
                $balance = (float) $activeTreatment->costo_total - (float) $activeTreatment->payments()->where('estado', 'pagado')->sum('monto');
            }
            $p->setAttribute('next_appointment', $next);
            $p->setAttribute('active_treatment', $activeTreatment);
            $p->setAttribute('balance', $balance);
            return $p;
        });

        if ($this->filter === 'with_balance') {
            $filtered = $patients->getCollection()->filter(fn ($p) => $p->balance > 0)->values();
            $patients->setCollection($filtered);
        }

        $totalCount = KineProfile::count();
        $activeCount = KineProfile::whereHas('treatments', fn ($q) => $q->where('estado', 'activo'))->count();
        $noNextCount = KineProfile::whereHas('treatments', fn ($q) => $q->where('estado', 'activo'))
            ->whereDoesntHave('appointments', fn ($q) => $q->where('inicio', '>=', now())->whereIn('estado', ['pendiente', 'confirmado']))
            ->count();

        return view('livewire.admin.kine.patients.index', [
            'patients' => $patients,
            'counts' => [
                'all' => $totalCount,
                'active' => $activeCount,
                'no_next' => $noNextCount,
            ],
        ]);
    }
}
