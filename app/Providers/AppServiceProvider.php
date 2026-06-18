<?php

namespace App\Providers;

use App\Events\PaymentConfirmed;
use App\Listeners\GrantCreditsOnPayment;
use App\Listeners\NotifyAdminOnPayment;
use App\Support\ConnectionCheck;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Output\ConsoleOutput;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // CulqiService usa config() internamente → Laravel lo resuelve automáticamente.
        // No se necesitan bindings manuales.
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        // Pago confirmado → suma créditos de publicación al usuario.
        Event::listen(PaymentConfirmed::class, GrantCreditsOnPayment::class);

        // Pago confirmado → avisa por correo al administrador (ADMIN_EMAILS).
        Event::listen(PaymentConfirmed::class, NotifyAdminOnPayment::class);

        // Registra el proveedor Microsoft en Socialite (Google es nativo).
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('microsoft', \SocialiteProviders\Microsoft\Provider::class);
        });

        // Al levantar `php artisan serve`, mostrar el check de conexión (BD + Culqi).
        if ($this->app->runningInConsole() && in_array('serve', $_SERVER['argv'] ?? [], true)) {
            try {
                ConnectionCheck::render(new ConsoleOutput());
            } catch (\Throwable) {
                // Si el check falla, no impedir que el servidor arranque.
            }
        }
    }

    /**
     * Límites de peticiones POR USUARIO (o por IP si es invitado).
     * Aplicados con throttle:<nombre> en las rutas.
     */
    private function configureRateLimiting(): void
    {
        // Clave: id del usuario autenticado; si es invitado, su IP.
        $byUser = fn (Request $request) => $request->user()?->id ?: $request->ip();

        // Lectura general / API (navegar, filtrar anuncios, /me, etc.).
        RateLimiter::for('api', fn (Request $request) =>
            Limit::perMinute(120)->by($byUser($request)));

        // Acciones del usuario (publicar, activar/eliminar, datos privados).
        RateLimiter::for('per-user', fn (Request $request) =>
            Limit::perMinute(60)->by($byUser($request)));
    }
}
