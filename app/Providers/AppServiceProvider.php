<?php

namespace App\Providers;

use App\Events\PaymentConfirmed;
use App\Listeners\FeatureAdOnPayment;
use App\Support\ConnectionCheck;
use Illuminate\Support\Facades\Event;
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
        // Pago confirmado → destacar el anuncio (lógica de negocio #2).
        Event::listen(PaymentConfirmed::class, FeatureAdOnPayment::class);

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
}
