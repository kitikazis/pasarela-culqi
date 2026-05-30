<?php

namespace App\Providers;

use App\Support\ConnectionCheck;
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
