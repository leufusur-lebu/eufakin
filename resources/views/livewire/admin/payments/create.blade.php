<div class="p-6 max-w-2xl">
    <flux:heading size="xl">Nuevo pago GYM</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select label="Persona" wire:model.live="person_id" class="md:col-span-2">
                <flux:select.option value="">Seleccionar...</flux:select.option>
                @foreach ($people as $p)
                    <flux:select.option value="{{ $p->id }}">{{ $p->full_name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select label="Suscripción" wire:model.live="subscription_id" class="md:col-span-2">
                <flux:select.option value="">—</flux:select.option>
                @foreach ($subscriptions as $s)
                    <flux:select.option value="{{ $s->id }}">{{ $s->plan?->name }} ({{ $s->start_date?->format('d/m/Y') }})</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="date" label="Fecha" wire:model="payment_date" />
            <flux:input type="number" step="0.01" label="Monto" wire:model="amount" />
            <flux:select label="Método" wire:model="payment_type">
                <flux:select.option value="efectivo">Efectivo</flux:select.option>
                <flux:select.option value="transferencia">Transferencia</flux:select.option>
                <flux:select.option value="debito">Débito</flux:select.option>
                <flux:select.option value="credito">Crédito</flux:select.option>
                <flux:select.option value="mercadopago">MercadoPago</flux:select.option>
                <flux:select.option value="otro">Otro</flux:select.option>
            </flux:select>
            <flux:select label="Estado" wire:model="status">
                <flux:select.option value="pagado">Pagado</flux:select.option>
                <flux:select.option value="pendiente">Pendiente</flux:select.option>
                <flux:select.option value="anulado">Anulado</flux:select.option>
            </flux:select>
            <flux:textarea label="Notas" wire:model="notes" class="md:col-span-2" />
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">Guardar</flux:button>
            <flux:button href="{{ route('admin.payments.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>
</div>
