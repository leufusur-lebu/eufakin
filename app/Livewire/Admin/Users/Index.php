<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url] public string $search = '';

    // Modal crear/editar
    public bool $formOpen = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $role = 'staff';
    public bool $active = true;
    public string $password = '';
    public string $password_confirmation = '';

    // Modal reset password
    public bool $pwOpen = false;
    public ?int $pwUserId = null;
    public ?string $pwUserName = null;
    public string $newPassword = '';
    public string $newPassword_confirmation = '';

    public function mount(): void
    {
        // Solo administradores pueden gestionar usuarios
        abort_unless(auth()->user()?->isAdmin(), 403, 'Solo los administradores pueden gestionar usuarios.');
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'email', 'role', 'active', 'password', 'password_confirmation']);
        $this->role = 'staff';
        $this->active = true;
        $this->resetErrorBag();
        $this->formOpen = true;
    }

    public function openEdit(int $id): void
    {
        $u = User::findOrFail($id);
        $this->editingId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->role = $u->role;
        $this->active = (bool) $u->active;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetErrorBag();
        $this->formOpen = true;
    }

    public function save(): void
    {
        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role'  => ['required', 'in:admin,staff'],
            'active'=> ['boolean'],
        ];

        // Password requerido al crear; opcional al editar
        if (!$this->editingId) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } elseif ($this->password !== '') {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $this->validate($rules, [], [
            'name' => 'nombre', 'email' => 'correo', 'role' => 'rol', 'password' => 'contraseña',
        ]);

        // No permitir que el último admin activo se degrade/desactive a sí mismo
        if ($this->editingId === auth()->id() && ($this->role !== 'admin' || !$this->active)) {
            if ($this->isLastActiveAdmin($this->editingId)) {
                $this->addError('role', 'No puedes quitar tu rol de administrador: eres el único administrador activo.');
                return;
            }
        }

        $data = [
            'name'   => $this->name,
            'email'  => $this->email,
            'role'   => $this->role,
            'active' => $this->active,
        ];
        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Usuario actualizado.');
        } else {
            $data['password'] = Hash::make($this->password);
            $data['email_verified_at'] = now();
            User::create($data);
            session()->flash('success', 'Usuario creado correctamente.');
        }

        $this->formOpen = false;
    }

    public function toggleActive(int $id): void
    {
        $u = User::findOrFail($id);

        if ($u->id === auth()->id()) {
            session()->flash('error', 'No puedes desactivar tu propia cuenta.');
            return;
        }
        if ($u->active && $u->isAdmin() && $this->isLastActiveAdmin($u->id)) {
            session()->flash('error', 'No puedes desactivar al único administrador activo.');
            return;
        }

        $u->update(['active' => !$u->active]);
        session()->flash('success', $u->active ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    public function openResetPassword(int $id): void
    {
        $u = User::findOrFail($id);
        $this->pwUserId = $u->id;
        $this->pwUserName = $u->name;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->resetErrorBag();
        $this->pwOpen = true;
    }

    public function resetPassword(): void
    {
        $this->validate([
            'newPassword' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], ['newPassword' => 'contraseña']);

        User::findOrFail($this->pwUserId)->update([
            'password' => Hash::make($this->newPassword),
        ]);
        session()->flash('success', 'Contraseña restablecida.');
        $this->pwOpen = false;
    }

    public function delete(int $id): void
    {
        $u = User::findOrFail($id);

        if ($u->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta.');
            return;
        }
        if ($u->isAdmin() && $this->isLastActiveAdmin($u->id)) {
            session()->flash('error', 'No puedes eliminar al único administrador activo.');
            return;
        }

        $u->delete();
        session()->flash('success', 'Usuario eliminado.');
    }

    protected function isLastActiveAdmin(int $excludeId): bool
    {
        return User::where('role', 'admin')->where('active', true)
            ->where('id', '!=', $excludeId)->doesntExist();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) =>
                $w->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'totalActivos' => User::where('active', true)->count(),
            'totalAdmins'  => User::where('role', 'admin')->where('active', true)->count(),
        ]);
    }
}
