<div class="p-6 max-w-3xl">
    <flux:heading size="xl">{{ $treatment ? 'Editar' : 'Nuevo' }} tratamiento estética</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select label="Paciente" wire:model="estetic_profile_id" class="md:col-span-2">
                <flux:select.option value="">Seleccionar...</flux:select.option>
                @foreach ($profiles as $p)
                    <flux:select.option value="{{ $p->id }}">{{ $p->person?->full_name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select label="Tipo de tratamiento" wire:model.live="tipo_tratamiento_id">
                <flux:select.option value="">—</flux:select.option>
                @foreach ($tipos as $tipo)
                    <flux:select.option value="{{ $tipo->id }}">{{ $tipo->nombre }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select label="Profesional" wire:model="professional_id">
                <flux:select.option value="">—</flux:select.option>
                @foreach ($professionals as $pro)
                    <flux:select.option value="{{ $pro->id }}">{{ $pro->full_name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input label="Zona tratada" wire:model="zona_tratada" />
            <flux:select label="Estado" wire:model="estado">
                <flux:select.option value="activo">Activo</flux:select.option>
                <flux:select.option value="finalizado">Finalizado</flux:select.option>
                <flux:select.option value="suspendido">Suspendido</flux:select.option>
                <flux:select.option value="cancelado">Cancelado</flux:select.option>
            </flux:select>
            <flux:input label="Descripción del plan" wire:model="descripcion_plan" class="md:col-span-2" />
            <flux:input type="date" label="Inicio" wire:model="fecha_inicio" />
            <flux:input type="date" label="Fin" wire:model="fecha_fin" />
            <flux:input type="number" label="Sesiones totales" wire:model.live="sesiones_totales" />
            <flux:input type="number" label="Sesiones realizadas" wire:model="sesiones_realizadas" />
            <flux:input type="number" step="0.01" label="Costo por sesión" wire:model.live="costo_sesion" />
            <flux:input type="number" step="0.01" label="Costo total" wire:model="costo_total" />
            <flux:textarea label="Observaciones" wire:model="observaciones" class="md:col-span-2" />
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">Guardar</flux:button>
            <flux:button href="{{ route('admin.estetic.treatments.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>
</div>
