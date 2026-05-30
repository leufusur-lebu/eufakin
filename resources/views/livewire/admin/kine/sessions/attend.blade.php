<div class="p-6 max-w-4xl space-y-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-500">
            <a href="{{ route('admin.kine.patients.show', $appointment->kine_profile_id) }}" wire:navigate class="hover:underline">Ficha del paciente</a>
            <flux:icon.chevron-right class="size-3" />
            <span>Atender sesión</span>
        </div>
        <flux:heading size="xl">Atender sesión kinésica</flux:heading>
        <flux:text class="text-zinc-500">{{ $appointment->kineProfile?->person?->full_name }} · {{ $appointment->inicio?->format('d/m/Y H:i') }}</flux:text>
    </div>

    <div class="rounded-xl border border-sky-200 bg-gradient-to-br from-sky-50 to-indigo-50 p-5 dark:border-sky-900 dark:from-sky-950/30 dark:to-indigo-950/30">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-sky-600">Cita</div>
                <h3 class="text-lg font-semibold">{{ $appointment->treatment?->tipoTratamiento?->nombre ?? $appointment->motivo ?? 'Sesión' }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                    {{ $appointment->treatment?->diagnostico ?? '—' }}
                    @if ($appointment->treatment?->zona_tratada) · {{ $appointment->treatment->zona_tratada }}@endif
                    @if ($appointment->professional) · con {{ $appointment->professional->full_name }}@endif
                </p>
            </div>
            @if ($appointment->treatment)
                <div class="rounded-lg bg-white p-3 text-center text-xs dark:bg-zinc-900">
                    <div class="text-zinc-500">Progreso</div>
                    <div class="text-lg font-bold text-sky-600">
                        {{ ($appointment->treatment->sesiones_realizadas ?? 0) + 1 }} / {{ $appointment->treatment->sesiones_totales }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Alertas clínicas del paciente --}}
    @if ($appointment->kineProfile?->person)
        <x-clinical-alerts :person="$appointment->kineProfile->person" />
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- Mediciones rápidas --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Mediciones</h3>
            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Escala de dolor (EVA)</label>
                    <input type="range" min="0" max="10" wire:model.live="escala_dolor" class="w-full accent-rose-500">
                    <div class="flex items-center justify-between text-xs text-zinc-500">
                        <span>0</span>
                        <span class="font-bold {{ $escala_dolor >= 7 ? 'text-rose-600' : ($escala_dolor >= 4 ? 'text-amber-600' : 'text-emerald-600') }}">EVA {{ $escala_dolor }}/10</span>
                        <span>10</span>
                    </div>
                </div>
                <flux:input wire:model="rom" label="ROM" placeholder="Ej. 90° flex / 30° ext" />
                <flux:input wire:model="fuerza_muscular" label="Fuerza muscular" placeholder="Ej. 4/5" />
                <flux:input type="number" min="1" wire:model="duracion_real_minutos" label="Duración real (min)" />
            </div>
        </div>

        {{-- Registro clínico --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Registro clínico</h3>
            <div class="space-y-4">
                <flux:textarea wire:model="evolucion" rows="2" label="Evolución" placeholder="Cómo respondió el paciente, cambios desde la sesión anterior..." />
                <flux:textarea wire:model="ejercicios" rows="2" label="Ejercicios realizados" placeholder="Series y repeticiones, equipamiento usado..." />
                <flux:textarea wire:model="notas_clinicas" rows="3" label="Notas clínicas" placeholder="Observaciones, pendientes, indicaciones para casa..." />
            </div>
        </div>

        {{-- Fotos --}}
        <div class="rounded-xl border border-sky-200 bg-sky-50/30 p-5 dark:border-sky-900 dark:bg-sky-950/20">
            <div class="mb-4 flex items-center gap-2">
                <flux:icon.camera class="size-5 text-sky-600" />
                <h3 class="text-sm font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Fotos / archivos clínicos</h3>
                <span class="text-xs text-zinc-500">JPG/PNG · máx 5MB cada una</span>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach (['inicial' => ['Estado inicial', 'photos_inicial', 'sky'], 'evolucion' => ['Evolución', 'photos_evolucion', 'amber'], 'final' => ['Estado final', 'photos_final', 'emerald']] as $key => [$label, $field, $color])
                    <div class="rounded-lg border border-{{ $color }}-200 bg-white p-3 dark:border-{{ $color }}-900 dark:bg-zinc-900">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wide text-{{ $color }}-700 dark:text-{{ $color }}-300">{{ $label }}</span>
                            <span class="rounded-full bg-{{ $color }}-100 px-1.5 text-[10px] font-medium text-{{ $color }}-700">{{ count($$field) }}</span>
                        </div>
                        <input type="file" wire:model="{{ $field }}" multiple accept="image/*"
                            class="block w-full text-xs file:mr-2 file:rounded file:border-0 file:bg-{{ $color }}-100 file:px-3 file:py-1.5 file:text-{{ $color }}-700 hover:file:bg-{{ $color }}-200">

                        @if (count($$field) > 0)
                            <div class="mt-3 grid grid-cols-3 gap-1.5">
                                @foreach ($$field as $photo)
                                    @if ($photo)
                                        <div class="relative aspect-square overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                                            <img src="{{ $photo->temporaryUrl() }}" class="h-full w-full object-cover">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        @error("{$field}.*") <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Medición rápida (alimenta ficha clínica) --}}
        <div class="rounded-xl border border-rose-200 bg-rose-50/30 p-5 dark:border-rose-900 dark:bg-rose-950/20">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model.live="record_measurement" class="rounded">
                <span class="flex items-center gap-1.5 text-sm font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">
                    <flux:icon.heart class="size-4" />
                    Registrar medición rápida en la ficha clínica
                </span>
            </label>
            @if ($record_measurement)
                <div class="mt-4 grid gap-3 md:grid-cols-4">
                    <flux:input type="number" step="0.1" wire:model="m_weight_kg" label="Peso (kg)" />
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Presión arterial</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="m_bp_sys" placeholder="Sistólica" class="w-full rounded-lg border border-zinc-300 px-3 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                            <span class="text-zinc-400">/</span>
                            <input type="number" wire:model="m_bp_dia" placeholder="Diastólica" class="w-full rounded-lg border border-zinc-300 px-3 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        </div>
                    </div>
                    <flux:input type="number" wire:model="m_heart_rate" label="FC (bpm)" />
                </div>
                <p class="mt-2 text-xs text-zinc-500">💡 Esta medición se guardará en la <strong>ficha clínica del paciente</strong> con origen "Sesión kine".</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-sm text-zinc-500">
                Al confirmar: la cita pasará a <strong class="text-emerald-600">Atendida</strong>, se registrará la sesión clínica y se subirán las fotos.
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('admin.kine.patients.show', $appointment->kine_profile_id) }}" variant="ghost" wire:navigate>Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="check">Registrar sesión</flux:button>
            </div>
        </div>
    </form>
</div>
