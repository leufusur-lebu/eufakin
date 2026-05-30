<div class="p-6 max-w-3xl">
    <flux:heading size="xl">Editar persona</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:input label="RUT" wire:model="rut" />
            <flux:select label="Género" wire:model="gender">
                <flux:select.option value="">—</flux:select.option>
                <flux:select.option value="M">Masculino</flux:select.option>
                <flux:select.option value="F">Femenino</flux:select.option>
                <flux:select.option value="O">Otro</flux:select.option>
            </flux:select>
            <flux:input label="Nombre" wire:model="first_name" />
            <flux:input label="Apellido" wire:model="last_name" />
            <flux:input label="Apodo" wire:model="nickname" />
            <flux:input type="date" label="Fecha de nacimiento" wire:model="birth_date" />
            <flux:input label="Teléfono" wire:model="phone" />
            <flux:input type="email" label="Email" wire:model="email" />
            <flux:input label="Dirección" wire:model="address" class="md:col-span-2" />
        </div>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">Guardar</flux:button>
            <flux:button href="{{ route('admin.people.show', $person) }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>
</div>
