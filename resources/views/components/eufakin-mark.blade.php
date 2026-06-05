@props([])

@php
    // Cache-busting: la URL cambia cuando el archivo cambia (rompe caché de navegador/CDN)
    $logoPath = public_path('images/eufakin-logo.svg');
    $logoVer  = file_exists($logoPath) ? filemtime($logoPath) : '1';
@endphp

{{-- Logo oficial EUFAKIN (silueta + wordmark) --}}
<img src="{{ asset('images/eufakin-logo.svg') }}?v={{ $logoVer }}" alt="EUFAKIN · Centro Kinésico"
     {{ $attributes->merge(['class' => 'object-contain']) }}>
