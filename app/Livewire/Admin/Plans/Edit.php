<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use App\Models\Plan;

class Edit extends Component
{
    public Plan $plan;

    public $name;
    public $description;
    public $price;
    public $duration_days;

    public function mount(Plan $plan)
    {
        $this->plan = $plan;
        $this->name = $plan->name;
        $this->description = $plan->description;
        $this->price = $plan->price;
        $this->duration_days = $plan->duration_days;
    }

    public function update()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        $this->plan->update($validated);

        session()->flash('success', 'Plan actualizado exitosamente.');
        return redirect()->route('admin.plans.index');
    }

    public function render()
    {
        return view('livewire.admin.plans.edit');
    }
}
