@props([])

{{-- Logo oficial EUFAKIN (isotipo + wordmark) --}}
<img src="{{ asset('images/eufakin-logo.svg') }}" alt="EUFAKIN · Centro Kinésico"
     {{ $attributes->merge(['class' => 'object-contain']) }}>
