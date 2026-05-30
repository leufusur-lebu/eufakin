@props(['person'])

@php
    $cp = $person->clinicalProfile;
    $activeEvents = $person->clinicalEvents()->whereIn('status', ['activo','en_tratamiento'])->count();
    $hasAny = $cp && (
        $cp->allergies || $cp->chronic_diseases || $cp->is_pregnant || $cp->blood_type
    );
@endphp

@if ($hasAny || $activeEvents > 0)
    <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-3 dark:border-rose-900 dark:bg-rose-950/20">
        <div class="flex flex-wrap items-center gap-2">
            <span class="flex items-center gap-1 text-xs font-bold uppercase tracking-wide text-rose-700 dark:text-rose-300">
                <flux:icon.heart class="size-4" /> Información clínica
            </span>

            @if ($cp?->allergies)
                <span class="inline-flex items-center gap-1 rounded-full bg-rose-200 px-2 py-0.5 text-xs font-medium text-rose-800 dark:bg-rose-900/60 dark:text-rose-200" title="{{ $cp->allergies }}">
                    <flux:icon.shield-exclamation class="size-3.5" /> Alergias
                </span>
            @endif

            @if ($cp?->chronic_diseases)
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-300" title="{{ $cp->chronic_diseases }}">
                    <flux:icon.exclamation-triangle class="size-3.5" /> Crónicas
                </span>
            @endif

            @if ($cp?->is_pregnant)
                <span class="inline-flex items-center gap-1 rounded-full bg-pink-100 px-2 py-0.5 text-xs font-medium text-pink-800 dark:bg-pink-900/40 dark:text-pink-300">
                    <flux:icon.heart class="size-3.5" /> Embarazo {{ $cp->pregnancy_weeks ? '· '.$cp->pregnancy_weeks.' sem' : '' }}
                </span>
            @endif

            @if ($cp?->blood_type)
                <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <flux:icon.beaker class="size-3.5" /> {{ $cp->blood_type }}
                </span>
            @endif

            @if ($activeEvents > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900/40 dark:text-orange-300">
                    <flux:icon.exclamation-circle class="size-3.5" /> {{ $activeEvents }} {{ Str::plural('evento activo', $activeEvents) }}
                </span>
            @endif

            <a href="{{ route('admin.people.clinical', $person) }}" wire:navigate
                class="ml-auto text-xs font-medium text-rose-700 hover:underline dark:text-rose-300">
                Ver ficha clínica →
            </a>
        </div>

        @if ($cp?->allergies || $cp?->chronic_medications)
            <div class="mt-2 grid gap-1 text-xs md:grid-cols-2">
                @if ($cp->allergies)
                    <div class="rounded bg-white px-2 py-1 text-rose-800 dark:bg-zinc-900 dark:text-rose-300">
                        <strong>Alergias:</strong> {{ $cp->allergies }}
                    </div>
                @endif
                @if ($cp->chronic_medications)
                    <div class="rounded bg-white px-2 py-1 text-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                        <strong>Medicación:</strong> {{ $cp->chronic_medications }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
