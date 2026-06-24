<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Asegura el rename ads -> publicaciones en bases de datos YA existentes.
 *
 * Es defensiva e idempotente para cubrir los tres escenarios reales:
 *   1) BD nueva: la tabla ya nació como 'publicaciones' (migración 140100) -> no hace nada.
 *   2) BD donde 'ads' aún existe: la renombra a 'publicaciones' (conserva datos).
 *   3) Producción donde 'ads' fue borrada y solo quedó el registro en `migrations`:
 *      crea 'publicaciones' desde cero.
 *
 * Además renombra la columna transactions.ad_id -> publicacion_id (esa columna
 * siempre va NULL: ningún request la valida, así que recrearla no pierde datos)
 * y elimina la vista huérfana 'papelera' que referenciaba la tabla vieja.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Vista creada a mano que apuntaba a 'ads' (la app NO la usa: la Papelera
        // se resuelve con SoftDeletes). Si 'ads' ya no existe, esta vista da error #1356.
        DB::statement('DROP VIEW IF EXISTS papelera');

        // ── Tabla principal ────────────────────────────────────────────────
        if (Schema::hasTable('ads') && ! Schema::hasTable('publicaciones')) {
            Schema::rename('ads', 'publicaciones');
        }

        if (! Schema::hasTable('publicaciones')) {
            Schema::create('publicaciones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                $table->string('category');
                $table->string('description', 144);
                $table->string('phone', 9);

                $table->string('coverage')->default('departamental');
                $table->string('department')->nullable();
                $table->string('province')->nullable();
                $table->string('district')->nullable();

                $table->string('status')->default('active');
                $table->timestamp('featured_until')->nullable();
                $table->unsignedInteger('views')->default(0);

                $table->timestamps();
                $table->softDeletes();

                $table->index('status');
                $table->index('featured_until');
                $table->index('category');
                $table->index(['status', 'created_at'], 'publicaciones_status_created_idx');
                $table->index(['status', 'category', 'created_at'], 'publicaciones_status_cat_created_idx');
                $table->index(['status', 'department'], 'publicaciones_status_dep_idx');
                $table->index('coverage', 'publicaciones_coverage_idx');
                $table->index(['user_id', 'status'], 'publicaciones_user_status_idx');
            });
        }

        // ── Columna FK en transactions ─────────────────────────────────────
        // La vieja 'ad_id' siempre va NULL (ningún request la setea), así que la
        // quitamos y dejamos 'publicacion_id'. Sin pérdida de datos.
        if (Schema::hasColumn('transactions', 'ad_id')) {
            try {
                Schema::table('transactions', fn (Blueprint $t) => $t->dropForeign(['ad_id']));
            } catch (\Throwable $e) {
                // La FK ya no existía (se borró junto con 'ads'); continuamos.
            }
            Schema::table('transactions', fn (Blueprint $t) => $t->dropColumn('ad_id'));
        }

        if (! Schema::hasColumn('transactions', 'publicacion_id')) {
            Schema::table('transactions', function (Blueprint $t) {
                $t->foreignId('publicacion_id')->nullable()->after('user_id')
                    ->constrained('publicaciones')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // El estado deseado es 'publicaciones'; no revertimos el rename.
    }
};
