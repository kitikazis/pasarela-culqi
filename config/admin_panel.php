<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credenciales del panel de administración (login de prueba)
    |--------------------------------------------------------------------------
    | Login simple usuario/contraseña SOLO para el panel /admin (es independiente
    | del login OAuth de los usuarios y de ADMIN_EMAILS, que protege las
    | devoluciones).
    |
    | Las credenciales viven SOLO en el .env (sin valores por defecto en el
    | código): si no se definen, el panel queda BLOQUEADO (fail-secure) en vez
    | de abrirse con un admin/123 conocido.
    |
    | ⚠️ PRODUCCIÓN: define ADMIN_PANEL_USER y un ADMIN_PANEL_PASSWORD FUERTE en
    | el .env. Idealmente migrar a OAuth + ADMIN_EMAILS.
    */

    'user'     => env('ADMIN_PANEL_USER'),
    'password' => env('ADMIN_PANEL_PASSWORD'),

];
