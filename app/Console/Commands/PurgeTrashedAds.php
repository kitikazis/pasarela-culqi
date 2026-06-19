<?php

namespace App\Console\Commands;

use App\Models\Ad;
use Illuminate\Console\Command;

/**
 * Borra DEFINITIVAMENTE los anuncios que llevan más de N días en la Papelera
 * (soft-deleted). Se ejecuta a diario desde el scheduler.
 */
class PurgeTrashedAds extends Command
{
    protected $signature = 'ads:purge-trash {--days=30}';

    protected $description = 'Borra definitivamente los anuncios con más de N días en la Papelera.';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $count = Ad::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($days))
            ->forceDelete();

        $this->info("Anuncios purgados (más de {$days} días en Papelera): {$count}.");

        return self::SUCCESS;
    }
}
