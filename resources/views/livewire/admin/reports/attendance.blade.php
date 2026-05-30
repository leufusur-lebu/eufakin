<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-500">
                <a href="{{ route('admin.reports.index') }}" wire:navigate class="hover:underline">Reportes</a>
                <flux:icon.chevron-right class="size-3" />
                <span>Asistencias</span>
            </div>
            <flux:heading size="xl">Reporte de Asistencias</flux:heading>
            <flux:text class="text-zinc-500">Citas atendidas, ausentes y canceladas por módulo</flux:text>
        </div>
        <flux:button icon="arrow-down-tray" variant="primary" wire:click="export">Exportar CSV</flux:button>
    </div>

    {{-- Filtros --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-wrap gap-1">
                <flux:button size="sm" variant="ghost" wire:click="setRange('today')">Hoy</flux:button>
                <flux:button size="sm" variant="ghost" wire:click="setRange('week')">Semana</flux:button>
                <flux:button size="sm" variant="ghost" wire:click="setRange('month')">Mes</flux:button>
                <flux:button size="sm" variant="ghost" wire:click="setRange('year')">Año</flux:button>
            </div>
            <div><flux:input type="date" wire:model.live="from" label="Desde" /></div>
            <div><flux:input type="date" wire:model.live="to" label="Hasta" /></div>
            <div class="min-w-[160px]">
                <flux:select wire:model.live="module" label="Módulo">
                    <flux:select.option value="all">Todos</flux:select.option>
                    <flux:select.option value="kine">Kinesiología</flux:select.option>
                    <flux:select.option value="estetic">Estética</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">Total citas</div>
            <div class="mt-2 text-3xl font-bold">{{ number_format($total) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900 dark:bg-emerald-950/30">
            <div class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-400">Tasa de asistencia</div>
            <div class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">{{ $tasa }}%</div>
            <div class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">{{ $atendidos }} atendidos</div>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 dark:border-rose-900 dark:bg-rose-950/30">
            <div class="text-xs font-medium uppercase tracking-wide text-rose-700 dark:text-rose-400">Tasa de ausencia</div>
            <div class="mt-2 text-3xl font-bold text-rose-700 dark:text-rose-300">{{ $tasaAusencia }}%</div>
            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $estados['ausente'] + $estados['cancelado'] }} ausencias/canceladas</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">Distribución</div>
            <div class="mt-2 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-emerald-600">Atendidos</span><span class="font-semibold">{{ $estados['atendido'] }}</span></div>
                <div class="flex justify-between"><span class="text-sky-600">Confirmados</span><span class="font-semibold">{{ $estados['confirmado'] }}</span></div>
                <div class="flex justify-between"><span class="text-amber-600">Pendientes</span><span class="font-semibold">{{ $estados['pendiente'] }}</span></div>
                <div class="flex justify-between"><span class="text-rose-600">Ausentes/Cancel.</span><span class="font-semibold">{{ $estados['ausente'] + $estados['cancelado'] }}</span></div>
            </div>
        </div>
    </div>

    {{-- Barra estados --}}
    @if ($total > 0)
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Distribución por estado</h3>
            <div class="flex h-4 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                @php
                    $segs = [
                        ['k' => 'atendido', 'c' => 'bg-emerald-500'],
                        ['k' => 'confirmado', 'c' => 'bg-sky-500'],
                        ['k' => 'pendiente', 'c' => 'bg-amber-500'],
                        ['k' => 'cancelado', 'c' => 'bg-rose-400'],
                        ['k' => 'ausente', 'c' => 'bg-rose-600'],
                    ];
                @endphp
                @foreach ($segs as $s)
                    @php $pct = $total ? ($estados[$s['k']] / $total) * 100 : 0; @endphp
                    @if ($pct > 0)
                        <div class="{{ $s['c'] }}" style="width: {{ $pct }}%" title="{{ ucfirst($s['k']) }}: {{ $estados[$s['k']] }}"></div>
                    @endif
                @endforeach
            </div>
            <div class="mt-3 flex flex-wrap gap-4 text-xs">
                @foreach ($segs as $s)
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block size-3 rounded {{ $s['c'] }}"></span>
                        <span class="text-zinc-600 dark:text-zinc-400">{{ ucfirst($s['k']) }} ({{ $estados[$s['k']] }})</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tops --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Top pacientes</h3>
            <div class="space-y-2">
                @forelse ($topPacientes as $name => $stats)
                    <div class="flex items-center justify-between text-sm">
                        <span class="truncate">{{ $name }}</span>
                        <span class="text-zinc-500">{{ $stats['atendidos'] }}/{{ $stats['count'] }}</span>
                    </div>
                @empty
                    <div class="text-sm text-zinc-500">Sin datos</div>
                @endforelse
            </div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Top profesionales</h3>
            <div class="space-y-2">
                @forelse ($topProfesionales as $name => $stats)
                    <div class="flex items-center justify-between text-sm">
                        <span class="truncate">{{ $name }}</span>
                        <span class="text-zinc-500">{{ $stats['atendidos'] }}/{{ $stats['count'] }}</span>
                    </div>
                @empty
                    <div class="text-sm text-zinc-500">Sin datos</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
            <h3 class="text-sm font-semibold">Detalle (primeras 200)</h3>
            <span class="text-xs text-zinc-500">{{ $rows->count() }} de {{ $total }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Hora</th>
                        <th class="px-4 py-2 text-left">Módulo</th>
                        <th class="px-4 py-2 text-left">Paciente</th>
                        <th class="px-4 py-2 text-left">Profesional</th>
                        <th class="px-4 py-2 text-left">Motivo</th>
                        <th class="px-4 py-2 text-left">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($rows as $r)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $r['inicio']?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $r['inicio']?->format('H:i') }}</td>
                            <td class="px-4 py-2">
                                @php $c = $r['module'] === 'kine' ? 'sky' : 'pink'; @endphp
                                <span class="inline-flex rounded-full bg-{{ $c }}-100 px-2 py-0.5 text-xs font-medium text-{{ $c }}-700 dark:bg-{{ $c }}-900/40 dark:text-{{ $c }}-300">{{ strtoupper($r['module']) }}</span>
                            </td>
                            <td class="px-4 py-2">{{ $r['person'] }}</td>
                            <td class="px-4 py-2 text-zinc-600 dark:text-zinc-300">{{ $r['professional'] }}</td>
                            <td class="px-4 py-2 text-zinc-600 dark:text-zinc-300">{{ $r['motivo'] }}</td>
                            <td class="px-4 py-2">
                                @php
                                    $map = [
                                        'atendido' => 'emerald', 'confirmado' => 'sky',
                                        'pendiente' => 'amber', 'cancelado' => 'rose', 'ausente' => 'rose',
                                    ];
                                    $col = $map[$r['estado']] ?? 'zinc';
                                @endphp
                                <span class="inline-flex rounded-full bg-{{ $col }}-100 px-2 py-0.5 text-xs font-medium text-{{ $col }}-700 dark:bg-{{ $col }}-900/40 dark:text-{{ $col }}-300">{{ ucfirst($r['estado']) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-zinc-500">Sin citas en el rango seleccionado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
