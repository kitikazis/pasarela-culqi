<?php

/*
|--------------------------------------------------------------------------
| PHP Built-in Server Router
|--------------------------------------------------------------------------
| Usado por `php artisan serve`. Sirve archivos estáticos directamente
| y delega el resto a index.php (Laravel).
*/

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Raíz → index.html del frontend
if ($uri === '/' && file_exists(__DIR__.'/public/index.html')) {
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__.'/public/index.html');
    return true;
}

// Archivos estáticos en public/
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false; // El servidor built-in lo sirve con el Content-Type correcto
}
// Todo lo demás → Laravel
// Todo lo demás → Laravel
require_once __DIR__.'/public/index.php';
