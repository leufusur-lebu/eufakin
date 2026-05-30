<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Tratamientos estética</flux:heading>
        <flux:button href="{{ route('admin.estetic.treatments.create') }}" variant="primary" icon="plus" wire:navigate>Nuevo</flux:button>
    </div>

    @if (session('success')) <div class="rounded bg-green-100 p-3 text-green-700">{{ session('success') }}</div> @endif

    <div class="flex gap-3">
        <flux:input placeholder="Buscar zona tratada..." wire:model.live.debounce.400ms="search" icon="magnifying-glass" class="flex-1" />
        <flux:select wire:model.live="estado">
            <flux:select.option value="">Todos los estados</flux:select.option>
            <flux:select.option value="activo">Activo</flux:select.option>
            <flux:select.option value="finalizado">Finalizado</flux:select.option>
            <flux:select.option value="suspendido">Suspendido</flux:select.option>
            <flux:select.option value="cancelado">Cancelado</flux:select.option>
        </flux:select>
    </div>

    <div class="overflow-x-auto rounded-lg border dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    <th class="px-3 py-2 text-left">Paciente</th>
                    <th class="px-3 py-2 text-left">Tipo</th>
                    <th class="px-3 py-2 text-left">Zona</th>
                    <th class="px-3 py-2 text-left">Sesiones</th>
                    <th class="px-3 py-2 text-left">Costo total</th>
                    <th class="px-3 py-2 text-left">Estado</th>
                    <th class="px-3 py-2 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-zinc-700">
                @forelse ($treatments as $t)
                    <tr>
                        <td class="px-3 py-2">{{ $t->esteticProfile?->person?->full_name }}</td>
                        <td class="px-3 py-2">{{ $t->tipoTratamiento?->nombre ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $t->zona_tratada }}</td>
                        <td class="px-3 py-2">{{ $t->sesiones_realizadas }}/{{ $t->sesiones_totales }}</td>
                        <td class="px-3 py-2">${{ number_format($t->costo_total, 0, ',', '.') }}</td>
                        <td class="px-3 py-2">
                            <flux:badge size="sm" :color="match($t->estado){'activo'=>'green','finalizado'=>'blue','suspendido'=>'amber','cancelado'=>'red',default=>'zinc'}">{{ ucfirst($t->estado) }}</flux:badge>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <flux:button size="sm" href="{{ route('admin.estetic.treatments.edit', $t) }}" wire:navigate>Editar</flux:button>
                            <flux:button size="sm" variant="subtle" wire:click="delete({{ $t->id }})" wire:confirm="¿Eliminar?">Eliminar</flux:button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-6 text-center text-zinc-500">Sin tratamientos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $treatments->links() }}</div>
</div>
