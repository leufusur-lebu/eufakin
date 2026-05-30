<div class="p-6 max-w-3xl">
    <flux:heading size="xl">{{ $appointment ? 'Editar' : 'Nueva' }} cita kinesiología</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select label="Paciente" wire:model.live="kine_profile_id">
                <flux:select.option value="">Seleccionar...</flux:select.option>
                @foreach ($profiles as $p)
                    <flux:select.option value="{{ $p->id }}">{{ $p->person?->full_name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select label="Tratamiento" wire:model="tratamiento_id">
                <flux:select.option value="">—</flux:select.option>
                @foreach ($treatments as $t)
                    <flux:select.option value="{{ $t->id }}">{{ $t->diagnostico }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select label="Profesional" wire:model="professional_id">
                <flux:select.option value="">—</flux:select.option>
                @foreach ($professionals as $pro)
                    <flux:select.option value="{{ $pro->id }}">{{ $pro->full_name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select label="Estado" wire:model="estado">
                <flux:select.option value="pendiente">Pendiente</flux:select.option>
                <flux:select.option value="confirmado">Confirmado</flux:select.option>
                <flux:select.option value="atendido">Atendido</flux:select.option>
                <flux:select.option value="cancelado">Cancelado</flux:select.option>
                <flux:select.option value="ausente">Ausente</flux:select.option>
            </flux:select>
            <flux:input type="date" label="Fecha" wire:model="fecha" />
            <flux:input type="time" label="Hora inicio" wire:model="hora_inicio" />
            <flux:input type="number" label="Duración (min)" wire:model="duracion_min" />
            <flux:input label="Motivo" wire:model="motivo" />
            <flux:textarea label="Notas" wire:model="notas" class="md:col-span-2" />
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">Guardar</flux:button>
            <flux:button href="{{ route('admin.kine.appointments.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>
</div>
