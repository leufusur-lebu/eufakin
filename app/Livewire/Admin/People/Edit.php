<?php

namespace App\Livewire\Admin\People;

use App\Models\Person;
use App\Support\RutHelper;
use Livewire\Component;

class Edit extends Component
{
    public Person $person;

    public string $rut = '';
    public string $first_name = '';
    public string $last_name = '';
    public ?string $nickname = null;
    public ?string $birth_date = null;
    public ?string $gender = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $address = null;

    public function mount(Person $person): void
    {
        $this->person = $person;
        $this->rut = $person->rut;
        $this->first_name = $person->first_name;
        $this->last_name = $person->last_name;
        $this->nickname = $person->nickname;
        $this->birth_date = $person->birth_date?->format('Y-m-d');
        $this->gender = $person->gender;
        $this->phone = $person->phone;
        $this->email = $person->email;
        $this->address = $person->address;
    }

    protected function rules(): array
    {
        return [
            'rut' => ['required', 'string', 'max:20'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'nickname' => ['nullable', 'string', 'max:80'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:M,F,O'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if (!RutHelper::validate($data['rut'])) {
            $this->addError('rut', 'El RUT ingresado no es válido.');
            return;
        }

        $this->person->update($data);
        session()->flash('success', 'Persona actualizada.');
        $this->redirectRoute('admin.people.show', $this->person, navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.people.edit');
    }
}
