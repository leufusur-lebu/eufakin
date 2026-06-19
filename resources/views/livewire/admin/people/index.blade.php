<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Personas</flux:heading>
            <flux:text class="text-zinc-500">Listado unificado de clientes y pacientes</flux:text>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-green-100 p-3 text-green-700">{{ session('success') }}</div>
    @endif

    <div class="flex gap-3">
        <flux:input placeholder="Buscar por nombre, RUT, email..." wire:model.live.debounce.400ms="search" icon="magnifying-glass" class="flex-1" />
        <flux:select wire:model.live="module" placeholder="Todos los módulos">
            <flux:select.option value="">Todos</flux:select.option>
            <flux:select.option value="gym">GYM</flux:select.option>
            <flux:select.option value="kine">Kinesiología</flux:select.option>
            <flux:select.option value="estetic">Estética</flux:select.option>
        </flux:select>
    </div>

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-3 py-2 text-left">Nombre</th>
                    <th class="px-3 py-2 text-left">RUT</th>
                    <th class="px-3 py-2 text-left">Contacto</th>
                    <th class="px-3 py-2 text-left">Módulos</th>
                    <th class="px-3 py-2 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($people as $p)
                    <tr>
                        <td class="px-3 py-2 font-medium">{{ $p->full_name }}</td>
                        <td class="px-3 py-2">{{ \App\Support\RutHelper::format($p->rut) }}</td>
                        <td class="px-3 py-2">
                            <div>{{ $p->phone }}</div>
                            <div class="text-xs text-zinc-500">{{ $p->email }}</div>
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex gap-1">
                                @if ($p->gymProfile)     <flux:badge color="blue"   size="sm">GYM</flux:badge> @endif
                                @if ($p->kineProfile)    <flux:badge color="green"  size="sm">KINE</flux:badge> @endif
                                @if ($p->esteticProfile) <flux:badge color="pink"   size="sm">EST</flux:badge> @endif
                            </div>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <flux:button size="sm" href="{{ route('admin.people.show', $p) }}" wire:navigate>Ver</flux:button>
                            <flux:button size="sm" variant="ghost" icon="scale" href="{{ route('admin.people.clinical', $p) }}?tab=measurements" wire:navigate title="Registrar medición">Medición</flux:button>
                            <flux:button size="sm" variant="subtle" wire:click="delete({{ $p->id }})" wire:confirm="¿Eliminar esta persona?">Eliminar</flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-zinc-500">No hay personas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $people->links() }}</div>
</div>
