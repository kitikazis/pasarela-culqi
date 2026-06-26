<?php

namespace App\Console\Commands;

use App\Models\Ad;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Métricas de salud del sistema (datos + configuración) en un vistazo.
 *
 *   php artisan health:check
 */
class HealthCheck extends Command
{
    protected $signature = 'health:check';

    protected $description = 'Muestra métricas de salud del sistema (BD, datos, config).';

    public function handle(): int
    {
        $metrics = [
            'database'         => $this->databaseOk() ? 'OK' : 'ERROR',
            'ads_total'        => Ad::count(),
            'ads_active'       => Ad::where('estado', 'active')->count(),
            'ads_inactive'     => Ad::where('estado', 'inactive')->count(),
            'ads_en_papelera'  => Ad::onlyTrashed()->count(),
            'users_total'      => User::count(),
            'tx_total'         => Transaction::count(),
            'tx_paid'          => Transaction::where('status', 'paid')->count(),
            'tx_pending'       => Transaction::where('status', 'pending')->count(),
            'cache_driver'     => config('cache.default'),
            'queue_connection' => config('queue.default'),
            'mail_mailer'      => config('mail.default'),
            'app_env'          => app()->environment(),
            'app_debug'        => config('app.debug') ? 'true' : 'false',
            'php_version'      => PHP_VERSION,
            'memory_limit'     => ini_get('memory_limit'),
        ];

        $this->line(json_encode($metrics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    private function databaseOk(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
