<?php

namespace Database\Seeders;

use App\Models\GymProfile;
use App\Models\Person;
use App\Models\Plan;
use App\Models\Professional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Jonathan Rodriguez',
            'email' => 'jo.rodriguez88@gmail.com',
            'password' => bcrypt('Acer4520'),
        ]);

        // Plans
        if (Plan::count() === 0 && \Schema::hasTable('plans')) {
            Plan::create(['name' => 'Mensual', 'price' => 25000, 'duration_days' => 30]);
            Plan::create(['name' => 'Trimestral', 'price' => 70000, 'duration_days' => 90]);
        }

        // Professionals
        Professional::create([
            'user_id' => $admin->id, 'name' => 'Jonathan', 'last_name' => 'Rodríguez',
            'rut' => '16123456-7', 'module' => 'both', 'specialty' => 'Kinesiología / Estética', 'active' => true,
        ]);

        // Sample people
        $peopleData = [
            ['rut' => '15111222-3', 'first_name' => 'María',  'last_name' => 'González',  'gender' => 'F', 'birth_date' => '1985-04-12', 'phone' => '+56911110001', 'email' => 'maria.gonzalez@example.com'],
            ['rut' => '13333444-5', 'first_name' => 'Juan',   'last_name' => 'Pérez',     'gender' => 'M', 'birth_date' => '1978-11-03', 'phone' => '+56911110002', 'email' => 'juan.perez@example.com'],
            ['rut' => '17555666-9', 'first_name' => 'Sofía',  'last_name' => 'Ramírez',   'gender' => 'F', 'birth_date' => '1992-07-22', 'phone' => '+56911110003', 'email' => 'sofia.ramirez@example.com'],
            ['rut' => '12777888-K', 'first_name' => 'Carlos', 'last_name' => 'López',     'gender' => 'M', 'birth_date' => '1970-02-15', 'phone' => '+56911110004', 'email' => 'carlos.lopez@example.com'],
            ['rut' => '18999000-1', 'first_name' => 'Lucía',  'last_name' => 'Fernández', 'gender' => 'F', 'birth_date' => '1998-09-30', 'phone' => '+56911110005', 'email' => 'lucia.fernandez@example.com'],
        ];

        foreach ($peopleData as $i => $data) {
            $person = Person::create(array_merge($data, [
                'address' => 'Calle Ejemplo ' . (100 + $i),
            ]));

            GymProfile::create([
                'person_id' => $person->id,
                'registered_at' => Carbon::now()->subDays(rand(1, 180)),
                'active' => true,
            ]);
        }

        $this->call([
            KineSeeder::class,
            EsteticSeeder::class,
        ]);
    }
}
