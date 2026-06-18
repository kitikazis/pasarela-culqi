<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PageController extends Controller
{
    /**
     * Sirve una página HTML "cascarón" con CACHE-BUSTING AUTOMÁTICO.
     *
     * A cada asset local (styles.css y *.js) le añade ?v=<fecha-de-modificación>.
     * Cuando el archivo cambia (un deploy), su fecha cambia => la URL cambia =>
     * el navegador descarga la versión nueva SOLO, sin que el usuario limpie
     * caché ni entre en incógnito. Los CDN (https://...) no se tocan.
     *
     * La página se devuelve con "no-cache" para que ese ?v= actualizado llegue
     * siempre al usuario en la siguiente carga.
     */
    public function show(string $file): Response
    {
        $path = public_path($file);
        abort_unless(is_file($path), 404);

        $html = preg_replace_callback(
            '/(href|src)="([\w\-.\/]+\.(?:css|js))"/i',
            function (array $m): string {
                $asset = public_path(ltrim($m[2], '/'));
                $ver   = is_file($asset) ? '?v=' . filemtime($asset) : '';

                return $m[1] . '="' . $m[2] . $ver . '"';
            },
            file_get_contents($path)
        );

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
