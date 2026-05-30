<?php

namespace Database\Seeders;

use App\Models\Estetic\Payment;
use App\Models\Estetic\Sesion;
use App\Models\Estetic\TipoTratamiento;
use App\Models\Estetic\Treatment;
use App\Models\Estetic\Appointment;
use App\Models\EsteticProfile;
use App\Models\Person;
use App\Models\Professional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EsteticSeeder extends Seeder
{
    public function run(): void
    {
        $professional = Professional::estetic()->first();
        $user = User::first();

        $tipos = [
            ['nombre' => 'Limpieza facial profunda', 'duracion_minutos' => 60, 'precio_base' => 8000,  'categoria' => 'facial'],
            ['nombre' => 'Masaje facial relajante',   'duracion_minutos' => 45, 'precio_base' => 6500,  'categoria' => 'masajes'],
            ['nombre' => 'Tratamiento antiedad',       'duracion_minutos' => 60, 'precio_base' => 12000, 'categoria' => 'facial'],
            ['nombre' => 'Depilación con láser',       'duracion_minutos' => 30, 'precio_base' => 4000,  'categoria' => 'depilacion'],
            ['nombre' => 'Masaje corporal relajante',  'duracion_minutos' => 60, 'precio_base' => 7000,  'categoria' => 'masajes'],
        ];

        foreach ($tipos as $t) {
            TipoTratamiento::create(array_merge($t, ['activo' => true]));
        }

        $tiposAll = TipoTratamiento::all();
        $skinTypes = ['grasa', 'normal', 'sensible', 'seca', 'mixta'];

        $people = Person::skip(2)->take(3)->get();

        foreach ($people as $i => $person) {
            $profile = EsteticProfile::create([
                'person_id' => $person->id,
                'skin_type' => $skinTypes[$i % count($skinTypes)],
                'allergies' => $i % 3 === 0 ? 'Sensible a fragancias' : null,
                'medical_observations' => 'Sin antecedentes relevantes.',
                'active' => true,
            ]);

            $total = rand(6, 12);
            $done = rand(0, $total);
            $tipo = $tiposAll->random();
            $cost = $tipo->precio_base;

            $treatment = Treatment::create([
                'estetic_profile_id' => $profile->id,
                'tipo_tratamiento_id' => $tipo->id,
                'professional_id' => $professional?->id,
                'descripcion_plan' => "Plan de {$total} sesiones - {$tipo->nombre}",
                'zona_tratada' => $i % 2 === 0 ? 'Rostro completo' : 'Cuerpo',
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
                    'fecha' => Carbon::parse($treatment->fecha_inicio)->addDays(($n - 1) * 5),
                    'productos_utilizados' => 'Producto tipo ' . ($n % 3 + 1),
                    'resultados_observados' => "Excelente evolución sesión {$n}",
                    'duracion_real_minutos' => $tipo->duracion_minutos,
                    'estado' => 'realizada',
                ]);
            }

            $start = Carbon::now()->addDays($i + 1)->setTime(14 + $i, 0);
            Appointment::create([
                'estetic_profile_id' => $profile->id,
                'tratamiento_id' => $treatment->id,
                'professional_id' => $professional?->id,
                'inicio' => $start,
                'fin' => (clone $start)->addMinutes($tipo->duracion_minutos),
                'estado' => 'confirmado',
                'motivo' => "Sesión de {$tipo->nombre}",
            ]);

            for ($j = 0; $j < rand(1, 3); $j++) {
                Payment::create([
                    'estetic_profile_id' => $profile->id,
                    'tratamiento_id' => $treatment->id,
                    'fecha' => Carbon::now()->subDays(rand(0, 30)),
                    'monto' => $cost,
                    'metodo' => ['efectivo', 'transferencia', 'mercadopago'][rand(0, 2)],
                    'estado' => 'pagado',
                    'comprobante' => 'REC-E-' . str_pad($profile->id . $j, 5, '0', STR_PAD_LEFT),
                    'registrado_por' => $user?->id,
                ]);
            }
        }
    }
}
