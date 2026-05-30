<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Planes</flux:heading>
            <flux:text class="text-zinc-500">Catálogo de planes del gimnasio</flux:text>
        </div>
        <flux:button href="{{ route('admin.plans.create') }}" variant="primary" icon="plus" wire:navigate>Nuevo plan</flux:button>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Planes</span>
                <flux:icon.clipboard-document-list class="size-5 text-indigo-500" />
            </div>
            <div class="mt-2 text-3xl font-bold">{{ $totalPlanes }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Suscripciones activas</span>
                <flux:icon.users class="size-5 text-emerald-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $totalActivas }}</div>
        </div>
        
    </div>

    {{-- Plan cards --}}
    @if ($plans->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <flux:icon.clipboard-document-list class="mx-auto size-10 text-zinc-300" />
            <p class="mt-2 text-zinc-500">Aún no hay planes registrados.</p>
            <flux:button class="mt-4" href="{{ route('admin.plans.create') }}" variant="primary" wire:navigate>Crear el primero</flux:button>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($plans as $plan)
                @php
                    $palettes = [
                        ['from' => 'from-sky-500', 'to' => 'to-indigo-600', 'text' => 'text-sky-600', 'ring' => 'ring-sky-100'],
                        ['from' => 'from-emerald-500', 'to' => 'to-teal-600', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
                        ['from' => 'from-amber-500', 'to' => 'to-orange-600', 'text' => 'text-amber-600', 'ring' => 'ring-amber-100'],
                        ['from' => 'from-pink-500', 'to' => 'to-rose-600', 'text' => 'text-pink-600', 'ring' => 'ring-pink-100'],
                    ];
                    $pal = $palettes[$loop->index % count($palettes)];
                @endphp
                <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="h-2 bg-gradient-to-r {{ $pal['from'] }} {{ $pal['to'] }}"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">{{ $plan->name }}</h3>
                                @if ($plan->description)
                                    <p class="mt-1 line-clamp-2 text-sm text-zinc-500">{{ $plan->description }}</p>
                                @endif
                            </div>
                            <flux:icon.sparkles class="size-5 {{ $pal['text'] }}" />
                        </div>

                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-3xl font-bold">${{ number_format($plan->price, 0, ',', '.') }}</span>
                            <span class="text-sm text-zinc-500">/ {{ $plan->duration_days }}d</span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2 border-t border-zinc-100 pt-3 text-sm dark:border-zinc-800">
                            <div>
                                <div class="text-xs text-zinc-500">Activas</div>
                                <div class="font-semibold {{ $pal['text'] }}">{{ $plan->active_count }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Histórico</div>
                                <div class="font-semibold">{{ $plan->subscriptions_count }}</div>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <flux:button href="{{ route('admin.plans.show', $plan) }}" size="sm" variant="subtle" icon="eye" wire:navigate class="flex-1">Ver</flux:button>
                            <flux:button href="{{ route('admin.plans.edit', $plan) }}" size="sm" icon="pencil-square" wire:navigate class="flex-1">Editar</flux:button>
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="deletePlan({{ $plan->id }})" wire:confirm="¿Eliminar el plan {{ $plan->name }}?" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
