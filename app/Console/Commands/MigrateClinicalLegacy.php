<?php

namespace App\Console\Commands;

use App\Models\ClinicalProfile;
use App\Models\EsteticProfile;
use App\Models\KineProfile;
use Illuminate\Console\Command;

class MigrateClinicalLegacy extends Command
{
    protected $signature = 'clinical:migrate-legacy {--dry-run : Mostrar qué se haría sin ejecutar}';
    protected $description = 'Migra datos clínicos legacy (KineProfile.background, EsteticProfile.allergies, etc.) a clinical_profiles unificada.';

    public function handle(): int
    {
        $this->warn('⚠️  Este comando ya fue ejecutado y los campos legacy fueron eliminados del schema.');
        $this->line('   Si necesitas re-migrar datos, primero restaura las columnas con la migración:');
        $this->line('   php artisan migrate:rollback --path=database/migrations/2026_05_08_140000_drop_legacy_clinical_fields.php');
        return self::SUCCESS;

        // ===== Lógica histórica (preservada como referencia) =====
        // phpcs:disable
        /** @phpstan-ignore-next-line */
        $dry = $this->option('dry-run');
        $kine = 0; $estetic = 0; $created = 0;

        $this->info($dry ? '🔬 Modo simulación (no se guardará nada)' : '🚚 Migrando datos clínicos legacy...');
        $this->newLine();

        // ===== KINE PROFILES =====
        KineProfile::with('person')->each(function (KineProfile $kp) use (&$kine, &$created, $dry) {
            if (!$kp->person) return;

            $hasData = $kp->background || $kp->observations;
            if (!$hasData) return;

            $cp = ClinicalProfile::firstOrNew(['person_id' => $kp->person_id]);
            $isNew = !$cp->exists;

            $changes = [];

            if ($kp->background && empty($cp->chronic_diseases)) {
                $cp->chronic_diseases = $kp->background;
                $changes[] = "background → chronic_diseases";
            }

            if ($kp->observations) {
                $newNote = '[Kine] '.$kp->observations;
                if (empty($cp->notes)) {
                    $cp->notes = $newNote;
                } elseif (!str_contains($cp->notes, $newNote)) {
                    $cp->notes = $cp->notes."\n\n".$newNote;
                }
                $changes[] = "observations → notes";
            }

            if (count($changes) > 0) {
                if (!$dry) {
                    if ($isNew) $cp->updated_by = null;
                    $cp->save();
                }
                $kine++;
                if ($isNew) $created++;
                $this->line("  ✓ {$kp->person->full_name}: ".implode(', ', $changes).($isNew ? ' (perfil creado)' : ''));
            }
        });

        // ===== ESTETIC PROFILES =====
        EsteticProfile::with('person')->each(function (EsteticProfile $ep) use (&$estetic, &$created, $dry) {
            if (!$ep->person) return;

            $hasData = $ep->allergies || $ep->medical_observations;
            if (!$hasData) return;

            $cp = ClinicalProfile::firstOrNew(['person_id' => $ep->person_id]);
            $isNew = !$cp->exists;

            $changes = [];

            if ($ep->allergies && empty($cp->allergies)) {
                $cp->allergies = $ep->allergies;
                $changes[] = "allergies";
            }

            if ($ep->medical_observations) {
                $newNote = '[Estética] '.$ep->medical_observations;
                if (empty($cp->notes)) {
                    $cp->notes = $newNote;
                } elseif (!str_contains($cp->notes, $newNote)) {
                    $cp->notes = $cp->notes."\n\n".$newNote;
                }
                $changes[] = "medical_observations → notes";
            }

            if (count($changes) > 0) {
                if (!$dry) {
                    if ($isNew) $cp->updated_by = null;
                    $cp->save();
                }
                $estetic++;
                if ($isNew) $created++;
                $this->line("  ✓ {$ep->person->full_name}: ".implode(', ', $changes).($isNew ? ' (perfil creado)' : ''));
            }
        });

        $this->newLine();
        $this->info("📊 Resumen:");
        $this->line("   Kine procesados:    $kine");
        $this->line("   Estética procesados: $estetic");
        $this->line("   Perfiles clínicos nuevos: $created");

        if ($dry) {
            $this->warn('Esto fue una simulación. Ejecuta sin --dry-run para aplicar.');
        }

        return self::SUCCESS;
    }
}
