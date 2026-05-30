<div class="mx-auto max-w-lg py-8">
    <x-card>
        <x-slot name="header">
            <h2 class="text-xl font-bold">Nuevo Plan</h2>
        </x-slot>

        <form wire:submit.prevent="save" class="space-y-5">
            <x-input.group label="Nombre del plan" for="name" :error="$errors->first('name')">
                <x-input.text id="name" wire:model.defer="name" required />
            </x-input.group>

            <x-input.group label="Descripción" for="description" :error="$errors->first('description')">
                <x-input.text id="description" wire:model.defer="description" />
            </x-input.group>

            <x-input.group label="Precio ($)" for="price" :error="$errors->first('price')">
                <x-input.text id="price" wire:model.defer="price" type="number" step="0.01" min="0" required />
            </x-input.group>

            <x-input.group label="Duración (días)" for="duration_days" :error="$errors->first('duration_days')">
                <x-input.text id="duration_days" wire:model.defer="duration_days" type="number" min="1" required />
            </x-input.group>

            <div class="flex justify-end">
                <x-button.primary>
                    Guardar Plan
                </x-button.primary>
            </div>
        </form>
    </x-card>
</div>
