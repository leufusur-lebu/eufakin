<div class="p-6 space-y-6">
    <div class="flex items-end justify-between">
        <div>
            <flux:heading size="xl">Suscripciones</flux:heading>
            <flux:text class="text-zinc-500">Gestión de planes contratados</flux:text>
        </div>
        <flux:button href="{{ route('admin.subscriptions.create') }}" variant="primary" icon="plus" wire:navigate>Nueva suscripción</flux:button>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Total</span>
                <flux:icon.credit-card class="size-5 text-zinc-400" />
            </div>
            <div class="mt-2 text-3xl font-bold">{{ $counts['all'] }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Activas</span>
                <flux:icon.check-badge class="size-5 text-emerald-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $counts['active'] }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Por vencer (7d)</span>
                <flux:icon.clock class="size-5 text-amber-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-amber-600">{{ $porVencer }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Expiradas</span>
                <flux:icon.x-circle class="size-5 text-zinc-400" />
            </div>
            <div class="mt-2 text-3xl font-bold text-zinc-500">{{ $counts['expired'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre o RUT..." class="max-w-xs" />
        @php
            $tabs = [
                '' => ['Todas', $counts['all'], 'zinc'],
                'active' => ['Activas', $counts['active'], 'emerald'],
                'paused' => ['Pausadas', $counts['paused'], 'amber'],
                'cancelled' => ['Canceladas', $counts['cancelled'], 'red'],
                'expired' => ['Expiradas', $counts['expired'], 'zinc'],
            ];
        @endphp
        <div class="flex flex-wrap gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
            @foreach ($tabs as $val => [$label, $count, $color])
                <button wire:click="setStatus('{{ $val }}')"
                    class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition {{ $status === $val ? 'bg-white shadow-sm dark:bg-zinc-900' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                    <span>{{ $label }}</span>
                    <span class="rounded-full bg-zinc-200 px-1.5 text-[10px] dark:bg-zinc-700">{{ $count }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left">Persona</th>
                    <th class="px-4 py-3 text-left">Plan</th>
                    <th class="px-4 py-3 text-left">Período</th>
                    <th class="px-4 py-3 text-left">Progreso</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($subscriptions as $s)
                    @php
                        $today = now()->startOfDay();
                        $start = $s->start_date;
                        $end = $s->end_date;
                        if ($start && $end) {
                            $totalDays = max(1, $start->diffInDays($end));
                            $elapsed = max(0, min($totalDays, $start->diffInDays($today)));
                            $pct = round(($elapsed / $totalDays) * 100);
                            $diasRest = $today->diffInDays($end, false);
                        } else {
                            $pct = 0; $diasRest = null;
                        }
                        $statusColor = match($s->status) {
                            'active' => 'green', 'paused' => 'amber',
                            'cancelled' => 'red', 'expired' => 'zinc', default => 'zinc',
                        };
                        $barColor = match(true) {
                            $s->status !== 'active' => 'bg-zinc-300',
                            $diasRest !== null && $diasRest <= 2 => 'bg-red-500',
                            $diasRest !== null && $diasRest <= 7 => 'bg-amber-500',
                            default => 'bg-emerald-500',
                        };
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                    {{ strtoupper(substr($s->person?->first_name ?? '?', 0, 1).substr($s->person?->last_name ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium">{{ $s->person?->full_name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $s->person?->rut }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $s->plan?->name }}</div>
                            <div class="text-xs text-zinc-500">${{ number_format($s->plan?->price ?? 0, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <div>{{ $s->start_date?->format('d/m/Y') }}</div>
                            <div class="text-zinc-500">→ {{ $s->end_date?->format('d/m/Y') ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-32">
                                <div class="h-1.5 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="mt-1 text-[11px] text-zinc-500">
                                    @if ($diasRest !== null && $s->status === 'active')
                                        {{ $diasRest > 0 ? $diasRest.' días restantes' : 'Vencida' }}
                                    @else
                                        {{ $pct }}%
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" :color="$statusColor">{{ ucfirst($s->status) }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <flux:button size="sm" variant="subtle" icon="scale" href="{{ route('admin.people.clinical', $s->person_id) }}?tab=measurements" wire:navigate tooltip="Registrar medición" />
                                @if (in_array($s->status, ['active','paused']))
                                    <flux:button size="sm" variant="subtle" icon="pencil-square" wire:click="openEdit({{ $s->id }})" tooltip="Editar fechas" />
                                @endif
                                <flux:button size="sm" variant="subtle" icon="trash" wire:click="delete({{ $s->id }})" wire:confirm="¿Eliminar suscripción?" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-zinc-400">Sin suscripciones que mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $subscriptions->links() }}</div>

    {{-- Modal edición de fechas --}}
    <flux:modal wire:model="editOpen" class="md:w-[640px]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Editar fechas de suscripción</flux:heading>
                @if ($edit_person_name)
                    <flux:text class="text-zinc-500">{{ $edit_person_name }} · {{ $edit_plan_name }}</flux:text>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="date" label="Fecha de inicio" wire:model="edit_start_date" />
                <flux:input type="date" label="Fecha de término" wire:model="edit_end_date" />
            </div>

            <flux:textarea
                label="Glosa / Motivo del cambio"
                wire:model="edit_glosa"
                rows="3"
                placeholder="Ej: Suspensión por viaje, congelamiento por enfermedad, ajuste por error administrativo..."
                description="Quedará registrada en el historial de la suscripción (mín. 5 caracteres)."
            />

            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50 px-4 py-2 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <span class="flex items-center gap-2 text-sm font-medium">
                        <flux:icon.clock class="size-4 text-zinc-500" />
                        Historial de cambios
                    </span>
                    <span class="rounded-full bg-zinc-200 px-2 text-xs text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">{{ count($edit_history) }}</span>
                </div>
                @if (count($edit_history) > 0)
                    <div class="max-h-72 divide-y divide-zinc-100 overflow-y-auto dark:divide-zinc-800">
                        @foreach ($edit_history as $h)
                            <div class="px-4 py-3 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $h['fecha'] }}</span>
                                    <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ $h['usuario'] }}</span>
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-zinc-600 dark:text-zinc-300">
                                    <span class="rounded bg-rose-50 px-1.5 py-0.5 text-rose-700 line-through dark:bg-rose-950/30 dark:text-rose-300">{{ $h['prev'] }}</span>
                                    <flux:icon.arrow-right class="size-3 text-zinc-400" />
                                    <span class="rounded bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300">{{ $h['new'] }}</span>
                                </div>
                                <div class="mt-1.5 rounded bg-zinc-50 px-2 py-1 italic text-zinc-600 dark:bg-zinc-800/40 dark:text-zinc-400">"{{ $h['glosa'] }}"</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-4 py-6 text-center text-xs text-zinc-400">
                        Sin cambios registrados todavía. El primer cambio en las fechas quedará aquí registrado.
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeEdit">Cancelar</flux:button>
                <flux:button variant="primary" icon="check" wire:click="saveEdit">Guardar cambios</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
