<?php

namespace Database\Seeders;

use App\Models\Estetic\TipoTratamiento;
use Illuminate\Database\Seeder;

class EsteticCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Estructura: nombre, categoria, sesiones, intervalo_dias, duracion, precio_total, protocolo, color
        $reductivos = [
            [
                'Pack Reducción Lipo', 10, 7, 60, 180000,
                '5 sesiones de Lipoláser + 5 sesiones de Radiofrecuencia · Presoterapia incluida en cada sesión',
                '#a21caf', // fuchsia-700
            ],
            [
                'Pack Reafirmante y Activación de Colágeno', 10, 7, 60, 180000,
                '10 sesiones de Radiofrecuencia · Presoterapia incluida en cada sesión',
                '#be185d', // pink-700
            ],
            [
                'Pack Adiós Flacidez', 10, 7, 60, 185000,
                '3 sesiones de Lipoláser + 4 sesiones de Radiofrecuencia + 3 sesiones de Cavitación · Presoterapia incluida',
                '#c026d3', // fuchsia-600
            ],
            [
                'Pack Ponte Guapa', 15, 7, 60, 270000,
                '2 sesiones de Lipoláser + 5 sesiones de Radiofrecuencia + 5 sesiones de Cavitación + 3 sesiones de Onda Rusa · Presoterapia incluida',
                '#db2777', // pink-600
            ],
            [
                'Pack Me lo Merezco', 10, 7, 60, 185000,
                '5 sesiones de Cavitación + 5 sesiones de Radiofrecuencia · Presoterapia incluida',
                '#e11d48', // rose-600
            ],
            [
                'Pack Reducción de Papada', 7, 7, 60, 185000,
                '7 sesiones de Radiofrecuencia · Drenaje linfático incluido en cada sesión',
                '#9333ea', // purple-600
            ],
            [
                'Pack Eliminación de Grasa', 10, 7, 60, 185000,
                '10 sesiones de Cavitación · Presoterapia incluida en cada sesión',
                '#ec4899', // pink-500
            ],
            [
                'Pack Levantamiento de Glúteo y Anticelulítico', 10, 7, 60, 175000,
                '10 sesiones de Onda Rusa + Maderoterapia + Copa',
                '#a855f7', // purple-500
            ],
            [
                'Pack Presoterapia', 1, 7, 45, 25000,
                'Sesión única: 45 minutos de Presoterapia en 2 zonas a elección (Abdomen / Piernas / Brazos)',
                '#f472b6', // pink-400
            ],
        ];

        foreach ($reductivos as [$nombre, $sesiones, $intervalo, $duracion, $precio_total, $protocolo, $color]) {
            $precio_sesion = (int) round($precio_total / $sesiones);
            TipoTratamiento::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion'            => null,
                    'duracion_minutos'       => $duracion,
                    'precio_base'            => $precio_sesion,
                    'sesiones_recomendadas'  => $sesiones,
                    'intervalo_dias'         => $intervalo,
                    'protocolo'              => $protocolo,
                    'color'                  => $color,
                    'categoria'              => 'reductivos',
                    'activo'                 => true,
                ]
            );
        }

        // Masajes terapéuticos
        $masajes = [
            ['Masaje Relajante',          21000, 'Masaje de cuerpo completo enfocado en relajación muscular y reducción del estrés.', '#f59e0b'],
            ['Masaje Descontracturante',  23500, 'Masaje terapéutico para liberar contracturas y aliviar dolor muscular.',           '#d97706'],
        ];

        foreach ($masajes as [$nombre, $precio, $protocolo, $color]) {
            TipoTratamiento::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion'            => null,
                    'duracion_minutos'       => 45,
                    'precio_base'            => $precio,
                    'sesiones_recomendadas'  => 1,
                    'intervalo_dias'         => 7,
                    'protocolo'              => $protocolo,
                    'color'                  => $color,
                    'categoria'              => 'masajes',
                    'activo'                 => true,
                ]
            );
        }

        $this->command->info('Catálogo de Estética cargado: 9 packs reductivos + 2 masajes = 11 servicios.');
    }
}
