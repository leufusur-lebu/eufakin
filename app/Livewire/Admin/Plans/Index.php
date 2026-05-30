<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $plans = Plan::withCount([
            'subscriptions',
            'subscriptions as active_count' => fn ($q) => $q->where('status', 'active'),
        ])->orderBy('price')->get();

        return view('livewire.admin.plans.index', [
            'plans' => $plans,
            'totalPlanes' => $plans->count(),
            'totalActivas' => (int) $plans->sum('active_count'),
            'ingresoPotencial' => (float) $plans->sum(fn ($p) => $p->active_count * $p->price),
        ]);
    }

    public function deletePlan($planId)
    {
        Plan::findOrFail($planId)->delete();
        session()->flash('success', 'Plan eliminado exitosamente.');
    }
}
