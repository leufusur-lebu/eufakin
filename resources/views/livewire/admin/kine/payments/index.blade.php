<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Caja Kinesiología</flux:heading>
            <flux:text class="text-zinc-500">Cuentas por cobrar y movimientos del período</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('admin.kine.payments.create') }}" icon="plus" wire:navigate>Pago manual</flux:button>
            @if ($tab === 'movements')
                <flux:button wire:click="export" variant="ghost" icon="arrow-down-tray">Exportar CSV</flux:button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    {{-- KPIs --}}
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-amber-700">Por cobrar</span>
                <flux:icon.clock class="size-5 text-amber-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-300">${{ number_format($totalPendiente, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-amber-600">{{ $receivables['count'] }} cuotas pendientes</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-emerald-700">Mes en curso</span>
                <flux:icon.arrow-trending-up class="size-5 text-emerald-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">${{ number_format($totalMes, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-emerald-600">{{ now()->isoFormat('MMMM YYYY') }}</div>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900 dark:bg-rose-950/30">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-rose-700">Pendiente FONASA</span>
                <flux:icon.shield-check class="size-5 text-rose-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-rose-700 dark:text-rose-300">${{ number_format($fonasaPendiente, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-rose-600">esperan rendición</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Período</span>
                <flux:icon.banknotes class="size-5 text-sky-500" />
            </div>
            <div class="mt-2 text-3xl font-bold">${{ number_format($movements['total'], 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-zinc-500">{{ $movements['count'] }} cobros</div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-1 border-b border-zinc-200 dark:border-zinc-700">
        <button wire:click="$set('tab','receivables')"
            class="flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition
                {{ $tab === 'receivables' ? 'border-sky-500 text-sky-600' : 'border-transparent text-zinc-500 hover:text-zinc-900' }}">
            <flux:icon.clock class="size-4" /> Por cobrar
            <span class="rounded-full bg-amber-100 px-1.5 text-[10px] text-amber-700">{{ $receivables['groups']->count() }}</span>
        </button>
        <button wire:click="$set('tab','movements')"
            class="flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition
                {{ $tab === 'movements' ? 'border-sky-500 text-sky-600' : 'border-transparent text-zinc-500 hover:text-zinc-900' }}">
            <flux:icon.banknotes class="size-4" /> Movimientos
            <span class="rounded-full bg-emerald-100 px-1.5 text-[10px] text-emerald-700">{{ $movements['count'] }}</span>
        </button>
    </div>

    {{-- ========== POR COBRAR ========== --}}
    @if ($tab === 'receivables')
        <div class="space-y-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar paciente..." class="max-w-sm" />

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($receivables['groups'] as $g)
                    @php
                        $days = (int) $g['days_overdue'];
                        $color = $days >= 30 ? 'rose' : ($days >= 14 ? 'amber' : 'zinc');
                    @endphp
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start gap-3">
                            <div class="flex size-11 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-indigo-500 text-sm font-bold text-white">
                                {{ strtoupper(substr($g['person']->first_name, 0, 1).substr($g['person']->last_name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.kine.patients.show', $g['profile_id']) }}" wire:navigate class="block truncate font-semibold hover:text-sky-600">
                                    {{ $g['person']->full_name }}
                                </a>
                                <div class="truncate text-xs text-zinc-500">{{ $g['person']->rut }}</div>
                            </div>
                            <span class="rounded bg-{{ $color }}-100 px-2 py-0.5 text-[10px] font-semibold text-{{ $color }}-700 dark:bg-{{ $color }}-900/40 dark:text-{{ $color }}-300">
                                {{ $days }}d
                            </span>
                        </div>

                        @if ($g['fonasa'])
                            <div class="mt-2 inline-flex items-center gap-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                                <flux:icon.shield-check class="size-3" /> FONASA
                            </div>
                        @endif

                        <div class="mt-3 flex items-baseline justify-between">
                            <div>
                                <div class="text-[10px] uppercase tracking-wide text-zinc-500">Saldo total</div>
                                <div class="text-xl font-bold text-amber-600">${{ number_format($g['total_due'], 0, ',', '.') }}</div>
                            </div>
                            <div class="text-right text-xs text-zinc-500">
                                {{ $g['pending_count'] }} {{ Str::plural('cuota', $g['pending_count']) }}<br>
                                desde {{ \Carbon\Carbon::parse($g['oldest_date'])->format('d/m/Y') }}
                            </div>
                        </div>

                        @if ($g['protocols']->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach ($g['protocols']->take(2) as $proto)
                                    <span class="rounded bg-sky-50 px-1.5 py-0.5 text-[10px] text-sky-700 dark:bg-sky-950/30 dark:text-sky-300">{{ $proto }}</span>
                                @endforeach
                                @if ($g['protocols']->count() > 2)
                                    <span class="text-[10px] text-zinc-500">+{{ $g['protocols']->count() - 2 }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="mt-3 flex gap-1">
                            <flux:button wire:click="openBulk({{ $g['profile_id'] }})" variant="primary" size="sm" icon="banknotes" class="flex-1">Cobrar todo</flux:button>
                            <x-whatsapp-button
                                :phone="$g['person']->phone"
                                template="payment_overdue"
                                :vars="[
                                    'nombre' => $g['person']->first_name,
                                    'monto' => number_format($g['total_due'], 0, ',', '.'),
                                    'concepto' => 'kinesiología',
                                ]"
                                label="" />
                            <flux:button href="{{ route('admin.kine.patients.show', $g['profile_id']) }}?tab=finance" wire:navigate variant="ghost" size="sm" icon="eye" />
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-xl border border-dashed border-emerald-300 bg-emerald-50/50 p-12 text-center dark:border-emerald-900 dark:bg-emerald-950/20">
                        <flux:icon.check-circle class="mx-auto size-10 text-emerald-400" />
                        <p class="mt-3 font-semibold text-emerald-700 dark:text-emerald-300">¡Sin pagos pendientes!</p>
                        <p class="text-xs text-emerald-600">Todos los pacientes están al día.</p>
                    </div>
                @endforelse
            </div>

            @if ($receivables['rows']->isNotEmpty())
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                        <h3 class="font-semibold">Detalle de cuotas pendientes</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-2 text-left">Vence</th>
                                <th class="px-4 py-2 text-left">Paciente</th>
                                <th class="px-4 py-2 text-left">Tratamiento</th>
                                <th class="px-4 py-2 text-left">Cuota</th>
                                <th class="px-4 py-2 text-left">Método</th>
                                <th class="px-4 py-2 text-right">Monto</th>
                                <th class="px-4 py-2 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($receivables['rows']->take(50) as $p)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $p->fecha?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2">{{ $p->kineProfile?->person?->full_name }}</td>
                                    <td class="px-4 py-2 text-zinc-600">{{ $p->treatment?->tipoTratamiento?->nombre ?? $p->treatment?->diagnostico ?? '—' }}</td>
                                    <td class="px-4 py-2 text-zinc-600 text-xs">{{ $p->observaciones ?: '—' }}</td>
                                    <td class="px-4 py-2 text-xs">
                                        @if ($p->metodo === 'obra_social')
                                            <span class="rounded bg-rose-100 px-1.5 py-0.5 font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">FONASA</span>
                                        @else
                                            <span class="capitalize">{{ $p->metodo }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right font-semibold text-amber-600">${{ number_format($p->monto, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <flux:button wire:click="openPay({{ $p->id }})" size="sm" variant="primary" icon="banknotes">Cobrar</flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- ========== MOVIMIENTOS ========== --}}
    @if ($tab === 'movements')
        <div class="space-y-4">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex flex-wrap gap-1">
                        <flux:button size="sm" variant="ghost" wire:click="setRange('today')">Hoy</flux:button>
                        <flux:button size="sm" variant="ghost" wire:click="setRange('week')">Semana</flux:button>
                        <flux:button size="sm" variant="ghost" wire:click="setRange('month')">Mes</flux:button>
                        <flux:button size="sm" variant="ghost" wire:click="setRange('year')">Año</flux:button>
                    </div>
                    <flux:input type="date" wire:model.live="from" label="Desde" />
                    <flux:input type="date" wire:model.live="to" label="Hasta" />
                    <flux:select wire:model.live="method" label="Método">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="efectivo">Efectivo</flux:select.option>
                        <flux:select.option value="debito">Débito</flux:select.option>
                        <flux:select.option value="credito">Crédito</flux:select.option>
                        <flux:select.option value="transferencia">Transferencia</flux:select.option>
                        <flux:select.option value="mercadopago">Mercado Pago</flux:select.option>
                        <flux:select.option value="obra_social">FONASA / Obra social</flux:select.option>
                        <flux:select.option value="otro">Otro</flux:select.option>
                    </flux:select>
                </div>
            </div>

            @if ($movements['totalsByMethod']->isNotEmpty())
                <div class="grid gap-3 md:grid-cols-3 lg:grid-cols-7">
                    @foreach ($movements['totalsByMethod'] as $m => $amt)
                        @php
                            $icons = ['efectivo'=>'banknotes','transferencia'=>'arrows-right-left','debito'=>'credit-card','credito'=>'credit-card','mercadopago'=>'qr-code','obra_social'=>'shield-check','otro'=>'currency-dollar'];
                        @endphp
                        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon :name="$icons[$m] ?? 'currency-dollar'" class="size-4" />
                                <span class="capitalize">{{ str_replace('_',' ',$m) }}</span>
                            </div>
                            <div class="mt-1 text-lg font-bold">${{ number_format($amt, 0, ',', '.') }}</div>
                            @if ($movements['total'] > 0)
                                <div class="text-[10px] text-zinc-400">{{ round(($amt / $movements['total']) * 100) }}%</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Recaudación diaria</h3>
                    <span class="text-xs text-zinc-500">Máx: ${{ number_format($movements['maxDay'], 0, ',', '.') }}</span>
                </div>
                <div class="flex h-32 items-end gap-1 overflow-x-auto">
                    @foreach ($movements['days'] as $d)
                        @php $h = $movements['maxDay'] > 0 ? max(2, round(($d['total'] / $movements['maxDay']) * 100)) : 2; @endphp
                        <div class="group relative flex min-w-[16px] flex-1 flex-col items-center">
                            <div class="w-full rounded-t bg-gradient-to-t from-sky-500 to-indigo-400" style="height: {{ $h }}%" title="{{ $d['label'] }}: ${{ number_format($d['total'], 0, ',', '.') }}"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-[10px] text-zinc-500">
                    <span>{{ collect($movements['days'])->first()['label'] ?? '' }}</span>
                    <span>{{ collect($movements['days'])->last()['label'] ?? '' }}</span>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                    <h3 class="font-semibold">Cobros del período</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-2 text-left">Fecha</th>
                            <th class="px-4 py-2 text-left">Paciente</th>
                            <th class="px-4 py-2 text-left">Tratamiento</th>
                            <th class="px-4 py-2 text-left">Método</th>
                            <th class="px-4 py-2 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($movements['rows']->take(100) as $p)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-4 py-2 whitespace-nowrap">{{ $p->fecha?->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">{{ $p->kineProfile?->person?->full_name }}</td>
                                <td class="px-4 py-2 text-zinc-600">{{ $p->treatment?->tipoTratamiento?->nombre ?? $p->treatment?->diagnostico ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    @if ($p->metodo === 'obra_social')
                                        <span class="rounded bg-rose-100 px-1.5 py-0.5 text-xs font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">FONASA</span>
                                    @else
                                        <span class="capitalize">{{ $p->metodo }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right font-semibold text-emerald-600">${{ number_format($p->monto, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-zinc-400">Sin cobros en el período seleccionado.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($movements['rows']->isNotEmpty())
                        <tfoot class="bg-zinc-50 font-bold dark:bg-zinc-800">
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right">Total del período</td>
                                <td class="px-4 py-2 text-right text-emerald-600">${{ number_format($movements['total'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif

    {{-- Modal cobro individual --}}
    <flux:modal wire:model="payOpen" class="md:w-[560px]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Registrar pago</flux:heading>
                <flux:text class="text-zinc-500">{{ $payPerson }} @if ($payProtocol) · <span class="text-sky-600">{{ $payProtocol }}</span>@endif</flux:text>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="number" step="1" min="0" label="Monto" wire:model="payAmount" />
                <flux:input type="date" label="Fecha" wire:model="payDate" />
                <flux:select label="Método" wire:model="payMethod" class="md:col-span-2">
                    <flux:select.option value="efectivo">Efectivo</flux:select.option>
                    <flux:select.option value="debito">Débito</flux:select.option>
                    <flux:select.option value="credito">Crédito</flux:select.option>
                    <flux:select.option value="transferencia">Transferencia</flux:select.option>
                    <flux:select.option value="mercadopago">Mercado Pago</flux:select.option>
                    <flux:select.option value="obra_social">FONASA / Obra social</flux:select.option>
                    <flux:select.option value="otro">Otro</flux:select.option>
                </flux:select>
                <flux:input label="Observaciones" wire:model="payNotes" class="md:col-span-2" />
            </div>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('payOpen', false)">Cancelar</flux:button>
                <flux:button variant="primary" icon="check" wire:click="confirmPay">Confirmar</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal cobro masivo --}}
    <flux:modal wire:model="bulkOpen" class="md:w-[560px]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Cobrar saldo del paciente</flux:heading>
                <flux:text class="text-zinc-500">{{ $bulkPerson }}</flux:text>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm dark:border-amber-900 dark:bg-amber-950/30">
                Saldo total adeudado: <strong>${{ number_format($bulkTotal ?? 0, 0, ',', '.') }}</strong>
                <div class="text-xs text-zinc-500 mt-1">Si cobras menos del total, se generará automáticamente una cuota nueva con el saldo restante.</div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="number" step="1" min="0" label="Monto a cobrar" wire:model="bulkAmount" />
                <flux:input type="date" label="Fecha" wire:model="bulkDate" />
                <flux:select label="Método" wire:model="bulkMethod" class="md:col-span-2">
                    <flux:select.option value="efectivo">Efectivo</flux:select.option>
                    <flux:select.option value="debito">Débito</flux:select.option>
                    <flux:select.option value="credito">Crédito</flux:select.option>
                    <flux:select.option value="transferencia">Transferencia</flux:select.option>
                    <flux:select.option value="mercadopago">Mercado Pago</flux:select.option>
                    <flux:select.option value="obra_social">FONASA / Obra social</flux:select.option>
                    <flux:select.option value="otro">Otro</flux:select.option>
                </flux:select>
            </div>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('bulkOpen', false)">Cancelar</flux:button>
                <flux:button variant="primary" icon="check" wire:click="confirmBulk">Confirmar cobro</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
