<?php

namespace App\Livewire\Admin\Estetic\TiposTratamientos;

use App\Livewire\Concerns\ManagesCategories;
use App\Models\Estetic\TipoTratamiento;
use App\Models\TreatmentCategory;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    use ManagesCategories;

    protected function categoryModule(): string
    {
        return 'estetic';
    }

    #[Url(as: 'q', except: '')] public string $search = '';
    #[Url(as: 'categoria', except: '')] public string $categoria = '';
    #[Url(as: 'estado', except: '')] public string $estado = '';

    public function deleteTipo(int $tipoId): void
    {
        TipoTratamiento::findOrFail($tipoId)->delete();
        session()->flash('success', 'Protocolo eliminado.');
    }

    public function toggleActivo(int $id): void
    {
        $t = TipoTratamiento::findOrFail($id);
        $t->update(['activo' => !$t->activo]);
    }

    public function render()
    {
        $term = trim($this->search);

        $tipos = TipoTratamiento::query()
            ->when($term !== '', fn ($q) => $q->where('nombre', 'like', "%{$term}%"))
            ->when($this->categoria !== '', fn ($q) => $q->where('categoria', $this->categoria))
            ->when($this->estado === 'activos',   fn ($q) => $q->where('activo', true))
            ->when($this->estado === 'inactivos', fn ($q) => $q->where('activo', false))
            ->withCount('tratamientos')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');

        $counts = [
            'all'        => TipoTratamiento::count(),
            'activos'    => TipoTratamiento::where('activo', true)->count(),
            'inactivos'  => TipoTratamiento::where('activo', false)->count(),
        ];

        $categoriasDef = TreatmentCategory::defFor('estetic');
        $categories    = TreatmentCategory::where('module', 'estetic')->orderBy('sort_order')->get();

        return view('livewire.admin.estetic.tipos-tratamientos.index', [
            'grupos' => $tipos,
            'counts' => $counts,
            'categoriasDef' => $categoriasDef,
            'categories' => $categories,
        ]);
    }
}
