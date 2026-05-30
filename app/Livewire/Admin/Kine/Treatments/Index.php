<?php

namespace App\Livewire\Admin\Kine\Treatments;

use App\Models\Kine\Treatment;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $estado = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingEstado(): void { $this->resetPage(); }

    public function render()
    {
        $query = Treatment::query()
            ->with(['kineProfile.person', 'professional'])
            ->when($this->search, fn ($q) => $q->where('diagnostico', 'like', "%{$this->search}%"))
            ->when($this->estado, fn ($q) => $q->where('estado', $this->estado))
            ->latest();

        return view('livewire.admin.kine.treatments.index', [
            'treatments' => $query->paginate(15),
        ]);
    }

    public function delete(int $id): void
    {
        Treatment::findOrFail($id)->delete();
        session()->flash('success', 'Tratamiento eliminado.');
    }
}
