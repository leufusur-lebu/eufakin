<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-500">
                <a href="{{ route('admin.reports.index') }}" wire:navigate class="hover:underline">Reportes</a>
                <flux:icon.chevron-right class="size-3" />
                <span>Pagos</span>
            </div>
            <flux:heading size="xl">Reporte de Pagos</flux:heading>
            <flux:text class="text-zinc-500">Recaudación consolidada de Gym, Kinesiología y Estética</flux:text>
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
            <div>
                <flux:input type="date" wire:model.live="from" label="Desde" />
            </div>
            <div>
                <flux:input type="date" wire:model.live="to" label="Hasta" />
            </div>
            <div class="min-w-[160px]">
                <flux:select wire:model.live="module" label="Módulo">
                    <flux:select.option value="all">Todos</flux:select.option>
                    <flux:select.option value="gym">Gym</flux:select.option>
                    <flux:select.option value="kine">Kinesiología</flux:select.option>
                    <flux:select.option value="estetic">Estética</flux:select.option>
                </flux:select>
            </div>
            <div class="min-w-[160px]">
                <flux:select wire:model.live="status" label="Estado">
                    <flux:select.option value="all">Todos</flux:select.option>
                    <flux:select.option value="pagado">Pagado</flux:select.option>
                    <flux:select.option value="pendiente">Pendiente</flux:select.option>
                    <flux:select.option value="anulado">Anulado</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">Transacciones</div>
            <div class="mt-2 text-3xl font-bold">{{ number_format($totals['count']) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900 dark:bg-emerald-950/30">
            <div class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-400">Total recaudado</div>
            <div class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">${{ number_format($totals['total'], 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">Pagado: ${{ number_format($totals['pagado'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/30">
            <div class="text-xs font-medium uppercase tracking-wide text-amber-700 dark:text-amber-400">Pendiente</div>
            <div class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-300">${{ number_format($totals['pendiente'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs font-medium uppercase tracking-wide text-zinc-500">Por módulo</div>
            <div class="mt-2 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-amber-600">Gym</span><span class="font-semibold">${{ number_format($totals['gym'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-sky-600">Kine</span><span class="font-semibold">${{ number_format($totals['kine'], 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-pink-600">Estética</span><span class="font-semibold">${{ number_format($totals['estetic'], 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>

    {{-- Serie diaria --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Recaudación diaria</h3>
            <span class="text-xs text-zinc-500">Máx: ${{ number_format($maxDay, 0, ',', '.') }}</span>
        </div>
        <div class="flex h-40 items-end gap-1 overflow-x-auto">
            @foreach ($days as $d)
                @php $h = $maxDay > 0 ? max(2, round(($d['total'] / $maxDay) * 100)) : 2; @endphp
                <div class="group relative flex min-w-[18px] flex-1 flex-col items-center">
                    <div class="w-full rounded-t bg-gradient-to-t from-emerald-500 to-teal-400" style="height: {{ $h }}%" title="{{ $d['label'] }}: ${{ number_format($d['total'], 0, ',', '.') }}"></div>
                </div>
            @endforeach
        </div>
        <div class="mt-2 flex justify-between text-[10px] text-zinc-500">
            <span>{{ collect($days)->first()['label'] ?? '' }}</span>
            <span>{{ collect($days)->last()['label'] ?? '' }}</span>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
            <h3 class="text-sm font-semibold">Detalle (primeras 200)</h3>
            <span class="text-xs text-zinc-500">{{ $rows->count() }} de {{ $totals['count'] }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Módulo</th>
                        <th class="px-4 py-2 text-left">Persona</th>
                        <th class="px-4 py-2 text-left">Concepto</th>
                        <th class="px-4 py-2 text-left">Método</th>
                        <th class="px-4 py-2 text-left">Estado</th>
                        <th class="px-4 py-2 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($rows as $r)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $r['date']?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">
                                @php
                                    $colors = ['gym' => 'amber', 'kine' => 'sky', 'estetic' => 'pink'];
                                    $c = $colors[$r['module']] ?? 'zinc';
                                @endphp
                                <span class="inline-flex rounded-full bg-{{ $c }}-100 px-2 py-0.5 text-xs font-medium text-{{ $c }}-700 dark:bg-{{ $c }}-900/40 dark:text-{{ $c }}-300">{{ strtoupper($r['module']) }}</span>
                            </td>
                            <td class="px-4 py-2">{{ $r['person'] }}</td>
                            <td class="px-4 py-2 text-zinc-600 dark:text-zinc-300">{{ $r['concept'] }}</td>
                            <td class="px-4 py-2">{{ $r['method'] }}</td>
                            <td class="px-4 py-2">
                                @php $s = strtolower($r['status']); @endphp
                                @if ($s === 'pagado')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Pagado</span>
                                @elseif ($s === 'pendiente')
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Pendiente</span>
                                @else
                                    <span class="inline-flex rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $r['status'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format($r['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-zinc-500">Sin pagos en el rango seleccionado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
