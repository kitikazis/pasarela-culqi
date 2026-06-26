<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    // Los listeners se registran a mano en AppServiceProvider (Event::listen).
    // Apagamos el auto-discovery de Laravel para que NO queden registrados dos
    // veces: lo estaban, y por eso al confirmar un pago se enviaban correos
    // duplicados (y se entregaban créditos dobles).
    ->withEvents(discover: false)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // El webhook de Culqi es una petición servidor-a-servidor: sin CSRF.
        $middleware->validateCsrfTokens(except: [
            'culqi/webhook',
        ]);

        // Cabeceras de seguridad en todas las respuestas (anti-clickjacking, HSTS, etc.).
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Profiling de peticiones lentas / N+1 (se auto-desactiva en producción).
        $middleware->append(\App\Http\Middleware\LogQueryMetrics::class);

        // Alias para proteger acciones sensibles (ej. devoluciones).
        $middleware->alias([
            'admin'       => \App\Http\Middleware\EnsureAdmin::class,   // OAuth + ADMIN_EMAILS (refunds)
            'admin.panel' => \App\Http\Middleware\EnsureAdminPanel::class, // login del panel /admin
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }
        });
    })->create();
