<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega una columna calculada `amount_soles` para LECTURA humana (S/).
 * `amount` se mantiene en céntimos (lo que exige Culqi); esta columna
 * solo divide /100 automáticamente para verla legible en phpMyAdmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transactions', 'amount_soles')) {
            DB::statement('ALTER TABLE transactions
                ADD COLUMN amount_soles DECIMAL(10,2)
                AS (amount / 100) VIRTUAL AFTER amount');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'amount_soles')) {
            DB::statement('ALTER TABLE transactions DROP COLUMN amount_soles');
        }
    }
};
