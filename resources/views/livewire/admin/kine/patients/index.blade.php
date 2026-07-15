<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Pacientes Kinesiología</flux:heading>
            <flux:text class="text-zinc-500">Ficha integral del paciente — tratamientos, sesiones, evolución y estado financiero</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('admin.kine.appointments.create') }}" icon="calendar-days" wire:navigate>Agendar</flux:button>
            <flux:button href="{{ route('admin.kine.tipos-tratamientos.index') }}" variant="primary" icon="sparkles" wire:navigate>Aplicar protocolo</flux:button>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre, RUT o email..." class="max-w-sm" />
        @php
            $tabs = [
                'active'       => ['En tratamiento',        $counts['active'],  'sky'],
                'no_next'      => ['Sin próxima cita',      $counts['no_next'], 'amber'],
                'with_balance' => ['Con saldo pendiente',   null,               'orange'],
                'finished'     => ['Tratamiento terminado', null,               'emerald'],
                'all'          => ['Todos',                 $counts['all'],     'zinc'],
            ];
        @endphp
        <div class="flex flex-wrap gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
            @foreach ($tabs as $val => [$label, $count, $color])
                <button wire:click="setFilter('{{ $val }}')"
                    class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition
                        {{ $filter === $val
                            ? 'bg-white shadow-sm text-'.$color.'-700 dark:bg-zinc-900 dark:text-'.$color.'-300'
                            : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                    <span>{{ $label }}</span>
                    @if ($count !== null)
                        <span class="rounded-full bg-zinc-200 px-1.5 text-[10px] dark:bg-zinc-700">{{ $count }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left">Paciente</th>
                    <th class="px-4 py-3 text-left">Tratamiento activo</th>
                    <th class="px-4 py-3 text-center">Avance</th>
                    <th class="px-4 py-3 text-center">Realizadas</th>
                    <th class="px-4 py-3 text-center">Pendientes</th>
                    <th class="px-4 py-3 text-left">Próxima sesión</th>
                    <th class="px-4 py-3 text-left">Estado financiero</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($patients as $p)
                    @php
                        $person = $p->person;
                        $t      = $p->active_treatment;
                        $next   = $p->next_appointment;

                        $realizadas = $t?->sesiones_realizadas ?? 0;
                        $totales    = $t?->sesiones_totales    ?? 0;
                        $pendientes = max(0, $totales - $realizadas);
                        $progress   = $totales > 0 ? round(($realizadas / $totales) * 100) : 0;

                        $tratamientoNombre = $t?->tipoTratamiento?->nombre ?? $t?->diagnostico ?? '—';
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">

                        {{-- Paciente --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-indigo-500 text-xs font-bold text-white">
                                    {{ strtoupper(substr($person->first_name, 0, 1).substr($person->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium">{{ $person->full_name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $person->rut }}</div>
                                    @if ($p->health_insurance)
                                        <span class="inline-flex items-center gap-0.5 rounded bg-sky-100 px-1 py-0.5 text-[10px] font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                            <flux:icon.shield-check class="size-2.5" /> {{ $p->health_insurance }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Tratamiento --}}
                        <td class="px-4 py-3">
                            @if ($t)
                                <div class="font-medium">{{ $tratamientoNombre }}</div>
                                @if ($t->diagnostico && $t->diagnostico !== $tratamientoNombre)
                                    <div class="text-xs text-zinc-500">{{ $t->diagnostico }}</div>
                                @endif
                            @else
                                <span class="text-zinc-400">Sin tratamiento activo</span>
                            @endif
                        </td>

                        {{-- Avance --}}
                        <td class="px-4 py-3 text-center">
                            @if ($t)
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-24">
                                        <div class="h-2 overflow-hidden rounded-full bg-sky-100 dark:bg-sky-900/40">
                                            <div class="h-full rounded-full bg-gradient-to-r from-sky-400 to-indigo-500 transition-all"
                                                 style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400">{{ $progress }}%</span>
                                </div>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>

                        {{-- Realizadas --}}
                        <td class="px-4 py-3 text-center">
                            @if ($t)
                                <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">{{ $realizadas }}</span>
                                <span class="text-xs text-zinc-400">/{{ $totales }}</span>
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>

                        {{-- Pendientes --}}
                        <td class="px-4 py-3 text-center">
                            @if ($t)
                                @if ($pendientes > 0)
                                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        {{ $pendientes }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                                        <flux:icon.check-circle class="size-4" /> Completo
                                    </span>
                                @endif
                            @else
                                <span class="text-zinc-300">—</span>
                            @endif
                        </td>

                        {{-- Próxima sesión --}}
                        <td class="px-4 py-3">
                            @if ($next)
                                <div class="flex items-start gap-2">
                                    <flux:icon.calendar class="mt-0.5 size-4 shrink-0 text-sky-500" />
                                    <div>
                                        <div class="font-medium">{{ $next->inicio->locale('es')->isoFormat('ddd D MMM') }}</div>
                                        <div class="text-xs text-zinc-500">{{ $next->inicio->format('H:i') }} h</div>
                                    </div>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-1.5 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                    <flux:icon.exclamation-circle class="size-3" /> Sin agendar
                                </span>
                            @endif
                        </td>

                        {{-- Estado financiero --}}
                        <td class="px-4 py-3">
                            @if ($p->balance > 0)
                                <span class="inline-flex items-center gap-1 rounded bg-red-100 px-1.5 py-0.5 text-[11px] font-medium text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                    Debe ${{ number_format($p->balance, 0, ',', '.') }}
                                </span>
                            @elseif ($t)
                                <span class="inline-flex items-center gap-1 rounded bg-emerald-100 px-1.5 py-0.5 text-[11px] font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <flux:icon.check-circle class="size-3" /> Al día
                                </span>
                            @else
                                <span class="text-xs text-zinc-300">—</span>
                            @endif
                        </td>

                        {{-- Acciones --}}
                        <td class="px-4 py-3 text-right">
                            <flux:button size="sm" variant="subtle" href="{{ route('admin.kine.patients.show', $p) }}" wire:navigate icon="eye" tooltip="Ver ficha" />
                            @if (!$next && $t && $pendientes > 0)
                                <flux:button size="sm" variant="subtle" href="{{ route('admin.kine.appointments.create') }}" wire:navigate icon="calendar-plus" tooltip="Agendar sesión" />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center">
                            <flux:icon.user-group class="mx-auto size-10 text-zinc-300" />
                            <p class="mt-3 text-sm text-zinc-500">No hay pacientes que coincidan con el filtro.</p>
                            @if ($filter === 'active')
                                <a href="{{ route('admin.kine.tipos-tratamientos.index') }}" wire:navigate
                                   class="mt-3 inline-flex items-center gap-1 rounded-md bg-sky-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-sky-700">
                                    <flux:icon.sparkles class="size-3.5" /> Aplicar un protocolo
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $patients->links() }}</div>
</div>
