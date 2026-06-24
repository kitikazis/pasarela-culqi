<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índice (user_id, status) para acelerar "Mis anuncios": el usuario filtra
 * sus propios anuncios por estado (todos / activos / inactivos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'publicaciones_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->dropIndex('publicaciones_user_status_idx');
        });
    }
};
