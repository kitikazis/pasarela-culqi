<?php

namespace App\Support;

use App\Services\CulqiService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Renderiza el check de conexión (BD + Culqi) en la terminal.
 * Lo usan tanto el comando `culqi:check` como el arranque de `php artisan serve`.
 */
class ConnectionCheck
{
    public static function render(OutputInterface $o): bool
    {
        $allOk = true;

        $o->writeln('');
        $o->writeln('  <fg=cyan;options=bold>🔌 Check de conexión — Pasarela Culqi</>');
        $o->writeln('  <fg=gray>'.now()->format('Y-m-d H:i:s').'</>');
        $o->writeln('');

        // ── Base de datos ───────────────────────────────────────
        $connection = config('database.default');
        $db         = config("database.connections.{$connection}");

        $o->writeln('  <options=bold>Base de datos</>');
        $o->writeln("    Host:     {$db['host']}:{$db['port']}");
        $o->writeln("    BD:       {$db['database']}");
        $o->writeln("    Usuario:  {$db['username']}");

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $o->writeln('    Estado:   <fg=green;options=bold>✔ CONECTADO</>');
        } catch (\Throwable $e) {
            $allOk = false;
            $o->writeln('    Estado:   <fg=red;options=bold>✘ FALLÓ</>');
            $o->writeln('    <fg=red>'.self::shorten($e->getMessage()).'</>');
        }

        $o->writeln('');

        // ── Culqi ───────────────────────────────────────────────
        $o->writeln('  <options=bold>Culqi API</>');
        $o->writeln('    Base URL: '.config('culqi.base_url'));
        $o->writeln('    Pública:  '.self::mask(config('culqi.public_key')));
        $o->writeln('    Secreta:  '.self::mask(config('culqi.secret_key')));
        $o->writeln('    RSA:      '.(config('culqi.rsa_id') ? '<fg=yellow>activa</>' : '<fg=gray>desactivada (sandbox)</>'));

        try {
            $ping = app(CulqiService::class)->ping();
        } catch (\Throwable $e) {
            $ping = ['ok' => false, 'message' => self::shorten($e->getMessage())];
        }

        if ($ping['ok']) {
            $o->writeln('    Estado:   <fg=green;options=bold>✔ CREDENCIALES VÁLIDAS</>');
        } else {
            $allOk = false;
            $o->writeln('    Estado:   <fg=red;options=bold>✘ FALLÓ</> <fg=red>('.$ping['message'].')</>');
        }

        $o->writeln('');

        // ── Resumen ─────────────────────────────────────────────
        if ($allOk) {
            $o->writeln('  <bg=green;fg=black;options=bold> TODO OK ✔ </> Listo para procesar pagos.');
        } else {
            $o->writeln('  <bg=red;fg=white;options=bold> HAY PROBLEMAS ✘ </> Revisa los errores de arriba.');
        }
        $o->writeln('');

        return $allOk;
    }

    private static function mask(?string $key): string
    {
        if (! $key) {
            return '<fg=red>(no configurada)</>';
        }
        if (strlen($key) <= 12) {
            return substr($key, 0, 4).'****';
        }
        return substr($key, 0, 8).'…'.substr($key, -4);
    }

    private static function shorten(string $msg): string
    {
        return strlen($msg) > 120 ? substr($msg, 0, 120).'…' : $msg;
    }
}
