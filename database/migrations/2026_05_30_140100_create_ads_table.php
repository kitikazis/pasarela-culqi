<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anuncios publicados por los usuarios.
 * featured_until: hasta cuándo el anuncio está DESTACADO (lo activa un pago).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('categoria');                 // venta | compra | trabajo | busca
            $table->text('descripcion');
            $table->string('telefono', 9);

            $table->string('cobertura')->default('departamental'); // nacional|departamental|provincial|distrital
            $table->string('departamento')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();

            $table->string('estado')->default('active'); // active | inactive
            $table->timestamp('destacado_hasta')->nullable();
            $table->unsignedInteger('vistas')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('destacado_hasta');
            $table->index('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
};
