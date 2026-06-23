<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege el panel de administración (/admin/*).
 *
 * El acceso se concede SOLO si la sesión fue marcada por AdminController::login()
 * tras validar las credenciales de config('admin_panel'). Sin esa marca, redirige
 * al login del panel (nunca muestra el dashboard a un invitado).
 */
class EnsureAdminPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('admin_authenticated')) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
