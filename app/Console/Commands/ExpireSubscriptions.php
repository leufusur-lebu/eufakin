<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature   = 'subscriptions:expire';
    protected $description = 'Marca como expiradas las suscripciones activas cuya fecha de término ya pasó';

    public function handle(): int
    {
        $updated = Subscription::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        $this->info("$updated suscripción(es) marcada(s) como expiradas.");

        return self::SUCCESS;
    }
}
