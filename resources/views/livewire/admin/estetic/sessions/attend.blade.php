<div class="p-6 max-w-4xl space-y-6">
    {{-- Header --}}
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-500">
            <a href="{{ route('admin.estetic.patients.show', $appointment->estetic_profile_id) }}" wire:navigate class="hover:underline">Ficha del paciente</a>
            <flux:icon.chevron-right class="size-3" />
            <span>Atender sesión</span>
        </div>
        <flux:heading size="xl">Atender sesión</flux:heading>
        <flux:text class="text-zinc-500">{{ $appointment->esteticProfile?->person?->full_name }} · {{ $appointment->inicio?->format('d/m/Y H:i') }}</flux:text>
    </div>

    {{-- Resumen de la cita --}}
    <div class="rounded-xl border border-pink-200 bg-gradient-to-br from-pink-50 to-rose-50 p-5 dark:border-pink-900 dark:from-pink-950/30 dark:to-rose-950/30">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-pink-600">Cita</div>
                <h3 class="text-lg font-semibold">{{ $appointment->treatment?->tipoTratamiento?->nombre ?? $appointment->motivo ?? 'Sesión' }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                    {{ $appointment->treatment?->zona_tratada ?? '—' }}
                    @if ($appointment->professional) · con {{ $appointment->professional->full_name }}@endif
                </p>
            </div>
            @if ($appointment->treatment)
                <div class="rounded-lg bg-white p-3 text-center text-xs dark:bg-zinc-900">
                    <div class="text-zinc-500">Progreso</div>
                    <div class="text-lg font-bold text-pink-600">
                        {{ ($appointment->treatment->sesiones_realizadas ?? 0) + 1 }} / {{ $appointment->treatment->sesiones_totales }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Alertas clínicas del paciente --}}
    @if ($appointment->esteticProfile?->person)
        <x-clinical-alerts :person="$appointment->esteticProfile->person" />
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- Datos clínicos --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Registro clínico</h3>
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="zona_especifica" label="Zona específica" placeholder="Ej. mejilla derecha" />
                <flux:select wire:model="intensidad" label="Intensidad">
                    <flux:select.option value="baja">Baja</flux:select.option>
                    <flux:select.option value="media">Media</flux:select.option>
                    <flux:select.option value="alta">Alta</flux:select.option>
                </flux:select>
                <flux:input type="number" min="1" wire:model="duracion_real_minutos" label="Duración real (min)" />
            </div>
            <div class="mt-4 space-y-4">
                <flux:textarea wire:model="productos_utilizados" rows="2" label="Productos utilizados" placeholder="Ácidos, máscaras, equipos..." />
                <flux:textarea wire:model="resultados_observados" rows="2" label="Resultados observados" placeholder="Cómo respondió la piel, eritema, mejoras..." />
                <flux:textarea wire:model="notas_clinicas" rows="3" label="Notas clínicas" placeholder="Indicaciones post-sesión, recomendaciones, próximos pasos..." />
            </div>
        </div>

        {{-- Fotos --}}
        <div class="rounded-xl border border-pink-200 bg-pink-50/30 p-5 dark:border-pink-900 dark:bg-pink-950/20">
            <div class="mb-4 flex items-center gap-2">
                <flux:icon.camera class="size-5 text-pink-600" />
                <h3 class="text-sm font-semibold uppercase tracking-wide text-pink-700 dark:text-pink-300">Fotos clínicas</h3>
                <span class="text-xs text-zinc-500">JPG/PNG · máx 5MB cada una</span>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach (['antes' => ['Antes', 'photos_antes', 'sky'], 'durante' => ['Durante', 'photos_durante', 'amber'], 'despues' => ['Después', 'photos_despues', 'emerald']] as $key => [$label, $field, $color])
                    <div class="rounded-lg border border-{{ $color }}-200 bg-white p-3 dark:border-{{ $color }}-900 dark:bg-zinc-900">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wide text-{{ $color }}-700 dark:text-{{ $color }}-300">{{ $label }}</span>
                            <span class="rounded-full bg-{{ $color }}-100 px-1.5 text-[10px] font-medium text-{{ $color }}-700">{{ count($$field) }}</span>
                        </div>
                        <input type="file" wire:model="{{ $field }}" multiple accept="image/*"
                            class="block w-full text-xs file:mr-2 file:rounded file:border-0 file:bg-{{ $color }}-100 file:px-3 file:py-1.5 file:text-{{ $color }}-700 hover:file:bg-{{ $color }}-200">

                        @if (count($$field) > 0)
                            <div class="mt-3 grid grid-cols-3 gap-1.5">
                                @foreach ($$field as $idx => $photo)
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
            <p class="mt-3 text-xs text-zinc-500">💡 Tip: las fotos "Antes" se usan para comparar contra "Después" en la galería del paciente.</p>
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
                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <flux:input type="number" step="0.1" wire:model="m_weight_kg" label="Peso (kg)" />
                    <flux:input type="number" step="0.1" wire:model="m_waist_cm" label="Cintura (cm)" />
                    <flux:input type="number" step="0.1" wire:model="m_hip_cm" label="Cadera (cm)" />
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-sm font-medium">Presión arterial (opcional)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="m_bp_sys" placeholder="Sistólica" class="w-full rounded-lg border border-zinc-300 px-3 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                            <span class="text-zinc-400">/</span>
                            <input type="number" wire:model="m_bp_dia" placeholder="Diastólica" class="w-full rounded-lg border border-zinc-300 px-3 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-xs text-zinc-500">💡 Útil para protocolos de drenaje linfático o tratamientos corporales — la medición se guardará en la <strong>ficha clínica del paciente</strong>.</p>
            @endif
        </div>

        {{-- Acciones --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-sm text-zinc-500">
                Al confirmar: la cita pasará a <strong class="text-emerald-600">Atendida</strong>, se registrará la sesión clínica y se subirán las fotos.
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('admin.estetic.patients.show', $appointment->estetic_profile_id) }}" variant="ghost" wire:navigate>Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="check">Registrar sesión</flux:button>
            </div>
        </div>
    </form>
</div>
