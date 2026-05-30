<?php

namespace App\Livewire\Admin\Admission;

use App\Models\EsteticProfile;
use App\Models\GymProfile;
use App\Models\KineProfile;
use App\Models\Person;
use App\Models\Plan;
use App\Models\Professional;
use App\Models\Subscription;
use App\Models\Kine\Treatment as KineTreatment;
use App\Models\Kine\Appointment as KineAppointment;
use App\Models\Estetic\Treatment as EsteticTreatment;
use App\Models\Estetic\Appointment as EsteticAppointment;
use App\Models\Estetic\TipoTratamiento;
use App\Support\RutHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class Wizard extends Component
{
    public int $step = 1;
    public int $totalSteps = 9;

    // Step 1: Personal
    public string $rut = '';
    public ?bool $rutValid = null;
    public ?int $existing_person_id = null;
    public bool $person_locked = false;
    public string $first_name = '';
    public string $last_name = '';
    public ?string $nickname = null;
    public ?string $birth_date = null;
    public string $gender = 'M';
    public ?string $phone = null; // local part (without +56)
    public ?string $email = null;
    public ?string $address = null;
    public ?string $poblacion = null;
    public ?string $comuna = null;

    // Step 2: Emergency contact
    public ?string $emergency_contact_name = null;
    public ?string $emergency_contact_phone = null;
    public ?string $emergency_contact_relationship = null;

    // Step 3: Datos clínicos (antecedentes + lesión opcional + medición inicial opcional)
    // Antecedentes → clinical_profile
    public ?string $cli_chronic_diseases = null;     // comorbilidades
    public ?string $cli_surgical_history = null;     // operaciones recientes
    public ?string $cli_chronic_medications = null;  // medicamentos
    public ?string $cli_allergies = null;            // alergias
    // Lesión opcional → clinical_events (type=lesion)
    public ?string $lesion_description = null;
    public ?string $lesion_body_region = null;
    public ?string $lesion_severity = null;          // leve|moderada|grave|null
    // Medición inicial opcional → clinical_measurements (source=admission)
    public ?float $meas_weight_kg = null;
    public ?int   $meas_height_cm = null;
    public ?float $meas_waist_cm = null;
    public ?float $meas_hip_cm = null;
    public ?float $meas_chest_cm = null;
    public ?float $meas_arm_right_cm = null;
    public ?float $meas_arm_left_cm = null;
    public ?float $meas_thigh_right_cm = null;
    public ?float $meas_thigh_left_cm = null;

    // Step 4: Modules
    #[Url] public array $modules = []; // gym, kine, estetic (pre-seleccionable por URL)

    // Step 5: GYM
    public ?int $plan_id = null;
    public ?string $subscription_start = null;
    public ?string $subscription_end = null;
    // Pago de la suscripción
    public string $gym_payment_choice = 'pending'; // pending | now
    public ?float $gym_payment_amount = null;
    public ?string $gym_payment_date = null;
    public string $gym_payment_type = 'efectivo';
    public ?string $gym_payment_notes = null;

    // Step 6: Kine
    public ?string $health_insurance = null;
    public ?string $insurance_number = null;
    public ?string $kine_diagnostico = null;
    public ?string $kine_plan = null;
    public int $kine_sessions_total = 10;
    public float $kine_cost_session = 8000;
    public ?int $kine_professional_id = null;

    // Step 7: Estetic
    public ?string $skin_type = null;
    #[Url] public ?int $estetic_tipo_id = null;
    public ?string $estetic_zona = null;
    public int $estetic_sessions_total = 6;
    public int $estetic_intervalo_dias = 7;
    public ?float $estetic_costo_sesion = null;
    public ?int $estetic_professional_id = null;
    public string $estetic_start_time = '10:00';
    public ?string $estetic_start_date = null;
    public bool $estetic_protocol_locked = false;

    // Pago estética
    public string $estetic_payment_choice = 'pending'; // pending|full|installments
    public int $estetic_cuotas = 1;
    public ?float $estetic_abono_inicial = null;
    public string $estetic_payment_method = 'efectivo';

    // Step 8: Schedule
    public ?string $first_appointment_date = null;
    public ?string $first_appointment_time = '10:00';

    public function mount(): void
    {
        $this->subscription_start = now()->format('Y-m-d');
        $this->subscription_end   = now()->addDays(30)->format('Y-m-d');
        $this->first_appointment_date = now()->addDay()->format('Y-m-d');
        $this->gym_payment_date = now()->format('Y-m-d');
        $this->estetic_start_date = now()->addDay()->format('Y-m-d');

        // Si llegó con protocolo pre-seleccionado (?estetic_tipo_id=N), pre-llenar desde la plantilla
        if ($this->estetic_tipo_id) {
            $tipo = TipoTratamiento::find($this->estetic_tipo_id);
            if ($tipo) {
                $this->estetic_protocol_locked = true;
                $this->estetic_sessions_total = $tipo->sesiones_recomendadas ?: 6;
                $this->estetic_intervalo_dias = $tipo->intervalo_dias ?: 7;
                $this->estetic_costo_sesion   = (float) $tipo->precio_base;
                $this->estetic_zona           = $tipo->nombre;
                // Forzar módulo estética
                if (!in_array('estetic', $this->modules)) {
                    $this->modules[] = 'estetic';
                }
            }
        }
    }

    /**
     * Al cambiar la fecha de inicio, recalcula automáticamente la fecha de término
     * sumando 30 días. La asistente puede sobrescribir manualmente si el plan es distinto.
     */
    public function updatedSubscriptionStart($value): void
    {
        if ($value) {
            $this->subscription_end = Carbon::parse($value)->addDays(30)->format('Y-m-d');
        }
    }

    /**
     * Al elegir un protocolo estético (manualmente o desde URL), pre-llena
     * sesiones, intervalo y costo por sesión desde la plantilla del catálogo.
     */
    public function updatedEsteticTipoId($value): void
    {
        if (!$value) return;
        $tipo = TipoTratamiento::find($value);
        if (!$tipo) return;
        $this->estetic_sessions_total = $tipo->sesiones_recomendadas ?: $this->estetic_sessions_total;
        $this->estetic_intervalo_dias = $tipo->intervalo_dias ?: $this->estetic_intervalo_dias;
        $this->estetic_costo_sesion   = (float) $tipo->precio_base;
        if (empty($this->estetic_zona)) {
            $this->estetic_zona = $tipo->nombre;
        }
    }

    /**
     * Auto-llena el monto del pago con el precio del plan al seleccionarlo.
     */
    public function updatedPlanId($id): void
    {
        if ($id && $plan = \App\Models\Plan::find($id)) {
            $this->gym_payment_amount = (float) $plan->price;
        }
    }

    public function updatedRut($value): void
    {
        $clean = RutHelper::clean($value);
        // Limitar a 9 caracteres (8 dígitos + DV)
        if (strlen($clean) > 9) {
            $clean = substr($clean, 0, 9);
        }

        if (strlen($clean) >= 2) {
            $this->rut = RutHelper::format($clean);
            $this->rutValid = RutHelper::validate($clean);
        } else {
            $this->rut = $clean;
            $this->rutValid = null;
        }

        // Si el RUT es válido, buscar persona existente y autocompletar
        if ($this->rutValid) {
            $person = Person::where('rut', $this->rut)->first();
            if ($person) {
                $this->fillFromPerson($person);
                $this->resetErrorBag('rut');
            } else {
                // Si previamente se autocompletó con otra persona, liberar
                if ($this->person_locked) {
                    $this->clearPersonFields();
                }
                $this->resetErrorBag('rut');
            }
        } else {
            if ($this->person_locked) {
                $this->clearPersonFields();
            }
        }
    }

    protected function fillFromPerson(Person $person): void
    {
        $this->existing_person_id = $person->id;
        $this->person_locked = true;
        $this->first_name = (string) $person->first_name;
        $this->last_name  = (string) $person->last_name;
        $this->nickname   = $person->nickname;
        $this->birth_date = $person->birth_date?->format('Y-m-d');
        $this->gender     = (string) ($person->gender ?? 'M');
        $this->phone      = $this->stripCountryCode($person->phone);
        $this->email      = $person->email;
        $this->address    = $person->address;
        $this->poblacion  = $person->poblacion;
        $this->comuna     = $person->comuna;
        $this->emergency_contact_name         = $person->emergency_contact_name;
        $this->emergency_contact_phone        = $this->stripCountryCode($person->emergency_contact_phone);
        $this->emergency_contact_relationship = $person->emergency_contact_relationship;

        // Pre-seleccionar módulos ya existentes
        $preset = [];
        if ($person->gymProfile)     $preset[] = 'gym';
        if ($person->kineProfile)    $preset[] = 'kine';
        if ($person->esteticProfile) $preset[] = 'estetic';
        // Pasa al paso 2 sugerido; dejamos que el usuario marque nuevos módulos,
        // pero sin duplicar los existentes.
        $this->modules = []; // se selecciona qué nuevo módulo sumar

        // Si tiene perfil kine, precargar datos del perfil
        if ($person->kineProfile) {
            $this->health_insurance = $person->kineProfile->health_insurance;
            $this->insurance_number = $person->kineProfile->insurance_number;
        }
        if ($person->esteticProfile) {
            $this->skin_type = $person->esteticProfile->skin_type;
        }

        // Pre-cargar antecedentes clínicos si existen
        $person->load('clinicalProfile');
        if ($person->clinicalProfile) {
            $this->cli_chronic_diseases    = $person->clinicalProfile->chronic_diseases;
            $this->cli_surgical_history    = $person->clinicalProfile->surgical_history;
            $this->cli_chronic_medications = $person->clinicalProfile->chronic_medications;
            $this->cli_allergies           = $person->clinicalProfile->allergies;
        }
    }

    public function clearPersonFields(): void
    {
        $this->existing_person_id = null;
        $this->person_locked = false;
        $this->first_name = '';
        $this->last_name = '';
        $this->nickname = null;
        $this->birth_date = null;
        $this->gender = 'M';
        $this->phone = null;
        $this->email = null;
        $this->address = null;
        $this->poblacion = null;
        $this->comuna = null;
        $this->emergency_contact_name = null;
        $this->emergency_contact_phone = null;
        $this->emergency_contact_relationship = null;
    }

    /**
     * Devuelve el teléfono sin el prefijo +56/56 para mostrar en el input.
     */
    protected function stripCountryCode(?string $phone): ?string
    {
        if (!$phone) return null;
        $clean = preg_replace('/\D/', '', $phone);
        if (str_starts_with($clean, '56') && strlen($clean) >= 10) {
            return substr($clean, 2);
        }
        return $clean !== '' ? $clean : null;
    }

    /**
     * Devuelve el teléfono con prefijo +56 para guardar en BD.
     */
    protected function withCountryCode(?string $phone): ?string
    {
        if (!$phone) return null;
        $clean = preg_replace('/\D/', '', $phone);
        if ($clean === '') return null;
        if (str_starts_with($clean, '56')) return '+' . $clean;
        return '+56' . $clean;
    }

    public function next(): void
    {
        $this->validateStep();

        // Skip steps not applicable based on selected modules
        $this->step++;
        while ($this->shouldSkipStep($this->step) && $this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function back(): void
    {
        $this->step--;
        while ($this->shouldSkipStep($this->step) && $this->step > 1) {
            $this->step--;
        }
    }

    protected function shouldSkipStep(int $step): bool
    {
        return match ($step) {
            5 => !in_array('gym', $this->modules),
            6 => !in_array('kine', $this->modules),
            7 => !in_array('estetic', $this->modules),
            8 => !array_intersect(['kine', 'estetic'], $this->modules),
            default => false,
        };
    }

    protected function validateStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'rut' => ['required', 'string', 'max:20'],
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'birth_date' => ['nullable', 'date'],
                'gender' => ['required', 'in:M,F,O'],
                'email' => ['nullable', 'email'],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string', 'max:255'],
                'poblacion' => ['nullable', 'string', 'max:150'],
                'comuna' => ['nullable', 'string', 'max:100'],
            ]),
            2 => $this->validate([
                'emergency_contact_name'  => ['nullable', 'string', 'max:150'],
                'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
                'emergency_contact_relationship' => ['nullable', 'string', 'max:50'],
            ]),
            3 => $this->validate([
                'cli_chronic_diseases'    => ['nullable', 'string', 'max:2000'],
                'cli_surgical_history'    => ['nullable', 'string', 'max:2000'],
                'cli_chronic_medications' => ['nullable', 'string', 'max:2000'],
                'cli_allergies'           => ['nullable', 'string', 'max:2000'],
                'lesion_description'      => ['nullable', 'string', 'max:255'],
                'lesion_body_region'      => ['nullable', 'string', 'max:150'],
                'lesion_severity'         => ['nullable', 'in:leve,moderada,grave'],
                'meas_weight_kg'   => ['nullable', 'numeric', 'min:1', 'max:500'],
                'meas_height_cm'   => ['nullable', 'integer', 'min:100', 'max:250'],
                'meas_waist_cm'    => ['nullable', 'numeric', 'min:30', 'max:250'],
                'meas_hip_cm'      => ['nullable', 'numeric', 'min:30', 'max:250'],
                'meas_chest_cm'    => ['nullable', 'numeric', 'min:30', 'max:250'],
                'meas_arm_right_cm'=> ['nullable', 'numeric', 'min:5', 'max:80'],
                'meas_arm_left_cm' => ['nullable', 'numeric', 'min:5', 'max:80'],
                'meas_thigh_right_cm' => ['nullable', 'numeric', 'min:10', 'max:120'],
                'meas_thigh_left_cm'  => ['nullable', 'numeric', 'min:10', 'max:120'],
            ]),
            4 => $this->validate([
                'modules' => ['required', 'array', 'min:1'],
                'modules.*' => ['in:gym,kine,estetic'],
            ], [], ['modules' => 'módulos']),
            5 => (function () {
                $this->validate([
                    'plan_id' => ['required', 'exists:plans,id'],
                    'subscription_start' => ['required', 'date'],
                    'subscription_end'   => ['required', 'date', 'after_or_equal:subscription_start'],
                    'gym_payment_choice' => ['required', 'in:pending,now'],
                ]);
                if ($this->gym_payment_choice === 'now') {
                    $this->validate([
                        'gym_payment_amount' => ['required', 'numeric', 'min:0'],
                        'gym_payment_date'   => ['required', 'date'],
                        'gym_payment_type'   => ['required', 'string', 'max:50'],
                        'gym_payment_notes'  => ['nullable', 'string', 'max:500'],
                    ], [], [
                        'gym_payment_amount' => 'monto', 'gym_payment_date' => 'fecha de pago',
                        'gym_payment_type'   => 'método',
                    ]);
                }
                return true;
            })(),
            6 => $this->validate([
                'kine_diagnostico' => ['required', 'string', 'max:255'],
                'kine_sessions_total' => ['required', 'integer', 'min:1'],
                'kine_cost_session' => ['required', 'numeric', 'min:0'],
            ]),
            7 => $this->validate([
                'estetic_tipo_id' => ['required', 'exists:este_tipos_tratamientos,id'],
                'estetic_zona' => ['required', 'string', 'max:255'],
                'estetic_sessions_total' => ['required', 'integer', 'min:1', 'max:50'],
                'estetic_intervalo_dias' => ['required', 'integer', 'min:1', 'max:365'],
                'estetic_costo_sesion'   => ['required', 'numeric', 'min:0'],
                'estetic_start_date'     => ['required', 'date'],
                'estetic_start_time'     => ['required'],
                'estetic_payment_choice' => ['required', 'in:pending,full,installments'],
            ]),
            default => null,
        };

        if ($this->step === 1) {
            if (!RutHelper::validate($this->rut)) {
                $this->addError('rut', 'RUT inválido. Verifica el dígito verificador.');
                $this->validate(['rut' => ['required']]);
            }
            // Si es existente, aseguramos cargar los datos más recientes
            if (!$this->existing_person_id) {
                $found = Person::where('rut', RutHelper::format($this->rut))->first();
                if ($found) $this->fillFromPerson($found);
            }
        }
    }

    public function submit()
    {
        $this->validateStep();

        $person = DB::transaction(function () {
            $data = [
                'rut' => RutHelper::format($this->rut),
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'nickname' => $this->nickname,
                'birth_date' => $this->birth_date,
                'gender' => $this->gender,
                'phone' => $this->withCountryCode($this->phone),
                'email' => $this->email,
                'address' => $this->address,
                'poblacion' => $this->poblacion,
                'comuna' => $this->comuna,
                'emergency_contact_name'  => $this->emergency_contact_name,
                'emergency_contact_phone' => $this->withCountryCode($this->emergency_contact_phone),
                'emergency_contact_relationship' => $this->emergency_contact_relationship,
            ];

            if ($this->existing_person_id) {
                $person = Person::findOrFail($this->existing_person_id);
                $person->update($data);
            } else {
                $person = Person::create($data);
            }

            // Antecedentes clínicos → clinical_profile
            $hasClinical = $this->cli_chronic_diseases || $this->cli_surgical_history
                || $this->cli_chronic_medications || $this->cli_allergies;
            if ($hasClinical) {
                \App\Models\ClinicalProfile::updateOrCreate(
                    ['person_id' => $person->id],
                    [
                        'chronic_diseases'    => $this->cli_chronic_diseases,
                        'surgical_history'    => $this->cli_surgical_history,
                        'chronic_medications' => $this->cli_chronic_medications,
                        'allergies'           => $this->cli_allergies,
                        'updated_by'          => auth()->id(),
                    ]
                );
            }

            // Lesión activa → clinical_events (si hay descripción)
            if ($this->lesion_description) {
                \App\Models\ClinicalEvent::create([
                    'person_id'   => $person->id,
                    'type'        => 'lesion',
                    'event_date'  => now()->format('Y-m-d'),
                    'description' => $this->lesion_description,
                    'severity'    => $this->lesion_severity ?: null,
                    'status'      => 'activo',
                    'body_region' => $this->lesion_body_region,
                    'recorded_by' => auth()->id(),
                ]);
            }

            // Medición inicial → clinical_measurements (si al menos hay peso o alguna circunferencia)
            $hasMeas = $this->meas_weight_kg || $this->meas_waist_cm || $this->meas_hip_cm
                || $this->meas_chest_cm || $this->meas_arm_right_cm || $this->meas_arm_left_cm
                || $this->meas_thigh_right_cm || $this->meas_thigh_left_cm;
            if ($hasMeas) {
                \App\Models\ClinicalMeasurement::create([
                    'person_id'        => $person->id,
                    'measured_at'      => now(),
                    'source'           => 'admission',
                    'weight_kg'        => $this->meas_weight_kg,
                    'height_cm'        => $this->meas_height_cm,
                    'bmi'              => \App\Models\ClinicalMeasurement::computeBmi(
                        $this->meas_weight_kg ? (float) $this->meas_weight_kg : null,
                        $this->meas_height_cm ? (int) $this->meas_height_cm : null
                    ),
                    'waist_cm'         => $this->meas_waist_cm,
                    'hip_cm'           => $this->meas_hip_cm,
                    'chest_cm'         => $this->meas_chest_cm,
                    'arm_right_cm'     => $this->meas_arm_right_cm,
                    'arm_left_cm'      => $this->meas_arm_left_cm,
                    'thigh_right_cm'   => $this->meas_thigh_right_cm,
                    'thigh_left_cm'    => $this->meas_thigh_left_cm,
                    'whr'              => \App\Models\ClinicalMeasurement::computeWhr(
                        $this->meas_waist_cm ? (float) $this->meas_waist_cm : null,
                        $this->meas_hip_cm ? (float) $this->meas_hip_cm : null
                    ),
                    'notes'            => 'Medición inicial al admitir',
                    'recorded_by'      => auth()->id(),
                ]);
            }

            if (in_array('gym', $this->modules)) {
                GymProfile::firstOrCreate(
                    ['person_id' => $person->id],
                    ['registered_at' => now(), 'active' => true]
                );

                $subscription = Subscription::create([
                    'person_id' => $person->id,
                    'plan_id' => $this->plan_id,
                    'start_date' => $this->subscription_start,
                    'end_date'   => $this->subscription_end,
                    'status' => 'active',
                ]);

                // Pago de la suscripción
                $plan = \App\Models\Plan::find($this->plan_id);
                if ($this->gym_payment_choice === 'now') {
                    \App\Models\Payment::create([
                        'person_id'       => $person->id,
                        'subscription_id' => $subscription->id,
                        'amount'          => $this->gym_payment_amount,
                        'payment_date'    => $this->gym_payment_date,
                        'payment_type'    => $this->gym_payment_type,
                        'status'          => 'pagado',
                        'notes'           => $this->gym_payment_notes,
                    ]);
                } else {
                    \App\Models\Payment::create([
                        'person_id'       => $person->id,
                        'subscription_id' => $subscription->id,
                        'amount'          => $plan?->price ?? 0,
                        'payment_date'    => $this->subscription_start,
                        'payment_type'    => 'pendiente',
                        'status'          => 'pendiente',
                        'notes'           => 'Pago generado automáticamente al admitir',
                    ]);
                }
            }

            $appointmentAt = $this->first_appointment_date
                ? Carbon::parse("{$this->first_appointment_date} {$this->first_appointment_time}")
                : null;

            if (in_array('kine', $this->modules)) {
                $kineProfile = KineProfile::firstOrCreate(
                    ['person_id' => $person->id],
                    [
                        'health_insurance' => $this->health_insurance,
                        'insurance_number' => $this->insurance_number,
                        'active' => true,
                    ]
                );
                // Si ya existía, actualizamos datos recibidos
                $kineProfile->fill(array_filter([
                    'health_insurance' => $this->health_insurance,
                    'insurance_number' => $this->insurance_number,
                ]))->save();

                $treatment = KineTreatment::create([
                    'kine_profile_id' => $kineProfile->id,
                    'professional_id' => $this->kine_professional_id,
                    'diagnostico' => $this->kine_diagnostico,
                    'plan' => $this->kine_plan,
                    'fecha_inicio' => now(),
                    'sesiones_totales' => $this->kine_sessions_total,
                    'sesiones_realizadas' => 0,
                    'costo_sesion' => $this->kine_cost_session,
                    'costo_total' => $this->kine_cost_session * $this->kine_sessions_total,
                    'estado' => 'activo',
                ]);

                if ($appointmentAt) {
                    KineAppointment::create([
                        'kine_profile_id' => $kineProfile->id,
                        'tratamiento_id' => $treatment->id,
                        'professional_id' => $this->kine_professional_id,
                        'inicio' => $appointmentAt,
                        'fin' => (clone $appointmentAt)->addMinutes(45),
                        'estado' => 'pendiente',
                        'motivo' => 'Primera sesión kinésica',
                    ]);
                }
            }

            if (in_array('estetic', $this->modules)) {
                $esteticProfile = EsteticProfile::firstOrCreate(
                    ['person_id' => $person->id],
                    [
                        'skin_type' => $this->skin_type,
                        'active' => true,
                    ]
                );
                $esteticProfile->fill(array_filter([
                    'skin_type' => $this->skin_type,
                ]))->save();

                $tipo  = TipoTratamiento::find($this->estetic_tipo_id);
                $costoSesion = $this->estetic_costo_sesion ?? (float) ($tipo?->precio_base ?? 0);
                $total = $costoSesion * $this->estetic_sessions_total;

                $treatment = EsteticTreatment::create([
                    'estetic_profile_id' => $esteticProfile->id,
                    'tipo_tratamiento_id' => $this->estetic_tipo_id,
                    'professional_id' => $this->estetic_professional_id,
                    'descripcion_plan' => "Plan basado en protocolo: {$tipo?->nombre}",
                    'zona_tratada' => $this->estetic_zona,
                    'fecha_inicio' => $this->estetic_start_date,
                    'fecha_fin' => Carbon::parse($this->estetic_start_date)
                        ->addDays(($this->estetic_sessions_total - 1) * $this->estetic_intervalo_dias)
                        ->format('Y-m-d'),
                    'sesiones_totales' => $this->estetic_sessions_total,
                    'sesiones_realizadas' => 0,
                    'costo_sesion' => $costoSesion,
                    'costo_total' => $total,
                    'estado' => 'activo',
                    'observaciones' => $tipo?->protocolo,
                ]);

                // Generar cronograma completo de citas
                $cursor = Carbon::parse($this->estetic_start_date . ' ' . $this->estetic_start_time);
                for ($i = 0; $i < $this->estetic_sessions_total; $i++) {
                    $inicio = $cursor->copy();
                    $fin = $inicio->copy()->addMinutes($tipo?->duracion_minutos ?? 60);
                    EsteticAppointment::create([
                        'estetic_profile_id' => $esteticProfile->id,
                        'tratamiento_id'     => $treatment->id,
                        'professional_id'    => $this->estetic_professional_id,
                        'inicio'             => $inicio,
                        'fin'                => $fin,
                        'estado'             => 'pendiente',
                        'motivo'             => 'Sesión '.($i+1).'/'.$this->estetic_sessions_total.' — '.$tipo?->nombre,
                    ]);
                    $cursor->addDays($this->estetic_intervalo_dias);
                }

                // Generar pagos según modalidad
                $payBase = [
                    'estetic_profile_id' => $esteticProfile->id,
                    'tratamiento_id'     => $treatment->id,
                    'metodo'             => $this->estetic_payment_method ?: 'efectivo',
                ];

                if ($this->estetic_payment_choice === 'full') {
                    \App\Models\Estetic\Payment::create([
                        ...$payBase,
                        'fecha' => now()->format('Y-m-d'),
                        'monto' => $total,
                        'estado' => 'pagado',
                        'observaciones' => 'Pago completo del protocolo (admisión)',
                    ]);
                } elseif ($this->estetic_payment_choice === 'installments' && $this->estetic_cuotas > 1) {
                    $abono = (float) ($this->estetic_abono_inicial ?? 0);
                    $restante = $total - $abono;
                    $valorCuota = round($restante / $this->estetic_cuotas, 0);
                    if ($abono > 0) {
                        \App\Models\Estetic\Payment::create([
                            ...$payBase,
                            'fecha' => now()->format('Y-m-d'),
                            'monto' => $abono,
                            'estado' => 'pagado',
                            'observaciones' => 'Abono inicial',
                        ]);
                    }
                    $cursor = Carbon::parse($this->estetic_start_date);
                    for ($i = 1; $i <= $this->estetic_cuotas; $i++) {
                        \App\Models\Estetic\Payment::create([
                            ...$payBase,
                            'fecha' => $cursor->copy()->format('Y-m-d'),
                            'monto' => $valorCuota,
                            'estado' => 'pendiente',
                            'metodo' => 'efectivo',
                            'observaciones' => "Cuota {$i}/{$this->estetic_cuotas}",
                        ]);
                        $cursor->addDays($this->estetic_intervalo_dias);
                    }
                } else {
                    \App\Models\Estetic\Payment::create([
                        ...$payBase,
                        'fecha' => $this->estetic_start_date,
                        'monto' => $total,
                        'estado' => 'pendiente',
                        'metodo' => 'efectivo',
                        'observaciones' => 'Pago generado al aplicar el protocolo',
                    ]);
                }
            }

            return $person;
        });

        session()->flash('success', 'Admisión completada correctamente.');

        // Si vino con protocolo estético pre-cargado, redirige a la ficha estética del paciente
        if ($this->estetic_protocol_locked && in_array('estetic', $this->modules)) {
            $esteticProfile = $person->esteticProfile()->first();
            if ($esteticProfile) {
                return $this->redirectRoute('admin.estetic.patients.show', [
                    'profile' => $esteticProfile->id,
                    'tab' => 'appointments',
                ], navigate: true);
            }
        }

        return $this->redirectRoute('admin.people.show', $person, navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.admission.wizard', [
            'plans' => Plan::orderBy('name')->get(),
            'tipos' => TipoTratamiento::where('activo', true)->orderBy('nombre')->get(),
            'kineProfessionals' => Professional::kine()->where('active', true)->get(),
            'esteticProfessionals' => Professional::estetic()->where('active', true)->get(),
        ]);
    }
}
