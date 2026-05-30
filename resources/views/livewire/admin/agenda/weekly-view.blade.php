<div class="p-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <flux:heading size="xl">Agenda semanal</flux:heading>
            <flux:text class="text-zinc-500">Semana del {{ $start->format('d/m/Y') }}</flux:text>
            <div class="mt-1 flex gap-3 text-xs">
                <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded border border-sky-400 bg-sky-100"></span> Kinesiología</span>
                <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded border border-pink-400 bg-pink-100"></span> Estética</span>
            </div>
        </div>
        <div class="flex gap-2">
            <flux:select wire:model.live="module">
                <flux:select.option value="all">Todos</flux:select.option>
                <flux:select.option value="kine">Kinesiología</flux:select.option>
                <flux:select.option value="estetic">Estética</flux:select.option>
            </flux:select>
            <flux:button wire:click="prevWeek" icon="chevron-left">Anterior</flux:button>
            <flux:button wire:click="today">Hoy</flux:button>
            <flux:button wire:click="nextWeek" icon:trailing="chevron-right">Siguiente</flux:button>
        </div>
    </div>

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
                            <td class="min-w-[120px] border-b border-l p-1 align-top dark:border-zinc-700">
                                @foreach ($cell as $a)
                                    @php
                                        $bg = $a['type'] === 'kine'
                                            ? 'bg-sky-100 text-sky-900 border-sky-400'
                                            : 'bg-pink-100 text-pink-900 border-pink-400';
                                        $badge = $a['type'] === 'kine'
                                            ? 'bg-sky-600 text-white'
                                            : 'bg-pink-600 text-white';
                                        $estadoClass = match($a['estado']) {
                                            'atendido'  => 'opacity-60 line-through',
                                            'cancelado' => 'opacity-40 line-through',
                                            'ausente'   => 'opacity-40',
                                            default     => '',
                                        };
                                        $canAttend = in_array($a['estado'], ['pendiente', 'confirmado']);
                                        $attendRoute = $a['type'] === 'kine'
                                            ? route('admin.kine.sessions.attend', $a['id'])
                                            : route('admin.estetic.sessions.attend', $a['id']);
                                    @endphp
                                    <div class="group relative mb-1 rounded border-l-4 border px-2 py-1 {{ $bg }} {{ $estadoClass }}">
                                        <div class="flex items-center gap-1">
                                            <span class="rounded px-1 text-[9px] font-bold uppercase {{ $badge }}">{{ $a['type'] }}</span>
                                            <span>{{ $a['inicio']->format('H:i') }}–{{ $a['fin']->format('H:i') }}</span>
                                            @if ($a['estado'] === 'atendido')
                                                <flux:icon.check-circle class="size-3 text-emerald-600 ml-auto" />
                                            @elseif ($a['estado'] === 'confirmado')
                                                <span class="ml-auto rounded bg-emerald-500 px-1 text-[8px] font-bold uppercase text-white">OK</span>
                                            @endif
                                        </div>
                                        <div class="font-medium">{{ $a['person'] }}</div>
                                        @if ($a['professional'])
                                            <div class="text-[10px] opacity-70">{{ $a['professional'] }}</div>
                                        @endif
                                        @if ($canAttend)
                                            <a href="{{ $attendRoute }}" wire:navigate
                                                class="absolute right-1 top-1 rounded bg-emerald-600 px-1.5 py-0.5 text-[9px] font-bold uppercase text-white opacity-0 shadow-sm transition group-hover:opacity-100">
                                                ✓ Atender
                                            </a>
                                        @endif
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
