<div class="p-6 space-y-6">
    <div>
        <flux:heading size="xl">Reportes</flux:heading>
        <flux:text class="text-zinc-500">Análisis operativo y financiero del negocio</flux:text>
    </div>

    @php
        $reports = [
            [
                'name' => 'Pagos',
                'desc' => 'Recaudación consolidada por módulo, método y estado',
                'icon' => 'banknotes',
                'url' => route('admin.reports.payments'),
                'from' => 'from-emerald-500', 'to' => 'to-teal-600',
                'enabled' => true,
            ],
            [
                'name' => 'Asistencias',
                'desc' => 'Citas atendidas, ausentes y canceladas por módulo',
                'icon' => 'calendar-days',
                'url' => route('admin.reports.attendance'),
                'from' => 'from-sky-500', 'to' => 'to-indigo-600',
                'enabled' => true,
            ],
            [
                'name' => 'Suscripciones',
                'desc' => 'Activas, vencimientos, renovaciones y churn',
                'icon' => 'credit-card',
                'url' => '#', 'from' => 'from-amber-500', 'to' => 'to-orange-600',
                'enabled' => false,
            ],
            [
                'name' => 'Tratamientos',
                'desc' => 'Progreso de sesiones kine y estéticas',
                'icon' => 'clipboard-document-check',
                'url' => '#', 'from' => 'from-pink-500', 'to' => 'to-rose-600',
                'enabled' => false,
            ],
            [
                'name' => 'Personas',
                'desc' => 'Altas, distribución por módulo y estado',
                'icon' => 'user-group',
                'url' => '#', 'from' => 'from-violet-500', 'to' => 'to-purple-600',
                'enabled' => false,
            ],
            [
                'name' => 'Profesionales',
                'desc' => 'Productividad y carga de agenda',
                'icon' => 'briefcase',
                'url' => '#', 'from' => 'from-zinc-500', 'to' => 'to-zinc-700',
                'enabled' => false,
            ],
        ];
    @endphp

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($reports as $r)
            @if ($r['enabled'])
                <a href="{{ $r['url'] }}" wire:navigate class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="h-2 bg-gradient-to-r {{ $r['from'] }} {{ $r['to'] }}"></div>
                    <div class="p-5">
                        <div class="mb-3 flex size-10 items-center justify-center rounded-lg bg-gradient-to-br {{ $r['from'] }} {{ $r['to'] }} text-white">
                            <flux:icon :name="$r['icon']" class="size-5" />
                        </div>
                        <h3 class="text-lg font-semibold">{{ $r['name'] }}</h3>
                        <p class="mt-1 text-sm text-zinc-500">{{ $r['desc'] }}</p>
                        <div class="mt-4 flex items-center gap-1 text-sm font-medium text-zinc-700 group-hover:text-zinc-900 dark:text-zinc-300">
                            Ver reporte <flux:icon.arrow-right class="size-4 transition group-hover:translate-x-0.5" />
                        </div>
                    </div>
                </a>
            @else
                <div class="relative overflow-hidden rounded-xl border border-dashed border-zinc-300 bg-white p-5 opacity-60 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mb-3 flex size-10 items-center justify-center rounded-lg bg-zinc-200 text-zinc-500 dark:bg-zinc-800">
                        <flux:icon :name="$r['icon']" class="size-5" />
                    </div>
                    <h3 class="text-lg font-semibold">{{ $r['name'] }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ $r['desc'] }}</p>
                    <span class="mt-4 inline-block rounded bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800">Próximamente</span>
                </div>
            @endif
        @endforeach
    </div>
</div>
