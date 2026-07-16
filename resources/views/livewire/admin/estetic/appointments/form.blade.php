<div class="mx-auto max-w-4xl p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <flux:button href="{{ route('admin.estetic.appointments.index') }}" size="sm" variant="subtle" icon="arrow-left" wire:navigate>Volver</flux:button>
        <div>
            <flux:heading size="xl">{{ $appointment ? 'Editar cita estética' : 'Nueva cita estética' }}</flux:heading>
            <flux:text class="text-zinc-500">{{ $appointment ? 'Actualiza los datos de la cita' : 'Agenda una nueva sesión' }}</flux:text>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- ══ PASO 1: PACIENTE ══ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-full bg-pink-100 text-xs font-bold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">1</span>
                    <h3 class="font-semibold">Paciente</h3>
                </div>
                @if ($patientMode === 'search')
                    <flux:button type="button" size="sm" variant="ghost" icon="user-plus" wire:click="startCreating">
                        Nuevo paciente
                    </flux:button>
                @endif
            </div>
            <div class="p-5">

                {{-- Paciente seleccionado --}}
                @if ($patientMode === 'selected' && $this->selectedPerson)
                    @php $p = $this->selectedPerson; @endphp
                    <div class="flex items-center gap-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-pink-400 to-rose-500 text-sm font-bold text-white">
                            {{ strtoupper(substr($p->first_name, 0, 1).substr($p->last_name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-emerald-900 dark:text-emerald-100">{{ $p->full_name }}</div>
                            <div class="text-xs text-emerald-700 dark:text-emerald-300">
                                {{ $p->rut ?: 'sin RUT' }} · {{ $p->phone ?: 'sin teléfono' }}
                            </div>
                        </div>
                        @if (!$appointment)
                            <flux:button type="button" size="sm" variant="ghost" icon="x-mark" wire:click="clearPatient">Cambiar</flux:button>
                        @endif
                    </div>

                {{-- Creación rápida --}}
                @elseif ($patientMode === 'creating')
                    <div class="space-y-4 rounded-lg border border-pink-200 bg-pink-50/40 p-4 dark:border-pink-900 dark:bg-pink-950/20">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-pink-700 dark:text-pink-300">Datos básicos del nuevo paciente</span>
                            <flux:button type="button" size="sm" variant="ghost" wire:click="cancelCreating">Cancelar</flux:button>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <flux:input wire:model="new_first_name" label="Nombre" placeholder="María" required autofocus />
                            <flux:input wire:model="new_last_name"  label="Apellido" placeholder="González" required />
                            <flux:input wire:model="new_phone" label="Teléfono" placeholder="912345678" />
                            <flux:input wire:model="new_rut"   label="RUT (opcional)" placeholder="12.345.678-9" />
                        </div>
                        @error('new_first_name') <flux:error>{{ $message }}</flux:error> @enderror
                        @error('new_last_name')  <flux:error>{{ $message }}</flux:error> @enderror
                        <flux:button type="button" variant="primary" size="sm" icon="user-plus" wire:click="createAndSelect">
                            Crear y seleccionar
                        </flux:button>
                    </div>

                {{-- Búsqueda --}}
                @else
                    <flux:input
                        wire:model.live.debounce.300ms="personSearch"
                        icon="magnifying-glass"
                        placeholder="Buscar por nombre, RUT o teléfono..."
                        autofocus
                    />
                    <div class="mt-3 max-h-72 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                        @forelse ($this->searchResults as $person)
                            <button type="button" wire:click="selectPerson({{ $person->id }})"
                                class="flex w-full items-center gap-3 border-b border-zinc-100 px-4 py-2.5 text-left last:border-b-0 hover:bg-pink-50 dark:border-zinc-800 dark:hover:bg-pink-950/30">
                                <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-xs font-semibold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                                    {{ strtoupper(substr($person->first_name, 0, 1).substr($person->last_name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium truncate">{{ $person->full_name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $person->rut ?: 'sin RUT' }} · {{ $person->phone ?: 'sin teléfono' }}</div>
                                </div>
                                @if ($person->esteticProfile)
                                    <span class="shrink-0 rounded bg-pink-100 px-1.5 text-[10px] font-medium text-pink-700">EST</span>
                                @endif
                            </button>
                        @empty
                            <div class="p-4 text-center text-sm text-zinc-500">
                                Sin resultados.
                                <button type="button" wire:click="startCreating" class="ml-1 font-medium text-pink-600 hover:underline">
                                    Crear nuevo paciente
                                </button>
                            </div>
                        @endforelse
                    </div>
                @endif

                @error('estetic_profile_id') <flux:error class="mt-2">{{ $message }}</flux:error> @enderror
            </div>
        </div>

        {{-- ══ PASO 2: SERVICIO Y PROFESIONAL ══ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <span class="flex size-7 items-center justify-center rounded-full bg-pink-100 text-xs font-bold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">2</span>
                <h3 class="font-semibold">Servicio y profesional</h3>
            </div>
            <div class="p-5 grid gap-4 md:grid-cols-2">
                <flux:select label="Tratamiento activo (opcional)" wire:model="tratamiento_id">
                    <flux:select.option value="">Sin vincular a tratamiento</flux:select.option>
                    @foreach ($this->treatments as $t)
                        <flux:select.option value="{{ $t->id }}">
                            {{ $t->tipoTratamiento?->nombre ?? $t->zona_tratada }}
                            ({{ $t->sesiones_realizadas }}/{{ $t->sesiones_totales }} ses.)
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select label="Profesional" wire:model="professional_id">
                    <flux:select.option value="">Sin asignar</flux:select.option>
                    @foreach ($professionals as $pro)
                        <flux:select.option value="{{ $pro->id }}">{{ $pro->full_name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input label="Motivo" wire:model="motivo" placeholder="Ej. Primera consulta, sesión de protocolo..." class="md:col-span-2" />
            </div>
        </div>

        {{-- ══ PASO 3: FECHA Y HORA ══ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <span class="flex size-7 items-center justify-center rounded-full bg-pink-100 text-xs font-bold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">3</span>
                <h3 class="font-semibold">Fecha y hora</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <flux:input type="date" label="Fecha" wire:model="fecha" required />
                    <flux:input type="time" label="Hora inicio" wire:model="hora_inicio" required />
                    <flux:input type="number" min="5" step="5" label="Duración (min)" wire:model.live="duracion_min" required />
                </div>
                <div class="rounded-lg bg-zinc-50 px-4 py-2.5 text-sm text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                    <flux:icon.clock class="inline size-4 mr-1" />
                    Finaliza aprox. a las
                    <strong class="text-pink-600">
                        @php
                            try { echo \Carbon\Carbon::parse($fecha.' '.$hora_inicio)->addMinutes((int) $duracion_min)->format('H:i'); }
                            catch (\Throwable $e) { echo '—'; }
                        @endphp
                    </strong>
                </div>
                <flux:select label="Estado de la cita" wire:model="estado">
                    <flux:select.option value="pendiente">Pendiente</flux:select.option>
                    <flux:select.option value="confirmado">Confirmado</flux:select.option>
                    <flux:select.option value="atendido">Atendido</flux:select.option>
                    <flux:select.option value="cancelado">Cancelado</flux:select.option>
                    <flux:select.option value="ausente">Ausente</flux:select.option>
                </flux:select>
                <flux:textarea label="Notas" wire:model="notas" rows="2" placeholder="Observaciones clínicas, indicaciones..." />
            </div>
        </div>

        {{-- ══ PASO 4: PAGO ══ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <span class="flex size-7 items-center justify-center rounded-full bg-pink-100 text-xs font-bold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">4</span>
                <h3 class="font-semibold">Pago</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <flux:switch wire:model.live="register_payment" />
                    <span class="text-sm font-medium">Registrar pago en esta cita</span>
                </div>

                @if ($register_payment)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 dark:border-emerald-900 dark:bg-emerald-950/20 space-y-4">

                        {{-- Fecha y observaciones compartidas --}}
                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:input type="date" label="Fecha del pago" wire:model="payment_date" required />
                            <flux:input label="Observaciones" wire:model="payment_notes" placeholder="Nº comprobante, referencia..." />
                        </div>

                        {{-- Indicador de total acumulado --}}
                        @php
                            $splitsTotal = collect($payment_splits)->sum(fn($s) => (float)($s['monto'] ?? 0));
                        @endphp
                        @if ($splitsTotal > 0)
                            <div class="flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                <flux:icon.check-circle class="size-4" />
                                Total registrado: ${{ number_format($splitsTotal, 0, ',', '.') }}
                            </div>
                        @endif

                        {{-- Splits --}}
                        <div class="space-y-3">
                            @foreach ($payment_splits as $i => $split)
                                @php $needsCode = in_array($split['metodo'] ?? '', ['debito','credito','transferencia','webpay']); @endphp
                                <div class="rounded-lg border border-zinc-100 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900/50 space-y-2">
                                    <div class="flex items-end gap-2">
                                        <div class="flex-1">
                                            <flux:input
                                                type="number" step="1" min="1"
                                                label="Monto ($)"
                                                wire:model.live="payment_splits.{{ $i }}.monto"
                                                placeholder="0"
                                            />
                                        </div>
                                        <div class="flex-1">
                                            <flux:select
                                                label="Método"
                                                wire:model.live="payment_splits.{{ $i }}.metodo"
                                            >
                                                <flux:select.option value="efectivo">Efectivo</flux:select.option>
                                                <flux:select.option value="debito">Tarjeta de débito</flux:select.option>
                                                <flux:select.option value="credito">Tarjeta de crédito</flux:select.option>
                                                <flux:select.option value="transferencia">Transferencia</flux:select.option>
                                                <flux:select.option value="webpay">Webpay</flux:select.option>
                                                <flux:select.option value="otro">Otro</flux:select.option>
                                            </flux:select>
                                        </div>
                                        @if (count($payment_splits) > 1)
                                            <flux:button type="button" size="sm" variant="ghost" icon="x-mark" wire:click="removePaymentSplit({{ $i }})" />
                                        @endif
                                    </div>
                                    @if ($needsCode)
                                        <flux:input
                                            label="Código / Nº de transacción"
                                            wire:model="payment_splits.{{ $i }}.comprobante"
                                            placeholder="Ej. 123456789"
                                        />
                                    @endif
                                </div>
                                @error("payment_splits.{$i}.monto") <flux:error>{{ $message }}</flux:error> @enderror
                            @endforeach
                        </div>

                        @if (count($payment_splits) < 4)
                            <flux:button type="button" size="sm" variant="ghost" icon="plus" wire:click="addPaymentSplit">
                                Agregar otra forma de pago
                            </flux:button>
                        @endif

                        @error('payment_splits')
                            <div class="rounded-lg border border-red-200 bg-red-50 p-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <p class="text-sm text-zinc-500">Si no se registra pago ahora, quedará como cita sin pago asociado. Se puede registrar después desde el módulo de pagos.</p>
                @endif

                @error('payment_date') <flux:error>{{ $message }}</flux:error> @enderror
            </div>
        </div>

        {{-- Acciones --}}
        <div class="flex justify-end gap-2">
            <flux:button href="{{ route('admin.estetic.appointments.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ $appointment ? 'Actualizar cita' : 'Crear cita' }}
            </flux:button>
        </div>

    </form>
</div>
