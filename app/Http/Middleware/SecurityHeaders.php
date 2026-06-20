<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad en TODAS las respuestas.
 *
 *  - X-Frame-Options / frame-ancestors  → anti-clickjacking (no embeber en iframe ajeno).
 *  - X-Content-Type-Options: nosniff     → evita que el navegador "adivine" tipos MIME.
 *  - Referrer-Policy                     → no filtrar URLs completas a sitios externos.
 *  - Permissions-Policy                  → desactiva APIs sensibles que no usamos.
 *  - Strict-Transport-Security (HSTS)    → fuerza HTTPS en visitas futuras (solo sobre TLS).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS solo cuando la conexión ya es HTTPS (no romper el desarrollo local en http).
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
