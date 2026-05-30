@props([
    'phone' => null,
    'template' => null,
    'vars' => [],
    'size' => 'sm',
    'label' => null,
    'icon' => 'chat-bubble-left-right',
])

@php
    $link = \App\Support\WhatsApp::linkFromTemplate($phone, $template, $vars);
@endphp

@if ($link)
    <flux:button
        as="a"
        href="{{ $link }}"
        target="_blank"
        size="{{ $size }}"
        variant="ghost"
        icon="{{ $icon }}"
        class="!text-emerald-600 hover:!bg-emerald-50 dark:hover:!bg-emerald-950/30"
        title="Enviar WhatsApp"
    >
        {{ $label ?? 'WhatsApp' }}
    </flux:button>
@elseif ($phone === null || $phone === '')
    <flux:button size="{{ $size }}" variant="ghost" icon="{{ $icon }}" disabled title="Sin teléfono">
        {{ $label ?? 'WhatsApp' }}
    </flux:button>
@endif
