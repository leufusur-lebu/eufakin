<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Caja del día</flux:heading>
            <flux:text class="text-zinc-500">Cuadre consolidado Gym + Kine + Estética</flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <flux:button size="sm" variant="ghost" icon="chevron-left" wire:click="shiftDate(-1)" />
            <flux:input type="date" wire:model.live="date" class="w-40" />
            <flux:button size="sm" variant="ghost" icon="chevron-right" wire:click="shiftDate(1)" />
            <flux:button size="sm" variant="ghost" wire:click="setDate('today')">Hoy</flux:button>
            <flux:button size="sm" variant="ghost" wire:click="setDate('yesterday')">Ayer</flux:button>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    {{-- Banner de cierre si existe --}}
    @if ($thisClose)
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/30">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <flux:icon.check-badge class="size-6 text-emerald-600 mt-0.5" />
                    <div>
                        <div class="font-semibold text-emerald-800 dark:text-emerald-200">Caja cerrada</div>
                        <div class="text-xs text-emerald-700 dark:text-emerald-300">
                            Cerrada el {{ $thisClose->closed_at?->format('d/m/Y H:i') }}
                            @if ($thisClose->user) por {{ $thisClose->user->name }} @endif
                            · Sistema: ${{ number_format($thisClose->total_sistema, 0, ',', '.') }}
                            · Efectivo contado: ${{ number_format($thisClose->total_efectivo_contado, 0, ',', '.') }}
                            @if ((float) $thisClose->diferencia !== 0.0)
                                · <strong class="{{ $thisClose->diferencia < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $thisClose->diferencia > 0 ? 'Sobrante' : 'Faltante' }} ${{ number_format(abs($thisClose->diferencia), 0, ',', '.') }}
                                </strong>
                            @else
                                · <span class="text-emerald-700">Cuadrado</span>
                            @endif
                        </div>
                        @if ($thisClose->observaciones)
                            <div class="mt-1 text-xs italic text-emerald-700 dark:text-emerald-300">{{ $thisClose->observaciones }}</div>
                        @endif
                    </div>
                </div>
                <flux:button size="sm" variant="ghost" wire:click="reopenClose" wire:confirm="¿Revertir el cierre? Podrás volver a registrarlo después.">Revertir cierre</flux:button>
            </div>
        </div>
    @endif

    {{-- KPIs principales --}}
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 dark:border-emerald-900 dark:from-emerald-950/30 dark:to-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-emerald-700">Total ingresos</span>
                <flux:icon.arrow-trending-up class="size-5 text-emerald-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">${{ number_format($totalIn, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-emerald-600">{{ $totalCount }} transacciones</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Efectivo en caja</span>
                <flux:icon.banknotes class="size-5 text-zinc-400" />
            </div>
            <div class="mt-2 text-3xl font-bold">${{ number_format($totalCash, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-zinc-500">{{ $totalIn > 0 ? round(($totalCash / $totalIn) * 100) : 0 }}% del total</div>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950/30">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-amber-700">Pendientes hoy</span>
                <flux:icon.clock class="size-5 text-amber-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-300">${{ number_format($pendingTotal, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-amber-600">{{ $pending->count() }} cuotas</div>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 dark:border-rose-900 dark:bg-rose-950/30">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-rose-700">Por cobrar acumulado</span>
                <flux:icon.exclamation-triangle class="size-5 text-rose-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-rose-700 dark:text-rose-300">${{ number_format($overdueTotal, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-rose-600">{{ $overdue->count() }} cuotas atrasadas</div>
        </div>
    </div>

    {{-- Breakdown método y módulo --}}
    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Método de pago --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Por método de pago</h3>
            @if ($byMethod->isEmpty())
                <p class="text-sm text-zinc-400">Sin movimientos.</p>
            @else
                <div class="space-y-3">
                    @php
                        $methodIcons = [
                            'efectivo' => 'banknotes', 'transferencia' => 'arrows-right-left',
                            'debito' => 'credit-card', 'credito' => 'credit-card',
                            'webpay' => 'qr-code', 'mercadopago' => 'qr-code',
                            'obra_social' => 'shield-check', 'pendiente' => 'clock',
                            'otro' => 'currency-dollar',
                        ];
                        $methodColors = [
                            'efectivo' => 'emerald', 'transferencia' => 'sky',
                            'debito' => 'indigo', 'credito' => 'violet',
                            'webpay' => 'amber', 'mercadopago' => 'amber',
                            'obra_social' => 'rose', 'otro' => 'zinc', 'pendiente' => 'zinc',
                        ];
                    @endphp
                    @foreach ($byMethod as $method => $data)
                        @php
                            $icon = $methodIcons[$method] ?? 'currency-dollar';
                            $color = $methodColors[$method] ?? 'zinc';
                            $pct = $totalIn > 0 ? round(($data['total'] / $totalIn) * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="flex size-7 items-center justify-center rounded-lg bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/40 dark:text-{{ $color }}-300">
                                        <flux:icon :name="$icon" class="size-4" />
                                    </span>
                                    <span class="font-medium capitalize">{{ str_replace('_', ' ', $method) }}</span>
                                    <span class="text-xs text-zinc-500">· {{ $data['count'] }} cobros</span>
                                </div>
                                <span class="font-bold">${{ number_format($data['total'], 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full bg-{{ $color }}-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Módulo --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Por módulo</h3>
            @if ($byModule->isEmpty())
                <p class="text-sm text-zinc-400">Sin movimientos.</p>
            @else
                @php
                    $moduleDef = [
                        'gym'     => ['Gimnasio', 'amber', 'fire'],
                        'kine'    => ['Kinesiología', 'sky', 'bolt'],
                        'estetic' => ['Estética', 'pink', 'sparkles'],
                    ];
                @endphp
                <div class="grid gap-3 grid-cols-3">
                    @foreach (['gym', 'kine', 'estetic'] as $mod)
                        @php
                            [$label, $color, $icon] = $moduleDef[$mod];
                            $data = $byModule[$mod] ?? ['total' => 0, 'count' => 0];
                            $pct = $totalIn > 0 ? round(($data['total'] / $totalIn) * 100) : 0;
                        @endphp
                        <div class="rounded-lg border border-{{ $color }}-200 bg-{{ $color }}-50 p-3 dark:border-{{ $color }}-900 dark:bg-{{ $color }}-950/30">
                            <div class="flex items-center gap-1.5 text-xs font-medium text-{{ $color }}-700 dark:text-{{ $color }}-300">
                                <flux:icon :name="$icon" class="size-3.5" />
                                {{ $label }}
                            </div>
                            <div class="mt-1 text-lg font-bold text-{{ $color }}-700 dark:text-{{ $color }}-300">${{ number_format($data['total'], 0, ',', '.') }}</div>
                            <div class="text-[10px] text-{{ $color }}-600">{{ $data['count'] }} · {{ $pct }}%</div>
                        </div>
                    @endforeach
                </div>

                {{-- Barra apilada --}}
                <div class="mt-4 flex h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    @foreach (['gym', 'kine', 'estetic'] as $mod)
                        @php
                            [$label, $color] = [$moduleDef[$mod][0], $moduleDef[$mod][1]];
                            $data = $byModule[$mod] ?? ['total' => 0];
                            $w = $totalIn > 0 ? ($data['total'] / $totalIn) * 100 : 0;
                        @endphp
                        @if ($w > 0)
                            <div class="bg-{{ $color }}-500" style="width: {{ $w }}%" title="{{ $label }}"></div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Acción cierre --}}
    @if (!$thisClose)
        <div class="flex justify-end">
            <flux:button wire:click="openClose" variant="primary" icon="lock-closed">Cerrar caja del {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</flux:button>
        </div>
    @endif

    {{-- Tabla movimientos del día --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
            <h3 class="font-semibold">Movimientos del día</h3>
            <span class="text-xs text-zinc-500">{{ $paid->count() }} cobrados · {{ $pending->count() }} pendientes</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-2 text-left">Hora</th>
                    <th class="px-4 py-2 text-left">Módulo</th>
                    <th class="px-4 py-2 text-left">Persona</th>
                    <th class="px-4 py-2 text-left">Concepto</th>
                    <th class="px-4 py-2 text-left">Método</th>
                    <th class="px-4 py-2 text-center">Estado</th>
                    <th class="px-4 py-2 text-right">Monto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($paid->concat($pending) as $r)
                    @php
                        $modCols = ['gym' => 'amber', 'kine' => 'sky', 'estetic' => 'pink'];
                        $mc = $modCols[$r->modulo] ?? 'zinc';
                        $stCol = $r->estado === 'pagado' ? 'green' : ($r->estado === 'pendiente' ? 'amber' : 'red');
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $r->fecha?->format('H:i') }}</td>
                        <td class="px-4 py-2">
                            <span class="rounded-full bg-{{ $mc }}-100 px-2 py-0.5 text-[10px] font-medium uppercase text-{{ $mc }}-700 dark:bg-{{ $mc }}-900/40 dark:text-{{ $mc }}-300">{{ $r->modulo }}</span>
                        </td>
                        <td class="px-4 py-2">{{ $r->persona }}</td>
                        <td class="px-4 py-2 text-zinc-600 dark:text-zinc-400">{{ $r->concepto }}</td>
                        <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $r->metodo) }}</td>
                        <td class="px-4 py-2 text-center"><flux:badge size="sm" :color="$stCol">{{ ucfirst($r->estado) }}</flux:badge></td>
                        <td class="px-4 py-2 text-right font-semibold {{ $r->estado === 'pagado' ? 'text-emerald-600' : 'text-amber-600' }}">${{ number_format($r->monto, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-zinc-400">Sin movimientos en {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</td></tr>
                @endforelse
            </tbody>
            @if ($paid->isNotEmpty())
                <tfoot class="bg-zinc-50 font-bold dark:bg-zinc-800">
                    <tr>
                        <td colspan="6" class="px-4 py-2 text-right">Total cobrado</td>
                        <td class="px-4 py-2 text-right text-emerald-600">${{ number_format($totalIn, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Por cobrar acumulado (atrasados) --}}
    @if ($overdue->isNotEmpty())
        <div class="overflow-hidden rounded-xl border border-rose-200 bg-rose-50/30 dark:border-rose-900 dark:bg-rose-950/10">
            <div class="border-b border-rose-200 px-5 py-3 dark:border-rose-900">
                <h3 class="font-semibold text-rose-700 dark:text-rose-300">Cuotas atrasadas (vencimiento ≤ {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }})</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-rose-100/50 text-xs uppercase text-rose-600 dark:bg-rose-900/30">
                    <tr>
                        <th class="px-4 py-2 text-left">Vencimiento</th>
                        <th class="px-4 py-2 text-left">Mora</th>
                        <th class="px-4 py-2 text-left">Módulo</th>
                        <th class="px-4 py-2 text-left">Persona</th>
                        <th class="px-4 py-2 text-left">Concepto</th>
                        <th class="px-4 py-2 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-rose-100 dark:divide-rose-900/40">
                    @foreach ($overdue->take(20) as $o)
                        @php
                            $modCols = ['gym' => 'amber', 'kine' => 'sky', 'estetic' => 'pink'];
                            $mc = $modCols[$o->modulo] ?? 'zinc';
                            $sev = $o->dias_mora >= 30 ? 'rose' : ($o->dias_mora >= 14 ? 'amber' : 'zinc');
                        @endphp
                        <tr class="hover:bg-rose-100/30">
                            <td class="px-4 py-2 whitespace-nowrap">{{ $o->fecha?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">
                                <span class="rounded bg-{{ $sev }}-100 px-1.5 py-0.5 text-[10px] font-bold text-{{ $sev }}-700 dark:bg-{{ $sev }}-900/40 dark:text-{{ $sev }}-300">{{ $o->dias_mora }}d</span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="rounded-full bg-{{ $mc }}-100 px-2 py-0.5 text-[10px] font-medium uppercase text-{{ $mc }}-700 dark:bg-{{ $mc }}-900/40 dark:text-{{ $mc }}-300">{{ $o->modulo }}</span>
                            </td>
                            <td class="px-4 py-2">{{ $o->persona }}</td>
                            <td class="px-4 py-2 text-zinc-600 text-xs">{{ $o->concepto }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-rose-600">${{ number_format($o->monto, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if ($overdue->count() > 20)
                    <tfoot class="bg-rose-100/30">
                        <tr><td colspan="6" class="px-4 py-2 text-center text-xs text-rose-600">+ {{ $overdue->count() - 20 }} más</td></tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif

    {{-- Cierres recientes --}}
    @if ($recentCloses->isNotEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Cierres recientes</h3>
            <div class="grid gap-2 md:grid-cols-7">
                @foreach ($recentCloses->reverse() as $c)
                    <button wire:click="$set('date', '{{ $c->fecha->format('Y-m-d') }}')"
                        class="rounded-lg border p-2 text-left text-xs transition hover:border-zinc-400
                            {{ $c->fecha->format('Y-m-d') === $date
                                ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50 dark:bg-emerald-950/30'
                                : 'border-zinc-200 dark:border-zinc-700' }}">
                        <div class="font-semibold">{{ $c->fecha->format('d/m') }}</div>
                        <div class="text-zinc-500">${{ number_format($c->total_sistema, 0, ',', '.') }}</div>
                        @if ((float) $c->diferencia !== 0.0)
                            <div class="mt-0.5 text-{{ $c->diferencia < 0 ? 'rose' : 'emerald' }}-600">
                                {{ $c->diferencia > 0 ? '+' : '' }}${{ number_format($c->diferencia, 0, ',', '.') }}
                            </div>
                        @else
                            <div class="mt-0.5 text-emerald-600">✓ cuadrado</div>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Modal cierre --}}
    <flux:modal wire:model="closeOpen" class="md:w-[520px]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Cerrar caja del {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</flux:heading>
                <flux:text class="text-zinc-500">Confirma el efectivo contado para registrar el cierre.</flux:text>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex justify-between"><span>Total cobrado (sistema)</span><strong>${{ number_format($totalIn, 0, ',', '.') }}</strong></div>
                <div class="flex justify-between mt-1"><span>De ese total, en efectivo</span><strong class="text-emerald-700">${{ number_format($totalCash, 0, ',', '.') }}</strong></div>
            </div>

            <flux:input type="number" step="1" min="0" wire:model.live="counted_cash" label="Efectivo contado en caja" />

            @php
                $diff = ($counted_cash ?? 0) - $totalCash;
            @endphp
            <div class="rounded-lg border p-3 text-sm
                {{ $diff > 0 ? 'border-emerald-200 bg-emerald-50' : ($diff < 0 ? 'border-rose-200 bg-rose-50' : 'border-zinc-200 bg-zinc-50') }}
                dark:bg-opacity-30">
                <div class="flex justify-between">
                    <span class="font-medium">Diferencia</span>
                    <strong class="{{ $diff > 0 ? 'text-emerald-700' : ($diff < 0 ? 'text-rose-700' : '') }}">
                        {{ $diff > 0 ? 'Sobrante ' : ($diff < 0 ? 'Faltante ' : 'Cuadrado ') }}
                        ${{ number_format(abs($diff), 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            <flux:textarea wire:model="close_notes" rows="2" label="Observaciones (opcional)" placeholder="Caja chica, vueltos, etc." />

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('closeOpen', false)">Cancelar</flux:button>
                <flux:button variant="primary" icon="lock-closed" wire:click="confirmClose">Confirmar cierre</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
