<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices compuestos para acelerar el listado público de anuncios
 * (filtros por estado/categoría/departamento/cobertura + orden por fecha).
 * Pensado para que /api/ads siga rápido con miles de filas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            // Listado base: activos ordenados por fecha.
            $table->index(['status', 'created_at'], 'ads_status_created_idx');
            // Filtro por categoría + orden por fecha.
            $table->index(['status', 'category', 'created_at'], 'ads_status_cat_created_idx');
            // Filtro por departamento.
            $table->index(['status', 'department'], 'ads_status_dep_idx');
            // Filtro "Nacional".
            $table->index('coverage', 'ads_coverage_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropIndex('ads_status_created_idx');
            $table->dropIndex('ads_status_cat_created_idx');
            $table->dropIndex('ads_status_dep_idx');
            $table->dropIndex('ads_coverage_idx');
        });
    }
};
