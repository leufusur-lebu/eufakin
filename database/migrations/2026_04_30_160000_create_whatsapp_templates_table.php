<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique(); // appointment_reminder, payment_overdue, etc.
            $table->string('name', 100);
            $table->text('body');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Plantillas por defecto
        \DB::table('whatsapp_templates')->insert([
            [
                'key'  => 'appointment_reminder',
                'name' => 'Recordatorio de cita (24h antes)',
                'body' => "Hola {nombre}, te recordamos tu cita de {servicio} el {fecha} a las {hora}.\n\nPor favor responde *Confirmo* para confirmar tu asistencia o *Cancelar* si necesitas reagendar.\n\n¡Te esperamos!",
                'active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'key'  => 'appointment_confirmation',
                'name' => 'Confirmación al agendar',
                'body' => "Hola {nombre}, confirmamos tu cita de {servicio} para el {fecha} a las {hora}.\n\n¡Nos vemos pronto!",
                'active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'key'  => 'payment_overdue',
                'name' => 'Aviso pago atrasado',
                'body' => "Hola {nombre}, te recordamos que tienes un saldo pendiente de \${monto} por concepto de {concepto}.\n\nPodemos coordinar el pago cuando te quede cómodo. ¡Saludos!",
                'active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'key'  => 'session_thanks',
                'name' => 'Agradecimiento post-sesión',
                'body' => "Hola {nombre}, gracias por confiar en nosotros para tu sesión de hoy.\n\nRecuerda seguir las indicaciones que te dimos. Cualquier consulta nos escribes. ¡Saludos!",
                'active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'key'  => 'subscription_renewal',
                'name' => 'Recordatorio renovación GYM',
                'body' => "Hola {nombre}, tu suscripción al gimnasio vence el {fecha}.\n\n¿Te gustaría renovarla? Cualquier consulta avísanos.",
                'active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
