<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Agrega pase_uuid (CHAR(26) ULID) a las tablas que PASE necesita referenciar
 * desde un sistema externo: orders, payments, invoices, products.
 *
 * El backfill se hace en esta misma migration para que todas las filas
 * existentes queden con su propio ULID antes de aplicar el UNIQUE
 * constraint. Filas futuras los generan automaticamente via el trait
 * HasPaseUuid en el model.
 */
return new class extends Migration {
    private const TABLES = ['orders', 'payments', 'invoices', 'products'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->char('pase_uuid', 26)->nullable()->after('id');
            });

            // Backfill de filas existentes con un ULID nuevo.
            DB::table($table)->whereNull('pase_uuid')->orderBy('id')->each(function ($row) use ($table) {
                DB::table($table)->where('id', $row->id)->update([
                    'pase_uuid' => (string) Str::ulid(),
                ]);
            });

            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->unique('pase_uuid', "uniq_{$table}_pase_uuid");
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropUnique("uniq_{$table}_pase_uuid");
                $t->dropColumn('pase_uuid');
            });
        }
    }
};
