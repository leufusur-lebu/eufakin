<div class="p-6 space-y-6">
    <div class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('admin.kine.patients.index') }}" wire:navigate class="hover:underline">Pacientes Kinesiología</a>
        <flux:icon.chevron-right class="size-3" />
        <span>{{ $person->full_name }}</span>
    </div>

    {{-- Cabecera --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-sky-50 via-white to-indigo-50 dark:border-zinc-700 dark:from-sky-950/30 dark:via-zinc-900 dark:to-indigo-950/30">
        <div class="flex flex-wrap items-start gap-4 p-6">
            <div class="flex size-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-400 to-indigo-500 text-2xl font-bold text-white shadow-lg">
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

                <div class="mt-3 flex flex-wrap gap-2">
                    @if ($profile->health_insurance)
                        <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                            <flux:icon.shield-check class="size-3.5" />
                            {{ $profile->health_insurance }} {{ $profile->insurance_number ? '· '.$profile->insurance_number : '' }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <flux:button href="{{ route('admin.kine.tipos-tratamientos.index') }}" variant="primary" icon="sparkles" wire:navigate>Aplicar protocolo</flux:button>
                <flux:button href="{{ route('admin.kine.appointments.create') }}" icon="calendar-days" wire:navigate>Agendar</flux:button>
                <flux:button href="{{ route('admin.people.edit', $person) }}" variant="ghost" icon="pencil" wire:navigate>Editar</flux:button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-px border-t border-zinc-200 bg-zinc-200 md:grid-cols-4 dark:border-zinc-700 dark:bg-zinc-700">
            <div class="bg-white p-4 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Tratamientos activos</div>
                <div class="mt-1 text-2xl font-bold text-sky-600">{{ $stats['treatments_active'] }}</div>
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
            'appointments' => ['Citas',     'calendar'],
            'sessions'   => ['Sesiones',    'clipboard-document-list'],
            'evolution'  => ['Evolución',   'chart-bar'],
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
                        ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                <flux:icon :name="$icon" class="size-4" />
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ====== TAB: RESUMEN ====== --}}
    @if ($tab === 'overview')
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                @if ($activeTreatment)
                    @php $progress = $activeTreatment->sesiones_totales ? round(($activeTreatment->sesiones_realizadas / $activeTreatment->sesiones_totales) * 100) : 0; @endphp
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-sky-600">Tratamiento activo</div>
                                <h3 class="mt-1 text-lg font-semibold">{{ $activeTreatment->tipoTratamiento?->nombre ?? $activeTreatment->diagnostico }}</h3>
                                <p class="text-sm text-zinc-500">{{ $activeTreatment->diagnostico }} @if ($activeTreatment->zona_tratada) · {{ $activeTreatment->zona_tratada }}@endif</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <span>Progreso de sesiones</span>
                                <span>{{ $activeTreatment->sesiones_realizadas }} / {{ $activeTreatment->sesiones_totales }}</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-sky-100 dark:bg-sky-900/40">
                                <div class="h-full rounded-full bg-gradient-to-r from-sky-400 to-indigo-500" style="width: {{ $progress }}%"></div>
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
                        <flux:button href="{{ route('admin.kine.tipos-tratamientos.index') }}" variant="primary" size="sm" class="mt-3" wire:navigate>Aplicar protocolo</flux:button>
                    </div>
                @endif

                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                        <h3 class="font-semibold">Próximas citas</h3>
                        <span class="text-xs text-zinc-500">{{ $upcoming->count() }} agendadas</span>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($upcoming->take(5) as $a)
                            <div class="flex items-center gap-4 px-5 py-3">
                                <div class="flex size-10 flex-col items-center justify-center rounded-lg bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
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
                                        'servicio' => $a->treatment?->tipoTratamiento?->nombre ?? 'kinesiología',
                                        'fecha' => $a->inicio->isoFormat('dddd D [de] MMMM'),
                                        'hora' => $a->inicio->format('H:i'),
                                    ]"
                                    label="" />
                                <flux:button href="{{ route('admin.kine.sessions.attend', $a) }}" wire:navigate size="sm" variant="primary" icon="check">Atender</flux:button>
                            </div>
                        @empty
                            <div class="px-5 py-6 text-center text-sm text-zinc-400">Sin próximas citas agendadas</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Estado financiero</h3>
                    <div class="mt-3 space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500">Total tratamientos</span>
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
                        <flux:button href="{{ route('admin.kine.payments.create') }}" variant="primary" icon="banknotes" class="w-full" wire:navigate>Registrar pago</flux:button>
                    </div>
                </div>

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
                                <h3 class="font-semibold">{{ $t->tipoTratamiento?->nombre ?? $t->diagnostico }}</h3>
                                <flux:badge size="sm" :color="$estadoColor">{{ ucfirst($t->estado) }}</flux:badge>
                            </div>
                            <p class="text-sm text-zinc-500">{{ $t->diagnostico }} @if ($t->zona_tratada) · {{ $t->zona_tratada }}@endif</p>
                            <p class="text-xs text-zinc-400">Inicio {{ $t->fecha_inicio?->format('d/m/Y') }} @if ($t->fecha_fin) · Fin {{ $t->fecha_fin->format('d/m/Y') }}@endif</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs text-zinc-500">
                            <span>{{ $t->sesiones_realizadas }} / {{ $t->sesiones_totales }} sesiones</span>
                            <span>${{ number_format($t->costo_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-gradient-to-r from-sky-400 to-indigo-500" style="width: {{ $tProgress }}%"></div>
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

    {{-- ====== TAB: CITAS (gestión) ====== --}}
    @if ($tab === 'appointments')
        <div class="space-y-4">
            {{-- Header con acción --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Gestión de citas</h3>
                <flux:button wire:click="openAppointment" variant="primary" icon="plus">Agendar nueva cita</flux:button>
            </div>

            {{-- Filtros --}}
            @php
                $apptFilters = [
                    'all'      => ['Todas',         $apptCounts['all']],
                    'upcoming' => ['Próximas',      $apptCounts['upcoming']],
                    'active'   => ['Pend./Conf.',   $apptCounts['active']],
                    'past'     => ['Pasadas',       $apptCounts['past']],
                ];
            @endphp
            <div class="flex flex-wrap gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800 w-fit">
                @foreach ($apptFilters as $val => [$label, $count])
                    <button wire:click="$set('apptFilter', '{{ $val }}')"
                        class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition {{ $apptFilter === $val ? 'bg-white shadow-sm dark:bg-zinc-900' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                        <span>{{ $label }}</span>
                        <span class="rounded-full bg-zinc-200 px-1.5 text-[10px] dark:bg-zinc-700">{{ $count }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Lista cronológica --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <table class="w-full text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left">Fecha</th>
                            <th class="px-4 py-3 text-left">Hora</th>
                            <th class="px-4 py-3 text-left">Tratamiento</th>
                            <th class="px-4 py-3 text-left">Profesional</th>
                            <th class="px-4 py-3 text-left">Motivo</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($apptList as $a)
                            @php
                                $eColor = match($a->estado) {
                                    'atendido' => 'green', 'confirmado' => 'sky',
                                    'pendiente' => 'amber', 'cancelado' => 'red', 'ausente' => 'red',
                                    default => 'zinc',
                                };
                                $isFuture = $a->inicio?->gte(now());
                                $canAttend = in_array($a->estado, ['pendiente','confirmado']);
                                $rowClass = $a->estado === 'cancelado' ? 'opacity-50' : '';
                            @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 {{ $rowClass }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $a->inicio?->format('d/m/Y') }}</div>
                                    <div class="text-xs text-zinc-500">{{ ucfirst($a->inicio?->isoFormat('ddd')) }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono">
                                    {{ $a->inicio?->format('H:i') }}<span class="text-zinc-400">–{{ $a->fin?->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 text-zinc-600">{{ $a->treatment?->tipoTratamiento?->nombre ?? $a->treatment?->diagnostico ?? '—' }}</td>
                                <td class="px-4 py-3 text-zinc-600 text-xs">{{ $a->professional?->full_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-zinc-600 text-xs">{{ $a->motivo ?? '—' }}</td>
                                <td class="px-4 py-3 text-center"><flux:badge size="sm" :color="$eColor">{{ ucfirst($a->estado) }}</flux:badge></td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        @if ($a->estado === 'pendiente')
                                            <flux:button size="sm" variant="ghost" icon="check-badge" wire:click="confirmAppointment({{ $a->id }})" title="Confirmar" />
                                        @endif
                                        @if ($canAttend)
                                            <flux:button href="{{ route('admin.kine.sessions.attend', $a) }}" wire:navigate size="sm" variant="primary" icon="check">Atender</flux:button>
                                        @endif
                                        <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openAppointment({{ $a->id }})" title="Editar" />
                                        @if ($a->estado !== 'cancelado' && $a->estado !== 'atendido')
                                            <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="cancelAppointment({{ $a->id }})" wire:confirm="¿Cancelar esta cita?" title="Cancelar" />
                                        @endif
                                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteAppointment({{ $a->id }})" wire:confirm="¿Eliminar definitivamente esta cita?" title="Eliminar" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-zinc-400">
                                    <flux:icon.calendar class="mx-auto size-10 text-zinc-300" />
                                    <p class="mt-2 text-sm">No hay citas que coincidan con el filtro.</p>
                                    <flux:button wire:click="openAppointment" variant="primary" size="sm" icon="plus" class="mt-3">Agendar primera cita</flux:button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ====== TAB: SESIONES ====== --}}
    @if ($tab === 'sessions')
        <div class="space-y-4">
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
                                        <flux:button href="{{ route('admin.kine.sessions.attend', $a) }}" wire:navigate size="sm" variant="primary" icon="check">Atender</flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div>
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500">Historial clínico</h3>
                <div class="space-y-3">
                    @forelse ($clinicalSessions as $s)
                        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="flex size-8 items-center justify-center rounded-full bg-sky-100 text-xs font-bold text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">{{ $s->numero_sesion }}</span>
                                        <h4 class="font-semibold">{{ $s->treatment?->tipoTratamiento?->nombre ?? 'Sesión' }}</h4>
                                        @if ($s->escala_dolor !== null)
                                            <span class="rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">EVA {{ $s->escala_dolor }}/10</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs text-zinc-500">
                                        {{ $s->fecha?->format('d/m/Y') }}
                                        @if ($s->duracion_real_minutos) · {{ $s->duracion_real_minutos }} min @endif
                                        @if ($s->appointment?->professional) · {{ $s->appointment->professional->full_name }} @endif
                                    </div>
                                </div>
                                <flux:badge size="sm" color="green">Realizada</flux:badge>
                            </div>

                            @if ($s->evolucion || $s->ejercicios || $s->notas_clinicas || $s->rom || $s->fuerza_muscular)
                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    @if ($s->evolucion)
                                        <div class="rounded bg-emerald-50 p-3 text-xs dark:bg-emerald-950/30">
                                            <div class="font-semibold text-emerald-700 dark:text-emerald-300">Evolución</div>
                                            <div class="mt-1 text-emerald-700 dark:text-emerald-400">{{ $s->evolucion }}</div>
                                        </div>
                                    @endif
                                    @if ($s->ejercicios)
                                        <div class="rounded bg-sky-50 p-3 text-xs dark:bg-sky-950/30">
                                            <div class="font-semibold text-sky-700 dark:text-sky-300">Ejercicios</div>
                                            <div class="mt-1 text-sky-700 dark:text-sky-400">{{ $s->ejercicios }}</div>
                                        </div>
                                    @endif
                                    @if ($s->notas_clinicas)
                                        <div class="rounded bg-amber-50 p-3 text-xs md:col-span-2 dark:bg-amber-950/30">
                                            <div class="font-semibold text-amber-700 dark:text-amber-300">Notas clínicas</div>
                                            <div class="mt-1 text-amber-700 dark:text-amber-400">{{ $s->notas_clinicas }}</div>
                                        </div>
                                    @endif
                                    @if ($s->rom || $s->fuerza_muscular)
                                        <div class="rounded bg-violet-50 p-3 text-xs md:col-span-2 dark:bg-violet-950/30">
                                            <div class="font-semibold text-violet-700 dark:text-violet-300">Mediciones</div>
                                            <div class="mt-1 flex flex-wrap gap-3 text-violet-700 dark:text-violet-400">
                                                @if ($s->rom) <span><strong>ROM:</strong> {{ $s->rom }}</span> @endif
                                                @if ($s->fuerza_muscular) <span><strong>Fuerza:</strong> {{ $s->fuerza_muscular }}</span> @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($s->photos->count() > 0)
                                <div class="mt-3 flex gap-2 overflow-x-auto">
                                    @foreach ($s->photos as $ph)
                                        <a href="{{ $ph->url }}" target="_blank" class="group relative shrink-0">
                                            <img src="{{ $ph->url }}" class="size-20 rounded-lg border border-zinc-200 object-cover transition group-hover:scale-105 dark:border-zinc-700">
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
        </div>
    @endif

    {{-- ====== TAB: EVOLUCIÓN (gráfica EVA) ====== --}}
    @if ($tab === 'evolution')
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Evolución del dolor (escala EVA)</h3>
            @if ($painSeries->count() > 0)
                @php $maxEva = 10; @endphp
                <div class="flex h-48 items-end gap-2 overflow-x-auto border-b border-l border-zinc-200 pb-2 pl-2 dark:border-zinc-700">
                    @foreach ($painSeries as $point)
                        @php
                            $h = max(2, round(($point['eva'] / $maxEva) * 100));
                            $color = $point['eva'] >= 7 ? 'bg-rose-500' : ($point['eva'] >= 4 ? 'bg-amber-500' : 'bg-emerald-500');
                        @endphp
                        <div class="group flex min-w-[40px] flex-1 flex-col items-center gap-1">
                            <div class="text-[10px] font-bold text-zinc-700 dark:text-zinc-300">{{ $point['eva'] }}</div>
                            <div class="w-full rounded-t {{ $color }}" style="height: {{ $h }}%" title="{{ $point['fecha'] }}: EVA {{ $point['eva'] }}"></div>
                            <div class="text-[10px] text-zinc-500">{{ $point['fecha'] }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex flex-wrap gap-3 text-xs">
                    <div class="flex items-center gap-1"><span class="size-3 rounded bg-emerald-500"></span> 0–3 leve</div>
                    <div class="flex items-center gap-1"><span class="size-3 rounded bg-amber-500"></span> 4–6 moderado</div>
                    <div class="flex items-center gap-1"><span class="size-3 rounded bg-rose-500"></span> 7–10 severo</div>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <div class="rounded-lg bg-zinc-50 p-3 text-center dark:bg-zinc-800">
                        <div class="text-xs text-zinc-500">EVA inicial</div>
                        <div class="text-2xl font-bold">{{ $painSeries->first()['eva'] }}</div>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-3 text-center dark:bg-zinc-800">
                        <div class="text-xs text-zinc-500">EVA actual</div>
                        <div class="text-2xl font-bold">{{ $painSeries->last()['eva'] }}</div>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-3 text-center dark:bg-emerald-950/30">
                        <div class="text-xs text-emerald-600">Mejoría</div>
                        @php $delta = $painSeries->first()['eva'] - $painSeries->last()['eva']; @endphp
                        <div class="text-2xl font-bold {{ $delta >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $delta >= 0 ? '−' : '+' }}{{ abs($delta) }}</div>
                    </div>
                </div>
            @else
                <div class="py-12 text-center text-sm text-zinc-400">
                    <flux:icon.chart-bar class="mx-auto size-10 text-zinc-300" />
                    <p class="mt-2">Aún no hay registros con escala EVA.</p>
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
                @foreach (['inicial' => ['Estado inicial', 'sky'], 'evolucion' => ['Evolución', 'amber'], 'final' => ['Estado final', 'emerald'], 'rx' => ['Radiografías', 'violet'], 'otro' => ['Otras', 'zinc']] as $tipo => [$label, $color])
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
                <flux:button href="{{ route('admin.kine.payments.create') }}" variant="primary" size="sm" icon="plus" wire:navigate>Registrar pago</flux:button>
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
                        @php $pColor = match($p->estado) { 'pagado' => 'green', 'pendiente' => 'amber', 'anulado' => 'red', default => 'zinc' }; @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="px-4 py-3">{{ $p->fecha?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $p->treatment?->tipoTratamiento?->nombre ?? $p->treatment?->diagnostico ?? '—' }}</td>
                            <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $p->metodo)) }}</td>
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
                </dl>
                <flux:button href="{{ route('admin.people.edit', $person) }}" variant="ghost" size="sm" icon="pencil" class="mt-3" wire:navigate>Editar persona</flux:button>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Ficha kinésica</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-zinc-500">Previsión</dt><dd>{{ $profile->health_insurance ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">N° afiliado</dt><dd>{{ $profile->insurance_number ?? '—' }}</dd></div>
                </dl>
                <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs dark:border-rose-900 dark:bg-rose-950/30">
                    <p class="text-rose-700 dark:text-rose-300">
                        <flux:icon.information-circle class="inline size-4 -mt-0.5" />
                        Los antecedentes médicos, alergias, medicamentos y eventos clínicos viven ahora en la
                        <a href="{{ route('admin.people.clinical', $person) }}" wire:navigate class="font-semibold underline">ficha clínica unificada</a>.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- ============ MODAL: AGENDAR / EDITAR CITA ============ --}}
    <flux:modal wire:model="apptOpen" class="md:w-[640px]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">{{ $editingApptId ? 'Editar cita' : 'Agendar nueva cita' }}</flux:heading>
                <flux:text class="text-zinc-500">{{ $person->full_name }}</flux:text>
            </div>

            {{-- Fecha + hora + duración --}}
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input type="date" wire:model="appt_date" label="Fecha" />
                <flux:input type="time" wire:model="appt_time" label="Hora inicio" />
                <flux:input type="number" min="5" max="480" wire:model="appt_duration" label="Duración (min)" />
            </div>

            {{-- Tratamiento + profesional --}}
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <flux:select wire:model.live="appt_tratamiento_id" label="Tratamiento">
                        <flux:select.option value="">— Sin tratamiento asociado —</flux:select.option>
                        @foreach ($patientTreatments as $t)
                            <flux:select.option value="{{ $t->id }}">
                                {{ $t->tipoTratamiento?->nombre ?? $t->diagnostico }} ({{ $t->estado }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    {{-- Indicador de cupo --}}
                    @if ($this->slotInfo)
                        @php $info = $this->slotInfo; @endphp
                        @if (in_array($info['treatment_state'], ['cancelado', 'suspendido']))
                            <div class="mt-1 flex items-center gap-1 rounded bg-rose-50 px-2 py-1 text-xs text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">
                                <flux:icon.x-circle class="size-3.5" />
                                Tratamiento {{ $info['treatment_state'] }} — no se puede agendar.
                            </div>
                        @elseif ($info['is_full'])
                            <div class="mt-1 flex items-center gap-1 rounded bg-rose-50 px-2 py-1 text-xs text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">
                                <flux:icon.exclamation-triangle class="size-3.5" />
                                Cupo completo: {{ $info['consumed'] }} de {{ $info['total'] }} sesiones agendadas.
                            </div>
                        @else
                            @php $pct = $info['total'] > 0 ? round(($info['consumed'] / $info['total']) * 100) : 0; @endphp
                            <div class="mt-1 rounded bg-sky-50 px-2 py-1 text-xs text-sky-700 dark:bg-sky-950/30 dark:text-sky-300">
                                <div class="flex items-center justify-between">
                                    <span>
                                        @if ($editingApptId)
                                            Editando — {{ $info['consumed'] }} de {{ $info['total'] }} agendadas
                                        @else
                                            <strong>Cita {{ $info['position'] }} de {{ $info['total'] }}</strong> · quedan {{ $info['available'] }}
                                        @endif
                                    </span>
                                </div>
                                <div class="mt-1 h-1 overflow-hidden rounded bg-sky-100 dark:bg-sky-900/40">
                                    <div class="h-full bg-sky-500" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endif
                    @endif
                    @error('appt_tratamiento_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <flux:select wire:model="appt_professional_id" label="Profesional">
                    <flux:select.option value="">— Sin asignar —</flux:select.option>
                    @foreach ($professionals as $pro)
                        <flux:select.option value="{{ $pro->id }}">{{ $pro->full_name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Estado + motivo --}}
            <div class="grid gap-4 md:grid-cols-3">
                <flux:select wire:model="appt_estado" label="Estado">
                    <flux:select.option value="pendiente">Pendiente</flux:select.option>
                    <flux:select.option value="confirmado">Confirmado</flux:select.option>
                    <flux:select.option value="atendido">Atendido</flux:select.option>
                    <flux:select.option value="cancelado">Cancelado</flux:select.option>
                    <flux:select.option value="ausente">Ausente</flux:select.option>
                </flux:select>
                <flux:input wire:model="appt_motivo" label="Motivo" placeholder="Sesión kinésica" class="md:col-span-2" />
            </div>

            <flux:textarea wire:model="appt_notas" rows="2" label="Notas (opcional)" />

            <div class="flex justify-between gap-2">
                <div>
                    @if ($editingApptId)
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="deleteAppointment({{ $editingApptId }}); $set('apptOpen', false)"
                            wire:confirm="¿Eliminar definitivamente esta cita?">Eliminar cita</flux:button>
                    @endif
                </div>
                <div class="flex gap-2">
                    <flux:button variant="ghost" wire:click="$set('apptOpen', false)">Cancelar</flux:button>
                    <flux:button variant="primary" icon="check" wire:click="saveAppointment">
                        {{ $editingApptId ? 'Actualizar' : 'Agendar' }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
