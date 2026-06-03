@php
    $groups = [
        'INICIO' => [
            [
                'name' => 'Dashboard',
                'icon' => 'home',
                'url' => route('dashboard'),
                'current' => request()->routeIs('dashboard'),
            ],
            [
                'name' => 'Personas',
                'icon' => 'user-group',
                'url' => route('admin.people.index'),
                'current' => request()->routeIs('admin.people.*'),
            ],
            [
                'name' => 'Inscribir persona',
                'icon' => 'user-plus',
                'url' => route('admin.admission.create'),
                'current' => request()->routeIs('admin.admission.*'),
            ],
            [
                'name' => 'Agenda',
                'icon' => 'calendar-days',
                'url' => route('admin.agenda.index'),
                'current' => request()->routeIs('admin.agenda.*'),
            ],
            [
                'name' => 'Caja del día',
                'icon' => 'banknotes',
                'url' => route('admin.cash.daily'),
                'current' => request()->routeIs('admin.cash.*'),
            ],
        ],
        'GYM' => [
            [
                'name' => 'Planes',
                'icon' => 'clipboard-document-list',
                'url' => route('admin.plans.index'),
                'current' => request()->routeIs('admin.plans.*'),
            ],
            [
                'name' => 'Suscripciones',
                'icon' => 'credit-card',
                'url' => route('admin.subscriptions.index'),
                'current' => request()->routeIs('admin.subscriptions.*'),
            ],
            [
                'name' => 'Pagos',
                'icon' => 'banknotes',
                'url' => route('admin.payments.index'),
                'current' => request()->routeIs('admin.payments.*'),
            ],
        ],
        'KINESIOLOGÍA' => [
            [
                'name' => 'Pacientes',
                'icon' => 'user-group',
                'url' => route('admin.kine.patients.index'),
                'current' => request()->routeIs('admin.kine.patients.*') || request()->routeIs('admin.kine.treatments.*'),
            ],
            [
                'name' => 'Agenda',
                'icon' => 'calendar',
                'url' => route('admin.kine.appointments.index'),
                'current' => request()->routeIs('admin.kine.appointments.*'),
            ],
            [
                'name' => 'Catálogo',
                'icon' => 'sparkles',
                'url' => route('admin.kine.tipos-tratamientos.index'),
                'current' => request()->routeIs('admin.kine.tipos-tratamientos.*'),
            ],
            [
                'name' => 'Caja',
                'icon' => 'banknotes',
                'url' => route('admin.kine.payments.index'),
                'current' => request()->routeIs('admin.kine.payments.*'),
            ],
        ],
        'CONFIGURACIÓN' => [
            [
                'name' => 'Plantillas WhatsApp',
                'icon' => 'chat-bubble-left-right',
                'url' => route('admin.whatsapp.templates'),
                'current' => request()->routeIs('admin.whatsapp.*'),
            ],
        ],

        'REPORTES' => [
            [
                'name' => 'Panel de reportes',
                'icon' => 'chart-bar',
                'url' => route('admin.reports.index'),
                'current' => request()->routeIs('admin.reports.index'),
            ],
            [
                'name' => 'Pagos',
                'icon' => 'banknotes',
                'url' => route('admin.reports.payments'),
                'current' => request()->routeIs('admin.reports.payments'),
            ],
            [
                'name' => 'Asistencias',
                'icon' => 'calendar-days',
                'url' => route('admin.reports.attendance'),
                'current' => request()->routeIs('admin.reports.attendance'),
            ],
        ],
        'ESTÉTICA' => [
            [
                'name' => 'Pacientes',
                'icon' => 'user-group',
                'url' => route('admin.estetic.patients.index'),
                'current' => request()->routeIs('admin.estetic.patients.*') || request()->routeIs('admin.estetic.treatments.*'),
            ],
            [
                'name' => 'Agenda',
                'icon' => 'calendar',
                'url' => route('admin.estetic.appointments.index'),
                'current' => request()->routeIs('admin.estetic.appointments.*'),
            ],
            [
                'name' => 'Catálogo',
                'icon' => 'sparkles',
                'url' => route('admin.estetic.tipos-tratamientos.index'),
                'current' => request()->routeIs('admin.estetic.tipos-tratamientos.*'),
            ],
            [
                'name' => 'Caja',
                'icon' => 'banknotes',
                'url' => route('admin.estetic.payments.index'),
                'current' => request()->routeIs('admin.estetic.payments.*'),
            ],
        ],
    ];

    // Grupo solo visible para administradores
    if (auth()->user()?->isAdmin()) {
        $groups['ADMINISTRACIÓN'] = [
            [
                'name' => 'Usuarios',
                'icon' => 'user-group',
                'url' => route('admin.users.index'),
                'current' => request()->routeIs('admin.users.*'),
            ],
        ];
    }

@endphp



<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @livewireStyles
    </head>

   
    
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                @foreach ($groups as $group => $links)

                    <flux:navlist.group :heading="$group" class="grid">
                        @foreach ($links as $link)
                            <flux:navlist.item :icon="$link['icon']" :href="$link['url']" :current="$link['current']" wire:navigate>{{ $link['name'] }}</flux:navlist.item>
                        @endforeach
                    </flux:navlist.group>
                
                    @endforeach
            </flux:navlist>

            <flux:spacer />

            

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}
        {{-- @yield('content') --}}
        @fluxScripts
        
        @livewireScripts
    </body>
</html>
