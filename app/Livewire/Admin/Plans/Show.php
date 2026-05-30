<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use App\Models\Plan;

class Show extends Component
{
    public Plan $plan;

    public function mount(Plan $plan)
    {
        $this->plan = $plan;
    }

    public function render()
    {
        return view('livewire.admin.plans.show');
    }
}
