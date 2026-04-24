<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.A — arquitectura de 3 modos.
 *
 * dining_option es el enum del MODO del POS que originó/gestiona la orden,
 * distinto del `type` vendor (takeaway/pick_up/pre_order/catering/dine_in).
 *
 * Mapeo conceptual:
 *   - dine_in:  orden con mesa asignada, modo Salón (table_id != null)
 *   - counter:  barra / mostrador / foodtruck, sin mesa (modo Mostrador)
 *   - takeout:  retiro del cliente en local (modo Pedidos → retiro)
 *   - delivery: envío con motoquero, propio o integrado (modo Pedidos → envío)
 *
 * Backfill de órdenes existentes:
 *   - table_id != null → dine_in
 *   - type in (takeaway/pick_up) sin table → takeout
 *   - type in (pre_order/catering) sin table → takeout por default
 *   - resto sin table → counter
 *
 * Este ambiente tiene 0 orders (verificado pre-migration), el backfill
 * es trivial. Futuros installs pueden tener data real.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('dining_option', ['dine_in', 'counter', 'takeout', 'delivery'])
                ->default('dine_in')
                ->after('type');
        });

        $this->backfillDiningOption();
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('dining_option');
        });
    }

    private function backfillDiningOption(): void
    {
        // Reglas orden: más específica primero.
        DB::statement("
            UPDATE orders
            SET dining_option = CASE
                WHEN table_id IS NOT NULL THEN 'dine_in'
                WHEN type IN ('takeaway', 'pick_up', 'pre_order', 'catering') THEN 'takeout'
                WHEN type = 'drive_thru' THEN 'takeout'
                WHEN type = 'dine_in' THEN 'dine_in'
                ELSE 'counter'
            END
        ");
    }
};
