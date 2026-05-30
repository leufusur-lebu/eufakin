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

    <div class="flex flex-wrap items-center gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre, RUT o email..." class="max-w-sm" />
        @php
            $tabs = [
                'all'          => ['Todos',                $counts['all']],
                'active'       => ['En tratamiento',       $counts['active']],
                'no_next'      => ['Sin próxima cita',     $counts['no_next']],
                'with_balance' => ['Con saldo pendiente',  null],
                'finished'     => ['Tratamiento terminado',null],
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

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($patients as $p)
            @php
                $person = $p->person;
                $next = $p->next_appointment;
                $t = $p->active_treatment;
                $progress = $t && $t->sesiones_totales ? round(($t->sesiones_realizadas / $t->sesiones_totales) * 100) : 0;
            @endphp
            <a href="{{ route('admin.kine.patients.show', $p) }}" wire:navigate
                class="group rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-sky-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-sky-700">
                <div class="flex items-start gap-3">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-indigo-500 text-sm font-bold text-white">
                        {{ strtoupper(substr($person->first_name, 0, 1).substr($person->last_name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-semibold">{{ $person->full_name }}</div>
                        <div class="truncate text-xs text-zinc-500">{{ $person->rut }} · {{ $person->phone ?: 'sin teléfono' }}</div>
                        @if ($p->health_insurance)
                            <div class="mt-1 inline-flex items-center gap-1 rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                <flux:icon.shield-check class="size-3" /> {{ $p->health_insurance }}
                            </div>
                        @endif
                    </div>
                    <flux:icon.chevron-right class="size-4 shrink-0 text-zinc-400 transition group-hover:translate-x-0.5 group-hover:text-sky-500" />
                </div>

                @if ($t)
                    <div class="mt-4 rounded-lg border border-sky-100 bg-sky-50/50 p-3 dark:border-sky-900/40 dark:bg-sky-950/20">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-sky-800 dark:text-sky-300">{{ $t->tipoTratamiento?->nombre ?? $t->diagnostico }}</span>
                            <span class="text-sky-600 dark:text-sky-400">{{ $t->sesiones_realizadas }}/{{ $t->sesiones_totales }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-sky-100 dark:bg-sky-900/50">
                            <div class="h-full rounded-full bg-gradient-to-r from-sky-400 to-indigo-500" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-lg border border-dashed border-zinc-200 p-3 text-center text-xs text-zinc-400 dark:border-zinc-700">
                        Sin tratamiento activo
                    </div>
                @endif

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
