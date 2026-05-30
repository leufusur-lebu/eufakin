<?php

namespace Database\Seeders;

use App\Models\Kine\Payment;
use App\Models\Kine\Sesion;
use App\Models\Kine\Treatment;
use App\Models\Kine\Appointment;
use App\Models\KineProfile;
use App\Models\Person;
use App\Models\Professional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KineSeeder extends Seeder
{
    public function run(): void
    {
        $professional = Professional::kine()->first();
        $user = User::first();
        $people = Person::take(3)->get();

        $diagnosticos = [
            'Lumbalgia mecánica',
            'Tendinopatía del supraespinoso',
            'Esguince de tobillo grado II',
        ];

        foreach ($people as $i => $person) {
            $profile = KineProfile::create([
                'person_id' => $person->id,
                'health_insurance' => $i % 2 === 0 ? 'Fonasa' : 'Isapre Colmena',
                'insurance_number' => '000' . (1000 + $i),
                'background' => 'Sin antecedentes relevantes.',
                'observations' => null,
                'active' => true,
            ]);

            $total = rand(8, 20);
            $done = rand(0, $total);
            $cost = 8000;

            $treatment = Treatment::create([
                'kine_profile_id' => $profile->id,
                'professional_id' => $professional?->id,
                'diagnostico' => $diagnosticos[$i],
                'plan' => "Plan de rehabilitación de {$total} sesiones, 2x por semana.",
                'fecha_inicio' => Carbon::now()->subDays(rand(5, 60)),
                'sesiones_totales' => $total,
                'sesiones_realizadas' => $done,
                'costo_sesion' => $cost,
                'costo_total' => $cost * $total,
                'estado' => $done >= $total ? 'finalizado' : 'activo',
            ]);

            for ($n = 1; $n <= $done; $n++) {
                Sesion::create([
                    'tratamiento_id' => $treatment->id,
                    'numero_sesion' => $n,
                    'fecha' => Carbon::parse($treatment->fecha_inicio)->addDays(($n - 1) * 3),
                    'escala_dolor' => rand(1, 9),
                    'evolucion' => "Mejoría progresiva sesión {$n}.",
                    'ejercicios' => 'Movilidad + fortalecimiento.',
                    'estado' => 'realizada',
                ]);
            }

            $start = Carbon::now()->addDays($i + 1)->setTime(9 + $i, 0);
            Appointment::create([
                'kine_profile_id' => $profile->id,
                'tratamiento_id' => $treatment->id,
                'professional_id' => $professional?->id,
                'inicio' => $start,
                'fin' => (clone $start)->addMinutes(45),
                'estado' => 'confirmado',
                'motivo' => 'Sesión kinésica',
            ]);

            for ($j = 0; $j < rand(1, 3); $j++) {
                Payment::create([
                    'kine_profile_id' => $profile->id,
                    'tratamiento_id' => $treatment->id,
                    'fecha' => Carbon::now()->subDays(rand(0, 30)),
                    'monto' => $cost,
                    'metodo' => ['efectivo', 'transferencia', 'mercadopago'][rand(0, 2)],
                    'estado' => 'pagado',
                    'comprobante' => 'REC-K-' . str_pad($profile->id . $j, 6, '0', STR_PAD_LEFT),
                    'registrado_por' => $user?->id,
                ]);
            }
        }
    }
}
