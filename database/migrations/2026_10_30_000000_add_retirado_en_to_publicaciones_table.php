<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moderación: cuándo el admin retiró el anuncio. Mientras tenga fecha, el dueño
 * no puede reactivarlo, restaurarlo ni editarlo (solo el admin lo revoca).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->timestamp('retirado_en')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->dropColumn('retirado_en');
        });
    }
};
