<div class="p-6 space-y-6">
    <div>
        <flux:heading size="xl">Pagos GYM</flux:heading>
        <flux:text class="text-zinc-500">Cobros de suscripciones y servicios</flux:text>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-gradient-to-br from-emerald-50 to-white p-4 dark:border-zinc-700 dark:from-emerald-900/20 dark:to-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Recaudado mes</span>
                <flux:icon.arrow-trending-up class="size-5 text-emerald-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-emerald-600">${{ number_format($totalMes, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-zinc-500">{{ now()->isoFormat('MMMM YYYY') }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Histórico</span>
                <flux:icon.banknotes class="size-5 text-zinc-400" />
            </div>
            <div class="mt-2 text-3xl font-bold">${{ number_format($totalHistorico, 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Por cobrar</span>
                <flux:icon.clock class="size-5 text-amber-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-amber-600">${{ number_format($pendienteMonto, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-zinc-500">{{ $counts['pendiente'] }} pendientes</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Transacciones</span>
                <flux:icon.hashtag class="size-5 text-indigo-500" />
            </div>
            <div class="mt-2 text-3xl font-bold">{{ $counts['all'] }}</div>
            <div class="mt-1 text-xs text-zinc-500">{{ $counts['pagado'] }} completadas</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar persona..." class="max-w-xs" />
        {{-- Paginador de mes --}}
        @php
            $nextDisabled = !$month || \Carbon\Carbon::parse($month)->isCurrentMonth();
            $monthLabel   = $month
                ? \Carbon\Carbon::parse($month)->locale('es')->isoFormat('MMMM YYYY')
                : 'Todos los meses';
        @endphp
        <div class="flex items-center rounded-lg border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
            <button type="button" wire:click="prevMonth"
                class="flex items-center justify-center px-2 py-1.5 text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </button>
            <span class="min-w-[130px] border-x border-zinc-200 px-3 py-1.5 text-center text-sm font-medium capitalize dark:border-zinc-700">
                {{ $monthLabel }}
            </span>
            <button type="button" wire:click="nextMonth"
                class="flex items-center justify-center px-2 py-1.5 {{ $nextDisabled ? 'cursor-not-allowed text-zinc-300 dark:text-zinc-600' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}"
                {{ $nextDisabled ? 'disabled' : '' }}>
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>
        </div>
        @if ($month)
            <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="$set('month', '')">Ver todos</flux:button>
        @endif
        @php
            $tabs = [
                '' => ['Todos', $counts['all']],
                'pagado' => ['Pagados', $counts['pagado']],
                'pendiente' => ['Pendientes', $counts['pendiente']],
                'anulado' => ['Anulados', $counts['anulado']],
            ];
        @endphp
        <div class="flex flex-wrap gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
            @foreach ($tabs as $val => [$label, $count])
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
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-left">Persona</th>
                    <th class="px-4 py-3 text-left">Plan</th>
                    <th class="px-4 py-3 text-left">Método</th>
                    <th class="px-4 py-3 text-right">Monto</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($payments as $p)
                    @php
                        $statusColor = match($p->status) {
                            'pagado' => 'green', 'pendiente' => 'amber',
                            'anulado' => 'red', default => 'zinc',
                        };
                        $methodIcon = match(strtolower($p->payment_type ?? '')) {
                            'efectivo' => 'banknotes',
                            'transferencia' => 'arrows-right-left',
                            'debito', 'credito', 'tarjeta' => 'credit-card',
                            default => 'currency-dollar',
                        };
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $p->payment_date?->format('d/m/Y') }}</div>
                            <div class="text-xs text-zinc-500">{{ $p->payment_date?->isoFormat('ddd') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                    {{ strtoupper(substr($p->person?->first_name ?? '?', 0, 1).substr($p->person?->last_name ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium">{{ $p->person?->full_name ?? '—' }}</div>
                                    <div class="text-xs text-zinc-500">{{ $p->person?->rut }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $p->subscription?->plan?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-zinc-100 px-2 py-0.5 text-xs dark:bg-zinc-800">
                                <flux:icon :name="$methodIcon" class="size-3.5" />
                                {{ ucfirst($p->payment_type ?? '—') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">${{ number_format($p->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <flux:badge size="sm" :color="$statusColor">{{ ucfirst($p->status) }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                @if ($p->status === 'pendiente')
                                    <flux:button size="sm" variant="primary" icon="banknotes" wire:click="openPay({{ $p->id }})">Cobrar</flux:button>
                                @endif
                                <flux:button size="sm" variant="subtle" icon="trash" wire:click="delete({{ $p->id }})" wire:confirm="¿Eliminar pago?" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-zinc-400">Sin pagos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $payments->links() }}</div>

    {{-- Modal: registrar pago pendiente --}}
    <flux:modal wire:model="payOpen" class="md:w-[560px]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Registrar pago</flux:heading>
                @if ($pay_person)
                    <flux:text class="text-zinc-500">{{ $pay_person }}</flux:text>
                @endif
            </div>

            {{-- Fecha y observaciones compartidas --}}
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="date" label="Fecha de pago" wire:model="pay_date" />
                <flux:input label="Observaciones" wire:model="pay_notes" placeholder="Opcional" />
            </div>

            {{-- Indicador total vs monto original --}}
            @php $splitsTotal = collect($pay_splits)->sum(fn($s) => (float)($s['monto'] ?? 0)); @endphp
            @if ($pay_total)
                @php $diff = $pay_total - $splitsTotal; @endphp
                <div class="flex items-center gap-2 text-sm font-medium {{ abs($diff) < 0.01 ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400' }}">
                    @if (abs($diff) < 0.01)
                        <flux:icon.check-circle class="size-4" />
                        Total: ${{ number_format($splitsTotal, 0, ',', '.') }} ✓
                    @elseif ($diff > 0)
                        <flux:icon.exclamation-circle class="size-4" />
                        Faltan ${{ number_format($diff, 0, ',', '.') }} para completar ${{ number_format($pay_total, 0, ',', '.') }}
                    @else
                        <flux:icon.exclamation-circle class="size-4" />
                        Exceso de ${{ number_format(abs($diff), 0, ',', '.') }} sobre ${{ number_format($pay_total, 0, ',', '.') }}
                    @endif
                </div>
            @endif

            {{-- Splits --}}
            <div class="space-y-2">
                @foreach ($pay_splits as $i => $split)
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <flux:input
                                type="number" step="1" min="1"
                                label="{{ $loop->first ? 'Monto ($)' : '' }}"
                                wire:model.live="pay_splits.{{ $i }}.monto"
                                placeholder="0"
                            />
                        </div>
                        <div class="flex-1">
                            <flux:select
                                label="{{ $loop->first ? 'Método' : '' }}"
                                wire:model="pay_splits.{{ $i }}.metodo"
                            >
                                <flux:select.option value="efectivo">Efectivo</flux:select.option>
                                <flux:select.option value="debito">Tarjeta de débito</flux:select.option>
                                <flux:select.option value="credito">Tarjeta de crédito</flux:select.option>
                                <flux:select.option value="transferencia">Transferencia</flux:select.option>
                                <flux:select.option value="webpay">Webpay</flux:select.option>
                                <flux:select.option value="otro">Otro</flux:select.option>
                            </flux:select>
                        </div>
                        @if (count($pay_splits) > 1)
                            <flux:button type="button" size="sm" variant="ghost" icon="x-mark" wire:click="removePaySplit({{ $i }})" />
                        @endif
                    </div>
                    @error("pay_splits.{$i}.monto") <flux:error>{{ $message }}</flux:error> @enderror
                @endforeach
            </div>

            @if (count($pay_splits) < 4)
                <flux:button type="button" size="sm" variant="ghost" icon="plus" wire:click="addPaySplit">
                    Agregar otra forma de pago
                </flux:button>
            @endif

            @error('pay_splits')
                <div class="rounded-lg border border-red-200 bg-red-50 p-2 text-sm text-red-700">{{ $message }}</div>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closePay">Cancelar</flux:button>
                <flux:button variant="primary" icon="check" wire:click="confirmPay">Confirmar pago</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
