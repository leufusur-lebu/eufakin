<?php

namespace App\Livewire\Admin\Estetic\Patients;

use App\Models\EsteticProfile;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $filter = 'active'; // all|active|no_next|with_balance|finished

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilter(): void { $this->resetPage(); }
    public function setFilter(string $f): void { $this->filter = $f; $this->resetPage(); }

    public function render()
    {
        $base = EsteticProfile::query()
            ->with([
                'person.clinicalProfile',
                'treatments' => fn ($q) => $q->where('estado', 'activo')
                    ->with('tipoTratamiento')
                    ->orderByDesc('id')
                    ->limit(1),
            ])
            ->withCount([
                'treatments as treatments_active_count' => fn ($q) => $q->where('estado', 'activo'),
                'treatments as treatments_total_count',
            ]);

        // Búsqueda por persona
        if ($this->search !== '') {
            $term = $this->search;
            $base->whereHas('person', function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('rut', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        // Filtros rápidos
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

        // Datos enriquecidos por fila
        $patients->getCollection()->transform(function ($p) {
            $activeTreatment = $p->treatments->first(); // eager loaded (activo, limit 1)

            $next = $p->appointments()
                ->where('inicio', '>=', now())
                ->whereIn('estado', ['pendiente', 'confirmado'])
                ->orderBy('inicio')
                ->first();

            $balance = 0;
            if ($activeTreatment) {
                $balance = (float) $activeTreatment->costo_total
                    - (float) $activeTreatment->payments()->where('estado', 'pagado')->sum('monto');
            }

            $p->setAttribute('next_appointment', $next);
            $p->setAttribute('active_treatment', $activeTreatment);
            $p->setAttribute('balance', $balance);
            return $p;
        });

        // Filtro post-query para "with_balance"
        if ($this->filter === 'with_balance') {
            $filtered = $patients->getCollection()->filter(fn ($p) => $p->balance > 0)->values();
            $patients->setCollection($filtered);
        }

        // Counts para los tabs
        $totalCount = EsteticProfile::count();
        $activeCount = EsteticProfile::whereHas('treatments', fn ($q) => $q->where('estado', 'activo'))->count();
        $noNextCount = EsteticProfile::whereHas('treatments', fn ($q) => $q->where('estado', 'activo'))
            ->whereDoesntHave('appointments', fn ($q) => $q->where('inicio', '>=', now())->whereIn('estado', ['pendiente', 'confirmado']))
            ->count();

        return view('livewire.admin.estetic.patients.index', [
            'patients' => $patients,
            'counts' => [
                'all' => $totalCount,
                'active' => $activeCount,
                'no_next' => $noNextCount,
            ],
        ]);
    }
}
