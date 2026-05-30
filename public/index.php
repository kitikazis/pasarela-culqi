<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Archivos estáticos (servidor built-in de PHP)
|--------------------------------------------------------------------------
| php artisan serve usa server.php que los sirve automáticamente.
| php -S lo usa este bloque.
*/
if (PHP_SAPI === 'cli-server') {
    $uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $file = __DIR__.$uri;

    if ($uri !== '/' && file_exists($file) && is_file($file)) {
        return false; // El servidor built-in lo sirve directamente
    }

    if (($uri === '/' || $uri === '') && file_exists(__DIR__.'/index.html')) {
        header('Content-Type: text/html; charset=utf-8');
        readfile(__DIR__.'/index.html');
        exit;
    }
}

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
