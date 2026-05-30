<div class="mx-auto max-w-4xl p-6">
    <div class="mb-4 flex items-center gap-2">
        <flux:button href="{{ route('admin.estetic.tipos-tratamientos.index') }}" size="sm" variant="subtle" icon="arrow-left" wire:navigate>Volver</flux:button>
        <flux:heading size="xl">Nuevo protocolo</flux:heading>
    </div>
    <flux:text class="mb-6 text-zinc-500">Define un protocolo profesional con su plantilla de sesiones para aplicarlo en un click a cualquier paciente.</flux:text>

    <form wire:submit="save" class="space-y-6">
        {{-- Datos básicos --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Datos básicos</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="nombre" label="Nombre" placeholder="Ej. Limpieza facial profunda" required class="md:col-span-2" />
                <flux:select wire:model="categoria" label="Categoría">
                    @foreach ($categorias as $c)
                        <flux:select.option value="{{ $c->key }}">{{ $c->label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <div>
                    <label class="mb-1 block text-sm font-medium">Color identificador</label>
                    <input type="color" wire:model="color" class="h-10 w-full cursor-pointer rounded-lg border border-zinc-200 dark:border-zinc-700">
                </div>
                <flux:textarea wire:model="descripcion" label="Descripción" rows="2" class="md:col-span-2" />
            </div>
        </div>

        {{-- Plantilla de protocolo --}}
        <div class="rounded-xl border border-pink-200 bg-pink-50/40 p-5 dark:border-pink-900 dark:bg-pink-950/20">
            <div class="mb-4 flex items-center gap-2">
                <flux:icon.sparkles class="size-5 text-pink-600" />
                <h3 class="text-sm font-semibold uppercase tracking-wide text-pink-700 dark:text-pink-300">Plantilla de protocolo</h3>
            </div>
            <p class="mb-4 text-xs text-zinc-600 dark:text-zinc-400">Estos valores se usan al aplicar el protocolo a un paciente: se generan automáticamente N citas separadas por X días.</p>
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="sesiones_recomendadas" type="number" min="1" max="50" label="Sesiones recomendadas" required />
                <flux:input wire:model="intervalo_dias" type="number" min="1" max="365" label="Intervalo (días)" required />
                <flux:input wire:model="duracion_minutos" type="number" min="1" label="Duración por sesión (min)" required />
                <flux:input wire:model="precio_base" type="number" step="1" min="0" label="Precio por sesión" required />
            </div>
            <div class="mt-3 rounded-lg bg-white p-3 text-xs dark:bg-zinc-900">
                💡 Total estimado del protocolo:
                <strong class="text-pink-600">${{ number_format(($precio_base ?? 0) * ($sesiones_recomendadas ?? 1), 0, ',', '.') }}</strong>
                ({{ $sesiones_recomendadas ?? 1 }} sesiones × ${{ number_format($precio_base ?? 0, 0, ',', '.') }})
            </div>
            <flux:textarea wire:model="protocolo" label="Detalle del protocolo" rows="3" class="mt-4" placeholder="Pasos clínicos, productos a usar, post-tratamiento..." />
        </div>

        {{-- Clínico --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-500">Información clínica</h3>
            <div class="space-y-4">
                <flux:textarea wire:model="materiales_requeridos" label="Materiales requeridos" rows="2" />
                <flux:textarea wire:model="contraindicaciones" label="Contraindicaciones" rows="2" placeholder="Embarazo, alergias, condiciones médicas..." />
            </div>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:switch wire:model="activo" label="Disponible para aplicar" />
            <div class="flex gap-2">
                <flux:button href="{{ route('admin.estetic.tipos-tratamientos.index') }}" variant="subtle" wire:navigate>Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="check">Guardar protocolo</flux:button>
            </div>
        </div>
    </form>
</div>
