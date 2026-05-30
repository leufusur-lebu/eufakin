<?php

namespace App\Livewire\Admin\Kine\Payments;

use App\Models\Kine\Payment;
use App\Models\Kine\Treatment;
use App\Models\KineProfile;
use Livewire\Component;

class Create extends Component
{
    public ?int $kine_profile_id = null;
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
            'kine_profile_id' => ['required', 'exists:kine_profiles,id'],
            'tratamiento_id' => ['nullable', 'exists:kine_tratamientos,id'],
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0'],
            'metodo' => ['required', 'in:efectivo,transferencia,debito,credito,mercadopago,obra_social,otro'],
            'estado' => ['required', 'in:pendiente,pagado,anulado'],
            'comprobante' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $data['registrado_por'] = auth()->id();
        Payment::create($data);

        session()->flash('success', 'Pago registrado.');
        return $this->redirectRoute('admin.kine.payments.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.kine.payments.create', [
            'profiles' => KineProfile::with('person')->get(),
            'treatments' => $this->kine_profile_id
                ? Treatment::where('kine_profile_id', $this->kine_profile_id)->get()
                : collect(),
        ]);
    }
}
