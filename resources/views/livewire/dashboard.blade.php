<div class="p-6 space-y-6">
    <div class="flex items-end justify-between">
        <div>
            <flux:heading size="xl">Dashboard</flux:heading>
            <flux:text class="text-zinc-500">Resumen operativo — {{ now()->isoFormat('dddd D [de] MMMM') }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('admin.admission.create') }}" variant="primary" icon="plus" wire:navigate>Nueva admisión</flux:button>
            <flux:button href="{{ route('admin.agenda.index') }}" icon="calendar-days" wire:navigate>Agenda</flux:button>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Personas</span>
                <flux:icon.user-group class="size-5 text-zinc-400" />
            </div>
            <div class="mt-2 text-3xl font-bold">{{ number_format($personas, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-zinc-500">Registradas en el sistema</div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Suscripciones activas</span>
                <flux:icon.credit-card class="size-5 text-emerald-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $suscripcionesActivas }}</div>
            <div class="mt-1 text-xs text-zinc-500">Plan vigente</div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Citas hoy</span>
                <flux:icon.calendar class="size-5 text-indigo-500" />
            </div>
            <div class="mt-2 text-3xl font-bold">{{ $citasHoy }}</div>
            <div class="mt-1 flex gap-3 text-xs">
                <span class="text-sky-600">Kine: {{ $citasHoyKine }}</span>
                <span class="text-pink-600">Estética: {{ $citasHoyEstetic }}</span>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Ingresos mes</span>
                <flux:icon.banknotes class="size-5 text-amber-500" />
            </div>
            <div class="mt-2 text-3xl font-bold">${{ number_format($ingresosTotal, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-zinc-500">Total módulos</div>
        </div>
    </div>

    {{-- Gráfico + distribución --}}
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 lg:col-span-2 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <flux:heading size="lg">Ingresos últimos 7 días</flux:heading>
                    <flux:text class="text-xs text-zinc-500">Gym · Kinesiología · Estética</flux:text>
                </div>
                <div class="flex gap-3 text-xs">
                    <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded bg-amber-400"></span> Gym</span>
                    <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded bg-sky-400"></span> Kine</span>
                    <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded bg-pink-400"></span> Estética</span>
                </div>
            </div>
            <div class="flex h-52 items-end gap-2">
                @foreach ($serie as $s)
                    @php $h = $s['total'] > 0 ? max(4, ($s['total'] / $serieMax) * 180) : 2; @endphp
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <div class="text-[10px] font-medium text-zinc-600 dark:text-zinc-400">
                            {{ $s['total'] > 0 ? '$'.number_format($s['total']/1000, 0).'k' : '' }}
                        </div>
                        <div class="flex w-full flex-col-reverse overflow-hidden rounded-t" style="height: {{ $h }}px">
                            @if ($s['gym'] > 0)
                                <div class="bg-amber-400" style="flex: {{ $s['gym'] }}"></div>
                            @endif
                            @if ($s['kine'] > 0)
                                <div class="bg-sky-400" style="flex: {{ $s['kine'] }}"></div>
                            @endif
                            @if ($s['est'] > 0)
                                <div class="bg-pink-400" style="flex: {{ $s['est'] }}"></div>
                            @endif
                            @if ($s['total'] == 0)
                                <div class="h-0.5 w-full bg-zinc-200 dark:bg-zinc-700"></div>
                            @endif
                        </div>
                        <div class="text-[11px] text-zinc-500">{{ ucfirst($s['label']) }}</div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 grid grid-cols-3 gap-2 border-t pt-3 text-sm dark:border-zinc-700">
                <div><span class="text-xs text-zinc-500">Gym mes</span><div class="font-semibold text-amber-600">${{ number_format($ingresosGym, 0, ',', '.') }}</div></div>
                <div><span class="text-xs text-zinc-500">Kine mes</span><div class="font-semibold text-sky-600">${{ number_format($ingresosKine, 0, ',', '.') }}</div></div>
                <div><span class="text-xs text-zinc-500">Estética mes</span><div class="font-semibold text-pink-600">${{ number_format($ingresosEstetic, 0, ',', '.') }}</div></div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">Citas de la semana</flux:heading>
            <flux:text class="text-xs text-zinc-500">Distribución por estado</flux:text>
            <div class="mt-4 space-y-3">
                @php
                    $colores = [
                        'pendiente' => 'bg-zinc-400',
                        'confirmado' => 'bg-blue-500',
                        'atendido' => 'bg-emerald-500',
                        'cancelado' => 'bg-red-400',
                        'ausente' => 'bg-amber-500',
                    ];
                @endphp
                @foreach ($distribucion as $estado => $count)
                    @php $pct = $totalSemana > 0 ? round(($count / $totalSemana) * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs">
                            <span class="font-medium capitalize">{{ $estado }}</span>
                            <span class="text-zinc-500">{{ $count }} ({{ $pct }}%)</span>
                        </div>
                        <div class="mt-1 h-2 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full {{ $colores[$estado] }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 border-t pt-3 text-center text-xs text-zinc-500 dark:border-zinc-700">
                Total semana: <strong>{{ array_sum($distribucion) }}</strong>
            </div>
        </div>
    </div>

    {{-- Próximas + por vencer + pagos --}}
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="lg">Próximas citas de hoy</flux:heading>
                <flux:icon.clock class="size-5 text-zinc-400" />
            </div>
            @forelse ($proximas as $c)
                <a href="{{ $c['url'] }}" wire:navigate class="mb-2 flex items-center gap-2 rounded border border-zinc-100 p-2 text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase text-white {{ $c['type'] === 'kine' ? 'bg-sky-600' : 'bg-pink-600' }}">{{ $c['type'] }}</span>
                    <span class="font-mono text-xs">{{ $c['inicio']->format('H:i') }}</span>
                    <span class="flex-1 truncate">{{ $c['person'] }}</span>
                    <span class="text-xs text-zinc-500">{{ ucfirst($c['estado']) }}</span>
                </a>
            @empty
                <div class="py-6 text-center text-sm text-zinc-400">Sin citas programadas para hoy</div>
            @endforelse
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="lg">Suscripciones por vencer</flux:heading>
                <flux:icon.exclamation-triangle class="size-5 text-amber-500" />
            </div>
            @forelse ($porVencer as $s)
                @php $dias = now()->startOfDay()->diffInDays($s->end_date, false); @endphp
                <div class="mb-2 flex items-center gap-2 rounded border border-zinc-100 p-2 text-sm dark:border-zinc-700">
                    <div class="flex-1">
                        <div class="font-medium">{{ $s->person?->full_name }}</div>
                        <div class="text-xs text-zinc-500">{{ $s->plan?->name }}</div>
                    </div>
                    <span class="rounded px-2 py-0.5 text-xs font-semibold {{ $dias <= 2 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $dias <= 0 ? 'Hoy' : $dias.'d' }}
                    </span>
                </div>
            @empty
                <div class="py-6 text-center text-sm text-zinc-400">Ninguna por vencer</div>
            @endforelse
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="lg">Pagos recientes</flux:heading>
                <flux:icon.banknotes class="size-5 text-emerald-500" />
            </div>
            @forelse ($pagosRecientes as $p)
                @php
                    $badge = ['gym' => 'bg-amber-500', 'kine' => 'bg-sky-600', 'estetic' => 'bg-pink-600'][$p['type']];
                @endphp
                <div class="mb-2 flex items-center gap-2 rounded border border-zinc-100 p-2 text-sm dark:border-zinc-700">
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase text-white {{ $badge }}">{{ $p['type'] }}</span>
                    <div class="flex-1 truncate">
                        <div class="truncate">{{ $p['person'] }}</div>
                        <div class="text-xs text-zinc-500">{{ $p['date']?->format('d/m/Y') }}</div>
                    </div>
                    <span class="font-semibold text-emerald-600">${{ number_format($p['amount'], 0, ',', '.') }}</span>
                </div>
            @empty
                <div class="py-6 text-center text-sm text-zinc-400">Sin pagos registrados</div>
            @endforelse
        </div>
    </div>
</div>
