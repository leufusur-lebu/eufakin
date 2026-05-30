<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;
use App\Models\Plan;

class Create extends Component
{
    public $name;
    public $description;
    public $price;
    public $duration_days;

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        Plan::create($validated);

        session()->flash('success', 'Plan creado exitosamente.');
        return redirect()->route('admin.plans.index');
    }

    public function render()
    {
        return view('livewire.admin.plans.create');
    }
}
