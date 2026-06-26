<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Genera ~200 anuncios de demostración (variados: categoría, cobertura,
 * ubicación, fecha y vistas). Pensado para poblar la home de prueba.
 *
 * Ejecutar:  php artisan db:seed --class=AdsSeeder
 */
class AdsSeeder extends Seeder
{
    /** Cuántos anuncios crear. */
    private const CUANTOS = 2;

    public function run(): void
    {
        // Dueño de los anuncios: el primer usuario que exista, o uno de demo.
        $user = User::query()->first() ?? User::create([
            'name'            => 'Demo Anuncialo',
            'email'           => 'demo@anuncialo.pe',
            'provider'        => 'google',
            'provider_id'     => 'demo-seeder',
            'publish_credits' => 0,
        ]);

        $categorias = ['venta', 'compra', 'trabajo', 'busca'];

        // Plantillas por categoría (se rellenan con un producto u oficio).
        $plantillas = [
            'venta'   => ['Vendo %s en excelente estado, poco uso. Precio negociable.', 'Se vende %s casi nuevo, entrega inmediata.', 'Remato %s por viaje, conversable.'],
            'compra'  => ['Compro %s en buen estado, pago al contado.', 'Busco comprar %s, urgente. Pago justo.'],
            'trabajo' => ['Se necesita %s con experiencia, pago puntual.', 'Empresa solicita %s, sueldo acorde al mercado.'],
            'busca'   => ['Busco %s confiable para trabajo por horas.', 'Necesito %s con referencias en mi zona.'],
        ];

        $productos = ['laptop', 'celular', 'bicicleta', 'refrigeradora', 'moto lineal', 'sofá', 'televisor', 'cámara', 'consola de videojuegos', 'juego de comedor', 'lavadora', 'taladro', 'guitarra', 'escritorio', 'aire acondicionado', 'cocina a gas'];
        $oficios   = ['gasfitero', 'electricista', 'cocinero', 'mozo', 'vendedor', 'chofer', 'niñera', 'jardinero', 'carpintero', 'recepcionista', 'cajero', 'community manager', 'diseñador', 'contador'];

        // Departamento => Provincia => [Distritos] (datos reales para que los filtros funcionen).
        $ubicaciones = [
            'Lima'        => ['Lima' => ['Miraflores', 'San Juan de Lurigancho', 'Santiago de Surco', 'Comas', 'Ate']],
            'Arequipa'    => ['Arequipa' => ['Cercado', 'Cayma', 'Yanahuara']],
            'La Libertad' => ['Trujillo' => ['Trujillo', 'Víctor Larco Herrera', 'El Porvenir']],
            'Cusco'       => ['Cusco' => ['Cusco', 'Wanchaq', 'San Sebastián']],
            'Piura'       => ['Piura' => ['Piura', 'Castilla', 'Catacaos']],
            'Lambayeque'  => ['Chiclayo' => ['Chiclayo', 'José Leonardo Ortiz']],
            'Junín'       => ['Huancayo' => ['Huancayo', 'El Tambo']],
            'Áncash'      => ['Santa' => ['Chimbote', 'Nuevo Chimbote']],
        ];
        $departamentos = array_keys($ubicaciones);
        $coberturas    = ['nacional', 'departamental', 'provincial', 'distrital'];

        $ahora = now();
        $filas = [];

        for ($i = 0; $i < self::CUANTOS; $i++) {
            $cat    = $categorias[array_rand($categorias)];
            $tpl    = $plantillas[$cat][array_rand($plantillas[$cat])];
            $sujeto = in_array($cat, ['trabajo', 'busca'], true)
                ? $oficios[array_rand($oficios)]
                : $productos[array_rand($productos)];
            $desc   = mb_substr(sprintf($tpl, $sujeto), 0, Ad::MAX_DESCRIPTION);

            $cobertura = $coberturas[array_rand($coberturas)];
            $dep = $prov = $dist = null;

            if ($cobertura !== 'nacional') {
                $dep = $departamentos[array_rand($departamentos)];
                if (in_array($cobertura, ['provincial', 'distrital'], true)) {
                    $prov = array_rand($ubicaciones[$dep]);
                    if ($cobertura === 'distrital') {
                        $dists = $ubicaciones[$dep][$prov];
                        $dist  = $dists[array_rand($dists)];
                    }
                }
            }

            $filas[] = [
                'user_id'      => $user->id,
                'categoria'    => $cat,
                'descripcion'  => $desc,
                'telefono'     => '9' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
                'cobertura'    => $cobertura,
                'departamento' => $dep,
                'provincia'    => $prov,
                'distrito'     => $dist,
                'estado'       => 'active',
                'vistas'       => random_int(0, 500),
                'created_at'   => $ahora->copy()->subDays(random_int(0, 30))->subMinutes(random_int(0, 1440)),
                'updated_at'   => $ahora,
            ];
        }

        // Inserción por lotes (rápida, sin disparar eventos del modelo).
        foreach (array_chunk($filas, 50) as $lote) {
            Ad::insert($lote);
        }

        $this->command->info(self::CUANTOS . ' anuncios de demo creados (dueño: ' . $user->email . ').');
    }
}
