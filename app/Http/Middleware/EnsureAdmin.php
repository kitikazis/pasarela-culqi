<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Permite el paso solo a administradores.
 *
 * No hay sistema de roles: el admin se define por correo en config('app.admins')
 * (variable ADMIN_EMAILS del .env, separada por comas). Suficiente y seguro para
 * acciones sensibles como las devoluciones, sin sobre-ingeniería.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user   = Auth::user();
        $admins = (array) config('app.admins', []);

        if (! $user || ! in_array($user->email, $admins, true)) {
            abort(403, 'Acción permitida solo para administradores.');
        }

        return $next($request);
    }
}
