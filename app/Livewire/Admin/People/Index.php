<?php

namespace App\Livewire\Admin\People;

use App\Models\Person;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $module = ''; // gym, kine, estetic, or ''

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingModule(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Person::query()
            ->with(['gymProfile', 'kineProfile', 'esteticProfile'])
            ->search($this->search);

        if ($this->module === 'gym')     $query->whereHas('gymProfile');
        if ($this->module === 'kine')    $query->whereHas('kineProfile');
        if ($this->module === 'estetic') $query->whereHas('esteticProfile');

        $people = $query->orderBy('last_name')->paginate(15);

        return view('livewire.admin.people.index', compact('people'));
    }

    public function delete(int $id): void
    {
        Person::findOrFail($id)->delete();
        session()->flash('success', 'Persona eliminada correctamente.');
    }
}
