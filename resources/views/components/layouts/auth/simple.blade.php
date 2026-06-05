<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            :root {
                --euf-lime: #EE5A6A; --euf-green: #DE6B8E;
                --euf-cyan: #D3265A; --euf-teal: #C02A6A; --euf-navy: #6B3051;
            }
            .euf-gradient { background: linear-gradient(150deg, var(--euf-lime) 0%, var(--euf-teal) 55%, var(--euf-cyan) 100%); }
            .euf-blob { position:absolute; border-radius:9999px; filter:blur(70px); opacity:.4; }
        </style>
    </head>
    <body class="min-h-screen bg-slate-50 antialiased">
        <div class="grid min-h-svh lg:grid-cols-2">

            {{-- Panel de branding (desktop) --}}
            <div class="euf-gradient relative hidden flex-col justify-between overflow-hidden p-12 lg:flex">
                <div class="euf-blob -left-10 top-10 h-64 w-64 bg-white/30"></div>
                <div class="euf-blob bottom-10 right-0 h-72 w-72 bg-white/20"></div>

                <a href="{{ route('home') }}" class="relative z-10 inline-flex w-fit rounded-2xl bg-white p-4 shadow-lg">
                    <img src="{{ asset('images/eufakin-logo.svg') }}" alt="EUFAKIN" class="h-20 object-contain">
                </a>

                <div class="relative z-10 text-white">
                    <h2 class="font-display text-3xl font-extrabold leading-tight" style="font-family: 'Poppins', sans-serif;">
                        Sistema de gestión<br>EUFAKIN
                    </h2>
                    <p class="mt-3 max-w-md text-white/90">
                        Administra kinesiología, gimnasio y estética desde un solo lugar:
                        pacientes, agenda, tratamientos y caja.
                    </p>
                    <div class="mt-6 flex gap-6 text-sm">
                        <div class="flex items-center gap-2"><span class="size-2 rounded-full bg-white"></span> Kinesiología</div>
                        <div class="flex items-center gap-2"><span class="size-2 rounded-full bg-white"></span> Gimnasio</div>
                        <div class="flex items-center gap-2"><span class="size-2 rounded-full bg-white"></span> Estética</div>
                    </div>
                </div>

                <div class="relative z-10 text-xs text-white/70">
                    © {{ date('Y') }} EUFAKIN · Centro Kinésico
                </div>
            </div>

            {{-- Panel del formulario --}}
            <div class="flex flex-col items-center justify-center gap-6 p-6 md:p-10">
                <div class="flex w-full max-w-sm flex-col gap-6">
                    {{-- Logo (visible en móvil) --}}
                    <a href="{{ route('home') }}" class="mx-auto flex lg:hidden" wire:navigate>
                        <img src="{{ asset('images/eufakin-logo.svg') }}" alt="EUFAKIN" class="h-20 object-contain">
                    </a>

                    <div class="rounded-3xl border border-slate-100 bg-white p-8 shadow-xl">
                        {{ $slot }}
                    </div>

                    <a href="{{ route('home') }}" class="text-center text-sm text-slate-500 transition hover:text-slate-700" wire:navigate>
                        ← Volver al sitio
                    </a>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
