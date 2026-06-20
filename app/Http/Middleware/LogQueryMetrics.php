<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detector de cuellos de botella: registra en el log las peticiones lentas
 * o con muchas queries (posible N+1).
 *
 * SOLO actúa FUERA de producción (en prod añade overhead y memoria del query
 * log). Útil en local/staging para encontrar qué optimizar.
 *
 * Ver alertas:  tail -f storage/logs/laravel.log | grep "Slow request"
 */
class LogQueryMetrics
{
    /** Umbrales para considerar una petición "lenta". */
    private const MAX_MS = 500;
    private const MAX_QUERIES = 15;

    public function handle(Request $request, Closure $next): Response
    {
        // En producción no hacemos nada (sin overhead).
        if (app()->isProduction()) {
            return $next($request);
        }

        $start = microtime(true);
        DB::enableQueryLog();

        $response = $next($request);

        $durationMs = (microtime(true) - $start) * 1000;
        $queries    = DB::getQueryLog();
        $queryCount = count($queries);

        if ($durationMs > self::MAX_MS || $queryCount > self::MAX_QUERIES) {
            Log::warning('Slow request', [
                'path'            => $request->path(),
                'method'          => $request->method(),
                'duration_ms'     => round($durationMs, 1),
                'query_count'     => $queryCount,
                'peak_memory_mb'  => round(memory_get_peak_usage() / 1048576, 1),
            ]);
        }

        DB::flushQueryLog();

        return $response;
    }
}
