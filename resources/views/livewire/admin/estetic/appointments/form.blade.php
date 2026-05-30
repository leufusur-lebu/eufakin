<div class="mx-auto max-w-4xl p-6 space-y-6">
    <div class="flex items-center gap-2">
        <flux:button href="{{ route('admin.estetic.appointments.index') }}" size="sm" variant="subtle" icon="arrow-left" wire:navigate>Volver</flux:button>
        <div>
            <flux:heading size="xl">{{ $appointment ? 'Editar cita estética' : 'Nueva cita estética' }}</flux:heading>
            <flux:text class="text-zinc-500">{{ $appointment ? 'Actualiza los datos de la cita' : 'Agenda una nueva cita' }}</flux:text>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- Paciente / tratamiento / profesional --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                    <flux:icon.user class="size-4" />
                </div>
                <flux:heading size="lg">Paciente y servicio</flux:heading>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:select label="Paciente" wire:model.live="estetic_profile_id" required class="md:col-span-2">
                    <flux:select.option value="">Seleccionar paciente...</flux:select.option>
                    @foreach ($profiles as $p)
                        <flux:select.option value="{{ $p->id }}">{{ $p->person?->full_name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select label="Tratamiento" wire:model="tratamiento_id">
                    <flux:select.option value="">Sin tratamiento asociado</flux:select.option>
                    @foreach ($treatments as $t)
                        <flux:select.option value="{{ $t->id }}">{{ $t->zona_tratada }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select label="Profesional" wire:model="professional_id">
                    <flux:select.option value="">Sin asignar</flux:select.option>
                    @foreach ($professionals as $pro)
                        <flux:select.option value="{{ $pro->id }}">{{ $pro->full_name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        {{-- Fecha y hora --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                    <flux:icon.calendar class="size-4" />
                </div>
                <flux:heading size="lg">Fecha y hora</flux:heading>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input type="date" label="Fecha" wire:model="fecha" required />
                <flux:input type="time" label="Hora inicio" wire:model="hora_inicio" required />
                <flux:input type="number" min="5" step="5" label="Duración (min)" wire:model="duracion_min" required />
            </div>
            <div class="mt-3 rounded-lg bg-zinc-50 p-3 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                <flux:icon.clock class="inline size-3.5" /> Finaliza aprox. a las
                <strong class="text-pink-600">
                    @php
                        try { echo \Carbon\Carbon::parse($fecha.' '.$hora_inicio)->addMinutes((int) $duracion_min)->format('H:i'); }
                        catch (\Throwable $e) { echo '—'; }
                    @endphp
                </strong>
            </div>
        </div>

        {{-- Estado y detalles --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <flux:icon.clipboard-document-check class="size-4" />
                </div>
                <flux:heading size="lg">Detalles</flux:heading>
            </div>
            <div class="space-y-4">
                <flux:select label="Estado" wire:model="estado">
                    <flux:select.option value="pendiente">Pendiente</flux:select.option>
                    <flux:select.option value="confirmado">Confirmado</flux:select.option>
                    <flux:select.option value="atendido">Atendido</flux:select.option>
                    <flux:select.option value="cancelado">Cancelado</flux:select.option>
                    <flux:select.option value="ausente">Ausente</flux:select.option>
                </flux:select>
                <flux:input label="Motivo" wire:model="motivo" placeholder="Ej. Consulta, sesión, control..." />
                <flux:textarea label="Notas" wire:model="notas" rows="3" placeholder="Observaciones, indicaciones..." />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button href="{{ route('admin.estetic.appointments.index') }}" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" icon="check">{{ $appointment ? 'Actualizar' : 'Crear cita' }}</flux:button>
        </div>
    </form>
</div>
