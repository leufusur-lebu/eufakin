<div class="mx-auto max-w-lg py-8">
    <x-card>
        <x-slot name="header">
            <h2 class="text-xl font-bold">Detalle del Plan</h2>
        </x-slot>

        <div class="space-y-4">
            <div>
                <strong>Nombre del plan:</strong>
                <span class="ml-2">{{ $plan->name }}</span>
            </div>
            <div>
                <strong>Descripción:</strong>
                <span class="ml-2">{{ $plan->description ?? '—' }}</span>
            </div>
            <div>
                <strong>Precio:</strong>
                <span class="ml-2">${{ number_format($plan->price, 0, ',', '.') }}</span>
            </div>
            <div>
                <strong>Duración:</strong>
                <span class="ml-2">{{ $plan->duration_days }} días</span>
            </div>
        </div>

        <div class="flex justify-end mt-6 gap-2">
            <a href="{{ route('admin.plans.edit', $plan) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded font-semibold">
                Editar
            </a>
            <a href="{{ route('admin.plans.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-semibold">
                Volver al listado
            </a>
        </div>
    </x-card>
</div>
