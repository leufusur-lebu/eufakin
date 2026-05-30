<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Agenda estética</flux:heading>
            <flux:text class="text-zinc-500">Semana del {{ $start->format('d/m/Y') }}</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:select wire:model.live="estado">
                <flux:select.option value="">Todos los estados</flux:select.option>
                <flux:select.option value="pendiente">Pendiente</flux:select.option>
                <flux:select.option value="confirmado">Confirmado</flux:select.option>
                <flux:select.option value="atendido">Atendido</flux:select.option>
                <flux:select.option value="cancelado">Cancelado</flux:select.option>
                <flux:select.option value="ausente">Ausente</flux:select.option>
            </flux:select>
            <flux:button wire:click="prevWeek" icon="chevron-left">Anterior</flux:button>
            <flux:button wire:click="today">Hoy</flux:button>
            <flux:button wire:click="nextWeek" icon:trailing="chevron-right">Siguiente</flux:button>
            <flux:button href="{{ route('admin.estetic.appointments.create') }}" variant="primary" icon="plus" wire:navigate>Nueva</flux:button>
        </div>
    </div>

    @if (session('success')) <div class="rounded bg-green-100 p-3 text-green-700">{{ session('success') }}</div> @endif

    <div class="overflow-x-auto rounded-lg border dark:border-zinc-700">
        <table class="w-full text-xs">
            <thead class="bg-zinc-50 dark:bg-zinc-800 sticky top-0">
                <tr>
                    <th class="w-20 border-b p-2 text-left dark:border-zinc-700">Hora</th>
                    @foreach ($days as $day)
                        <th class="border-b border-l p-2 text-left dark:border-zinc-700">
                            <div class="font-semibold">{{ ucfirst($day->isoFormat('ddd')) }}</div>
                            <div class="text-zinc-500">{{ $day->format('d/m') }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($slots as $slot)
                    <tr>
                        <td class="border-b p-1 text-zinc-500 dark:border-zinc-700">{{ $slot }}</td>
                        @foreach ($days as $day)
                            @php $key = $day->format('Y-m-d'); $cell = $grid[$key][$slot] ?? []; @endphp
                            <td class="min-w-[140px] border-b border-l p-1 align-top dark:border-zinc-700">
                                @foreach ($cell as $a)
                                    @php
                                        $bg = match($a->color) {
                                            'green' => 'bg-green-100 text-green-900 border-green-300',
                                            'blue'  => 'bg-blue-100 text-blue-900 border-blue-300',
                                            'amber' => 'bg-amber-100 text-amber-900 border-amber-300',
                                            'red'   => 'bg-red-100 text-red-900 border-red-300',
                                            default => 'bg-zinc-100 text-zinc-900 border-zinc-300',
                                        };
                                    @endphp
                                    @php
                                        $estadoClass = match($a->estado) {
                                            'atendido'  => 'opacity-60',
                                            'cancelado' => 'opacity-40 line-through',
                                            'ausente'   => 'opacity-40',
                                            default     => '',
                                        };
                                        $canAttend = in_array($a->estado, ['pendiente', 'confirmado']);
                                    @endphp
                                    <div class="mb-1 rounded border px-2 py-1 {{ $bg }} {{ $estadoClass }}">
                                        <div class="flex items-center justify-between gap-1">
                                            <span>{{ $a->inicio->format('H:i') }}–{{ $a->fin->format('H:i') }}</span>
                                            @if ($a->estado === 'atendido')
                                                <flux:icon.check-circle class="size-3.5 text-emerald-600" />
                                            @elseif ($a->estado === 'confirmado')
                                                <span class="rounded bg-emerald-500 px-1 text-[8px] font-bold uppercase text-white">OK</span>
                                            @endif
                                        </div>
                                        <div class="font-medium">{{ $a->esteticProfile?->person?->full_name ?? '—' }}</div>
                                        @if ($a->professional)
                                            <div class="text-[10px] opacity-70">{{ $a->professional->full_name }}</div>
                                        @endif
                                        @if ($a->motivo)
                                            <div class="text-[10px] opacity-80 truncate">{{ $a->motivo }}</div>
                                        @endif
                                        <div class="mt-1 flex gap-1">
                                            @if ($canAttend)
                                                <a href="{{ route('admin.estetic.sessions.attend', $a) }}" wire:navigate
                                                    class="flex-1 rounded bg-emerald-600 px-1.5 py-0.5 text-center text-[10px] font-bold uppercase text-white hover:bg-emerald-700">
                                                    ✓ Atender
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.estetic.appointments.edit', $a) }}" wire:navigate
                                                class="rounded bg-zinc-200 px-1.5 py-0.5 text-[10px] uppercase text-zinc-700 hover:bg-zinc-300">
                                                ✎
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
