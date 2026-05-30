<div class="mx-auto max-w-4xl p-6 space-y-6">
    <div class="flex items-center gap-2">
        <flux:button href="{{ route('admin.kine.treatments.index') }}" size="sm" variant="subtle" icon="arrow-left" wire:navigate>Volver</flux:button>
        <div>
            <flux:heading size="xl">{{ $treatment ? 'Editar tratamiento' : 'Nuevo tratamiento kine' }}</flux:heading>
            <flux:text class="text-zinc-500">{{ $treatment ? 'Actualiza los datos del tratamiento' : 'Registra un nuevo plan de rehabilitación' }}</flux:text>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- Paciente y profesional --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                    <flux:icon.user class="size-4" />
                </div>
                <flux:heading size="lg">Paciente y profesional</flux:heading>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:select label="Paciente" wire:model="kine_profile_id" required class="md:col-span-2">
                    <flux:select.option value="">Seleccionar paciente...</flux:select.option>
                    @foreach ($profiles as $p)
                        <flux:select.option value="{{ $p->id }}">{{ $p->person?->full_name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select label="Profesional" wire:model="professional_id">
                    <flux:select.option value="">Sin asignar</flux:select.option>
                    @foreach ($professionals as $pro)
                        <flux:select.option value="{{ $pro->id }}">{{ $pro->full_name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select label="Estado" wire:model="estado">
                    <flux:select.option value="activo">Activo</flux:select.option>
                    <flux:select.option value="finalizado">Finalizado</flux:select.option>
                    <flux:select.option value="suspendido">Suspendido</flux:select.option>
                    <flux:select.option value="cancelado">Cancelado</flux:select.option>
                </flux:select>
            </div>
        </div>

        {{-- Diagnóstico y plan --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                    <flux:icon.clipboard-document-check class="size-4" />
                </div>
                <flux:heading size="lg">Diagnóstico y plan</flux:heading>
            </div>
            <div class="space-y-4">
                <flux:input label="Diagnóstico" wire:model="diagnostico" placeholder="Ej. Lumbalgia crónica" required />
                <flux:textarea label="Plan de tratamiento" wire:model="plan" rows="3" placeholder="Objetivos, técnicas, frecuencia..." />
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input type="date" label="Fecha de inicio" wire:model="fecha_inicio" required />
                    <flux:input type="date" label="Fecha de término (estimada)" wire:model="fecha_fin" />
                </div>
            </div>
        </div>

        {{-- Sesiones y costos --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-4 flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <flux:icon.banknotes class="size-4" />
                </div>
                <flux:heading size="lg">Sesiones y costos</flux:heading>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="number" min="1" label="Sesiones totales" wire:model.live="sesiones_totales" />
                <flux:input type="number" min="0" label="Sesiones realizadas" wire:model="sesiones_realizadas" />
                <flux:input type="number" step="0.01" min="0" label="Costo por sesión" wire:model.live="costo_sesion" />
                <flux:input type="number" step="0.01" min="0" label="Costo total" wire:model="costo_total" />
            </div>

            {{-- Progreso visual --}}
            @php
                $totales = max(1, (int) $sesiones_totales);
                $pct = min(100, round(((int) $sesiones_realizadas / $totales) * 100));
            @endphp
            <div class="mt-4 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-medium text-zinc-600 dark:text-zinc-300">Progreso</span>
                    <span class="text-zinc-500">{{ (int) $sesiones_realizadas }} / {{ $totales }} sesiones ({{ $pct }}%)</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded bg-zinc-200 dark:bg-zinc-700">
                    <div class="h-full bg-gradient-to-r from-sky-500 to-indigo-500" style="width: {{ $pct }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-zinc-500">
                    <span>Costo calculado: <strong class="text-emerald-600">${{ number_format($sesiones_totales * $costo_sesion, 0, ',', '.') }}</strong></span>
                    @if ($costo_total != $sesiones_totales * $costo_sesion)
                        <span class="text-amber-600">⚠ Costo total editado manualmente</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Observaciones --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:textarea label="Observaciones" wire:model="observaciones" rows="3" placeholder="Notas adicionales, restricciones, contraindicaciones..." />
        </div>

        <div class="flex justify-end gap-2">
            <flux:button href="{{ route('admin.kine.treatments.index') }}" variant="subtle" wire:navigate>Cancelar</flux:button>
            <flux:button type="submit" variant="primary" icon="check">{{ $treatment ? 'Actualizar' : 'Crear tratamiento' }}</flux:button>
        </div>
    </form>
</div>
