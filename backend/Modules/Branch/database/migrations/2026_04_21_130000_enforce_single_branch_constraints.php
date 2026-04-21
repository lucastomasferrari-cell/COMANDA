<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Las 28 tablas del vendor que tienen columna branch_id (via macro
     * $table->branch() de Support). Todas tienen FK
     * <table>_branch_id_foreign con ON DELETE SET NULL.
     */
    private const TABLES = [
        'discounts',
        'floors',
        'gift_card_batches',
        'gift_card_transactions',
        'gift_cards',
        'ingredientables',
        'ingredients',
        'invoices',
        'menus',
        'online_menus',
        'option_values',
        'options',
        'orders',
        'payments',
        'pos_cash_movements',
        'pos_registers',
        'pos_sessions',
        'print_agents',
        'printers',
        'purchases',
        'stock_movements',
        'suppliers',
        'table_merges',
        'tables',
        'taxes',
        'users',
        'vouchers',
        'zones',
    ];

    public function up(): void
    {
        // Fix is_main=0 en la branch principal (anomalia post-reseed i18n:
        // el seeder setea is_main=true pero quedo en 0 tras ultimo re-seed).
        DB::table('branches')->where('id', 1)->update(['is_main' => 1]);

        // MySQL 8 prohibe CHECK constraints sobre columnas que forman parte
        // de FKs con accion referencial (SET NULL, CASCADE, etc.). Las 28
        // FKs del vendor usan ON DELETE SET NULL. Solucion: re-crear cada FK
        // con ON DELETE RESTRICT (coherente con "branch 1 es inmutable",
        // nunca se va a disparar porque DELETE /branches esta bloqueado por
        // middleware) y agregar el CHECK despues.
        //
        // Hay que separar DROP FK y ADD FK en dos ALTER distintos: MySQL 8
        // rechaza DROP+ADD de un FK con el mismo nombre en el mismo ALTER
        // (error 1826 "Duplicate foreign key constraint name").
        //
        // CHECK permite NULL porque el macro $table->branch() crea columna
        // nullable y varios flujos legitimos usan NULL (admin user, taxes
        // globales, discounts factory default).
        foreach (self::TABLES as $table) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$table}_branch_id_foreign`");
            DB::statement(<<<SQL
                ALTER TABLE `{$table}`
                    ADD CONSTRAINT `chk_{$table}_branch_id`
                        CHECK (`branch_id` IS NULL OR `branch_id` = 1),
                    ADD CONSTRAINT `{$table}_branch_id_foreign`
                        FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT
            SQL);
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            DB::statement(<<<SQL
                ALTER TABLE `{$table}`
                    DROP FOREIGN KEY `{$table}_branch_id_foreign`,
                    DROP CHECK `chk_{$table}_branch_id`
            SQL);
            DB::statement(<<<SQL
                ALTER TABLE `{$table}`
                    ADD CONSTRAINT `{$table}_branch_id_foreign`
                        FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL
            SQL);
        }
    }
};
