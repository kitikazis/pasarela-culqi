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

            $table->string('category');                  // venta | compra | trabajo | busca
            $table->text('description');
            $table->string('phone', 9);

            $table->string('coverage')->default('departamental'); // nacional|departamental|provincial|distrital
            $table->string('department')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();

            $table->string('status')->default('active'); // active | inactive
            $table->timestamp('featured_until')->nullable();
            $table->unsignedInteger('views')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('featured_until');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
};
