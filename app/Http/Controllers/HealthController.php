<?php

namespace App\Http\Controllers;

use App\Services\CulqiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Diagnóstico de la aplicación (BD + Culqi). Separado de PaymentController
 * porque es una preocupación distinta (operaciones/health, no pagos).
 */
class HealthController extends Controller
{
    public function __construct(private CulqiService $culqi) {}

    public function index(): JsonResponse
    {
        $connection = config('database.default');
        $db         = config("database.connections.{$connection}");
        $debug      = (bool) config('app.debug');

        $database = ['ok' => false, 'connection' => $connection, 'message' => null];

        // Los detalles de conexión (host/usuario) solo se exponen en modo debug.
        if ($debug) {
            $database += [
                'host'     => $db['host'] ?? null,
                'port'     => $db['port'] ?? null,
                'database' => $db['database'] ?? null,
                'username' => $db['username'] ?? null,
            ];
        }

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $database['ok']      = true;
            $database['message'] = 'Conexión exitosa';
        } catch (\Throwable $e) {
            $database['message'] = $this->dbErrorHint($e->getMessage());
            if ($debug) {
                $database['error_raw'] = $e->getMessage();
            }
        }

        $culqi = $this->culqi->ping();

        return response()->json([
            'status'    => ($database['ok'] && $culqi['ok']) ? 'ok' : 'degraded',
            'database'  => $database,
            'culqi'     => $culqi,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /** Traduce errores comunes de MySQL a una pista accionable. */
    private function dbErrorHint(string $error): string
    {
        return match (true) {
            str_contains($error, 'Access denied')              => 'Usuario o contraseña incorrectos, o el usuario no está asignado a la base de datos.',
            str_contains($error, 'Unknown database')           => 'La base de datos no existe con ese nombre.',
            str_contains($error, 'timed out'),
            str_contains($error, 'Connection timed out')        => 'Timeout: el servidor no responde. Probablemente el puerto 3306 está bloqueado o falta autorizar tu IP en Remote MySQL.',
            str_contains($error, 'Connection refused')          => 'Conexión rechazada: el host/puerto es incorrecto o el servidor no acepta conexiones remotas.',
            str_contains($error, 'No such host'),
            str_contains($error, 'getaddrinfo'),
            str_contains($error, 'name or service not known')   => 'No se pudo resolver el host. Verifica DB_HOST.',
            str_contains($error, "Host '")                      => 'Tu IP NO está autorizada en el servidor. Habilita Remote MySQL en cPanel con tu IP pública.',
            default                                             => 'No se pudo conectar a la base de datos.',
        };
    }
}
