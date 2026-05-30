<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $person->full_name }}</flux:heading>
            <flux:text class="text-zinc-500">RUT {{ \App\Support\RutHelper::format($person->rut) }} · {{ $person->email }} · {{ $person->phone }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button href="{{ route('admin.people.clinical', $person) }}" icon="heart" variant="primary" wire:navigate>Ficha clínica</flux:button>
            <flux:button href="{{ route('admin.people.edit', $person) }}" icon="pencil" wire:navigate>Editar</flux:button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border p-4 dark:border-zinc-700">
            <flux:heading size="md">GYM</flux:heading>
            @if ($person->gymProfile)
                <flux:text class="mt-2 text-sm">Registrado: {{ $person->gymProfile->registered_at?->format('d/m/Y') }}</flux:text>
                <flux:text class="text-sm">Activo: {{ $person->gymProfile->active ? 'Sí' : 'No' }}</flux:text>
            @else
                <flux:text class="mt-2 text-sm text-zinc-500">Sin perfil GYM</flux:text>
            @endif
        </div>

        <div class="rounded-lg border p-4 dark:border-zinc-700">
            <flux:heading size="md">Kinesiología</flux:heading>
            @if ($person->kineProfile)
                <flux:text class="mt-2 text-sm">Previsión: {{ $person->kineProfile->health_insurance ?? '—' }}</flux:text>
                <flux:text class="text-sm">Tratamientos: {{ $person->kineProfile->treatments->count() }}</flux:text>
                <flux:text class="text-sm">Citas: {{ $person->kineProfile->appointments->count() }}</flux:text>
            @else
                <flux:text class="mt-2 text-sm text-zinc-500">Sin perfil Kine</flux:text>
            @endif
        </div>

        <div class="rounded-lg border p-4 dark:border-zinc-700">
            <flux:heading size="md">Estética</flux:heading>
            @if ($person->esteticProfile)
                <flux:text class="mt-2 text-sm">Tipo de piel: {{ $person->esteticProfile->skin_type ?? '—' }}</flux:text>
                <flux:text class="text-sm">Tratamientos: {{ $person->esteticProfile->treatments->count() }}</flux:text>
                <flux:text class="text-sm">Citas: {{ $person->esteticProfile->appointments->count() }}</flux:text>
            @else
                <flux:text class="mt-2 text-sm text-zinc-500">Sin perfil Estética</flux:text>
            @endif
        </div>
    </div>
</div>
