<?php

namespace App\Livewire\Admin\Estetic\Protocols;

use App\Models\Estetic\Appointment;
use App\Models\Estetic\Payment;
use App\Models\Estetic\TipoTratamiento;
use App\Models\Estetic\Treatment;
use App\Models\EsteticProfile;
use App\Models\Person;
use App\Models\Professional;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Apply extends Component
{
    public TipoTratamiento $tipo;

    // Step 1: paciente (ahora trabaja a nivel Person, no EsteticProfile)
    #[Url] public ?int $person_id = null;
    public string $personSearch = '';

    // Step 2: configuración
    public ?int $professional_id = null;
    public string $zona_tratada = '';
    public ?string $start_date = null;
    public string $start_time = '10:00';
    public int $sesiones = 1;
    public int $intervalo_dias = 7;
    public ?float $costo_total = null;

    // Step 3: pago
    public string  $payment_mode   = 'pending'; // pending|full|installments
    public int     $cuotas         = 1;
    public string  $payment_method = 'efectivo';
    public ?float  $abono_inicial  = null;
    public ?string $comprobante    = null;

    public function mount(TipoTratamiento $tipo): void
    {
        $this->tipo            = $tipo;
        $this->sesiones        = $tipo->sesiones_recomendadas ?: 1;
        $this->intervalo_dias  = $tipo->intervalo_dias ?: 7;
        $this->costo_total     = (float) $tipo->precio_base;
        $this->zona_tratada    = $tipo->nombre;
        $this->start_date      = now()->addDay()->format('Y-m-d');
    }

    #[Computed]
    public function selectedPerson(): ?Person
    {
        return $this->person_id
            ? Person::with(['clinicalProfile', 'esteticProfile', 'gymProfile', 'kineProfile'])->find($this->person_id)
            : null;
    }

    #[Computed]
    public function patientResults()
    {
        // Busca en TODA la tabla de personas (no solo quienes ya tienen perfil estético)
        $base = Person::query()->with(['clinicalProfile', 'esteticProfile', 'gymProfile', 'kineProfile']);

        if (strlen(trim($this->personSearch)) < 2) {
            return $base->orderByDesc('updated_at')->limit(8)->get();
        }

        $term = trim($this->personSearch);
        return $base->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('rut', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        })->limit(15)->get();
    }

    public function selectPatient(int $personId): void
    {
        $this->person_id = $personId;
        $this->personSearch = '';
    }

    public function clearPatient(): void
    {
        $this->person_id = null;
    }

    #[Computed]
    public function preview(): array
    {
        if (!$this->start_date) return [];
        $dates = [];
        $cursor = Carbon::parse($this->start_date . ' ' . $this->start_time);
        for ($i = 0; $i < $this->sesiones; $i++) {
            $dates[] = [
                'numero' => $i + 1,
                'fecha'  => $cursor->copy(),
            ];
            $cursor->addDays($this->intervalo_dias);
        }
        return $dates;
    }

    #[Computed]
    public function totalCost(): float
    {
        return (float) ($this->costo_total ?? 0);
    }

    public function apply()
    {
        $this->validate([
            'person_id'       => ['required', 'exists:people,id'],
            'professional_id' => ['nullable', 'exists:professionals,id'],
            'zona_tratada'    => ['required', 'string', 'max:255'],
            'start_date'      => ['required', 'date'],
            'start_time'      => ['required'],
            'sesiones'        => ['required', 'integer', 'min:1', 'max:50'],
            'intervalo_dias'  => ['required', 'integer', 'min:1', 'max:365'],
            'costo_total'     => ['required', 'numeric', 'min:0'],
            'payment_mode'    => ['required', 'in:pending,full,installments'],
            'cuotas'          => ['required', 'integer', 'min:1', 'max:24'],
        ], [], ['person_id' => 'paciente']);

        $profileId = DB::transaction(function () {
            // 0. Asegurar perfil estético (se crea si la persona aún no lo tenía)
            $profile = EsteticProfile::firstOrCreate(
                ['person_id' => $this->person_id],
                ['active' => true]
            );

            $total = $this->totalCost;
            $costoSesion = $this->sesiones > 0 ? round($total / $this->sesiones, 2) : 0;

            // 1. Crear el Treatment
            $treatment = Treatment::create([
                'estetic_profile_id'  => $profile->id,
                'tipo_tratamiento_id' => $this->tipo->id,
                'professional_id'     => $this->professional_id,
                'descripcion_plan'    => "Plan basado en protocolo: {$this->tipo->nombre}",
                'zona_tratada'        => $this->zona_tratada,
                'fecha_inicio'        => $this->start_date,
                'fecha_fin'           => Carbon::parse($this->start_date)->addDays(($this->sesiones - 1) * $this->intervalo_dias)->format('Y-m-d'),
                'sesiones_totales'    => $this->sesiones,
                'sesiones_realizadas' => 0,
                'costo_sesion'        => $costoSesion,
                'costo_total'         => $total,
                'estado'              => 'activo',
                'observaciones'       => $this->tipo->protocolo,
            ]);

            // 2. Generar appointments
            $cursor = Carbon::parse($this->start_date . ' ' . $this->start_time);
            for ($i = 0; $i < $this->sesiones; $i++) {
                $inicio = $cursor->copy();
                $fin = $inicio->copy()->addMinutes($this->tipo->duracion_minutos ?: 60);

                Appointment::create([
                    'estetic_profile_id' => $profile->id,
                    'tratamiento_id'     => $treatment->id,
                    'professional_id'    => $this->professional_id,
                    'inicio'             => $inicio,
                    'fin'                => $fin,
                    'estado'             => 'pendiente',
                    'motivo'             => 'Sesión '.($i+1).'/'.$this->sesiones.' — '.$this->tipo->nombre,
                ]);

                $cursor->addDays($this->intervalo_dias);
            }

            // 3. Generar pagos según el modo
            $this->generatePayments($profile->id, $treatment, $total);

            return $profile->id;
        });

        session()->flash('success', "Protocolo aplicado: {$this->sesiones} sesiones agendadas correctamente.");
        return $this->redirectRoute('admin.estetic.patients.show', ['profile' => $profileId], navigate: true);
    }

    protected function generatePayments(int $profileId, Treatment $treatment, float $total): void
    {
        $needsCode = in_array($this->payment_method, ['debito', 'credito', 'transferencia', 'webpay', 'mercadopago']);

        $base = [
            'estetic_profile_id' => $profileId,
            'tratamiento_id'     => $treatment->id,
            'metodo'             => $this->payment_method ?: 'efectivo',
            'comprobante'        => $needsCode ? ($this->comprobante ?: null) : null,
        ];

        if ($this->payment_mode === 'full') {
            Payment::create([
                ...$base,
                'fecha'         => now()->format('Y-m-d'),
                'monto'         => $total,
                'estado'        => 'pagado',
                'observaciones' => 'Pago completo del protocolo',
            ]);
            return;
        }

        if ($this->payment_mode === 'installments' && $this->cuotas > 1) {
            $abono = (float) ($this->abono_inicial ?? 0);
            $restante = $total - $abono;
            $valorCuota = round($restante / $this->cuotas, 0);

            if ($abono > 0) {
                Payment::create([
                    ...$base,
                    'fecha'         => now()->format('Y-m-d'),
                    'monto'         => $abono,
                    'estado'        => 'pagado',
                    'observaciones' => 'Abono inicial',
                ]);
            }

            $cursor = Carbon::parse($this->start_date);
            for ($i = 1; $i <= $this->cuotas; $i++) {
                Payment::create([
                    ...$base,
                    'fecha'         => $cursor->copy()->format('Y-m-d'),
                    'monto'         => $valorCuota,
                    'estado'        => 'pendiente',
                    'metodo'        => 'efectivo',
                    'observaciones' => "Cuota {$i}/{$this->cuotas}",
                ]);
                $cursor->addDays($this->intervalo_dias);
            }
            return;
        }

        // pendiente — un solo pago por el total
        Payment::create([
            ...$base,
            'fecha'         => $this->start_date,
            'monto'         => $total,
            'estado'        => 'pendiente',
            'metodo'        => 'efectivo',
            'observaciones' => 'Pago generado al aplicar el protocolo',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.estetic.protocols.apply', [
            'professionals' => Professional::estetic()->orderBy('name')->get(),
        ]);
    }
}
