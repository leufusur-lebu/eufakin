<div class="p-6 space-y-6">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('admin.people.index') }}" wire:navigate class="hover:underline">Personas</a>
        <flux:icon.chevron-right class="size-3" />
        <a href="{{ route('admin.people.show', $person) }}" wire:navigate class="hover:underline">{{ $person->full_name }}</a>
        <flux:icon.chevron-right class="size-3" />
        <span>Ficha clínica</span>
    </div>

    {{-- Cabecera con datos críticos --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-rose-50 via-white to-sky-50 dark:border-zinc-700 dark:from-rose-950/30 dark:via-zinc-900 dark:to-sky-950/30">
        <div class="flex flex-wrap items-start gap-4 p-6">
            <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-400 to-rose-600 text-xl font-bold text-white shadow-lg">
                <flux:icon.heart class="size-8" />
            </div>
            <div class="min-w-0 flex-1">
                <flux:heading size="xl">Ficha clínica</flux:heading>
                <flux:text class="text-zinc-500">{{ $person->full_name }} · RUT {{ $person->rut }}</flux:text>

                {{-- Chips de alertas --}}
                <div class="mt-3 flex flex-wrap gap-2">
                    @if (($person->clinicalProfile?->allergies))
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                            <flux:icon.shield-exclamation class="size-3.5" /> Alergias
                        </span>
                    @endif
                    @if (($person->clinicalProfile?->chronic_diseases))
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                            <flux:icon.exclamation-triangle class="size-3.5" /> Enfermedades crónicas
                        </span>
                    @endif
                    @if ($person->clinicalProfile?->is_pregnant)
                        <span class="inline-flex items-center gap-1 rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-medium text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                            <flux:icon.heart class="size-3.5" /> Embarazo {{ $person->clinicalProfile->pregnancy_weeks ? '· '.$person->clinicalProfile->pregnancy_weeks.' sem' : '' }}
                        </span>
                    @endif
                    @if ($person->clinicalProfile?->blood_type)
                        <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            <flux:icon.beaker class="size-3.5" /> {{ $person->clinicalProfile->blood_type }}
                            @if ($person->clinicalProfile->donor) <span class="ml-1 rounded bg-emerald-200 px-1 text-[9px] text-emerald-800">Donante</span> @endif
                        </span>
                    @endif
                    @if ($stats['events_active'] > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-700 dark:bg-orange-900/40 dark:text-orange-300">
                            <flux:icon.exclamation-circle class="size-3.5" /> {{ $stats['events_active'] }} {{ Str::plural('evento activo', $stats['events_active']) }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button href="{{ route('admin.people.show', $person) }}" variant="ghost" icon="user" wire:navigate>Ver perfil</flux:button>
            </div>
        </div>

        {{-- Stats bar --}}
        <div class="grid grid-cols-2 gap-px border-t border-zinc-200 bg-zinc-200 md:grid-cols-4 dark:border-zinc-700 dark:bg-zinc-700">
            <div class="bg-white p-3 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Mediciones</div>
                <div class="mt-1 text-xl font-bold">{{ $stats['meas_count'] }}</div>
            </div>
            <div class="bg-white p-3 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Peso actual</div>
                <div class="mt-1 text-xl font-bold">{{ $latest?->weight_kg ? number_format($latest->weight_kg, 1).' kg' : '—' }}</div>
            </div>
            <div class="bg-white p-3 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">IMC</div>
                <div class="mt-1 text-xl font-bold">{{ $latest?->bmi ?? '—' }} <span class="text-xs font-normal text-zinc-500">{{ $latest?->bmi_category }}</span></div>
            </div>
            <div class="bg-white p-3 dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Adjuntos</div>
                <div class="mt-1 text-xl font-bold">{{ $stats['attachments'] }}</div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    {{-- Tabs --}}
    @php
        $tabs = [
            'overview'     => ['Resumen',       'home'],
            'baseline'     => ['Antecedentes',  'document-text'],
            'measurements' => ['Mediciones',    'scale'],
            'events'       => ['Eventos',       'exclamation-triangle'],
            'attachments'  => ['Adjuntos',      'paper-clip'],
        ];
    @endphp
    <div class="flex flex-wrap gap-1 border-b border-zinc-200 dark:border-zinc-700">
        @foreach ($tabs as $val => [$label, $icon])
            <button wire:click="$set('tab', '{{ $val }}')"
                class="flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition
                    {{ $tab === $val
                        ? 'border-rose-500 text-rose-600 dark:text-rose-400'
                        : 'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                <flux:icon :name="$icon" class="size-4" />
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ====== RESUMEN ====== --}}
    @if ($tab === 'overview')
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Última medición --}}
            <div class="lg:col-span-2 space-y-4">
                @if ($latest)
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-rose-600">Última medición</div>
                                <h3 class="mt-1 text-lg font-semibold">{{ $latest->measured_at?->isoFormat('dddd D [de] MMMM, YYYY') }}</h3>
                                <p class="text-sm text-zinc-500">{{ $latest->measured_at?->format('H:i') }} · fuente: {{ ucfirst($latest->source) }}</p>
                            </div>
                            <flux:button wire:click="$set('tab', 'measurements')" size="sm" variant="ghost">Ver todas →</flux:button>
                        </div>

                        <div class="mt-4 grid gap-3 grid-cols-2 md:grid-cols-4">
                            @if ($latest->weight_kg)
                                <div class="rounded-lg bg-rose-50 p-3 dark:bg-rose-950/30">
                                    <div class="text-[10px] uppercase text-rose-600">Peso</div>
                                    <div class="text-xl font-bold text-rose-700 dark:text-rose-300">{{ number_format($latest->weight_kg, 1) }} kg</div>
                                </div>
                            @endif
                            @if ($latest->bmi)
                                <div class="rounded-lg bg-amber-50 p-3 dark:bg-amber-950/30">
                                    <div class="text-[10px] uppercase text-amber-600">IMC</div>
                                    <div class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ $latest->bmi }}</div>
                                    <div class="text-[10px] text-amber-600">{{ $latest->bmi_category }}</div>
                                </div>
                            @endif
                            @if ($latest->body_fat_percent)
                                <div class="rounded-lg bg-violet-50 p-3 dark:bg-violet-950/30">
                                    <div class="text-[10px] uppercase text-violet-600">% Grasa</div>
                                    <div class="text-xl font-bold text-violet-700 dark:text-violet-300">{{ $latest->body_fat_percent }}%</div>
                                </div>
                            @endif
                            @if ($latest->skeletal_muscle_kg)
                                <div class="rounded-lg bg-emerald-50 p-3 dark:bg-emerald-950/30">
                                    <div class="text-[10px] uppercase text-emerald-600">Músculo esq.</div>
                                    <div class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ $latest->skeletal_muscle_kg }} kg</div>
                                </div>
                            @endif
                            @if ($latest->visceral_fat_level)
                                <div class="rounded-lg bg-orange-50 p-3 dark:bg-orange-950/30">
                                    <div class="text-[10px] uppercase text-orange-600">Grasa visceral</div>
                                    <div class="text-xl font-bold text-orange-700 dark:text-orange-300">Nivel {{ $latest->visceral_fat_level }}</div>
                                </div>
                            @endif
                            @if ($latest->blood_pressure_systolic && $latest->blood_pressure_diastolic)
                                <div class="rounded-lg bg-sky-50 p-3 dark:bg-sky-950/30">
                                    <div class="text-[10px] uppercase text-sky-600">Presión</div>
                                    <div class="text-xl font-bold text-sky-700 dark:text-sky-300">{{ $latest->blood_pressure_systolic }}/{{ $latest->blood_pressure_diastolic }}</div>
                                </div>
                            @endif
                            @if ($latest->bmr_kcal)
                                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                    <div class="text-[10px] uppercase text-zinc-500">BMR</div>
                                    <div class="text-xl font-bold">{{ $latest->bmr_kcal }} kcal</div>
                                </div>
                            @endif
                            @if ($latest->whr)
                                <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                                    <div class="text-[10px] uppercase text-zinc-500">WHR</div>
                                    <div class="text-xl font-bold">{{ $latest->whr }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:icon.scale class="mx-auto size-10 text-zinc-300" />
                        <p class="mt-2 text-sm text-zinc-500">Sin mediciones registradas</p>
                        <flux:button wire:click="openMeasurement" variant="primary" size="sm" icon="plus" class="mt-3">Registrar primera medición</flux:button>
                    </div>
                @endif

                {{-- Eventos activos --}}
                <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700">
                        <h3 class="font-semibold">Eventos activos / en tratamiento</h3>
                        <flux:button wire:click="openEvent" size="sm" variant="ghost" icon="plus">Nuevo</flux:button>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($events->whereIn('status', ['activo', 'en_tratamiento'])->take(5) as $ev)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <flux:icon :name="$ev->type_icon" class="size-5 text-rose-500" />
                                <div class="flex-1">
                                    <div class="text-sm font-medium">{{ $ev->type_label }}: {{ $ev->description }}</div>
                                    <div class="text-xs text-zinc-500">{{ $ev->event_date?->format('d/m/Y') }} @if ($ev->body_region) · {{ $ev->body_region }}@endif</div>
                                </div>
                                @php $sCol = match($ev->severity) { 'grave' => 'red', 'moderada' => 'amber', 'leve' => 'sky', default => 'zinc' }; @endphp
                                @if ($ev->severity)
                                    <flux:badge size="sm" :color="$sCol">{{ ucfirst($ev->severity) }}</flux:badge>
                                @endif
                            </div>
                        @empty
                            <div class="px-5 py-6 text-center text-sm text-zinc-400">Sin eventos activos.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Lateral: baseline + adjuntos --}}
            <div class="space-y-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Antecedentes</h3>
                        <flux:button wire:click="$set('tab', 'baseline')" size="sm" variant="ghost">Editar →</flux:button>
                    </div>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-rose-600 font-semibold">Alergias</dt>
                            <dd class="mt-0.5 rounded bg-rose-50 p-2 text-xs text-rose-800 dark:bg-rose-950/30 dark:text-rose-200">{{ $person->clinicalProfile?->allergies ?: 'Sin alergias registradas' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-amber-600 font-semibold">Enfermedades crónicas</dt>
                            <dd class="mt-0.5 rounded bg-amber-50 p-2 text-xs text-amber-800 dark:bg-amber-950/30 dark:text-amber-200">{{ $person->clinicalProfile?->chronic_diseases ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 font-semibold">Medicamentos habituales</dt>
                            <dd class="mt-0.5 rounded bg-zinc-50 p-2 text-xs dark:bg-zinc-800">{{ $person->clinicalProfile?->chronic_medications ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Adjuntos</h3>
                        <flux:button wire:click="$set('tab', 'attachments')" size="sm" variant="ghost">Ver →</flux:button>
                    </div>
                    @if ($attachments->count() > 0)
                        <div class="mt-3 space-y-2">
                            @foreach ($attachments->take(3) as $att)
                                <a href="{{ $att->url }}" target="_blank" class="flex items-center gap-2 rounded p-2 text-xs hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                    <flux:icon :name="$att->is_pdf ? 'document' : 'photo'" class="size-4 text-zinc-500" />
                                    <span class="flex-1 truncate">{{ $att->title }}</span>
                                    <span class="text-[10px] text-zinc-400">{{ $att->document_date?->format('d/m/Y') }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-xs text-zinc-400">Sin adjuntos.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ====== ANTECEDENTES (BASELINE) ====== --}}
    @if ($tab === 'baseline')
        <form wire:submit="saveBaseline" class="space-y-5">
            {{-- Sangre + donante --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Datos generales</h3>
                <div class="grid gap-4 md:grid-cols-3">
                    <flux:select wire:model="baseline.blood_type" label="Tipo de sangre">
                        <flux:select.option value="">—</flux:select.option>
                        <flux:select.option value="A+">A+</flux:select.option>
                        <flux:select.option value="A-">A-</flux:select.option>
                        <flux:select.option value="B+">B+</flux:select.option>
                        <flux:select.option value="B-">B-</flux:select.option>
                        <flux:select.option value="AB+">AB+</flux:select.option>
                        <flux:select.option value="AB-">AB-</flux:select.option>
                        <flux:select.option value="O+">O+</flux:select.option>
                        <flux:select.option value="O-">O-</flux:select.option>
                        <flux:select.option value="desconocido">Desconocido</flux:select.option>
                    </flux:select>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="baseline.donor"> Donante de sangre
                        </label>
                    </div>
                </div>
            </div>

            {{-- Enfermedades, medicamentos, alergias --}}
            <div class="rounded-xl border border-rose-200 bg-rose-50/30 p-5 dark:border-rose-900 dark:bg-rose-950/20">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">
                    <flux:icon.shield-exclamation class="size-5" /> Información crítica
                </h3>
                <div class="space-y-4">
                    <flux:textarea wire:model="baseline.allergies" rows="2" label="Alergias" placeholder="Medicamentos, alimentos, contacto..." />
                    <flux:textarea wire:model="baseline.chronic_diseases" rows="2" label="Enfermedades crónicas" placeholder="Diabetes, hipertensión, asma, hipotiroidismo..." />
                    <flux:textarea wire:model="baseline.chronic_medications" rows="2" label="Medicamentos habituales" placeholder="Qué medicamentos toma diariamente y dosis..." />
                </div>
            </div>

            {{-- Antecedentes y cirugías --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Antecedentes</h3>
                <div class="space-y-4">
                    <flux:textarea wire:model="baseline.surgical_history" rows="2" label="Cirugías previas" placeholder="Apendicectomía 2018, cesárea 2020..." />
                    <flux:textarea wire:model="baseline.family_history" rows="2" label="Antecedentes familiares" placeholder="Cáncer de mama materno, hipertensión paterna..." />
                </div>
            </div>

            {{-- Embarazo --}}
            <div class="rounded-xl border border-pink-200 bg-pink-50/30 p-5 dark:border-pink-900 dark:bg-pink-950/20">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-pink-700 dark:text-pink-300">Embarazo</h3>
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="baseline.is_pregnant"> Actualmente embarazada
                    </label>
                    @if ($baseline['is_pregnant'])
                        <flux:input type="number" min="1" max="42" wire:model="baseline.pregnancy_weeks" label="Semanas de gestación" />
                    @endif
                </div>
            </div>

            {{-- Hábitos --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Hábitos</h3>
                <div class="grid gap-4 md:grid-cols-3">
                    <flux:select wire:model="baseline.smoker" label="Tabaquismo">
                        <flux:select.option value="">—</flux:select.option>
                        <flux:select.option value="no">No fuma</flux:select.option>
                        <flux:select.option value="ex">Ex fumador</flux:select.option>
                        <flux:select.option value="ocasional">Ocasional</flux:select.option>
                        <flux:select.option value="habitual">Habitual</flux:select.option>
                    </flux:select>
                    <flux:select wire:model="baseline.alcohol" label="Alcohol">
                        <flux:select.option value="">—</flux:select.option>
                        <flux:select.option value="no">No bebe</flux:select.option>
                        <flux:select.option value="ocasional">Ocasional</flux:select.option>
                        <flux:select.option value="frecuente">Frecuente</flux:select.option>
                    </flux:select>
                    <flux:select wire:model="baseline.exercise_frequency" label="Frecuencia de ejercicio">
                        <flux:select.option value="">—</flux:select.option>
                        <flux:select.option value="sedentario">Sedentario</flux:select.option>
                        <flux:select.option value="ocasional">Ocasional</flux:select.option>
                        <flux:select.option value="regular">Regular (3-4 sem)</flux:select.option>
                        <flux:select.option value="intenso">Intenso (5+ sem)</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:textarea wire:model="baseline.notes" rows="3" label="Notas adicionales" />
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="check">Guardar antecedentes</flux:button>
            </div>
        </form>
    @endif

    {{-- ====== MEDICIONES ====== --}}
    @if ($tab === 'measurements')
        <div class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Historial de mediciones</h3>
                <flux:button wire:click="openMeasurement" variant="primary" size="sm" icon="plus">Nueva medición</flux:button>
            </div>

            {{-- Gráficas de circunferencias --}}
            @if ($series->count() >= 2)
                @php
                    $graphs = [
                        ['Peso (kg)',       'weight_kg',       'rose'],
                        ['IMC',             'bmi',             'amber'],
                        ['Cintura (cm)',    'waist_cm',        'orange'],
                        ['Cadera (cm)',     'hip_cm',          'pink'],
                        ['Busto (cm)',      'chest_cm',        'fuchsia'],
                        ['Brazo D (cm)',    'arm_right_cm',    'sky'],
                        ['Brazo I (cm)',    'arm_left_cm',     'indigo'],
                        ['Muslo D (cm)',    'thigh_right_cm',  'emerald'],
                        ['Muslo I (cm)',    'thigh_left_cm',   'teal'],
                    ];
                @endphp
                <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-5">
                    @foreach ($graphs as [$label, $field, $color])
                        @php
                            $vals = $series->pluck($field)->filter()->values();
                            $current = $vals->last();
                            $first   = $vals->first();
                            $maxV = $vals->max() ?: 1;
                            $minV = $vals->min() ?: 0;
                            $range = max(0.1, $maxV - $minV);
                        @endphp
                        @if ($vals->count() > 0)
                            <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-semibold uppercase tracking-wide text-zinc-500">{{ $label }}</span>
                                    @if ($current !== null && $first !== null && $current != $first)
                                        @php $delta = $current - $first; $sign = $delta >= 0 ? '+' : ''; $isGood = in_array($field, ['arm_right_cm','arm_left_cm','thigh_right_cm','thigh_left_cm']) ? $delta >= 0 : $delta <= 0; @endphp
                                        <span class="text-[10px] font-bold {{ $isGood ? 'text-emerald-600' : 'text-rose-600' }}">{{ $sign }}{{ number_format($delta, 1) }}</span>
                                    @endif
                                </div>
                                <div class="mt-1 text-xl font-bold">{{ $current !== null ? number_format($current, 1) : '—' }}</div>
                                @if ($vals->count() > 1)
                                    <div class="mt-2 flex h-8 items-end gap-0.5">
                                        @foreach ($vals as $v)
                                            @php $h = max(8, round((($v - $minV) / $range) * 100)); @endphp
                                            <div class="flex-1 rounded-t bg-{{ $color }}-400" style="height: {{ $h }}%"></div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
                <p class="text-xs text-zinc-500">💡 El delta verde indica progreso favorable (perder cintura, ganar musculatura), rojo lo opuesto.</p>
            @endif

            {{-- Tabla — foco en circunferencias --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 text-left">Fecha</th>
                                <th class="px-3 py-2 text-right">Peso</th>
                                <th class="px-3 py-2 text-right">IMC</th>
                                <th class="px-3 py-2 text-right">Cintura</th>
                                <th class="px-3 py-2 text-right">Cadera</th>
                                <th class="px-3 py-2 text-right">Busto</th>
                                <th class="px-3 py-2 text-right">Brazos D/I</th>
                                <th class="px-3 py-2 text-right">Muslos D/I</th>
                                <th class="px-3 py-2 text-center">Origen</th>
                                <th class="px-3 py-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($measurements as $m)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="font-medium">{{ $m->measured_at?->format('d/m/Y') }}</div>
                                        <div class="text-[10px] text-zinc-500">{{ $m->measured_at?->format('H:i') }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold">{{ $m->weight_kg ? number_format($m->weight_kg, 1) : '—' }}</td>
                                    <td class="px-3 py-2 text-right">{{ $m->bmi ?? '—' }}</td>
                                    <td class="px-3 py-2 text-right">{{ $m->waist_cm ? number_format($m->waist_cm, 1) : '—' }}</td>
                                    <td class="px-3 py-2 text-right">{{ $m->hip_cm ? number_format($m->hip_cm, 1) : '—' }}</td>
                                    <td class="px-3 py-2 text-right">{{ $m->chest_cm ? number_format($m->chest_cm, 1) : '—' }}</td>
                                    <td class="px-3 py-2 text-right text-xs">
                                        @if ($m->arm_right_cm || $m->arm_left_cm)
                                            {{ $m->arm_right_cm ? number_format($m->arm_right_cm,1) : '—' }} / {{ $m->arm_left_cm ? number_format($m->arm_left_cm,1) : '—' }}
                                        @else — @endif
                                    </td>
                                    <td class="px-3 py-2 text-right text-xs">
                                        @if ($m->thigh_right_cm || $m->thigh_left_cm)
                                            {{ $m->thigh_right_cm ? number_format($m->thigh_right_cm,1) : '—' }} / {{ $m->thigh_left_cm ? number_format($m->thigh_left_cm,1) : '—' }}
                                        @else — @endif
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @php
                                            $sCol = match($m->source) {
                                                'inbody' => 'rose', 'session_auto' => 'sky',
                                                'admission' => 'emerald', default => 'zinc',
                                            };
                                        @endphp
                                        <span class="rounded bg-{{ $sCol }}-100 px-1.5 py-0.5 text-[9px] uppercase text-{{ $sCol }}-700 dark:bg-{{ $sCol }}-900/40 dark:text-{{ $sCol }}-300">{{ $m->source }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openMeasurement({{ $m->id }})" />
                                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteMeasurement({{ $m->id }})" wire:confirm="¿Eliminar esta medición?" />
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-4 py-12 text-center text-zinc-400">Sin mediciones aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="text-xs text-zinc-500">📌 La tabla muestra circunferencias. Los datos INBODY y signos vitales se ven al editar una medición.</p>
        </div>
    @endif

    {{-- ====== EVENTOS ====== --}}
    @if ($tab === 'events')
        <div class="space-y-4">
            <div class="flex justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Timeline de eventos clínicos</h3>
                <flux:button wire:click="openEvent" variant="primary" size="sm" icon="plus">Nuevo evento</flux:button>
            </div>

            <div class="space-y-3">
                @forelse ($events as $ev)
                    @php
                        $sCol = match($ev->severity) { 'grave' => 'red', 'moderada' => 'amber', 'leve' => 'sky', default => 'zinc' };
                        $stCol = match($ev->status) { 'activo' => 'red', 'en_tratamiento' => 'amber', 'resuelto' => 'green', default => 'zinc' };
                    @endphp
                    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-300">
                                    <flux:icon :name="$ev->type_icon" class="size-5" />
                                </span>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-semibold">{{ $ev->type_label }}: {{ $ev->description }}</h4>
                                        @if ($ev->severity)<flux:badge size="sm" :color="$sCol">{{ ucfirst($ev->severity) }}</flux:badge>@endif
                                        <flux:badge size="sm" :color="$stCol">{{ str_replace('_',' ',ucfirst($ev->status)) }}</flux:badge>
                                    </div>
                                    <div class="text-xs text-zinc-500">{{ $ev->event_date?->format('d/m/Y') }} @if ($ev->body_region) · {{ $ev->body_region }}@endif</div>
                                    @if ($ev->notes)
                                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $ev->notes }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEvent({{ $ev->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteEvent({{ $ev->id }})" wire:confirm="¿Eliminar este evento?" />
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
                        <flux:icon.exclamation-triangle class="mx-auto size-10 text-zinc-300" />
                        <p class="mt-3 text-sm text-zinc-500">Sin eventos clínicos registrados.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ====== ADJUNTOS ====== --}}
    @if ($tab === 'attachments')
        <div class="space-y-4">
            <div class="flex justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Documentos clínicos</h3>
                <flux:button wire:click="openAttachment" variant="primary" size="sm" icon="cloud-arrow-up">Subir adjunto</flux:button>
            </div>

            @if ($attachments->isEmpty())
                <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
                    <flux:icon.paper-clip class="mx-auto size-10 text-zinc-300" />
                    <p class="mt-3 text-sm text-zinc-500">Sin documentos adjuntos.</p>
                    <p class="text-xs text-zinc-400">Sube exámenes, RX, informes médicos o reportes INBODY.</p>
                </div>
            @else
                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($attachments as $att)
                        @php
                            $catColor = match($att->category) {
                                'examen' => 'sky', 'imagen' => 'violet', 'informe' => 'emerald',
                                'receta' => 'amber', 'inbody' => 'rose', default => 'zinc'
                            };
                        @endphp
                        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                            @if ($att->is_image)
                                <a href="{{ $att->url }}" target="_blank" class="block aspect-video overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                    <img src="{{ $att->url }}" class="h-full w-full object-cover">
                                </a>
                            @else
                                <a href="{{ $att->url }}" target="_blank" class="flex aspect-video items-center justify-center bg-zinc-50 dark:bg-zinc-800">
                                    <flux:icon :name="$att->is_pdf ? 'document-text' : 'document'" class="size-12 text-zinc-400" />
                                </a>
                            @endif
                            <div class="p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="truncate text-sm font-semibold">{{ $att->title }}</h4>
                                        <div class="mt-0.5 flex items-center gap-2 text-xs text-zinc-500">
                                            <span class="rounded bg-{{ $catColor }}-100 px-1.5 py-0.5 text-[10px] uppercase text-{{ $catColor }}-700 dark:bg-{{ $catColor }}-900/40 dark:text-{{ $catColor }}-300">{{ $att->category }}</span>
                                            @if ($att->document_date) <span>{{ $att->document_date->format('d/m/Y') }}</span>@endif
                                        </div>
                                    </div>
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteAttachment({{ $att->id }})" wire:confirm="¿Eliminar adjunto?" />
                                </div>
                                @if ($att->notes)
                                    <p class="mt-2 line-clamp-2 text-xs text-zinc-500">{{ $att->notes }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- ============ MODAL: MEDICIÓN ============ --}}
    <flux:modal wire:model="mOpen" class="md:w-[800px]">
        <div class="space-y-5">
            <flux:heading size="lg">{{ $editingMeasurementId ? 'Editar medición' : 'Nueva medición' }}</flux:heading>

            <div class="grid gap-4 md:grid-cols-3">
                <flux:input type="datetime-local" wire:model="m.measured_at" label="Fecha y hora" />
                <flux:select wire:model="m.source" label="Origen">
                    <flux:select.option value="manual">Manual</flux:select.option>
                    <flux:select.option value="inbody">INBODY</flux:select.option>
                    <flux:select.option value="session_auto">Sesión kine</flux:select.option>
                    <flux:select.option value="admission">Admisión</flux:select.option>
                </flux:select>
            </div>

            {{-- ===== CIRCUNFERENCIAS (siempre visible, foco principal) ===== --}}
            <div class="rounded-lg border border-rose-200 bg-rose-50/40 p-4 dark:border-rose-900 dark:bg-rose-950/20">
                <h4 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">
                    <flux:icon.scale class="size-4" /> Circunferencias y peso
                </h4>
                <div class="grid gap-3 md:grid-cols-3">
                    <flux:input type="number" step="0.1" min="0" wire:model="m.weight_kg" label="Peso (kg)" />
                    <flux:input type="number" wire:model="m.height_cm" label="Altura (cm)" />
                    <flux:input type="number" step="0.1" wire:model="m.waist_cm" label="Cintura (cm)" />
                    <flux:input type="number" step="0.1" wire:model="m.hip_cm" label="Cadera (cm)" />
                    <flux:input type="number" step="0.1" wire:model="m.chest_cm" label="Busto (cm)" />
                    <div></div>
                    <flux:input type="number" step="0.1" wire:model="m.arm_right_cm" label="Brazo derecho (cm)" />
                    <flux:input type="number" step="0.1" wire:model="m.arm_left_cm" label="Brazo izquierdo (cm)" />
                    <div></div>
                    <flux:input type="number" step="0.1" wire:model="m.thigh_right_cm" label="Muslo derecho (cm)" />
                    <flux:input type="number" step="0.1" wire:model="m.thigh_left_cm" label="Muslo izquierdo (cm)" />
                </div>
                <p class="mt-3 text-xs text-rose-600 dark:text-rose-400">💡 El IMC se calcula automáticamente con peso y altura.</p>
            </div>

            {{-- ===== COMPOSICIÓN CORPORAL (INBODY) — colapsable ===== --}}
            <details class="rounded-lg border border-violet-200 bg-violet-50/40 dark:border-violet-900 dark:bg-violet-950/20">
                <summary class="cursor-pointer select-none px-4 py-3 text-xs font-semibold uppercase tracking-wide text-violet-700 hover:bg-violet-100/40 dark:text-violet-300">
                    <div class="inline-flex items-center gap-2">
                        <flux:icon.cube class="size-4" />
                        Composición corporal avanzada (INBODY) <span class="ml-1 normal-case text-zinc-500">— opcional</span>
                    </div>
                </summary>
                <div class="space-y-4 border-t border-violet-200 p-4 dark:border-violet-900">
                    <div>
                        <h5 class="mb-2 text-[10px] font-bold uppercase tracking-wider text-violet-600 dark:text-violet-400">Masa y composición</h5>
                        <div class="grid gap-3 md:grid-cols-3">
                            <flux:input type="number" step="0.01" wire:model="m.body_fat_kg" label="Grasa (kg)" />
                            <flux:input type="number" step="0.01" wire:model="m.body_fat_percent" label="% Grasa" />
                            <flux:input type="number" step="0.01" wire:model="m.skeletal_muscle_kg" label="Músculo esq. (kg)" />
                            <flux:input type="number" step="0.01" wire:model="m.fat_free_mass_kg" label="Masa libre grasa (kg)" />
                            <flux:input type="number" step="0.01" wire:model="m.soft_lean_mass_kg" label="Masa magra blanda (kg)" />
                            <flux:input type="number" step="0.01" wire:model="m.protein_kg" label="Proteína (kg)" />
                            <flux:input type="number" step="0.01" wire:model="m.mineral_kg" label="Minerales (kg)" />
                            <flux:input type="number" step="0.01" wire:model="m.phase_angle" label="Ángulo de fase (°)" />
                            <flux:input type="number" wire:model="m.inbody_score" label="InBody Score" />
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-2 text-[10px] font-bold uppercase tracking-wider text-violet-600 dark:text-violet-400">Agua corporal</h5>
                        <div class="grid gap-3 md:grid-cols-4">
                            <flux:input type="number" step="0.01" wire:model="m.total_body_water_l" label="TBW (L)" />
                            <flux:input type="number" step="0.01" wire:model="m.intracellular_water_l" label="ICW (L)" />
                            <flux:input type="number" step="0.01" wire:model="m.extracellular_water_l" label="ECW (L)" />
                            <flux:input type="number" step="0.001" wire:model="m.ecw_tbw_ratio" label="ECW/TBW" />
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-2 text-[10px] font-bold uppercase tracking-wider text-violet-600 dark:text-violet-400">Visceral y metabolismo</h5>
                        <div class="grid gap-3 md:grid-cols-3">
                            <flux:input type="number" wire:model="m.visceral_fat_area" label="Grasa visceral (cm²)" />
                            <flux:input type="number" wire:model="m.visceral_fat_level" label="Nivel visceral" />
                            <flux:input type="number" wire:model="m.bmr_kcal" label="BMR (kcal)" />
                        </div>
                    </div>
                </div>
            </details>

            {{-- ===== SIGNOS VITALES — colapsable ===== --}}
            <details class="rounded-lg border border-emerald-200 bg-emerald-50/40 dark:border-emerald-900 dark:bg-emerald-950/20">
                <summary class="cursor-pointer select-none px-4 py-3 text-xs font-semibold uppercase tracking-wide text-emerald-700 hover:bg-emerald-100/40 dark:text-emerald-300">
                    <div class="inline-flex items-center gap-2">
                        <flux:icon.heart class="size-4" />
                        Signos vitales <span class="ml-1 normal-case text-zinc-500">— opcional</span>
                    </div>
                </summary>
                <div class="border-t border-emerald-200 p-4 dark:border-emerald-900">
                    <div class="grid gap-3 md:grid-cols-4">
                        <flux:input type="number" wire:model="m.blood_pressure_systolic" label="Sistólica" />
                        <flux:input type="number" wire:model="m.blood_pressure_diastolic" label="Diastólica" />
                        <flux:input type="number" wire:model="m.heart_rate" label="FC (bpm)" />
                        <flux:input type="number" wire:model="m.glucose_mg_dl" label="Glicemia (mg/dL)" />
                    </div>
                </div>
            </details>

            <flux:textarea wire:model="m.notes" rows="2" label="Notas" />

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('mOpen', false)">Cancelar</flux:button>
                <flux:button variant="primary" icon="check" wire:click="saveMeasurement">Guardar medición</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============ MODAL: EVENTO ============ --}}
    <flux:modal wire:model="eOpen" class="md:w-[600px]">
        <div class="space-y-5">
            <flux:heading size="lg">{{ $editingEventId ? 'Editar evento' : 'Nuevo evento clínico' }}</flux:heading>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:select wire:model="e.type" label="Tipo">
                    <flux:select.option value="lesion">Lesión</flux:select.option>
                    <flux:select.option value="cirugia">Cirugía</flux:select.option>
                    <flux:select.option value="hospitalizacion">Hospitalización</flux:select.option>
                    <flux:select.option value="alergia_grave">Alergia grave</flux:select.option>
                    <flux:select.option value="vacuna">Vacuna</flux:select.option>
                    <flux:select.option value="enfermedad">Enfermedad</flux:select.option>
                    <flux:select.option value="otro">Otro</flux:select.option>
                </flux:select>
                <flux:input type="date" wire:model="e.event_date" label="Fecha" />
                <flux:input wire:model="e.description" label="Descripción" placeholder="Esguince grado II" class="md:col-span-2" />
                <flux:input wire:model="e.body_region" label="Zona corporal (opcional)" placeholder="Ej. tobillo derecho" />
                <flux:select wire:model="e.severity" label="Severidad">
                    <flux:select.option value="">—</flux:select.option>
                    <flux:select.option value="leve">Leve</flux:select.option>
                    <flux:select.option value="moderada">Moderada</flux:select.option>
                    <flux:select.option value="grave">Grave</flux:select.option>
                </flux:select>
                <flux:select wire:model="e.status" label="Estado" class="md:col-span-2">
                    <flux:select.option value="activo">Activo</flux:select.option>
                    <flux:select.option value="en_tratamiento">En tratamiento</flux:select.option>
                    <flux:select.option value="resuelto">Resuelto</flux:select.option>
                </flux:select>
                <flux:textarea wire:model="e.notes" rows="3" label="Notas" class="md:col-span-2" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('eOpen', false)">Cancelar</flux:button>
                <flux:button variant="primary" icon="check" wire:click="saveEvent">Guardar evento</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============ MODAL: ADJUNTO ============ --}}
    <flux:modal wire:model="aOpen" class="md:w-[560px]">
        <div class="space-y-5">
            <flux:heading size="lg">Subir adjunto</flux:heading>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="attTitle" label="Título" placeholder="Ej. RX rodilla derecha" class="md:col-span-2" />
                <flux:select wire:model="attCategory" label="Categoría">
                    <flux:select.option value="examen">Examen</flux:select.option>
                    <flux:select.option value="imagen">Imagen / RX</flux:select.option>
                    <flux:select.option value="informe">Informe médico</flux:select.option>
                    <flux:select.option value="receta">Receta</flux:select.option>
                    <flux:select.option value="inbody">Reporte INBODY</flux:select.option>
                    <flux:select.option value="otro">Otro</flux:select.option>
                </flux:select>
                <flux:input type="date" wire:model="attDate" label="Fecha del documento" />
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium">Archivo (PDF, JPG, PNG · máx 10MB)</label>
                    <input type="file" wire:model="attFile" accept=".pdf,image/*"
                        class="block w-full text-sm file:mr-2 file:rounded file:border-0 file:bg-rose-100 file:px-3 file:py-1.5 file:text-rose-700 hover:file:bg-rose-200">
                    @error('attFile') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <flux:textarea wire:model="attNotes" rows="2" label="Notas" class="md:col-span-2" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('aOpen', false)">Cancelar</flux:button>
                <flux:button variant="primary" icon="cloud-arrow-up" wire:click="saveAttachment">Subir</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
