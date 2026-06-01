<?php

/*
|--------------------------------------------------------------------------
| Moderación de contenido (al publicar anuncios)
|--------------------------------------------------------------------------
| El texto se normaliza antes de comparar: minúsculas, sin acentos, "leet"
| básico (0→o, 1→i, 3→e, 4→a, @→a, $→s) y se colapsan repeticiones.
|
|  - Entradas de UNA palabra  → se buscan como palabra completa (\bpalabra\b).
|  - Entradas con ESPACIOS    → se buscan como frase (subcadena), útil para
|    eufemismos como "dama de compañia" o "final feliz".
|
| Edita estas listas según tu criterio. Son un punto de partida.
*/

return [

    'enabled' => true,

    // ── Groserías / insultos ───────────────────────────────────
    'profanity' => [
        'puta', 'puto', 'putas', 'putos', 'putamadre', 'hijodeputa', 'hijaeputa',
        'mierda', 'mierdas', 'cagada', 'caca',
        'concha', 'conchatumadre', 'conchasumadre', 'ctm', 'csm',
        'imbecil', 'idiota', 'estupido', 'estupida', 'tarado', 'tarada',
        'pendejo', 'pendeja', 'pendejada',
        'cojudo', 'cojuda', 'cojudez', 'huevon', 'huevada', 'weon',
        'mongolo', 'retrasado', 'subnormal',
        'maricon', 'marica', 'cabro',
        'zorra', 'perra', 'malparido', 'desgraciado',
        'jodete', 'jodido', 'carajo',
        'fuck', 'fucking', 'shit', 'bitch', 'asshole',
    ],

    // ── Servicios para adultos / prostitución (eufemismos PE) ───
    'adult' => [
        // Términos directos
        'prostituta', 'prostitutas', 'prostitucion', 'putero', 'burdel', 'prostibulo',
        'escort', 'escorts', 'acompañante', 'acompañantes', 'trabajadora sexual',
        // Eufemismos comunes en Perú
        'kinesiologa', 'kinesiologas', 'kinesiologia',
        'dama de compañia', 'damas de compañia', 'dama de compania', 'damas de compania',
        'servicio sexual', 'servicios sexuales', 'servicio completo',
        'masaje erotico', 'masajes eroticos', 'masaje sensual', 'masaje con final feliz',
        'final feliz', 'trato completo', 'full service', 'sin condon', 'sin tabu',
        'atiendo caballeros', 'atiendo solo', 'solo caballeros',
        'soy complaciente', 'complaciente', 'morbosa', 'rica y caliente',
        'sexo', 'sexual', 'orgia', 'table dance', 'tabledance',
        'salida hotel', 'delivery de chicas', 'chicas a domicilio',
    ],

    // ── IA opcional (Google Perspective API) ───────────────────
    'perspective' => [
        'enabled'    => env('PERSPECTIVE_ENABLED', false),
        'api_key'    => env('PERSPECTIVE_API_KEY'),
        'threshold'  => (float) env('PERSPECTIVE_THRESHOLD', 0.85),
        'language'   => 'es',
        'attributes' => ['TOXICITY', 'SEVERE_TOXICITY', 'INSULT', 'PROFANITY', 'THREAT'],
    ],

];
