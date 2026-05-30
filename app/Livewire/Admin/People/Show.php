<?php

namespace App\Livewire\Admin\People;

use App\Models\Person;
use Livewire\Component;

class Show extends Component
{
    public Person $person;

    public function mount(Person $person): void
    {
        $this->person = $person->load([
            'gymProfile',
            'kineProfile.treatments', 'kineProfile.appointments', 'kineProfile.payments',
            'esteticProfile.treatments', 'esteticProfile.appointments', 'esteticProfile.payments',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.people.show');
    }
}
