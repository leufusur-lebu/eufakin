<?php

namespace App\Livewire\Concerns;

use App\Models\TreatmentCategory;
use Illuminate\Support\Str;

/**
 * Trait reutilizable para gestionar el CRUD de categorías desde el catálogo
 * de un módulo (kine o estetic). El componente debe definir el método
 * `categoryModule()` que retorne 'kine' o 'estetic'.
 */
trait ManagesCategories
{
    public bool $catModalOpen = false;

    public ?int $cat_id = null;
    public string $cat_label = '';
    public string $cat_icon = 'tag';
    public string $cat_color = 'zinc';
    public int $cat_sort_order = 10;
    public bool $cat_activo = true;

    public function openCategories(): void
    {
        $this->resetCategoryForm();
        $this->catModalOpen = true;
    }

    public function resetCategoryForm(): void
    {
        $this->cat_id = null;
        $this->cat_label = '';
        $this->cat_icon = 'tag';
        $this->cat_color = 'zinc';
        $this->cat_sort_order = TreatmentCategory::where('module', $this->categoryModule())->max('sort_order') + 1 ?? 10;
        $this->cat_activo = true;
        $this->resetErrorBag(['cat_label', 'cat_icon', 'cat_color']);
    }

    public function openCategoryEdit(int $id): void
    {
        $cat = TreatmentCategory::where('module', $this->categoryModule())->findOrFail($id);
        $this->cat_id = $cat->id;
        $this->cat_label = $cat->label;
        $this->cat_icon = $cat->icon;
        $this->cat_color = $cat->color;
        $this->cat_sort_order = $cat->sort_order;
        $this->cat_activo = $cat->activo;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'cat_label' => ['required', 'string', 'max:100'],
            'cat_icon'  => ['required', 'string', 'max:50'],
            'cat_color' => ['required', 'string', 'max:20'],
            'cat_sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ], [], ['cat_label' => 'nombre', 'cat_icon' => 'ícono', 'cat_color' => 'color']);

        $module = $this->categoryModule();

        if ($this->cat_id) {
            $cat = TreatmentCategory::where('module', $module)->findOrFail($this->cat_id);
            $cat->update([
                'label' => $this->cat_label,
                'icon' => $this->cat_icon,
                'color' => $this->cat_color,
                'sort_order' => $this->cat_sort_order,
                'activo' => $this->cat_activo,
            ]);
            session()->flash('success', 'Categoría actualizada.');
        } else {
            // Generar key única a partir del label
            $baseKey = Str::slug($this->cat_label, '_');
            $key = $baseKey;
            $i = 1;
            while (TreatmentCategory::where('module', $module)->where('key', $key)->exists()) {
                $key = $baseKey.'_'.$i++;
            }

            TreatmentCategory::create([
                'module' => $module,
                'key' => $key,
                'label' => $this->cat_label,
                'icon' => $this->cat_icon,
                'color' => $this->cat_color,
                'sort_order' => $this->cat_sort_order,
                'activo' => $this->cat_activo,
            ]);
            session()->flash('success', 'Categoría agregada.');
        }
        $this->resetCategoryForm();
    }

    public function toggleCategoryActive(int $id): void
    {
        $cat = TreatmentCategory::where('module', $this->categoryModule())->findOrFail($id);
        $cat->update(['activo' => !$cat->activo]);
    }

    public function deleteCategory(int $id): void
    {
        $cat = TreatmentCategory::where('module', $this->categoryModule())->findOrFail($id);
        if ($cat->countUsages() > 0) {
            session()->flash('error', "No se puede eliminar '{$cat->label}': tiene protocolos asignados.");
            return;
        }
        $cat->delete();
        session()->flash('success', 'Categoría eliminada.');
    }
}
