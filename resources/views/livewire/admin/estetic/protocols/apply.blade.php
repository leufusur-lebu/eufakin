<div class="p-6 max-w-5xl space-y-6">
    {{-- Breadcrumb + header --}}
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-500">
            <a href="{{ route('admin.estetic.tipos-tratamientos.index') }}" wire:navigate class="hover:underline">Catálogo</a>
            <flux:icon.chevron-right class="size-3" />
            <span>Aplicar protocolo</span>
        </div>
        <div class="mt-1 flex items-center gap-3">
            <span class="size-3 rounded-full" style="background: {{ $tipo->color ?? '#ec4899' }}"></span>
            <flux:heading size="xl">{{ $tipo->nombre }}</flux:heading>
        </div>
        <flux:text class="text-zinc-500">{{ $tipo->descripcion ?? 'Configura y aplica el protocolo a un paciente. Se generarán automáticamente las sesiones y pagos.' }}</flux:text>
    </div>

    <form wire:submit="apply" class="space-y-6">
        {{-- ============ STEP 1: PACIENTE ============ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-full bg-pink-100 text-xs font-bold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">1</span>
                    <h3 class="font-semibold">Paciente</h3>
                </div>
                @if (!$this->selectedPerson)
                    <a href="{{ route('admin.admission.create', ['modules' => ['estetic'], 'estetic_tipo_id' => $tipo->id]) }}" wire:navigate
                        class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1 text-xs font-medium text-white hover:bg-emerald-700">
                        <flux:icon.user-plus class="size-3.5" />
                        ¿Persona nueva? Ir al wizard
                    </a>
                @endif
            </div>
            <div class="p-5">
                @if ($this->selectedPerson)
                    @php $p = $this->selectedPerson; @endphp
                    <div class="flex items-center gap-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
                        <div class="flex size-12 items-center justify-center rounded-full bg-emerald-200 text-base font-bold text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100">
                            {{ strtoupper(substr($p->first_name, 0, 1).substr($p->last_name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-emerald-900 dark:text-emerald-100">{{ $p->full_name }}</div>
                            <div class="text-xs text-emerald-700 dark:text-emerald-300">{{ $p->rut }} · {{ $p->phone ?: 'sin teléfono' }}</div>
                            <div class="mt-1 flex flex-wrap items-center gap-1">
                                @if ($p->gymProfile)<span class="rounded bg-amber-100 px-1.5 text-[10px] font-medium text-amber-700">GYM</span>@endif
                                @if ($p->kineProfile)<span class="rounded bg-sky-100 px-1.5 text-[10px] font-medium text-sky-700">KINE</span>@endif
                                @if ($p->esteticProfile)<span class="rounded bg-pink-100 px-1.5 text-[10px] font-medium text-pink-700">ESTÉTICA</span>@endif
                                @if (!$p->esteticProfile)
                                    <span class="rounded bg-emerald-100 px-1.5 text-[10px] font-medium text-emerald-700">Primer tratamiento estético</span>
                                @endif
                            </div>
                            @if ($p->clinicalProfile?->allergies)
                                <div class="mt-1 inline-flex items-center gap-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-medium text-rose-700">
                                    <flux:icon.exclamation-triangle class="size-3" /> Alergias: {{ $p->clinicalProfile->allergies }}
                                </div>
                            @endif
                        </div>
                        <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="clearPatient">Cambiar</flux:button>
                    </div>
                @else
                    <flux:input wire:model.live.debounce.300ms="personSearch" icon="magnifying-glass" placeholder="Buscar cualquier persona por nombre, RUT o email..." autofocus />
                    <div class="mt-3 max-h-72 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                        @forelse ($this->patientResults as $person)
                            <button type="button" wire:click="selectPatient({{ $person->id }})"
                                class="flex w-full items-center gap-3 border-b border-zinc-100 px-4 py-2.5 text-left last:border-b-0 hover:bg-pink-50 dark:border-zinc-800 dark:hover:bg-pink-950/30">
                                <div class="flex size-9 items-center justify-center rounded-full bg-pink-100 text-xs font-semibold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                                    {{ strtoupper(substr($person->first_name, 0, 1).substr($person->last_name, 0, 1)) }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">{{ $person->full_name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $person->rut }} · {{ $person->phone ?: 'sin teléfono' }}</div>
                                </div>
                                <div class="flex gap-1">
                                    @if ($person->gymProfile)<span class="rounded bg-amber-100 px-1.5 text-[10px] font-medium text-amber-700">GYM</span>@endif
                                    @if ($person->kineProfile)<span class="rounded bg-sky-100 px-1.5 text-[10px] font-medium text-sky-700">KINE</span>@endif
                                    @if ($person->esteticProfile)<span class="rounded bg-pink-100 px-1.5 text-[10px] font-medium text-pink-700">EST</span>@endif
                                </div>
                            </button>
                        @empty
                            <div class="p-4 text-center text-sm text-zinc-500">
                                Sin resultados.
                                <div class="mt-2">
                                    <a href="{{ route('admin.admission.create', ['modules' => ['estetic'], 'estetic_tipo_id' => $tipo->id]) }}" wire:navigate
                                        class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                                        <flux:icon.user-plus class="size-3.5" />
                                        Crear nueva persona y aplicar protocolo
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @endif
                @error('person_id') <flux:error class="mt-2">{{ $message }}</flux:error> @enderror
            </div>
        </div>

        {{-- ============ STEP 2: PLAN DE SESIONES ============ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <span class="flex size-7 items-center justify-center rounded-full bg-pink-100 text-xs font-bold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">2</span>
                <h3 class="font-semibold">Plan de sesiones</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input wire:model.live="zona_tratada" label="Zona tratada" placeholder="Ej. cara completa, espalda baja..." />
                    <flux:select wire:model="professional_id" label="Profesional">
                        <flux:select.option value="">— Sin asignar —</flux:select.option>
                        @foreach ($professionals as $prof)
                            <flux:select.option value="{{ $prof->id }}">{{ $prof->full_name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="grid gap-4 md:grid-cols-4">
                    <flux:input type="number" min="1" max="50" wire:model.live="sesiones" label="Sesiones" />
                    <flux:input type="number" min="1" max="365" wire:model.live="intervalo_dias" label="Intervalo (días)" />
                    <flux:input type="number" min="0" step="1" wire:model.live="costo_sesion" label="Costo / sesión" />
                    <div class="rounded-lg border border-pink-200 bg-pink-50 p-3 text-center dark:border-pink-900 dark:bg-pink-950/30">
                        <div class="text-[10px] uppercase tracking-wide text-pink-600">Total protocolo</div>
                        <div class="mt-1 text-xl font-bold text-pink-700 dark:text-pink-300">${{ number_format($this->totalCost, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input type="date" wire:model.live="start_date" label="Primera sesión" />
                    <flux:input type="time" wire:model.live="start_time" label="Hora" />
                </div>

                {{-- Preview de fechas --}}
                @if (count($this->preview) > 0)
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/40">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-500">Cronograma generado</div>
                        <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            @foreach ($this->preview as $session)
                                <div class="flex items-center gap-2 rounded bg-white px-2 py-1.5 text-xs dark:bg-zinc-900">
                                    <span class="flex size-6 items-center justify-center rounded-full bg-pink-100 text-[10px] font-bold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">{{ $session['numero'] }}</span>
                                    <div>
                                        <div class="font-medium">{{ $session['fecha']->isoFormat('ddd D MMM') }}</div>
                                        <div class="text-zinc-500">{{ $session['fecha']->format('H:i') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ STEP 3: PAGO ============ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <span class="flex size-7 items-center justify-center rounded-full bg-pink-100 text-xs font-bold text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">3</span>
                <h3 class="font-semibold">Modalidad de pago</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach ([
                        'pending'      => ['Dejar pendiente', 'clock', 'amber', 'Un solo pago pendiente por el total'],
                        'full'         => ['Pago completo', 'banknotes', 'emerald', 'Cobro total registrado ahora'],
                        'installments' => ['En cuotas', 'rectangle-stack', 'sky', 'Genera N pagos pendientes con fechas'],
                    ] as $val => [$label, $icon, $color, $desc])
                        <button type="button" wire:click="$set('payment_mode', '{{ $val }}')"
                            class="rounded-xl border p-4 text-left transition
                                {{ $payment_mode === $val
                                    ? "border-{$color}-500 ring-2 ring-{$color}-500/20 bg-{$color}-50/50 dark:bg-{$color}-950/30"
                                    : 'border-zinc-200 hover:border-pink-300 dark:border-zinc-700' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <flux:icon :name="$icon" class="size-5 text-{{ $color }}-600" />
                                    <span class="font-semibold">{{ $label }}</span>
                                </div>
                                @if ($payment_mode === $val)
                                    <flux:icon.check-circle class="size-5 text-{{ $color }}-600" />
                                @endif
                            </div>
                            <p class="mt-2 text-xs text-zinc-500">{{ $desc }}</p>
                        </button>
                    @endforeach
                </div>

                @if ($payment_mode === 'full')
                    <flux:select wire:model="payment_method" label="Método de pago">
                        <flux:select.option value="efectivo">Efectivo</flux:select.option>
                        <flux:select.option value="debito">Tarjeta de débito</flux:select.option>
                        <flux:select.option value="credito">Tarjeta de crédito</flux:select.option>
                        <flux:select.option value="transferencia">Transferencia</flux:select.option>
                        <flux:select.option value="mercadopago">Mercado Pago</flux:select.option>
                        <flux:select.option value="otro">Otro</flux:select.option>
                    </flux:select>
                @endif

                @if ($payment_mode === 'installments')
                    <div class="grid gap-4 md:grid-cols-3">
                        <flux:input type="number" min="2" max="24" wire:model.live="cuotas" label="N° de cuotas" />
                        <flux:input type="number" min="0" wire:model="abono_inicial" label="Abono inicial (opcional)" />
                        <flux:select wire:model="payment_method" label="Método del abono">
                            <flux:select.option value="efectivo">Efectivo</flux:select.option>
                            <flux:select.option value="transferencia">Transferencia</flux:select.option>
                            <flux:select.option value="debito">Débito</flux:select.option>
                            <flux:select.option value="credito">Crédito</flux:select.option>
                        </flux:select>
                    </div>
                    @php
                        $abono = (float) ($abono_inicial ?? 0);
                        $restante = $this->totalCost - $abono;
                        $valorCuota = $cuotas > 0 ? round($restante / $cuotas, 0) : 0;
                    @endphp
                    <div class="rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm dark:border-sky-900 dark:bg-sky-950/30">
                        <strong>Plan:</strong> {{ $cuotas }} cuotas de <strong>${{ number_format($valorCuota, 0, ',', '.') }}</strong>
                        @if ($abono > 0) + abono inicial ${{ number_format($abono, 0, ',', '.') }}@endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ RESUMEN FINAL ============ --}}
        <div class="rounded-xl border border-pink-200 bg-gradient-to-br from-pink-50 to-rose-50 p-5 dark:border-pink-900 dark:from-pink-950/30 dark:to-rose-950/30">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm">
                    Se generará 1 tratamiento, <strong>{{ $sesiones }} citas</strong>
                    @if ($payment_mode === 'installments') y <strong>{{ $cuotas }}{{ ($abono_inicial ?? 0) > 0 ? '+1' : '' }} pagos</strong> en cuotas
                    @elseif ($payment_mode === 'full') y <strong>1 pago completo</strong>
                    @else y <strong>1 pago pendiente</strong>
                    @endif
                    por <strong class="text-pink-700">${{ number_format($this->totalCost, 0, ',', '.') }}</strong>.
                </div>
                <div class="flex gap-2">
                    <flux:button href="{{ route('admin.estetic.tipos-tratamientos.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" icon="sparkles">Aplicar protocolo</flux:button>
                </div>
            </div>
        </div>
    </form>
</div>
