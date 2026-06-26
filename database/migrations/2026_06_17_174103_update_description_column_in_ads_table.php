<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Limita la descripción del anuncio a 144 caracteres a nivel de BD,
     * para que coincida con la validación del backend (StoreAdRequest: max:144)
     * y el maxlength del formulario (publicar.html).
     *
     * Pasa de TEXT a VARCHAR(144). Laravel 12 soporta ->change() de forma
     * nativa (no requiere doctrine/dbal).
     */
    public function up(): void
    {
        // Recorta a 144 las descripciones existentes que se pasen del límite,
        // para que el cambio de columna no falle por truncamiento.
        // (Los anuncios nuevos ya vienen limitados a 144 por la validación.)
        // Las funciones de string difieren por motor (MySQL vs SQLite).
        if (DB::getDriverName() === 'sqlite') {
            DB::table('publicaciones')
                ->whereRaw('LENGTH(descripcion) > 144')
                ->update(['descripcion' => DB::raw('SUBSTR(descripcion, 1, 144)')]);
        } else {
            DB::table('publicaciones')
                ->whereRaw('CHAR_LENGTH(descripcion) > 144')
                ->update(['descripcion' => DB::raw('LEFT(descripcion, 144)')]);
        }

        Schema::table('publicaciones', function (Blueprint $table) {
            $table->string('descripcion', 144)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->text('descripcion')->change();
        });
    }
};
