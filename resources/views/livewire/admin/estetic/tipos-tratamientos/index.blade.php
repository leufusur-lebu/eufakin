<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Catálogo de protocolos</flux:heading>
            <flux:text class="text-zinc-500">Servicios profesionales con plantilla de sesiones lista para aplicar a un paciente</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button wire:click="openCategories" variant="ghost" icon="adjustments-horizontal">Categorías</flux:button>
            <flux:button href="{{ route('admin.estetic.tipos-tratamientos.create') }}" variant="primary" icon="plus" wire:navigate>Nuevo protocolo</flux:button>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar protocolo..." class="max-w-xs" />
        <flux:select wire:model.live="categoria" class="max-w-[200px]">
            <flux:select.option value="">Todas las categorías</flux:select.option>
            @foreach ($categoriasDef as $key => [$label, $icon, $color])
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        @php $tabs = ['' => ['Todos', $counts['all']], 'activos' => ['Activos', $counts['activos']], 'inactivos' => ['Inactivos', $counts['inactivos']]]; @endphp
        <div class="flex flex-wrap gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
            @foreach ($tabs as $val => [$label, $count])
                <button wire:click="$set('estado', '{{ $val }}')"
                    class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition {{ $estado === $val ? 'bg-white shadow-sm dark:bg-zinc-900' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                    <span>{{ $label }}</span>
                    <span class="rounded-full bg-zinc-200 px-1.5 text-[10px] dark:bg-zinc-700">{{ $count }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Grupos por categoría --}}
    @forelse ($grupos as $cat => $items)
        @php $def = $categoriasDef[$cat] ?? ['Otros', 'tag', 'zinc']; [$catLabel, $catIcon, $catColor] = $def; @endphp
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="flex size-8 items-center justify-center rounded-lg bg-{{ $catColor }}-100 text-{{ $catColor }}-700 dark:bg-{{ $catColor }}-900/40 dark:text-{{ $catColor }}-300">
                    <flux:icon :name="$catIcon" class="size-4" />
                </span>
                <h2 class="text-sm font-bold uppercase tracking-wide text-zinc-700 dark:text-zinc-200">{{ $catLabel }}</h2>
                <span class="rounded-full bg-zinc-100 px-2 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">{{ count($items) }}</span>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($items as $t)
                    <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 {{ !$t->activo ? 'opacity-60' : '' }}">
                        <div class="h-1.5" style="background: {{ $t->color ?? '#ec4899' }}"></div>
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-semibold">{{ $t->nombre }}</h3>
                                    @if ($t->descripcion)
                                        <p class="line-clamp-2 mt-1 text-xs text-zinc-500">{{ $t->descripcion }}</p>
                                    @endif
                                </div>
                                @if (!$t->activo)
                                    <span class="shrink-0 rounded bg-zinc-200 px-1.5 py-0.5 text-[10px] font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">Inactivo</span>
                                @endif
                            </div>

                            {{-- Plantilla --}}
                            <div class="mt-3 grid grid-cols-3 gap-2 rounded-lg bg-pink-50/60 p-2 text-center text-xs dark:bg-pink-950/20">
                                <div>
                                    <div class="font-bold text-pink-700 dark:text-pink-300">{{ $t->sesiones_recomendadas ?? 1 }}</div>
                                    <div class="text-[10px] text-zinc-500">sesiones</div>
                                </div>
                                <div>
                                    <div class="font-bold text-pink-700 dark:text-pink-300">{{ $t->intervalo_dias ?? 7 }}d</div>
                                    <div class="text-[10px] text-zinc-500">intervalo</div>
                                </div>
                                <div>
                                    <div class="font-bold text-pink-700 dark:text-pink-300">{{ $t->duracion_minutos }}'</div>
                                    <div class="text-[10px] text-zinc-500">duración</div>
                                </div>
                            </div>

                            {{-- Precio --}}
                            <div class="mt-3 flex flex-wrap items-baseline gap-2">
                                <span class="text-lg font-bold">${{ number_format($t->precio_base, 0, ',', '.') }}</span>
                                <span class="text-xs text-zinc-500">total</span>
                                @if (($t->sesiones_recomendadas ?? 1) > 0)
                                    <span class="ml-auto rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        ${{ number_format($t->precio_base / max(1, $t->sesiones_recomendadas), 0, ',', '.') }} / sesión
                                    </span>
                                @endif
                            </div>

                            @if ($t->contraindicaciones)
                                <div class="mt-3 flex items-start gap-1.5 rounded bg-rose-50 p-2 text-[10px] text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">
                                    <flux:icon.exclamation-triangle class="size-3 shrink-0 mt-0.5" />
                                    <span class="line-clamp-2">{{ $t->contraindicaciones }}</span>
                                </div>
                            @endif

                            {{-- Acciones --}}
                            <div class="mt-4 flex gap-1">
                                <flux:button href="{{ route('admin.estetic.protocols.apply', $t) }}" wire:navigate variant="primary" size="sm" icon="sparkles" class="flex-1">Aplicar</flux:button>
                                <flux:button href="{{ route('admin.estetic.tipos-tratamientos.edit', $t) }}" wire:navigate variant="ghost" size="sm" icon="pencil" />
                                <flux:button wire:click="toggleActivo({{ $t->id }})" variant="ghost" size="sm" icon="{{ $t->activo ? 'eye-slash' : 'eye' }}" />
                                @if ($t->tratamientos_count === 0)
                                    <flux:button wire:click="deleteTipo({{ $t->id }})" wire:confirm="¿Eliminar protocolo?" variant="ghost" size="sm" icon="trash" />
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
            <flux:icon.sparkles class="mx-auto size-10 text-zinc-300" />
            <p class="mt-3 text-sm text-zinc-500">No hay protocolos que coincidan.</p>
            <flux:button href="{{ route('admin.estetic.tipos-tratamientos.create') }}" variant="primary" size="sm" class="mt-3" wire:navigate>Crear primer protocolo</flux:button>
        </div>
    @endforelse

    {{-- Modal CRUD categorías --}}
    @include('partials.category-manager-modal')
</div>
