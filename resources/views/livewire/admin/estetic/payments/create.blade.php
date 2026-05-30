<div class="p-6 max-w-2xl">
    <flux:heading size="xl">Nuevo pago estética</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select label="Paciente" wire:model.live="estetic_profile_id" class="md:col-span-2">
                <flux:select.option value="">Seleccionar...</flux:select.option>
                @foreach ($profiles as $p)
                    <flux:select.option value="{{ $p->id }}">{{ $p->person?->full_name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select label="Tratamiento" wire:model="tratamiento_id" class="md:col-span-2">
                <flux:select.option value="">—</flux:select.option>
                @foreach ($treatments as $t)
                    <flux:select.option value="{{ $t->id }}">{{ $t->zona_tratada }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="date" label="Fecha" wire:model="fecha" />
            <flux:input type="number" step="0.01" label="Monto" wire:model="monto" />
            <flux:select label="Método" wire:model="metodo">
                <flux:select.option value="efectivo">Efectivo</flux:select.option>
                <flux:select.option value="transferencia">Transferencia</flux:select.option>
                <flux:select.option value="debito">Débito</flux:select.option>
                <flux:select.option value="credito">Crédito</flux:select.option>
                <flux:select.option value="mercadopago">MercadoPago</flux:select.option>
                <flux:select.option value="otro">Otro</flux:select.option>
            </flux:select>
            <flux:select label="Estado" wire:model="estado">
                <flux:select.option value="pagado">Pagado</flux:select.option>
                <flux:select.option value="pendiente">Pendiente</flux:select.option>
                <flux:select.option value="anulado">Anulado</flux:select.option>
            </flux:select>
            <flux:input label="Comprobante" wire:model="comprobante" />
            <flux:textarea label="Observaciones" wire:model="observaciones" class="md:col-span-2" />
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">Guardar</flux:button>
            <flux:button href="{{ route('admin.estetic.payments.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>
</div>
