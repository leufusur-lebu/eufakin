<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EUFAKIN · Centro Kinésico, Gimnasio y Estética</title>
    <meta name="description" content="EUFAKIN — Centro kinésico integral: rehabilitación, gimnasio y tratamientos estéticos en un solo lugar.">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800|instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --euf-lime: #8CC63F;
            --euf-green: #5BB949;
            --euf-cyan: #29ABE2;
            --euf-teal: #1CA9C9;
            --euf-navy: #1B3A5C;
        }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        .font-display { font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif; }
        .euf-gradient { background: linear-gradient(120deg, var(--euf-lime) 0%, var(--euf-teal) 55%, var(--euf-cyan) 100%); }
        .euf-gradient-text {
            background: linear-gradient(120deg, var(--euf-green) 0%, var(--euf-cyan) 100%);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .euf-blob {
            position: absolute; border-radius: 9999px; filter: blur(80px); opacity: .35; z-index: 0;
        }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased">

    {{-- ============ NAVBAR ============ --}}
    <header class="sticky top-0 z-50 border-b border-slate-100 bg-white/85 backdrop-blur-lg">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3">
            <a href="#inicio" class="flex items-center gap-2">
                <x-eufakin-mark class="h-12" />
            </a>

            <div class="hidden items-center gap-8 md:flex">
                <a href="#servicios" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Servicios</a>
                <a href="#nosotros" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Nosotros</a>
                <a href="#contacto" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Contacto</a>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="rounded-full euf-gradient px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:shadow-md">
                        Ir al sistema
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-1.5 rounded-full euf-gradient px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:shadow-md hover:brightness-105">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H2.25"/></svg>
                        Ingresar al sistema
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    {{-- ============ HERO ============ --}}
    <section id="inicio" class="relative overflow-hidden">
        <div class="euf-blob -left-20 top-10 h-72 w-72" style="background: var(--euf-lime)"></div>
        <div class="euf-blob -right-16 top-40 h-80 w-80" style="background: var(--euf-cyan)"></div>

        <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 md:grid-cols-2 md:py-28">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-600 shadow-sm">
                    <span class="size-2 rounded-full" style="background: var(--euf-lime)"></span>
                    Centro Kinésico Integral
                </span>

                <h1 class="font-display mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                    Tu salud y bienestar,
                    <span class="euf-gradient-text">en movimiento</span>
                </h1>

                <p class="mt-6 max-w-lg text-lg text-slate-600">
                    En <strong style="color: var(--euf-navy)">EUFA</strong><strong style="color: var(--euf-cyan)">KIN</strong> reunimos
                    <strong>kinesiología</strong>, <strong>gimnasio</strong> y <strong>estética</strong> en un mismo lugar,
                    con un equipo profesional dedicado a tu recuperación y a que te sientas mejor cada día.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="#servicios"
                       class="rounded-full euf-gradient px-7 py-3 text-base font-semibold text-white shadow-md transition hover:shadow-lg hover:brightness-105">
                        Conoce nuestros servicios
                    </a>
                    <a href="#contacto"
                       class="rounded-full border-2 px-7 py-3 text-base font-semibold transition hover:bg-slate-50"
                       style="border-color: var(--euf-navy); color: var(--euf-navy)">
                        Agenda tu hora
                    </a>
                </div>

                <div class="mt-10 flex items-center gap-8">
                    <div>
                        <div class="font-display text-3xl font-extrabold" style="color: var(--euf-navy)">+10</div>
                        <div class="text-sm text-slate-500">años de experiencia</div>
                    </div>
                    <div class="h-10 w-px bg-slate-200"></div>
                    <div>
                        <div class="font-display text-3xl font-extrabold" style="color: var(--euf-cyan)">3</div>
                        <div class="text-sm text-slate-500">áreas especializadas</div>
                    </div>
                    <div class="h-10 w-px bg-slate-200"></div>
                    <div>
                        <div class="font-display text-3xl font-extrabold" style="color: var(--euf-green)">100%</div>
                        <div class="text-sm text-slate-500">atención personalizada</div>
                    </div>
                </div>
            </div>

            {{-- Tarjeta visual del hero --}}
            <div class="relative">
                <div class="absolute inset-0 euf-gradient rounded-[2.5rem] opacity-10 blur-2xl"></div>
                <div class="relative rounded-[2.5rem] border border-slate-100 bg-white p-8 shadow-2xl">
                    <div class="flex justify-center">
                        <x-eufakin-mark class="h-48" />
                    </div>
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <span class="flex size-11 items-center justify-center rounded-xl text-white" style="background: var(--euf-green)">
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2.25M9 6a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 6m-6 0V4.5"/></svg>
                            </span>
                            <div>
                                <div class="font-semibold text-slate-800">Rehabilitación kinésica</div>
                                <div class="text-sm text-slate-500">Recupera tu movilidad</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <span class="flex size-11 items-center justify-center rounded-xl text-white" style="background: var(--euf-cyan)">
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.115 5.19l.319 1.913A6 6 0 008.11 10.36L9.75 12l-.387.775c-.217.433-.132.956.21 1.298l1.348 1.348c.21.21.329.497.329.795v1.089c0 .426.24.815.622 1.006l.153.076c.433.217.956.132 1.298-.21l.723-.723a8.7 8.7 0 002.288-4.042 1.087 1.087 0 00-.358-1.099l-1.33-1.108c-.251-.21-.582-.299-.905-.245l-1.17.195a1.125 1.125 0 01-.98-.314l-.295-.295a1.125 1.125 0 010-1.591l.13-.132a1.125 1.125 0 011.3-.21l.603.302a.809.809 0 001.086-1.086L14.25 7.5l1.256-.837a4.5 4.5 0 001.528-1.732l.146-.292M6.115 5.19A9 9 0 1017.18 4.64M6.115 5.19A8.965 8.965 0 0112 3c1.929 0 3.716.607 5.18 1.64"/></svg>
                            </span>
                            <div>
                                <div class="font-semibold text-slate-800">Gimnasio equipado</div>
                                <div class="text-sm text-slate-500">Entrena con propósito</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <span class="flex size-11 items-center justify-center rounded-xl text-white" style="background: var(--euf-lime)">
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
                            </span>
                            <div>
                                <div class="font-semibold text-slate-800">Estética avanzada</div>
                                <div class="text-sm text-slate-500">Realza tu bienestar</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ SERVICIOS ============ --}}
    <section id="servicios" class="bg-slate-50 py-24">
        <div class="mx-auto max-w-7xl px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span class="font-semibold uppercase tracking-wide" style="color: var(--euf-cyan)">Nuestros servicios</span>
                <h2 class="font-display mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                    Todo lo que necesitas para tu bienestar
                </h2>
                <p class="mt-4 text-lg text-slate-600">Tres áreas especializadas, un mismo equipo comprometido contigo.</p>
            </div>

            <div class="mt-16 grid gap-8 md:grid-cols-3">
                {{-- Kinesiología --}}
                <div class="group rounded-3xl border border-slate-100 bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <span class="flex size-14 items-center justify-center rounded-2xl text-white shadow-lg" style="background: linear-gradient(135deg, var(--euf-green), var(--euf-teal))">
                        <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/></svg>
                    </span>
                    <h3 class="font-display mt-6 text-xl font-bold text-slate-900">Kinesiología</h3>
                    <p class="mt-3 text-slate-600">Rehabilitación traumatológica, neurológica, respiratoria y deportiva con planes de tratamiento personalizados y seguimiento de tu evolución.</p>
                    <ul class="mt-5 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full" style="background: var(--euf-green)"></span> Evaluación kinésica integral</li>
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full" style="background: var(--euf-green)"></span> Atención con bono FONASA</li>
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full" style="background: var(--euf-green)"></span> Recuperación post-operatoria</li>
                    </ul>
                </div>

                {{-- Gimnasio --}}
                <div class="group rounded-3xl border border-slate-100 bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <span class="flex size-14 items-center justify-center rounded-2xl text-white shadow-lg" style="background: linear-gradient(135deg, var(--euf-teal), var(--euf-cyan))">
                        <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3"/></svg>
                    </span>
                    <h3 class="font-display mt-6 text-xl font-bold text-slate-900">Gimnasio</h3>
                    <p class="mt-3 text-slate-600">Espacio equipado para entrenamiento funcional y acondicionamiento físico, con planes de suscripción mensual flexibles.</p>
                    <ul class="mt-5 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full" style="background: var(--euf-cyan)"></span> Equipo INBODY de composición corporal</li>
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full" style="background: var(--euf-cyan)"></span> Planes desde 2 a 5 veces por semana</li>
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full" style="background: var(--euf-cyan)"></span> Seguimiento de tu progreso</li>
                    </ul>
                </div>

                {{-- Estética --}}
                <div class="group rounded-3xl border border-slate-100 bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <span class="flex size-14 items-center justify-center rounded-2xl text-white shadow-lg" style="background: linear-gradient(135deg, var(--euf-lime), var(--euf-green))">
                        <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
                    </span>
                    <h3 class="font-display mt-6 text-xl font-bold text-slate-900">Estética</h3>
                    <p class="mt-3 text-slate-600">Tratamientos reductivos, reafirmantes y masajes terapéuticos con protocolos profesionales y resultados visibles.</p>
                    <ul class="mt-5 space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full" style="background: var(--euf-lime)"></span> Packs reductivos y reafirmantes</li>
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full" style="background: var(--euf-lime)"></span> Masajes relajantes y descontracturantes</li>
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full" style="background: var(--euf-lime)"></span> Seguimiento con fotos de evolución</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ NOSOTROS ============ --}}
    <section id="nosotros" class="py-24">
        <div class="mx-auto grid max-w-7xl items-center gap-16 px-6 lg:grid-cols-2">
            <div class="relative">
                <div class="euf-gradient absolute -inset-4 rounded-[2.5rem] opacity-10 blur-2xl"></div>
                <div class="relative grid grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div class="rounded-2xl p-6 text-white shadow-lg" style="background: var(--euf-navy)">
                            <div class="font-display text-3xl font-extrabold">Kiné</div>
                            <div class="text-sm text-white/70">Rehabilitación profesional</div>
                        </div>
                        <div class="rounded-2xl p-6 text-white shadow-lg" style="background: var(--euf-green)">
                            <div class="font-display text-3xl font-extrabold">Salud</div>
                            <div class="text-sm text-white/80">Tu bienestar primero</div>
                        </div>
                    </div>
                    <div class="mt-8 space-y-4">
                        <div class="rounded-2xl p-6 text-white shadow-lg" style="background: var(--euf-cyan)">
                            <div class="font-display text-3xl font-extrabold">Movimiento</div>
                            <div class="text-sm text-white/80">Vuelve a moverte</div>
                        </div>
                        <div class="flex items-center justify-center rounded-2xl border border-slate-100 bg-white p-6 shadow-lg">
                            <x-eufakin-mark class="h-24" />
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <span class="font-semibold uppercase tracking-wide" style="color: var(--euf-green)">Sobre nosotros</span>
                <h2 class="font-display mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                    Un equipo que se mueve contigo
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    EUFAKIN nace de la pasión por el movimiento y la salud. Somos un centro kinésico
                    dirigido por profesionales que entienden que cada persona es única, y que la recuperación
                    y el bienestar van de la mano.
                </p>
                <div class="mt-8 space-y-5">
                    <div class="flex gap-4">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl text-white" style="background: var(--euf-green)">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <div>
                            <h4 class="font-semibold text-slate-900">Atención personalizada</h4>
                            <p class="text-slate-600">Cada plan se diseña según tus necesidades y objetivos.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl text-white" style="background: var(--euf-cyan)">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <div>
                            <h4 class="font-semibold text-slate-900">Profesionales certificados</h4>
                            <p class="text-slate-600">Kinesiólogos y especialistas con amplia experiencia clínica.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl text-white" style="background: var(--euf-lime)">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <div>
                            <h4 class="font-semibold text-slate-900">Seguimiento de tu evolución</h4>
                            <p class="text-slate-600">Registramos tu progreso para que veas resultados reales.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ CTA / CONTACTO ============ --}}
    <section id="contacto" class="euf-gradient relative overflow-hidden py-20">
        <div class="mx-auto max-w-4xl px-6 text-center">
            <h2 class="font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                ¿List@ para dar el primer paso?
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-white/90">
                Agenda tu evaluación y comienza tu proceso de recuperación y bienestar con nosotros.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="https://wa.me/56959083854?text=Hola%20EUFAKIN,%20quiero%20agendar%20una%20hora"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-full bg-white px-7 py-3 text-base font-semibold shadow-md transition hover:shadow-lg" style="color: var(--euf-navy)">
                    <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413"/></svg>
                    Escríbenos por WhatsApp
                </a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="rounded-full border-2 border-white px-7 py-3 text-base font-semibold text-white transition hover:bg-white/10">Ir al sistema</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full border-2 border-white px-7 py-3 text-base font-semibold text-white transition hover:bg-white/10">Acceso al sistema</a>
                @endauth
            </div>
        </div>
    </section>

    {{-- ============ FOOTER ============ --}}
    <footer class="bg-slate-900 py-12 text-slate-300">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex flex-col items-center justify-between gap-8 md:flex-row md:items-start">
                <div class="max-w-sm text-center md:text-left">
                    <div class="flex justify-center md:justify-start">
                        <span class="inline-flex rounded-2xl bg-white p-3 shadow-lg">
                            <x-eufakin-mark class="h-16" />
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-slate-400">
                        Centro kinésico integral. Kinesiología, gimnasio y estética en un solo lugar,
                        comprometidos con tu salud y bienestar.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-12 text-sm">
                    <div>
                        <h4 class="font-semibold text-white">Servicios</h4>
                        <ul class="mt-3 space-y-2 text-slate-400">
                            <li><a href="#servicios" class="transition hover:text-white">Kinesiología</a></li>
                            <li><a href="#servicios" class="transition hover:text-white">Gimnasio</a></li>
                            <li><a href="#servicios" class="transition hover:text-white">Estética</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-white">Contacto</h4>
                        <ul class="mt-3 space-y-2 text-slate-400">
                            <li>
                                <a href="https://wa.me/56959083854?text=Hola%20EUFAKIN,%20quiero%20agendar%20una%20hora" target="_blank" class="inline-flex items-center gap-1.5 transition hover:text-white">
                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></svg>
                                    +56 9 5908 3854
                                </a>
                            </li>
                            <li>
                                <a href="mailto:Centrokinesicoeufakin@gmail.com" class="break-all transition hover:text-white">Centrokinesicoeufakin@gmail.com</a>
                            </li>
                            <li><a href="{{ route('login') }}" class="transition hover:text-white">Acceso al sistema</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-10 border-t border-slate-800 pt-6 text-center text-sm text-slate-500">
                © {{ date('Y') }} EUFAKIN · Centro Kinésico. Todos los derechos reservados.
            </div>
        </div>
    </footer>

</body>
</html>
