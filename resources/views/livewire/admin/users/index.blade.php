<div class="p-6 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Usuarios del sistema</flux:heading>
            <flux:text class="text-zinc-500">Personas con acceso para administrar EUFAKIN</flux:text>
        </div>
        <flux:button wire:click="openCreate" variant="primary" icon="user-plus">Nuevo usuario</flux:button>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ session('error') }}</div>
    @endif

    {{-- KPIs --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Usuarios activos</span>
                <flux:icon.users class="size-5 text-sky-500" />
            </div>
            <div class="mt-2 text-3xl font-bold">{{ $totalActivos }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Administradores</span>
                <flux:icon.shield-check class="size-5 text-emerald-500" />
            </div>
            <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $totalAdmins }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase text-zinc-500">Total</span>
                <flux:icon.identification class="size-5 text-zinc-400" />
            </div>
            <div class="mt-2 text-3xl font-bold">{{ $users->count() }}</div>
        </div>
    </div>

    {{-- Buscador --}}
    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por nombre o correo..." class="max-w-sm" />

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left">Usuario</th>
                    <th class="px-4 py-3 text-left">Correo</th>
                    <th class="px-4 py-3 text-center">Rol</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($users as $u)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 {{ !$u->active ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-9 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-emerald-400 text-xs font-bold text-white">
                                    {{ $u->initials() }}
                                </div>
                                <div>
                                    <div class="font-medium">{{ $u->name }}</div>
                                    @if ($u->id === auth()->id())
                                        <div class="text-[10px] font-medium uppercase text-sky-600">Tú</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $u->email }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($u->isAdmin())
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <flux:icon.shield-check class="size-3" /> Administrador
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                    <flux:icon.user class="size-3" /> Asistente
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($u->active)
                                <flux:badge size="sm" color="green">Activo</flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc">Inactivo</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1">
                                <flux:button size="sm" variant="ghost" icon="pencil" wire:click="openEdit({{ $u->id }})" title="Editar" />
                                <flux:button size="sm" variant="ghost" icon="key" wire:click="openResetPassword({{ $u->id }})" title="Restablecer contraseña" />
                                @if ($u->id !== auth()->id())
                                    <flux:button size="sm" variant="ghost" icon="{{ $u->active ? 'no-symbol' : 'check-circle' }}" wire:click="toggleActive({{ $u->id }})" title="{{ $u->active ? 'Desactivar' : 'Activar' }}" />
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $u->id }})" wire:confirm="¿Eliminar a {{ $u->name }}? Esta acción no se puede deshacer." title="Eliminar" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-zinc-400">No hay usuarios que coincidan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== MODAL CREAR / EDITAR ===== --}}
    <flux:modal wire:model="formOpen" class="md:w-[560px]">
        <div class="space-y-5">
            <flux:heading size="lg">{{ $editingId ? 'Editar usuario' : 'Nuevo usuario' }}</flux:heading>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="name" label="Nombre completo" class="md:col-span-2" />
                <flux:input wire:model="email" type="email" label="Correo electrónico" class="md:col-span-2" />

                <flux:select wire:model="role" label="Rol">
                    <flux:select.option value="admin">Administrador</flux:select.option>
                    <flux:select.option value="staff">Asistente</flux:select.option>
                </flux:select>
                <div class="flex items-end pb-1">
                    <flux:switch wire:model="active" label="Cuenta activa" />
                </div>

                <flux:input wire:model="password" type="password" label="{{ $editingId ? 'Nueva contraseña (opcional)' : 'Contraseña' }}" viewable placeholder="Mínimo 8 caracteres" />
                <flux:input wire:model="password_confirmation" type="password" label="Confirmar contraseña" viewable />
            </div>

            <div class="rounded-lg bg-zinc-50 p-3 text-xs text-zinc-500 dark:bg-zinc-800/50">
                <strong>Administrador:</strong> acceso total, incluida la gestión de usuarios.<br>
                <strong>Asistente:</strong> acceso a la operación diaria (agenda, pacientes, caja), sin gestión de usuarios.
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('formOpen', false)">Cancelar</flux:button>
                <flux:button variant="primary" icon="check" wire:click="save">{{ $editingId ? 'Guardar cambios' : 'Crear usuario' }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ===== MODAL RESET PASSWORD ===== --}}
    <flux:modal wire:model="pwOpen" class="md:w-[480px]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Restablecer contraseña</flux:heading>
                <flux:text class="text-zinc-500">{{ $pwUserName }}</flux:text>
            </div>
            <flux:input wire:model="newPassword" type="password" label="Nueva contraseña" viewable placeholder="Mínimo 8 caracteres" />
            <flux:input wire:model="newPassword_confirmation" type="password" label="Confirmar contraseña" viewable />
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('pwOpen', false)">Cancelar</flux:button>
                <flux:button variant="primary" icon="key" wire:click="resetPassword">Restablecer</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
