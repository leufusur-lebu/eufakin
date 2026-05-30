<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Pacientes Estética</flux:heading>
            <flux:text class="text-zinc-500">Ficha integral de cada paciente — tratamientos, sesiones y estado financiero</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('admin.estetic.appointments.create') }}" icon="calendar-days" wire:navigate>Agendar</flux:button>
            <flux:button href="{{ route('admin.estetic.treatments.create') }}" variant="primary" icon="plus" wire:navigate>Nuevo tratamiento</flux:button>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre, RUT o email..." class="max-w-sm" />
        @php
            $tabs = [
                'all'          => ['Todos',                $counts['all']],
                'active'       => ['En tratamiento',       $counts['active']],
                'no_next'      => ['Sin próxima cita',     $counts['no_next']],
                'with_balance' => ['Con saldo pendiente',  null],
                'finished'     => ['Protocolo terminado',  null],
            ];
        @endphp
        <div class="flex flex-wrap gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
            @foreach ($tabs as $val => [$label, $count])
                <button wire:click="setFilter('{{ $val }}')"
                    class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition {{ $filter === $val ? 'bg-white shadow-sm dark:bg-zinc-900' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                    <span>{{ $label }}</span>
                    @if ($count !== null)
                        <span class="rounded-full bg-zinc-200 px-1.5 text-[10px] dark:bg-zinc-700">{{ $count }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Grid de pacientes --}}
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($patients as $p)
            @php
                $person = $p->person;
                $next = $p->next_appointment;
                $t = $p->active_treatment;
                $progress = $t && $t->sesiones_totales ? round(($t->sesiones_realizadas / $t->sesiones_totales) * 100) : 0;
            @endphp
            <a href="{{ route('admin.estetic.patients.show', $p) }}" wire:navigate
                class="group rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-pink-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-pink-700">
                <div class="flex items-start gap-3">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-pink-400 to-rose-500 text-sm font-bold text-white">
                        {{ strtoupper(substr($person->first_name, 0, 1).substr($person->last_name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-semibold">{{ $person->full_name }}</div>
                        <div class="truncate text-xs text-zinc-500">{{ $person->rut }} · {{ $person->phone ?: 'sin teléfono' }}</div>
                        @if ($person->clinicalProfile?->allergies)
                            <div class="mt-1 inline-flex items-center gap-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                                <flux:icon.exclamation-triangle class="size-3" /> Alergias
                            </div>
                        @endif
                    </div>
                    <flux:icon.chevron-right class="size-4 shrink-0 text-zinc-400 transition group-hover:translate-x-0.5 group-hover:text-pink-500" />
                </div>

                {{-- Tratamiento activo --}}
                @if ($t)
                    <div class="mt-4 rounded-lg border border-pink-100 bg-pink-50/50 p-3 dark:border-pink-900/40 dark:bg-pink-950/20">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-pink-800 dark:text-pink-300">{{ $t->tipoTratamiento?->nombre ?? $t->zona_tratada }}</span>
                            <span class="text-pink-600 dark:text-pink-400">{{ $t->sesiones_realizadas }}/{{ $t->sesiones_totales }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-pink-100 dark:bg-pink-900/50">
                            <div class="h-full rounded-full bg-gradient-to-r from-pink-400 to-rose-500" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-lg border border-dashed border-zinc-200 p-3 text-center text-xs text-zinc-400 dark:border-zinc-700">
                        Sin tratamiento activo
                    </div>
                @endif

                {{-- Footer info --}}
                <div class="mt-3 flex items-center justify-between text-xs">
                    <div>
                        @if ($next)
                            <div class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                <flux:icon.calendar class="size-3.5" />
                                {{ $next->inicio->format('d/m H:i') }}
                            </div>
                        @else
                            <div class="text-zinc-400">Sin próxima cita</div>
                        @endif
                    </div>
                    <div>
                        @if ($p->balance > 0)
                            <span class="rounded bg-amber-100 px-1.5 py-0.5 font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                Saldo ${{ number_format($p->balance, 0, ',', '.') }}
                            </span>
                        @else
                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                Al día
                            </span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="md:col-span-2 xl:col-span-3 rounded-xl border border-dashed border-zinc-200 p-12 text-center dark:border-zinc-700">
                <flux:icon.user-group class="mx-auto size-10 text-zinc-300" />
                <p class="mt-3 text-sm text-zinc-500">No hay pacientes que coincidan con el filtro.</p>
            </div>
        @endforelse
    </div>

    <div>{{ $patients->links() }}</div>
</div>
