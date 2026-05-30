<div class="p-6 max-w-5xl space-y-6">
    <div class="flex items-end justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-zinc-500">
                <a href="{{ route('admin.subscriptions.index') }}" wire:navigate class="hover:underline">Suscripciones</a>
                <flux:icon.chevron-right class="size-3" />
                <span>Nueva</span>
            </div>
            <flux:heading size="xl">Nueva suscripción</flux:heading>
            <flux:text class="text-zinc-500">Busca una persona registrada o crea una nueva en un solo paso.</flux:text>
        </div>
    </div>

    @if (session('info'))
        <div class="rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-200">
            {{ session('info') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- ============ STEP 1: PERSONA ============ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <div class="flex items-center gap-3">
                    <span class="flex size-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">1</span>
                    <h3 class="font-semibold">Persona</h3>
                </div>
                @if (!$this->selectedPerson)
                    <a href="{{ route('admin.admission.create', ['modules' => ['gym']]) }}" wire:navigate
                        class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1 text-xs font-medium text-white hover:bg-emerald-700">
                        <flux:icon.user-plus class="size-3.5" />
                        ¿Persona nueva? Ir al wizard
                    </a>
                @endif
            </div>

            <div class="p-5">
                {{-- Persona seleccionada --}}
                @if ($this->selectedPerson)
                    @php $p = $this->selectedPerson; @endphp
                    <div class="flex items-center gap-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
                        <div class="flex size-12 items-center justify-center rounded-full bg-emerald-200 text-base font-bold text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100">
                            {{ strtoupper(substr($p->first_name, 0, 1).substr($p->last_name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-emerald-900 dark:text-emerald-100">{{ $p->full_name }}</div>
                            <div class="text-xs text-emerald-700 dark:text-emerald-300">
                                RUT {{ $p->rut }} · {{ $p->email ?: 'sin email' }} · {{ $p->phone ?: 'sin teléfono' }}
                            </div>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @if ($p->gymProfile)
                                    <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">GYM</span>
                                @endif
                                @if ($p->kineProfile)
                                    <span class="rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">KINE</span>
                                @endif
                                @if ($p->esteticProfile)
                                    <span class="rounded bg-pink-100 px-1.5 py-0.5 text-[10px] font-medium text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">ESTÉTICA</span>
                                @endif
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="clearPerson">Cambiar</flux:button>
                    </div>

                    {{-- Aviso si ya tiene suscripción activa --}}
                    @if ($this->activeSubscription)
                        <div class="mt-3 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                            <flux:icon.exclamation-triangle class="size-5 shrink-0" />
                            <div>
                                <div class="font-semibold">Esta persona ya tiene una suscripción activa</div>
                                <div class="text-xs">Plan <strong>{{ $this->activeSubscription->plan?->name }}</strong> hasta {{ $this->activeSubscription->end_date?->format('d/m/Y') ?? '—' }}. Crear una nueva no la cancelará automáticamente.</div>
                            </div>
                        </div>
                    @endif
                @else
                    {{-- Buscador de personas existentes --}}
                    <flux:input
                        wire:model.live.debounce.300ms="personSearch"
                        icon="magnifying-glass"
                        placeholder="Buscar por nombre, apellido, RUT o email..."
                        autofocus
                    />
                    <div class="mt-3 max-h-80 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                        @forelse ($this->searchResults as $p)
                            <button type="button" wire:click="selectPerson({{ $p->id }})"
                                class="flex w-full items-center gap-3 border-b border-zinc-100 px-4 py-2.5 text-left last:border-b-0 hover:bg-indigo-50 dark:border-zinc-800 dark:hover:bg-indigo-950/30">
                                <div class="flex size-9 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                    {{ strtoupper(substr($p->first_name, 0, 1).substr($p->last_name, 0, 1)) }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">{{ $p->full_name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $p->rut }} · {{ $p->email ?: 'sin email' }}</div>
                                </div>
                                <div class="flex gap-1">
                                    @if ($p->gymProfile)<span class="rounded bg-amber-100 px-1.5 text-[10px] font-medium text-amber-700">GYM</span>@endif
                                    @if ($p->kineProfile)<span class="rounded bg-sky-100 px-1.5 text-[10px] font-medium text-sky-700">KINE</span>@endif
                                    @if ($p->esteticProfile)<span class="rounded bg-pink-100 px-1.5 text-[10px] font-medium text-pink-700">EST</span>@endif
                                </div>
                            </button>
                        @empty
                            <div class="px-4 py-6 text-center text-sm text-zinc-500">
                                @if (strlen(trim($personSearch)) >= 2)
                                    Sin resultados para <strong>"{{ $personSearch }}"</strong>.
                                @else
                                    Aún no hay personas registradas.
                                @endif
                                <div class="mt-3">
                                    <a href="{{ route('admin.admission.create', ['modules' => ['gym']]) }}" wire:navigate
                                        class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                                        <flux:icon.user-plus class="size-3.5" />
                                        Crear nueva persona en wizard
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ STEP 2: PLAN ============ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <span class="flex size-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">2</span>
                <h3 class="font-semibold">Plan</h3>
            </div>
            <div class="p-5">
                <div class="grid gap-3 md:grid-cols-3">
                    @forelse ($plans as $plan)
                        <button type="button" wire:click="selectPlan({{ $plan->id }})"
                            class="group rounded-xl border p-4 text-left transition
                                {{ $plan_id == $plan->id
                                    ? 'border-indigo-500 ring-2 ring-indigo-500/20 bg-indigo-50/50 dark:bg-indigo-950/30'
                                    : 'border-zinc-200 hover:border-indigo-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50' }}">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">{{ $plan->name }}</span>
                                @if ($plan_id == $plan->id)
                                    <flux:icon.check-circle class="size-5 text-indigo-600" />
                                @endif
                            </div>
                            <div class="mt-2 text-2xl font-bold">${{ number_format($plan->price, 0, ',', '.') }}</div>
                            <div class="mt-1 text-xs text-zinc-500">{{ $plan->duration_days }} días</div>
                            @if ($plan->description)
                                <p class="mt-2 line-clamp-2 text-xs text-zinc-500">{{ $plan->description }}</p>
                            @endif
                        </button>
                    @empty
                        <div class="md:col-span-3 text-sm text-zinc-500">No hay planes registrados.</div>
                    @endforelse
                </div>
                @error('plan_id') <flux:error class="mt-2">{{ $message }}</flux:error> @enderror
            </div>
        </div>

        {{-- ============ STEP 3: FECHAS Y ESTADO ============ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <span class="flex size-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">3</span>
                <h3 class="font-semibold">Fechas y estado</h3>
            </div>
            <div class="p-5">
                <div class="grid gap-4 md:grid-cols-3">
                    <flux:input type="date" label="Fecha de inicio" wire:model.live="start_date" />
                    <flux:input type="date" label="Fecha de término" wire:model="end_date" description="Se calcula automáticamente al elegir un plan" />
                    <flux:select label="Estado" wire:model="status">
                        <flux:select.option value="active">Activa</flux:select.option>
                        <flux:select.option value="paused">Pausada</flux:select.option>
                        <flux:select.option value="cancelled">Cancelada</flux:select.option>
                        <flux:select.option value="expired">Expirada</flux:select.option>
                    </flux:select>
                </div>

                {{-- Resumen --}}
                @if ($this->selectedPlan && $start_date)
                    <div class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Resumen</div>
                        <div class="mt-2 flex flex-wrap items-baseline gap-x-6 gap-y-1 text-sm">
                            <div><span class="text-zinc-500">Persona:</span> <strong>{{ $this->selectedPerson?->full_name ?? ($new_first_name.' '.$new_last_name) }}</strong></div>
                            <div><span class="text-zinc-500">Plan:</span> <strong>{{ $this->selectedPlan->name }}</strong></div>
                            <div><span class="text-zinc-500">Vigencia:</span> <strong>{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</strong> → <strong>{{ $end_date ? \Carbon\Carbon::parse($end_date)->format('d/m/Y') : '—' }}</strong></div>
                            <div><span class="text-zinc-500">Monto:</span> <strong class="text-emerald-600">${{ number_format($this->selectedPlan->price, 0, ',', '.') }}</strong></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ STEP 4: PAGO ============ --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                <span class="flex size-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">4</span>
                <h3 class="font-semibold">Pago</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid gap-3 md:grid-cols-2">
                    <button type="button" wire:click="$set('payment_choice', 'pending')"
                        class="rounded-xl border p-4 text-left transition
                            {{ $payment_choice === 'pending'
                                ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/50 dark:bg-amber-950/30'
                                : 'border-zinc-200 hover:border-amber-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon.clock class="size-5 text-amber-600" />
                                <span class="font-semibold">Dejar pendiente</span>
                            </div>
                            @if ($payment_choice === 'pending')
                                <flux:icon.check-circle class="size-5 text-amber-600" />
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">Se generará un pago con estado <strong>pendiente</strong> que aparecerá en la vista de Pagos para registrarlo después.</p>
                    </button>

                    <button type="button" wire:click="$set('payment_choice', 'now')"
                        class="rounded-xl border p-4 text-left transition
                            {{ $payment_choice === 'now'
                                ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-950/30'
                                : 'border-zinc-200 hover:border-emerald-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/50' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon.banknotes class="size-5 text-emerald-600" />
                                <span class="font-semibold">Registrar pago ahora</span>
                            </div>
                            @if ($payment_choice === 'now')
                                <flux:icon.check-circle class="size-5 text-emerald-600" />
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">Crea la suscripción y registra el pago como <strong>pagado</strong> en un solo paso.</p>
                    </button>
                </div>

                @if ($payment_choice === 'now')
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 dark:border-emerald-900 dark:bg-emerald-950/20">
                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:input type="number" step="1" min="0" label="Monto" wire:model="payment_amount" placeholder="0" />
                            <flux:input type="date" label="Fecha de pago" wire:model="payment_date" />
                            <flux:select label="Método de pago" wire:model="payment_type">
                                <flux:select.option value="efectivo">Efectivo</flux:select.option>
                                <flux:select.option value="debito">Tarjeta de débito</flux:select.option>
                                <flux:select.option value="credito">Tarjeta de crédito</flux:select.option>
                                <flux:select.option value="transferencia">Transferencia</flux:select.option>
                                <flux:select.option value="webpay">Webpay</flux:select.option>
                                <flux:select.option value="otro">Otro</flux:select.option>
                            </flux:select>
                            <flux:input label="Observaciones" wire:model="payment_notes" placeholder="Opcional: nº de comprobante, referencia..." />
                        </div>
                    </div>
                @else
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                        <div class="flex items-start gap-2">
                            <flux:icon.information-circle class="size-5 shrink-0" />
                            <div>
                                <strong>Pago pendiente:</strong> se creará por <strong>${{ number_format($this->selectedPlan?->price ?? 0, 0, ',', '.') }}</strong> y aparecerá en <em>Pagos → Pendientes</em> hasta que se registre.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ ACCIONES ============ --}}
        <div class="flex justify-end gap-2">
            <flux:button href="{{ route('admin.subscriptions.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" icon="check">Crear suscripción</flux:button>
        </div>
    </form>
</div>
