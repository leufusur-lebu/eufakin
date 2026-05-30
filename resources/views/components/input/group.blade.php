<div class="mb-4">
    <label for="{{ $for }}" class="block font-semibold mb-1">{{ $label }}</label>
    {{ $slot }}
    @if ($error)
        <div class="text-red-600 text-xs mt-1">{{ $error }}</div>
    @endif
</div>
