<div class="p-6 max-w-4xl space-y-6">
    <div>
        <flux:heading size="xl">Plantillas WhatsApp</flux:heading>
        <flux:text class="text-zinc-500">Mensajes pre-armados que la asistente envía con un click desde la agenda y la caja.</flux:text>
    </div>

    {{-- Cómo funciona --}}
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/30">
        <div class="flex items-start gap-3">
            <flux:icon.information-circle class="size-5 shrink-0 text-emerald-600 mt-0.5" />
            <div class="text-sm text-emerald-800 dark:text-emerald-200">
                <strong>Cómo funcionan:</strong> al editar una plantilla, los textos entre llaves <code class="rounded bg-white px-1 dark:bg-zinc-900">{nombre}</code>, <code class="rounded bg-white px-1 dark:bg-zinc-900">{fecha}</code>, <code class="rounded bg-white px-1 dark:bg-zinc-900">{hora}</code>, <code class="rounded bg-white px-1 dark:bg-zinc-900">{servicio}</code>, <code class="rounded bg-white px-1 dark:bg-zinc-900">{monto}</code>, <code class="rounded bg-white px-1 dark:bg-zinc-900">{concepto}</code> se reemplazan automáticamente al enviar.
                <p class="mt-1 text-xs">Cuando hagas click en "📱 Enviar" en una cita o cobro, se abrirá WhatsApp Web con el mensaje listo y solo tienes que apretar enviar.</p>
            </div>
        </div>
    </div>

    {{-- Plantillas --}}
    <div class="space-y-4">
        @foreach ($templates as $t)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-mono text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $t->key }}</span>
                            @if (session('saved-' . $t->id))
                                <span class="text-xs text-emerald-600">✓ {{ session('saved-' . $t->id) }}</span>
                            @endif
                        </div>
                        <flux:input wire:model="forms.{{ $t->id }}.name" label="Nombre" />
                        <flux:textarea wire:model="forms.{{ $t->id }}.body" rows="6" label="Mensaje" />
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-zinc-200 pt-3 dark:border-zinc-700">
                    <flux:switch wire:model="forms.{{ $t->id }}.active" label="Activa" />
                    <flux:button size="sm" variant="primary" icon="check" wire:click="save({{ $t->id }})">Guardar</flux:button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Roadmap --}}
    <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50/50 p-5 text-sm dark:border-zinc-700 dark:bg-zinc-800/30">
        <h3 class="font-semibold">🚀 Próximamente</h3>
        <p class="mt-2 text-zinc-600 dark:text-zinc-400">
            Cuando se haga el trámite con Meta Cloud API, los mensajes podrán enviarse de forma <strong>100% automática</strong> sin que la asistente tenga que hacer click — el sistema mandará los recordatorios solo. La pantalla seguirá igual: cambiará solo el "transporte" por detrás.
        </p>
    </div>
</div>
