<div>
    <input type="file" {{ $attributes->merge(['class' => 'border px-3 py-2 rounded w-full']) }}>
    @if (isset($preview) && $preview)
        <div class="mt-2">
            <img src="{{ $preview }}" class="w-20 h-20 object-cover rounded-full border" />
        </div>
    @endif
</div>
