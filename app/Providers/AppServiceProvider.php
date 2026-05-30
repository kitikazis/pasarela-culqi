<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // CulqiService usa config() internamente → Laravel lo resuelve automáticamente.
        // No se necesitan bindings manuales.
    }

    public function boot(): void
    {
        //
    }
}
