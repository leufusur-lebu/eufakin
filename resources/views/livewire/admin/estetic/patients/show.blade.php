<div class="p-6 space-y-6">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('admin.estetic.patients.index') }}" wire:navigate class="hover:underline">Pacientes Estética</a>
        <flux:icon.chevron-right class="size-3" />
        <span>{{ $person->full_name }}</span>
    </div>

    {{-- Cabecera del paciente --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-pink-50 via-white to-rose-50 dark:border-zinc-700 dark:from-pink-950/30 dark:via-zinc-900 dark:to-rose-950/30">
        <div class="flex flex-wrap items-start gap-4 p-6">
            <div class="flex size-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-400 to-rose-500 text-2xl font-bold text-white shadow-lg">
                {{ strtoupper(substr($person->first_name, 0, 1).substr($person->last_name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-baseline gap-3">
                    <flux:heading size="xl">{{ $person->full_name }}</flux:heading>
                    @if ($person->birth_date)
                        <span class="text-sm text-zinc-500">{{ $person->birth_date->age }} años</span>
                    @endif
                </div>
                <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span><flux:icon.identification class="inline size-4 -mt-0.5" /> {{ $person->rut }}</span>
                    @if ($person->phone) <span><flux:icon.phone class="inline size-4 -mt-0.5" /> {{ $person->phone }}</span>@endif
                    @if ($person->email) <span><flux:icon.envelope class="inline size-4 -mt-0.5" /> {{ $person->email }}</span>@endif
                </div>

                {{-- Alertas --}}
                <div class="mt-3 flex flex-wrap gap-2">
                    @if ($profile->skin_type)
                        <span class="inline-flex items-center gap-1 rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-medium text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                            <flux:icon.sparkles class="size-3.5" />
                            Piel: {{ $profile->skin_type }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Acciones rápidas --}}
            <div class="flex flex-wrap gap-2">
                <flux:button href="{{ route('admin.estetic.tipos-tratamientos.index') }}" variant="primary" icon="sparkles" wire:navigate>Aplicar protocolo</flux:button>
                <flux:button href="{{ route('admin.estetic.appointments.create') }}" icon="calendar-days" wire:navigate>Agendar</flux:button>
                <flux:button href="{{ route('admin.people.edit', $person) }}" variant="ghost" icon="pencil" wire:navigate>Editar</flux:button>
            </div>
        </div>

        {{-- Stats bar --}}
        <div class="grid grid-cols-2 gap-px border-t border-zinc-200 bg-zinc-200 md:grid-cols-4 dark:border-zinc-700 dark:bg-zinc-700">
            <div class="bg-white p-4 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Tratamientos activos</div>
                <div class="mt-1 text-2xl font-bold text-pink-600">{{ $stats['treatments_active'] }}</div>
            </div>
            <div class="bg-white p-4 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Sesiones realizadas</div>
                <div class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['sessions_done'] }}</div>
            </div>
            <div class="bg-white p-4 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Inversión total</div>
                <div class="mt-1 text-2xl font-bold">${{ number_format($totalProtocols, 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-4 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Saldo</div>
                <div class="mt-1 text-2xl font-bold {{ $balance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                    ${{ number_format($balance, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas clínicas cross-perfil --}}
    <x-clinical-alerts :person="$person" />

    {{-- Tabs --}}
    @php
        $tabs = [
            'overview'   => ['Resumen',     'home'],
            'treatments' => ['Tratamientos','clipboard-document-check'],
            'sessions'   => ['Sesiones',    'calendar-days'],
            'gallery'    => ['Galería',     'photo'],
            'finance'    => ['Finanzas',    'banknotes'],
            'profile'    => ['Perfil',      'user'],
        ];
    @endphp
    <div class="flex flex-wrap gap-1 border-b border-zinc-200 dark:border-zinc-700">
        @foreach ($tabs as $val => [$label, $icon])
            <button wire:click="$set('tab', '{{ $val }}')"
                class="flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition
                    {{ $tab === $val
                        ? 'border-pink-500 text-pink-600 dark:text-pink-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                <flux:icon :name="$icon" class="size-4" />
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ====== TAB: RESUMEN ====== --}}
    @if ($tab === 'overview')
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Tratamiento activo --}}
            <div class="lg:col-span-2 space-y-4">
                @if ($activeTreatment)
                    @php $progress = $activeTreatment->sesiones_totales ? round(($activeTreatment->sesiones_realizadas / $activeTreatment->sesiones_totales) * 100) : 0; @endphp
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-pink-600">Tratamiento activo</div>
                                <h3 class="mt-1 text-lg font-semibold">{{ $activeTreatment->tipoTratamiento?->nombre ?? $activeTreatment->zona_tratada }}</h3>
                                <p class="text-sm text-zinc-500">{{ $activeTreatment->zona_tratada }} · con {{ $activeTreatment->professional?->full_name ?? '—' }}</p>
                            </div>
                            <a href="{{ route('admin.estetic.treatments.edit', $activeTreatment) }}" wire:navigate class="text-sm text-pink-600 hover:underline">Ver detalle →</a>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <span>Progreso de sesiones</span>
                                <span>{{ $activeTreatment->sesiones_realizadas }} / {{ $activeTreatment->sesiones_totales }}</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-pink-100 dark:bg-pink-900/40">
                                <div class="h-full rounded-full bg-gradient-to-r from-pink-400 to-rose-500" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
                            <div>
                                <div class="text-xs text-zinc-500">Inicio</div>
                                <div class="font-medium">{{ $activeTreatment->fecha_inicio?->format('d/m/Y') }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Costo total</div>
                                <div class="font-medium">${{ number_format($activeTreatment->costo_total, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Saldo tratamiento</div>
                                @php $tBal = (float) $activeTreatment->costo_total - (float) $activeTreatment->payments()->where('estado','pagado')->sum('monto'); @endphp
                                <div class="font-medium {{ $tBal > 0 ? 'text-amber-600' : 'text-emerald-600' }}">${{ number_format($tBal, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:icon.clipboard-document-list class="mx-auto size-10 text-zinc-300" />
                        <p class="mt-2 text-sm text-zinc-500">Sin tratamiento activo</p>
                        <flux:button href="{{ route('admin.estetic.treatments.create') }}" variant="primary" size="sm" class="mt-3" wire:navigate>Crear tratamiento</flux:button>
                    </div>
                @endif

                {{-- Próximas citas --}}
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                        <h3 class="font-semibold">Próximas citas</h3>
                        <span class="text-xs text-zinc-500">{{ $upcoming->count() }} agendadas</span>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($upcoming->take(5) as $a)
                            <div class="flex items-center gap-4 px-5 py-3">
                                <div class="flex size-10 flex-col items-center justify-center rounded-lg bg-pink-50 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                                    <span class="text-[10px] font-semibold uppercase">{{ $a->inicio->isoFormat('MMM') }}</span>
                                    <span class="text-sm font-bold leading-none">{{ $a->inicio->format('d') }}</span>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium">{{ $a->inicio->format('H:i') }} — {{ $a->motivo ?? 'Sesión' }}</div>
                                    <div class="text-xs text-zinc-500">{{ $a->professional?->full_name ?? '—' }}</div>
                                </div>
                                <flux:badge size="sm" :color="$a->estado === 'confirmado' ? 'green' : 'amber'">{{ ucfirst($a->estado) }}</flux:badge>
                                <x-whatsapp-button
                                    :phone="$person->phone"
                                    template="appointment_reminder"
                                    :vars="[
                                        'nombre' => $person->first_name,
                                        'servicio' => $a->treatment?->tipoTratamiento?->nombre ?? 'estética',
                                        'fecha' => $a->inicio->isoFormat('dddd D [de] MMMM'),
                                        'hora' => $a->inicio->format('H:i'),
                                    ]"
                                    label="" />
                                <flux:button href="{{ route('admin.estetic.sessions.attend', $a) }}" wire:navigate size="sm" variant="primary" icon="check">Atender</flux:button>
                            </div>
                        @empty
                            <div class="px-5 py-6 text-center text-sm text-zinc-400">Sin próximas citas agendadas</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Lateral: estado financiero --}}
            <div class="space-y-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Estado financiero</h3>
                    <div class="mt-3 space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500">Total protocolos</span>
                            <span class="font-semibold">${{ number_format($totalProtocols, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500">Pagado</span>
                            <span class="font-semibold text-emerald-600">${{ number_format($paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500">Pendiente</span>
                            <span class="font-semibold text-amber-600">${{ number_format($pending, 0, ',', '.') }}</span>
                        </div>
                        <hr class="border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">Saldo</span>
                            <span class="text-lg font-bold {{ $balance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">${{ number_format($balance, 0, ',', '.') }}</span>
                        </div>
                        @php $progressPay = $totalProtocols > 0 ? min(100, round(($paid / $totalProtocols) * 100)) : 0; @endphp
                        <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $progressPay }}%"></div>
                        </div>
                        <flux:button href="{{ route('admin.estetic.payments.create') }}" variant="primary" icon="banknotes" class="w-full" wire:navigate>Registrar pago</flux:button>
                    </div>
                </div>

                {{-- Mini stats --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Asistencia</h3>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-emerald-600">Atendidas</span><span class="font-semibold">{{ $stats['sessions_done'] }}</span></div>
                        <div class="flex justify-between"><span class="text-rose-600">Ausencias / canceladas</span><span class="font-semibold">{{ $stats['sessions_no_show'] }}</span></div>
                        <div class="flex justify-between"><span class="text-zinc-500">Tratamientos finalizados</span><span class="font-semibold">{{ $stats['treatments_finished'] }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ====== TAB: TRATAMIENTOS ====== --}}
    @if ($tab === 'treatments')
        <div class="space-y-3">
            @forelse ($treatments as $t)
                @php
                    $tProgress = $t->sesiones_totales ? round(($t->sesiones_realizadas / $t->sesiones_totales) * 100) : 0;
                    $estadoColor = match($t->estado) {
                        'activo' => 'green', 'finalizado' => 'sky',
                        'suspendido' => 'amber', 'cancelado' => 'red', default => 'zinc',
                    };
                @endphp
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold">{{ $t->tipoTratamiento?->nombre ?? $t->zona_tratada }}</h3>
                                <flux:badge size="sm" :color="$estadoColor">{{ ucfirst($t->estado) }}</flux:badge>
                            </div>
                            <p class="text-sm text-zinc-500">{{ $t->zona_tratada }} · {{ $t->professional?->full_name ?? '—' }}</p>
                            <p class="text-xs text-zinc-400">Inicio {{ $t->fecha_inicio?->format('d/m/Y') }} @if ($t->fecha_fin) · Fin {{ $t->fecha_fin->format('d/m/Y') }} @endif</p>
                        </div>
                        <a href="{{ route('admin.estetic.treatments.edit', $t) }}" wire:navigate class="text-sm text-pink-600 hover:underline">Editar →</a>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs text-zinc-500">
                            <span>{{ $t->sesiones_realizadas }} / {{ $t->sesiones_totales }} sesiones</span>
                            <span>${{ number_format($t->costo_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-gradient-to-r from-pink-400 to-rose-500" style="width: {{ $tProgress }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
                    <p class="text-sm text-zinc-500">Aún no hay tratamientos registrados.</p>
                </div>
            @endforelse
        </div>
    @endif

    {{-- ====== TAB: SESIONES ====== --}}
    @if ($tab === 'sessions')
        <div class="space-y-4">
            {{-- Próximas con botón Atender --}}
            @if ($upcoming->count() > 0)
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                        <h3 class="font-semibold">Próximas citas</h3>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($upcoming as $a)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap font-medium">{{ $a->inicio?->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2 text-zinc-600">{{ $a->treatment?->tipoTratamiento?->nombre ?? $a->motivo ?? '—' }}</td>
                                    <td class="px-4 py-2 text-zinc-600 text-xs">{{ $a->professional?->full_name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-center"><flux:badge size="sm" :color="$a->estado === 'confirmado' ? 'green' : 'amber'">{{ ucfirst($a->estado) }}</flux:badge></td>
                                    <td class="px-4 py-2 text-right">
                                        <flux:button href="{{ route('admin.estetic.sessions.attend', $a) }}" wire:navigate size="sm" variant="primary" icon="check">Atender</flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Sesiones clínicas registradas (con notas + fotos) --}}
            <div>
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Historial clínico</h3>
                <div class="space-y-3">
                    @forelse ($clinicalSessions as $s)
                        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="flex size-8 items-center justify-center rounded-full bg-pink-100 text-xs font-bold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">{{ $s->numero_sesion }}</span>
                                        <h4 class="font-semibold">{{ $s->treatment?->tipoTratamiento?->nombre ?? 'Sesión' }}</h4>
                                        @if ($s->intensidad)
                                            <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] uppercase text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $s->intensidad }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-zinc-500">
                                        {{ $s->fecha?->format('d/m/Y') }}
                                        @if ($s->zona_especifica) · {{ $s->zona_especifica }} @endif
                                        @if ($s->duracion_real_minutos) · {{ $s->duracion_real_minutos }} min @endif
                                        @if ($s->appointment?->professional) · {{ $s->appointment->professional->full_name }} @endif
                                    </div>
                                </div>
                                <flux:badge size="sm" color="green">Realizada</flux:badge>
                            </div>

                            @if ($s->productos_utilizados || $s->resultados_observados || $s->notas_clinicas)
                                <div class="mt-3 grid gap-3 md:grid-cols-3">
                                    @if ($s->productos_utilizados)
                                        <div class="rounded bg-zinc-50 p-3 text-xs dark:bg-zinc-800/50">
                                            <div class="font-semibold text-zinc-600 dark:text-zinc-300">Productos</div>
                                            <div class="mt-1 text-zinc-600 dark:text-zinc-400">{{ $s->productos_utilizados }}</div>
                                        </div>
                                    @endif
                                    @if ($s->resultados_observados)
                                        <div class="rounded bg-emerald-50 p-3 text-xs dark:bg-emerald-950/30">
                                            <div class="font-semibold text-emerald-700 dark:text-emerald-300">Resultados</div>
                                            <div class="mt-1 text-emerald-700 dark:text-emerald-400">{{ $s->resultados_observados }}</div>
                                        </div>
                                    @endif
                                    @if ($s->notas_clinicas)
                                        <div class="rounded bg-pink-50 p-3 text-xs dark:bg-pink-950/30">
                                            <div class="font-semibold text-pink-700 dark:text-pink-300">Notas</div>
                                            <div class="mt-1 text-pink-700 dark:text-pink-400">{{ $s->notas_clinicas }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($s->photos->count() > 0)
                                <div class="mt-3 flex gap-2 overflow-x-auto">
                                    @foreach ($s->photos as $ph)
                                        <a href="{{ $ph->url }}" target="_blank" class="group relative shrink-0">
                                            <img src="{{ $ph->url }}" alt="" class="size-20 rounded-lg border border-zinc-200 object-cover transition group-hover:scale-105 dark:border-zinc-700">
                                            <span class="absolute bottom-1 left-1 rounded bg-black/60 px-1 text-[9px] uppercase text-white">{{ $ph->tipo }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                            <flux:icon.calendar-days class="mx-auto size-10 text-zinc-300" />
                            <p class="mt-2 text-sm text-zinc-500">Aún no hay sesiones clínicas registradas.</p>
                            <p class="text-xs text-zinc-400">Atiende una cita para comenzar el historial.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Citas históricas (sin nota clínica) --}}
            @if ($history->count() > 0)
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                        <h3 class="font-semibold">Citas pasadas</h3>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($history as $a)
                                @php $ec = match($a->estado) { 'atendido' => 'green', 'cancelado' => 'red', 'ausente' => 'red', default => 'zinc' }; @endphp
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $a->inicio?->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2 text-zinc-600">{{ $a->treatment?->tipoTratamiento?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-2 text-zinc-600 text-xs">{{ $a->motivo ?? '—' }}</td>
                                    <td class="px-4 py-2 text-center"><flux:badge size="sm" :color="$ec">{{ ucfirst($a->estado) }}</flux:badge></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- ====== TAB: GALERÍA ====== --}}
    @if ($tab === 'gallery')
        @if ($photos->count() === 0)
            <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
                <flux:icon.photo class="mx-auto size-10 text-zinc-300" />
                <p class="mt-3 text-sm text-zinc-500">Aún no hay fotos en la galería.</p>
                <p class="text-xs text-zinc-400">Las fotos se suben al atender una sesión.</p>
            </div>
        @else
            <div class="space-y-6">
                {{-- Comparador antes/después --}}
                @php
                    $antes   = $photosByTipo['antes']   ?? collect();
                    $durante = $photosByTipo['durante'] ?? collect();
                    $despues = $photosByTipo['despues'] ?? collect();
                @endphp

                @if ($antes->isNotEmpty() && $despues->isNotEmpty())
                    <div class="rounded-xl border border-pink-200 bg-pink-50/30 p-5 dark:border-pink-900 dark:bg-pink-950/20">
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-pink-700 dark:text-pink-300">
                            <flux:icon.arrows-right-left class="size-5" /> Comparativo Antes / Después
                        </h3>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <div class="mb-2 text-xs font-semibold uppercase text-sky-700">Antes — {{ $antes->first()->tomada_at?->format('d/m/Y') }}</div>
                                <a href="{{ $antes->first()->url }}" target="_blank">
                                    <img src="{{ $antes->first()->url }}" class="aspect-square w-full rounded-xl border border-sky-300 object-cover">
                                </a>
                            </div>
                            <div>
                                <div class="mb-2 text-xs font-semibold uppercase text-emerald-700">Después — {{ $despues->first()->tomada_at?->format('d/m/Y') }}</div>
                                <a href="{{ $despues->first()->url }}" target="_blank">
                                    <img src="{{ $despues->first()->url }}" class="aspect-square w-full rounded-xl border border-emerald-300 object-cover">
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Galería completa por tipo --}}
                @foreach (['antes' => ['Antes', 'sky'], 'durante' => ['Durante', 'amber'], 'despues' => ['Después', 'emerald'], 'otro' => ['Otras', 'zinc']] as $tipo => [$label, $color])
                    @php $set = $photosByTipo[$tipo] ?? collect(); @endphp
                    @if ($set->isNotEmpty())
                        <div>
                            <div class="mb-3 flex items-center gap-2">
                                <h3 class="text-sm font-bold uppercase tracking-wide text-{{ $color }}-700 dark:text-{{ $color }}-300">{{ $label }}</h3>
                                <span class="rounded-full bg-{{ $color }}-100 px-2 text-xs text-{{ $color }}-700 dark:bg-{{ $color }}-900/40 dark:text-{{ $color }}-300">{{ $set->count() }}</span>
                            </div>
                            <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                                @foreach ($set as $ph)
                                    <a href="{{ $ph->url }}" target="_blank" class="group relative overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                                        <img src="{{ $ph->url }}" class="aspect-square w-full object-cover transition group-hover:scale-105">
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2 opacity-0 transition group-hover:opacity-100">
                                            <div class="text-[10px] text-white">{{ $ph->tomada_at?->format('d/m/Y') }}</div>
                                            @if ($ph->treatment?->tipoTratamiento)
                                                <div class="text-[10px] text-white/80">{{ $ph->treatment->tipoTratamiento->nombre }}</div>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    @endif

    {{-- ====== TAB: FINANZAS ====== --}}
    @if ($tab === 'finance')
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <h3 class="font-semibold">Historial de pagos</h3>
                <flux:button href="{{ route('admin.estetic.payments.create') }}" variant="primary" size="sm" icon="plus" wire:navigate>Registrar pago</flux:button>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Tratamiento</th>
                        <th class="px-4 py-3 text-left">Método</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($payments as $p)
                        @php
                            $pColor = match($p->estado) { 'pagado' => 'green', 'pendiente' => 'amber', 'anulado' => 'red', default => 'zinc' };
                        @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="px-4 py-3">{{ $p->fecha?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $p->treatment?->tipoTratamiento?->nombre ?? $p->treatment?->zona_tratada ?? '—' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($p->metodo) }}</td>
                            <td class="px-4 py-3 text-center"><flux:badge size="sm" :color="$pColor">{{ ucfirst($p->estado) }}</flux:badge></td>
                            <td class="px-4 py-3 text-right font-semibold">${{ number_format($p->monto, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-zinc-400">Sin pagos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ====== TAB: PERFIL ====== --}}
    @if ($tab === 'profile')
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Datos personales</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-zinc-500">RUT</dt><dd>{{ $person->rut }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Nombre</dt><dd>{{ $person->full_name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Nacimiento</dt><dd>{{ $person->birth_date?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Género</dt><dd>{{ $person->gender }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Teléfono</dt><dd>{{ $person->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Email</dt><dd>{{ $person->email ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Dirección</dt><dd>{{ $person->address ?? '—' }}</dd></div>
                </dl>
                <flux:button href="{{ route('admin.people.edit', $person) }}" variant="ghost" size="sm" icon="pencil" class="mt-3" wire:navigate>Editar persona</flux:button>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Ficha estética</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-zinc-500">Tipo de piel</dt><dd>{{ $profile->skin_type ?? '—' }}</dd></div>
                    <div>
                        <dt class="text-zinc-500">Observaciones del módulo</dt>
                        <dd class="mt-1 rounded bg-zinc-50 p-2 dark:bg-zinc-800">{{ $profile->observations ?: '—' }}</dd>
                    </div>
                </dl>
                <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs dark:border-rose-900 dark:bg-rose-950/30">
                    <p class="text-rose-700 dark:text-rose-300">
                        <flux:icon.information-circle class="inline size-4 -mt-0.5" />
                        Alergias, antecedentes y observaciones médicas viven en la
                        <a href="{{ route('admin.people.clinical', $person) }}" wire:navigate class="font-semibold underline">ficha clínica unificada</a>.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
