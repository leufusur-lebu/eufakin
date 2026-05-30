{{-- Partial: requiere $categories (Collection) en el scope del padre Livewire.
    El padre debe usar el trait ManagesCategories que define:
    openCategoryEdit($id), saveCategory(), deleteCategory($id), toggleCategoryActive($id), resetCategoryForm()
    y las propiedades: $catModalOpen, $cat_id, $cat_label, $cat_icon, $cat_color, $cat_sort_order, $cat_activo
--}}

<flux:modal wire:model="catModalOpen" class="md:w-[820px]">
    <div class="space-y-5">
        <div>
            <flux:heading size="lg">Gestionar categorías</flux:heading>
            <flux:text class="text-zinc-500">
                Organiza el catálogo agregando, editando o desactivando categorías.
            </flux:text>
        </div>

        {{-- Lista de categorías existentes --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                    <tr>
                        <th class="px-3 py-2 text-left">Orden</th>
                        <th class="px-3 py-2 text-left">Categoría</th>
                        <th class="px-3 py-2 text-left">Color</th>
                        <th class="px-3 py-2 text-center">Estado</th>
                        <th class="px-3 py-2 text-center">Uso</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($categories as $cat)
                        @php $usages = $cat->countUsages(); @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="px-3 py-2 text-xs text-zinc-500">{{ $cat->sort_order }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex size-7 items-center justify-center rounded-lg bg-{{ $cat->color }}-100 text-{{ $cat->color }}-700 dark:bg-{{ $cat->color }}-900/40 dark:text-{{ $cat->color }}-300">
                                        <flux:icon :name="$cat->icon" class="size-4" />
                                    </span>
                                    <div>
                                        <div class="font-medium">{{ $cat->label }}</div>
                                        <div class="text-[10px] text-zinc-500">{{ $cat->key }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <span class="rounded bg-{{ $cat->color }}-100 px-1.5 py-0.5 text-[10px] text-{{ $cat->color }}-700">{{ $cat->color }}</span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if ($cat->activo)
                                    <flux:badge size="sm" color="green">Activa</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">Inactiva</flux:badge>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center text-xs">
                                @if ($usages > 0)
                                    <span class="rounded bg-zinc-100 px-1.5 py-0.5 dark:bg-zinc-800">{{ $usages }}</span>
                                @else
                                    <span class="text-zinc-400">0</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right">
                                <div class="inline-flex gap-1">
                                    <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openCategoryEdit({{ $cat->id }})" title="Editar" />
                                    <flux:button size="sm" variant="ghost" icon="{{ $cat->activo ? 'eye-slash' : 'eye' }}" wire:click="toggleCategoryActive({{ $cat->id }})" title="Activar/desactivar" />
                                    @if ($usages === 0)
                                        <flux:button size="sm" variant="ghost" icon="trash"
                                            wire:click="deleteCategory({{ $cat->id }})"
                                            wire:confirm="¿Eliminar categoría '{{ $cat->label }}'?" title="Eliminar" />
                                    @else
                                        <span title="No se puede eliminar: tiene {{ $usages }} protocolo(s) asignados">
                                            <flux:button size="sm" variant="ghost" icon="lock-closed" disabled />
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center text-zinc-400">Sin categorías</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Formulario crear/editar --}}
        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/40">
            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                {{ $cat_id ? 'Editar categoría' : 'Nueva categoría' }}
            </h4>
            <div class="grid gap-3 md:grid-cols-2">
                <flux:input wire:model="cat_label" label="Nombre" placeholder="Ej. Reductivos" />
                <flux:input type="number" min="0" wire:model="cat_sort_order" label="Orden" />

                <div>
                    <label class="mb-1 block text-sm font-medium">Ícono (Heroicon)</label>
                    <select wire:model="cat_icon" class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        @foreach (['tag','sparkles','sun','heart','fire','bolt','beaker','cpu-chip','cloud','trophy','hand-raised','scissors','user','shield-check','star','gift'] as $ico)
                            <option value="{{ $ico }}">{{ $ico }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Color</label>
                    <div class="flex flex-wrap gap-1">
                        @foreach (['zinc','sky','indigo','violet','fuchsia','pink','rose','red','orange','amber','emerald','teal'] as $col)
                            <button type="button" wire:click="$set('cat_color', '{{ $col }}')"
                                class="size-7 rounded-lg ring-2 transition
                                    bg-{{ $col }}-500
                                    {{ $cat_color === $col ? 'ring-zinc-900 dark:ring-white' : 'ring-transparent hover:ring-zinc-400' }}"
                                title="{{ $col }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="cat_activo" class="rounded">
                    Activa
                </label>
                <div class="flex gap-2">
                    @if ($cat_id)
                        <flux:button size="sm" variant="ghost" wire:click="resetCategoryForm">Cancelar edición</flux:button>
                    @endif
                    <flux:button size="sm" variant="primary" icon="check" wire:click="saveCategory">
                        {{ $cat_id ? 'Actualizar' : 'Agregar' }}
                    </flux:button>
                </div>
            </div>
            @error('cat_label') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <flux:button variant="ghost" wire:click="$set('catModalOpen', false)">Cerrar</flux:button>
        </div>
    </div>
</flux:modal>
