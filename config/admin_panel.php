<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credenciales del panel de administración (login de prueba)
    |--------------------------------------------------------------------------
    | Login simple usuario/contraseña SOLO para el panel /admin (es independiente
    | del login OAuth de los usuarios y de ADMIN_EMAILS, que protege las
    | devoluciones). Por defecto admin/123 para pruebas.
    |
    | ⚠️ PRODUCCIÓN: define ADMIN_PANEL_USER y un ADMIN_PANEL_PASSWORD FUERTE en
    | el .env (nunca dejes 123). Idealmente migrar a OAuth + ADMIN_EMAILS.
    */

    'user'     => env('ADMIN_PANEL_USER', 'admin'),
    'password' => env('ADMIN_PANEL_PASSWORD', '123'),

];
