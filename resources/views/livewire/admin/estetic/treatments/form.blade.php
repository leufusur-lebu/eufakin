<div class="p-6 max-w-4xl space-y-6">

    {{-- Breadcrumb --}}
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-500">
            <a href="{{ route('admin.estetic.patients.index') }}" wire:navigate class="hover:underline">Pacientes</a>
            <flux:icon.chevron-right class="size-3" />
            <a href="{{ route('admin.estetic.patients.show', $treatment->esteticProfile) }}" wire:navigate class="hover:underline">
                {{ $treatment->esteticProfile->person?->full_name }}
            </a>
            <flux:icon.chevron-right class="size-3" />
            <span>Editar tratamiento</span>
        </div>
        <flux:heading size="xl" class="mt-1">Editar tratamiento</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- ══ INFO DE REFERENCIA (solo lectura) ══ --}}
        <div class="grid gap-4 md:grid-cols-2">

            {{-- Paciente --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2 border-b border-zinc-100 bg-zinc-50 px-4 py-2 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <flux:icon.user class="size-4 text-zinc-400" />
                    <span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Paciente</span>
                </div>
                @php $person = $treatment->esteticProfile?->person; @endphp
                <div class="flex items-center gap-3 p-4">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-pink-400 to-rose-500 text-sm font-bold text-white">
                        {{ strtoupper(substr($person?->first_name ?? '?', 0, 1).substr($person?->last_name ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold">{{ $person?->full_name ?? '—' }}</div>
                        <div class="text-xs text-zinc-500">{{ $person?->rut }} · {{ $person?->phone ?: 'sin teléfono' }}</div>
                    </div>
                </div>
            </div>

            {{-- Protocolo --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2 border-b border-zinc-100 bg-zinc-50 px-4 py-2 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <flux:icon.sparkles class="size-4 text-pink-400" />
                    <span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Protocolo</span>
                </div>
                <div class="p-4">
                    @php $tipo = $treatment->tipoTratamiento; @endphp
                    <div class="flex items-center gap-2">
                        @if ($tipo?->color)
                            <span class="size-3 rounded-full shrink-0" style="background: {{ $tipo->color }}"></span>
                        @endif
                        <span class="font-semibold">{{ $tipo?->nombre ?? 'Sin protocolo' }}</span>
                    </div>
                    <div class="mt-2 flex items-center gap-3 text-xs text-zinc-500">
                        <span>{{ $treatment->sesiones_realizadas }}/{{ $treatment->sesiones_totales }} sesiones</span>
                        <span>·</span>
                        <span>Inicio {{ $treatment->fecha_inicio?->format('d/m/Y') }}</span>
                    </div>
                    {{-- Barra de progreso --}}
                    @php $pct = $treatment->sesiones_totales > 0 ? round(($treatment->sesiones_realizadas / $treatment->sesiones_totales) * 100) : 0; @endphp
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-pink-100 dark:bg-pink-900/40">
                        <div class="h-full rounded-full bg-gradient-to-r from-pink-400 to-rose-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="mt-1 text-[11px] text-zinc-400">{{ $pct }}% completado</div>
                </div>
            </div>
        </div>

        {{-- ══ CAMPOS EDITABLES ══ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-2 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <flux:icon.pencil-square class="size-4 text-zinc-400" />
                <h3 class="font-semibold">Detalles del tratamiento</h3>
            </div>
            <div class="p-5 space-y-5">

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input
                        label="Zona tratada"
                        wire:model="zona_tratada"
                        placeholder="Ej. cara completa, espalda baja..."
                        required
                    />
                    <flux:select label="Profesional" wire:model="professional_id">
                        <flux:select.option value="">— Sin asignar —</flux:select.option>
                        @foreach ($professionals as $pro)
                            <flux:select.option value="{{ $pro->id }}">{{ $pro->full_name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input type="date" label="Fecha de inicio" wire:model="fecha_inicio" required />
                    <flux:input type="date" label="Fecha de fin" wire:model="fecha_fin" description="Se ajusta si se agregan o eliminan sesiones" />
                </div>

                {{-- Costo total → costo/sesión calculado --}}
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <flux:input
                            type="number"
                            step="1"
                            min="0"
                            label="Costo total del protocolo"
                            wire:model.live="costo_total"
                            required
                        />
                    </div>
                    <div class="rounded-lg border border-pink-200 bg-pink-50 p-3 dark:border-pink-900 dark:bg-pink-950/30">
                        <div class="text-[10px] uppercase tracking-wide text-pink-600">Costo por sesión (calculado)</div>
                        <div class="mt-1 text-xl font-bold text-pink-700 dark:text-pink-300">
                            ${{ number_format($this->costoSesion, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-pink-500">{{ $treatment->sesiones_totales }} sesiones</div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:select label="Estado" wire:model="estado">
                        <flux:select.option value="activo">Activo</flux:select.option>
                        <flux:select.option value="finalizado">Finalizado</flux:select.option>
                        <flux:select.option value="suspendido">Suspendido</flux:select.option>
                        <flux:select.option value="cancelado">Cancelado</flux:select.option>
                    </flux:select>
                </div>

                <flux:textarea
                    label="Observaciones"
                    wire:model="observaciones"
                    rows="3"
                    placeholder="Notas clínicas, ajustes al protocolo, indicaciones especiales..."
                />
            </div>
        </div>

        {{-- ══ SESIONES PROGRAMADAS (vista previa) ══ --}}
        @php
            $upcoming = $treatment->appointments->where('estado', '!=', 'realizada')->sortBy('inicio');
            $done     = $treatment->appointments->where('estado', 'realizada')->count();
        @endphp
        @if ($treatment->appointments->count())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                    <div class="flex items-center gap-2">
                        <flux:icon.calendar-days class="size-4 text-zinc-400" />
                        <h3 class="font-semibold">Sesiones programadas</h3>
                    </div>
                    <span class="text-xs text-zinc-500">{{ $done }} realizadas · {{ $upcoming->count() }} pendientes</span>
                </div>
                <div class="grid gap-2 p-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($treatment->appointments->sortBy('inicio') as $apt)
                        @php
                            $isDone = $apt->estado === 'realizada';
                            $isPast = !$isDone && $apt->inicio->isPast();
                        @endphp
                        <div class="flex items-center gap-2 rounded-lg border px-3 py-2 text-xs
                            {{ $isDone  ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/30'
                            : ($isPast ? 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950/30'
                            : 'border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/50') }}">
                            <span class="flex size-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold
                                {{ $isDone ? 'bg-emerald-500 text-white' : ($isPast ? 'bg-red-400 text-white' : 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300') }}">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <div class="font-medium {{ $isDone ? 'text-emerald-700 dark:text-emerald-300' : ($isPast ? 'text-red-700 dark:text-red-300' : '') }}">
                                    {{ $apt->inicio->locale('es')->isoFormat('ddd D MMM') }}
                                </div>
                                <div class="text-zinc-400">{{ $apt->inicio->format('H:i') }} h</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Acciones --}}
        <div class="flex justify-end gap-2">
            <flux:button href="{{ route('admin.estetic.patients.show', $treatment->esteticProfile) }}" variant="ghost" wire:navigate>
                Cancelar
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">Guardar cambios</flux:button>
        </div>

    </form>
</div>
