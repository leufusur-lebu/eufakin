<?php

namespace App\Livewire\Admin\Estetic\Payments;

use App\Models\Estetic\Payment;
use App\Models\Estetic\Treatment;
use App\Models\EsteticProfile;
use Livewire\Component;

class Create extends Component
{
    public ?int $estetic_profile_id = null;
    public ?int $tratamiento_id = null;
    public ?string $fecha = null;
    public float $monto = 0;
    public string $metodo = 'efectivo';
    public string $estado = 'pagado';
    public ?string $comprobante = null;
    public ?string $observaciones = null;

    public function mount(): void
    {
        $this->fecha = now()->format('Y-m-d');
    }

    public function save()
    {
        $data = $this->validate([
            'estetic_profile_id' => ['required', 'exists:estetic_profiles,id'],
            'tratamiento_id' => ['nullable', 'exists:este_tratamientos,id'],
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0'],
            'metodo' => ['required', 'in:efectivo,transferencia,debito,credito,mercadopago,otro'],
            'estado' => ['required', 'in:pendiente,pagado,anulado'],
            'comprobante' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $data['registrado_por'] = auth()->id();
        Payment::create($data);

        session()->flash('success', 'Pago registrado.');
        return $this->redirectRoute('admin.estetic.payments.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.estetic.payments.create', [
            'profiles' => EsteticProfile::with('person')->get(),
            'treatments' => $this->estetic_profile_id
                ? Treatment::where('estetic_profile_id', $this->estetic_profile_id)->get()
                : collect(),
        ]);
    }
}
