<div class="p-6 max-w-3xl">
    <div class="mb-6">
        <flux:heading size="xl">Admisión de persona</flux:heading>
        <flux:text class="text-zinc-500">Paso {{ $step }} de {{ $totalSteps }}</flux:text>
        <div class="mt-2 h-2 rounded bg-zinc-200 dark:bg-zinc-700">
            <div class="h-2 rounded bg-blue-500" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
        </div>
    </div>

    <div class="rounded-lg border p-6 dark:border-zinc-700">
        {{-- STEP 1: Personal --}}
        @if ($step === 1)
            <flux:heading size="lg">Datos personales</flux:heading>
            @if ($person_locked)
                <div class="mt-3 flex items-start gap-2 rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-200">
                    <flux:icon.information-circle class="size-5 shrink-0" />
                    <div class="flex-1">
                        <div class="font-semibold">Persona ya registrada</div>
                        <div class="text-xs">Los datos se completaron automáticamente. Puedes continuar al siguiente paso para agregar un nuevo módulo o servicio.</div>
                    </div>
                    <button type="button" wire:click="clearPersonFields" class="text-xs underline hover:no-underline">Limpiar</button>
                </div>
            @endif
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <flux:input
                        label="RUT"
                        wire:model.live.debounce.2000ms="rut"
                        placeholder="12.345.678-9"
                        maxlength="13"
                        autocomplete="off"
                    />
                    @if ($rutValid === true && !$errors->has('rut'))
                        <div class="mt-1 flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                            <flux:icon.check-circle class="size-4" /> RUT válido
                        </div>
                    @elseif ($rutValid === false && !$errors->has('rut'))
                        <div class="mt-1 flex items-center gap-1 text-xs text-rose-600 dark:text-rose-400">
                            <flux:icon.x-circle class="size-4" /> RUT inválido
                        </div>
                    @endif
                </div>
                <flux:select label="Género" wire:model="gender">
                    <flux:select.option value="M">Masculino</flux:select.option>
                    <flux:select.option value="F">Femenino</flux:select.option>
                    <flux:select.option value="O">Otro</flux:select.option>
                </flux:select>
                <flux:input label="Nombre" wire:model="first_name" />
                <flux:input label="Apellido" wire:model="last_name" />
                <flux:input label="Apodo" wire:model="nickname" />
                <flux:input type="date" label="Fecha de nacimiento" wire:model="birth_date" />

                {{-- Teléfono con prefijo +56 --}}
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Teléfono</label>
                    <div class="flex">
                        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-zinc-300 bg-zinc-100 px-3 text-sm font-medium text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">+56</span>
                        <input type="tel"
                            wire:model="phone"
                            placeholder="9 1234 5678"
                            inputmode="numeric"
                            maxlength="12"
                            class="block w-full rounded-r-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                    </div>
                </div>

                <flux:input type="email" label="Email" wire:model="email" />
                <flux:input label="Dirección" wire:model="address" class="md:col-span-2" placeholder="Calle y número" />
                <flux:input label="Población / Villa / Sector" wire:model="poblacion" />
                <flux:input label="Comuna" wire:model="comuna" placeholder="Ej. Providencia" />
            </div>
        @endif

        {{-- STEP 2: Emergency contact --}}
        @if ($step === 2)
            <flux:heading size="lg">Contacto de emergencia</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Persona a contactar en caso de necesidad médica o eventualidad. Es opcional pero recomendado.</flux:text>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <flux:input label="Nombre completo" wire:model="emergency_contact_name" placeholder="Ej. María González" class="md:col-span-2" />
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Teléfono</label>
                    <div class="flex">
                        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-zinc-300 bg-zinc-100 px-3 text-sm font-medium text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">+56</span>
                        <input type="tel"
                            wire:model="emergency_contact_phone"
                            placeholder="9 1234 5678"
                            inputmode="numeric"
                            maxlength="12"
                            class="block w-full rounded-r-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm placeholder-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                    </div>
                </div>
                <flux:select label="Relación" wire:model="emergency_contact_relationship">
                    <flux:select.option value="">—</flux:select.option>
                    <flux:select.option value="conyuge">Cónyuge / pareja</flux:select.option>
                    <flux:select.option value="padre">Padre</flux:select.option>
                    <flux:select.option value="madre">Madre</flux:select.option>
                    <flux:select.option value="hijo">Hijo / hija</flux:select.option>
                    <flux:select.option value="hermano">Hermano / hermana</flux:select.option>
                    <flux:select.option value="amigo">Amigo / amiga</flux:select.option>
                    <flux:select.option value="tutor">Tutor legal</flux:select.option>
                    <flux:select.option value="otro">Otro</flux:select.option>
                </flux:select>
            </div>
        @endif

        {{-- STEP 3: Datos clínicos (antecedentes + lesión + medición inicial) --}}
        @if ($step === 3)
            <flux:heading size="lg">Datos clínicos básicos</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Todos los campos son opcionales pero muy recomendados. Quedan vinculados a la <strong>Ficha Clínica</strong> del paciente.</flux:text>

            {{-- Antecedentes --}}
            <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50/40 p-4 dark:border-rose-900 dark:bg-rose-950/20">
                <h4 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">
                    <flux:icon.shield-exclamation class="size-4" /> Antecedentes
                </h4>
                <div class="space-y-3">
                    <flux:textarea wire:model="cli_chronic_diseases" rows="2" label="Comorbilidades" placeholder="Diabetes, hipertensión, asma, hipotiroidismo..." />
                    <flux:textarea wire:model="cli_surgical_history" rows="2" label="Operaciones recientes" placeholder="Apendicectomía 2024, cesárea 2023..." />
                    <flux:textarea wire:model="cli_chronic_medications" rows="2" label="Medicamentos de uso frecuente o diario" placeholder="Losartán 50mg/día, levotiroxina 75mcg..." />
                    <flux:textarea wire:model="cli_allergies" rows="2" label="Alergias" placeholder="Penicilina, mariscos, látex..." />
                </div>
            </div>

            {{-- Lesión activa --}}
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50/40 p-4 dark:border-amber-900 dark:bg-amber-950/20">
                <h4 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                    <flux:icon.exclamation-triangle class="size-4" /> Lesión activa (opcional)
                </h4>
                <p class="mb-3 text-xs text-zinc-500">Si actualmente tiene una lesión, queda como evento clínico activo en su timeline.</p>
                <div class="grid gap-3 md:grid-cols-2">
                    <flux:input wire:model="lesion_description" label="Descripción" placeholder="Esguince de tobillo, lumbalgia..." class="md:col-span-2" />
                    <flux:input wire:model="lesion_body_region" label="Zona corporal" placeholder="Ej. tobillo derecho" />
                    <flux:select wire:model="lesion_severity" label="Severidad">
                        <flux:select.option value="">—</flux:select.option>
                        <flux:select.option value="leve">Leve</flux:select.option>
                        <flux:select.option value="moderada">Moderada</flux:select.option>
                        <flux:select.option value="grave">Grave</flux:select.option>
                    </flux:select>
                </div>
            </div>

            {{-- Medición inicial --}}
            <div class="mt-4 rounded-lg border border-sky-200 bg-sky-50/40 p-4 dark:border-sky-900 dark:bg-sky-950/20">
                <h4 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">
                    <flux:icon.scale class="size-4" /> Medición inicial (opcional)
                </h4>
                <p class="mb-3 text-xs text-zinc-500">Circunferencias y peso para iniciar el seguimiento. Más adelante podés cargar mediciones INBODY desde la ficha clínica.</p>
                <div class="grid gap-3 md:grid-cols-3">
                    <flux:input type="number" step="0.1" wire:model="meas_weight_kg" label="Peso (kg)" />
                    <flux:input type="number" wire:model="meas_height_cm" label="Altura (cm)" />
                    <flux:input type="number" step="0.1" wire:model="meas_waist_cm" label="Cintura (cm)" />
                    <flux:input type="number" step="0.1" wire:model="meas_hip_cm" label="Cadera (cm)" />
                    <flux:input type="number" step="0.1" wire:model="meas_chest_cm" label="Busto (cm)" />
                    <div></div>
                    <flux:input type="number" step="0.1" wire:model="meas_arm_right_cm" label="Brazo derecho (cm)" />
                    <flux:input type="number" step="0.1" wire:model="meas_arm_left_cm" label="Brazo izquierdo (cm)" />
                    <div></div>
                    <flux:input type="number" step="0.1" wire:model="meas_thigh_right_cm" label="Muslo derecho (cm)" />
                    <flux:input type="number" step="0.1" wire:model="meas_thigh_left_cm" label="Muslo izquierdo (cm)" />
                </div>
            </div>
        @endif

        {{-- STEP 4: Modules --}}
        @if ($step === 4)
            <flux:heading size="lg">Módulos</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Seleccioná en qué áreas se registrará la persona.</flux:text>
            <div class="mt-4 space-y-2">
                <label class="flex items-center gap-2 rounded border p-3 dark:border-zinc-700">
                    <input type="checkbox" wire:model="modules" value="gym"> <span class="font-medium">GYM</span> — Cliente del gimnasio con plan y suscripción
                </label>
                <label class="flex items-center gap-2 rounded border p-3 dark:border-zinc-700">
                    <input type="checkbox" wire:model="modules" value="kine"> <span class="font-medium">Kinesiología</span> — Paciente kine con tratamiento
                </label>
                <label class="flex items-center gap-2 rounded border p-3 dark:border-zinc-700">
                    <input type="checkbox" wire:model="modules" value="estetic"> <span class="font-medium">Estética</span> — Paciente estética con plan
                </label>
                @error('modules') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- STEP 5: GYM --}}
        @if ($step === 5)
            <flux:heading size="lg">GYM — Plan y suscripción</flux:heading>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <flux:select label="Plan" wire:model.live="plan_id" class="md:col-span-2">
                    <flux:select.option value="">Seleccionar...</flux:select.option>
                    @foreach ($plans as $plan)
                        <flux:select.option value="{{ $plan->id }}">{{ $plan->name }} (${{ number_format($plan->price, 0, ',', '.') }})</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input type="date" label="Fecha de inicio" wire:model.live="subscription_start" />
                <flux:input type="date" label="Fecha de término" wire:model="subscription_end" description="Se calcula a 30 días desde el inicio; puedes editarlo." />
            </div>

            {{-- Pago de la suscripción --}}
            <div class="mt-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h4 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-zinc-500">
                    <flux:icon.banknotes class="size-4" /> Pago de la suscripción
                </h4>

                <div class="grid gap-3 md:grid-cols-2">
                    <button type="button" wire:click="$set('gym_payment_choice', 'pending')"
                        class="rounded-xl border p-4 text-left transition
                            {{ $gym_payment_choice === 'pending'
                                ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/50 dark:bg-amber-950/30'
                                : 'border-zinc-200 hover:border-amber-300 dark:border-zinc-700' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon.clock class="size-5 text-amber-600" />
                                <span class="font-semibold">Dejar pendiente</span>
                            </div>
                            @if ($gym_payment_choice === 'pending')
                                <flux:icon.check-circle class="size-5 text-amber-600" />
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">Se generará un pago <strong>pendiente</strong> que aparecerá en la vista de Pagos.</p>
                    </button>

                    <button type="button" wire:click="$set('gym_payment_choice', 'now')"
                        class="rounded-xl border p-4 text-left transition
                            {{ $gym_payment_choice === 'now'
                                ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-950/30'
                                : 'border-zinc-200 hover:border-emerald-300 dark:border-zinc-700' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon.banknotes class="size-5 text-emerald-600" />
                                <span class="font-semibold">Registrar pago ahora</span>
                            </div>
                            @if ($gym_payment_choice === 'now')
                                <flux:icon.check-circle class="size-5 text-emerald-600" />
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">Cobra al admitir y registra el pago como <strong>pagado</strong>.</p>
                    </button>
                </div>

                @if ($gym_payment_choice === 'now')
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50/40 p-4 dark:border-emerald-900 dark:bg-emerald-950/20">
                        <div class="grid gap-3 md:grid-cols-2">
                            <flux:input type="number" step="1" min="0" wire:model="gym_payment_amount" label="Monto" placeholder="0" />
                            <flux:input type="date" wire:model="gym_payment_date" label="Fecha de pago" />
                            <flux:select wire:model="gym_payment_type" label="Método de pago">
                                <flux:select.option value="efectivo">Efectivo</flux:select.option>
                                <flux:select.option value="debito">Tarjeta de débito</flux:select.option>
                                <flux:select.option value="credito">Tarjeta de crédito</flux:select.option>
                                <flux:select.option value="transferencia">Transferencia</flux:select.option>
                                <flux:select.option value="webpay">Webpay</flux:select.option>
                                <flux:select.option value="otro">Otro</flux:select.option>
                            </flux:select>
                            <flux:input wire:model="gym_payment_notes" label="Observaciones" placeholder="N° comprobante, referencia..." />
                        </div>
                    </div>
                @else
                    @php $selectedPlan = $plans->firstWhere('id', $plan_id); @endphp
                    @if ($selectedPlan)
                        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                            <div class="flex items-start gap-2">
                                <flux:icon.information-circle class="size-5 shrink-0" />
                                <div>
                                    <strong>Pago pendiente:</strong> ${{ number_format($selectedPlan->price, 0, ',', '.') }}
                                    aparecerá en <em>Pagos → Pendientes</em>.
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        {{-- STEP 6: Kine --}}
        @if ($step === 6)
            <flux:heading size="lg">Kinesiología</flux:heading>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <flux:input label="Previsión" wire:model="health_insurance" placeholder="Fonasa / Isapre" />
                <flux:input label="N° Afiliado" wire:model="insurance_number" />
                <flux:input label="Diagnóstico" wire:model="kine_diagnostico" class="md:col-span-2" />
                <flux:textarea label="Plan de tratamiento" wire:model="kine_plan" class="md:col-span-2" />
                <flux:input type="number" label="Sesiones totales" wire:model="kine_sessions_total" />
                <flux:input type="number" step="0.01" label="Costo por sesión" wire:model="kine_cost_session" />
                <flux:select label="Profesional" wire:model="kine_professional_id" class="md:col-span-2">
                    <flux:select.option value="">—</flux:select.option>
                    @foreach ($kineProfessionals as $pro)
                        <flux:select.option value="{{ $pro->id }}">{{ $pro->full_name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        @endif

        {{-- STEP 7: Estetic --}}
        @if ($step === 7)
            <flux:heading size="lg">Estética — Protocolo</flux:heading>

            {{-- Selector de protocolo (bloqueado si vino por URL) --}}
            @if ($estetic_protocol_locked)
                @php $tipoSel = $tipos->firstWhere('id', $estetic_tipo_id); @endphp
                <div class="mt-4 rounded-xl border border-pink-200 bg-pink-50/40 p-4 dark:border-pink-900 dark:bg-pink-950/20">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="size-3 rounded-full" style="background: {{ $tipoSel?->color ?? '#ec4899' }}"></span>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-pink-700">Protocolo pre-seleccionado</div>
                                <div class="text-lg font-bold">{{ $tipoSel?->nombre }}</div>
                                <div class="text-xs text-zinc-500">
                                    {{ $tipoSel?->sesiones_recomendadas }} sesiones · cada {{ $tipoSel?->intervalo_dias }} días · {{ $tipoSel?->duracion_minutos }} min/sesión
                                </div>
                            </div>
                        </div>
                        <flux:button size="sm" variant="ghost" wire:click="$set('estetic_protocol_locked', false)">Cambiar</flux:button>
                    </div>
                </div>
            @else
                <div class="mt-4">
                    <flux:select label="Tipo de tratamiento" wire:model.live="estetic_tipo_id">
                        <flux:select.option value="">Seleccionar...</flux:select.option>
                        @foreach ($tipos as $tipo)
                            <flux:select.option value="{{ $tipo->id }}">{{ $tipo->nombre }} ({{ $tipo->sesiones_recomendadas ?: 1 }} ses · {{ $tipo->duracion_minutos }}min)</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            @endif

            {{-- Datos del paciente (tipo de piel) --}}
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <flux:select label="Tipo de piel" wire:model="skin_type">
                    <flux:select.option value="">—</flux:select.option>
                    <flux:select.option value="normal">Normal</flux:select.option>
                    <flux:select.option value="grasa">Grasa</flux:select.option>
                    <flux:select.option value="seca">Seca</flux:select.option>
                    <flux:select.option value="mixta">Mixta</flux:select.option>
                    <flux:select.option value="sensible">Sensible</flux:select.option>
                </flux:select>
                <div class="rounded bg-rose-50 p-2 text-xs text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">
                    💡 Las alergias y condiciones clínicas las cargaste en el paso 3 — ficha clínica.
                </div>
            </div>

            {{-- Plan de sesiones --}}
            <div class="mt-5 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Plan de sesiones</h4>
                <div class="grid gap-3 md:grid-cols-2">
                    <flux:input label="Zona tratada" wire:model="estetic_zona" />
                    <flux:select label="Profesional" wire:model="estetic_professional_id">
                        <flux:select.option value="">— Sin asignar —</flux:select.option>
                        @foreach ($esteticProfessionals as $pro)
                            <flux:select.option value="{{ $pro->id }}">{{ $pro->full_name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="mt-3 grid gap-3 md:grid-cols-4">
                    <flux:input type="number" min="1" max="50" wire:model="estetic_sessions_total" label="Sesiones" />
                    <flux:input type="number" min="1" max="365" wire:model="estetic_intervalo_dias" label="Intervalo (días)" />
                    <flux:input type="number" min="0" step="1" wire:model="estetic_costo_sesion" label="Costo / sesión" />
                    <div class="rounded-lg border border-pink-200 bg-pink-50 p-2 text-center dark:border-pink-900 dark:bg-pink-950/30">
                        <div class="text-[10px] uppercase text-pink-600">Total</div>
                        <div class="text-lg font-bold text-pink-700">${{ number_format(($estetic_costo_sesion ?? 0) * $estetic_sessions_total, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    <flux:input type="date" wire:model="estetic_start_date" label="Primera sesión" />
                    <flux:input type="time" wire:model="estetic_start_time" label="Hora" />
                </div>
            </div>

            {{-- Modalidad de pago --}}
            <div class="mt-5 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Modalidad de pago</h4>
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach ([
                        'pending'      => ['Dejar pendiente',  'clock',           'amber'],
                        'full'         => ['Pago completo',    'banknotes',       'emerald'],
                        'installments' => ['En cuotas',        'rectangle-stack', 'sky'],
                    ] as $val => [$label, $icon, $color])
                        <button type="button" wire:click="$set('estetic_payment_choice', '{{ $val }}')"
                            class="rounded-xl border p-3 text-left transition
                                {{ $estetic_payment_choice === $val
                                    ? "border-{$color}-500 ring-2 ring-{$color}-500/20 bg-{$color}-50/50"
                                    : 'border-zinc-200 hover:border-pink-300' }}">
                            <div class="flex items-center gap-2">
                                <flux:icon :name="$icon" class="size-4 text-{{ $color }}-600" />
                                <span class="text-sm font-semibold">{{ $label }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>

                @if ($estetic_payment_choice === 'full')
                    <div class="mt-3">
                        <flux:select wire:model="estetic_payment_method" label="Método de pago">
                            <flux:select.option value="efectivo">Efectivo</flux:select.option>
                            <flux:select.option value="debito">Tarjeta de débito</flux:select.option>
                            <flux:select.option value="credito">Tarjeta de crédito</flux:select.option>
                            <flux:select.option value="transferencia">Transferencia</flux:select.option>
                            <flux:select.option value="mercadopago">Mercado Pago</flux:select.option>
                            <flux:select.option value="otro">Otro</flux:select.option>
                        </flux:select>
                    </div>
                @endif

                @if ($estetic_payment_choice === 'installments')
                    <div class="mt-3 grid gap-3 md:grid-cols-3">
                        <flux:input type="number" min="2" max="24" wire:model="estetic_cuotas" label="N° de cuotas" />
                        <flux:input type="number" min="0" wire:model="estetic_abono_inicial" label="Abono inicial (opcional)" />
                        <flux:select wire:model="estetic_payment_method" label="Método del abono">
                            <flux:select.option value="efectivo">Efectivo</flux:select.option>
                            <flux:select.option value="transferencia">Transferencia</flux:select.option>
                            <flux:select.option value="debito">Débito</flux:select.option>
                            <flux:select.option value="credito">Crédito</flux:select.option>
                        </flux:select>
                    </div>
                @endif
            </div>
        @endif

        {{-- STEP 8: Schedule --}}
        @if ($step === 8)
            <flux:heading size="lg">Primera cita</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Agendamiento inicial para los módulos seleccionados.</flux:text>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <flux:input type="date" label="Fecha" wire:model="first_appointment_date" />
                <flux:input type="time" label="Hora" wire:model="first_appointment_time" />
            </div>
        @endif

        {{-- STEP 9: Confirm --}}
        @if ($step === 9)
            <flux:heading size="lg">Resumen</flux:heading>
            <div class="mt-4 space-y-2 text-sm">
                <div><strong>Persona:</strong> {{ $first_name }} {{ $last_name }} · RUT {{ $rut }}</div>
                <div><strong>Contacto:</strong> {{ $phone ? '+56 '.$phone : '—' }} · {{ $email ?? '—' }}</div>
                <div><strong>Domicilio:</strong> {{ $address }} @if ($poblacion) · {{ $poblacion }}@endif @if ($comuna) · {{ $comuna }}@endif</div>
                @if ($emergency_contact_name)
                    <div><strong>Contacto emergencia:</strong> {{ $emergency_contact_name }} ({{ $emergency_contact_relationship ?: 'sin relación' }}) — {{ $emergency_contact_phone ? '+56 '.$emergency_contact_phone : '—' }}</div>
                @endif
                <div><strong>Módulos:</strong>
                    @foreach ($modules as $m)
                        <flux:badge size="sm">{{ strtoupper($m) }}</flux:badge>
                    @endforeach
                </div>
                @if (in_array('gym', $modules))
                    <div><strong>Plan GYM:</strong> {{ $plans->firstWhere('id', $plan_id)?->name ?? '—' }} · {{ $subscription_start }} → {{ $subscription_end }}</div>
                    <div><strong>Pago:</strong>
                        @if ($gym_payment_choice === 'now')
                            <span class="text-emerald-600">Registrado · ${{ number_format($gym_payment_amount ?? 0, 0, ',', '.') }} · {{ ucfirst($gym_payment_type) }}</span>
                        @else
                            <span class="text-amber-600">Pendiente · ${{ number_format($plans->firstWhere('id', $plan_id)?->price ?? 0, 0, ',', '.') }}</span>
                        @endif
                    </div>
                @endif
                @if (in_array('kine', $modules))
                    <div><strong>Kine:</strong> {{ $kine_diagnostico }} — {{ $kine_sessions_total }} sesiones</div>
                @endif
                @if (in_array('estetic', $modules))
                    <div><strong>Estética:</strong> {{ $tipos->firstWhere('id', $estetic_tipo_id)?->nombre ?? '—' }} — {{ $estetic_sessions_total }} sesiones cada {{ $estetic_intervalo_dias }} días desde {{ $estetic_start_date }} {{ $estetic_start_time }}</div>
                    <div><strong>Pago Estética:</strong>
                        @if ($estetic_payment_choice === 'full')
                            <span class="text-emerald-600">Pago completo · ${{ number_format(($estetic_costo_sesion ?? 0) * $estetic_sessions_total, 0, ',', '.') }}</span>
                        @elseif ($estetic_payment_choice === 'installments')
                            <span class="text-sky-600">{{ $estetic_cuotas }} cuotas @if ($estetic_abono_inicial > 0) + abono ${{ number_format($estetic_abono_inicial, 0, ',', '.') }} @endif</span>
                        @else
                            <span class="text-amber-600">Pendiente · ${{ number_format(($estetic_costo_sesion ?? 0) * $estetic_sessions_total, 0, ',', '.') }}</span>
                        @endif
                    </div>
                @endif
                @if (array_intersect(['kine','estetic'], $modules))
                    <div><strong>Primera cita:</strong> {{ $first_appointment_date }} {{ $first_appointment_time }}</div>
                @endif
            </div>
        @endif
    </div>

    <div class="mt-6 flex justify-between">
        <flux:button wire:click="back" variant="ghost" :disabled="$step === 1">Atrás</flux:button>
        @if ($step < $totalSteps)
            <flux:button wire:click="next" variant="primary">Siguiente</flux:button>
        @else
            <flux:button wire:click="submit" variant="primary" icon="check">Confirmar admisión</flux:button>
        @endif
    </div>
</div>
