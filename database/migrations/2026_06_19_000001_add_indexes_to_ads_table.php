<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices compuestos para acelerar el listado público de anuncios
 * (filtros por estado/categoría/departamento/cobertura + orden por fecha).
 * Pensado para que /api/publicaciones siga rápido con miles de filas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            // Listado base: activos ordenados por fecha.
            $table->index(['estado', 'created_at'], 'publicaciones_status_created_idx');
            // Filtro por categoría + orden por fecha.
            $table->index(['estado', 'categoria', 'created_at'], 'publicaciones_status_cat_created_idx');
            // Filtro por departamento.
            $table->index(['estado', 'departamento'], 'publicaciones_status_dep_idx');
            // Filtro "Nacional".
            $table->index('cobertura', 'publicaciones_coverage_idx');
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->dropIndex('publicaciones_status_created_idx');
            $table->dropIndex('publicaciones_status_cat_created_idx');
            $table->dropIndex('publicaciones_status_dep_idx');
            $table->dropIndex('publicaciones_coverage_idx');
        });
    }
};
