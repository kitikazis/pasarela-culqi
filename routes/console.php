<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cada día, borra definitivamente los anuncios con +30 días en la Papelera.
Schedule::command('ads:purge-trash')->daily();

// Cada día, registra métricas de salud en storage/logs/health.log.
Schedule::command('health:check')->daily()->appendOutputTo(storage_path('logs/health.log'));
