<div class="relative">
    @if(isset($icon))
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
            {!! $icon !!}
        </span>
    @endif
    <input
        type="{{ $type ?? 'text' }}"
        id="{{ $attributes['id'] ?? '' }}"
        {{ $attributes->merge(['class' => 'border px-3 py-2 rounded w-full ' . (isset($icon) ? 'pl-10' : '')]) }}
    >
</div>
