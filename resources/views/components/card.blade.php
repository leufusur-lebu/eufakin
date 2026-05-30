<div class="bg-white shadow rounded-xl p-6 mb-8 {{ $attributes['class'] ?? '' }}">
    @isset($header)
        <div class="mb-4 text-lg font-bold">{{ $header }}</div>
    @endisset
    {{ $slot }}
</div>
