<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.A commit 2 — columnas para Orders Hub (modo Pedidos).
 *
 * Decisión B del sprint planning: NO extendemos OrderStatus del vendor
 * (pending/confirmed/preparing/ready/served/completed/cancelled/refunded/
 * merged). Agregamos flags paralelos que el composable useOrderHubStatus()
 * del frontend combina con status + closed_at + dispatched_at para
 * derivar los 6 estados visibles del Orders Hub (needs_approval,
 * in_kitchen, ready, in_transit, completed).
 *
 * Columnas:
 *   channel:        enum del canal de origen (propio/rappi/pedidosya/
 *                   telefono/whatsapp). El esqueleto está completo para
 *                   soportar integraciones futuras; hoy solo 'propio',
 *                   'telefono' y 'whatsapp' se siembran con data demo.
 *   needs_approval: flag bool. Rappi/PedidosYa (cuando lleguen) crean
 *                   órdenes en 'needs_approval=true' esperando que el
 *                   encargado apruebe antes de mandarlas a cocina.
 *   dispatched_at:  timestamp de cuándo salió con el motoquero.
 *                   Solo para delivery; null en dine_in/counter/takeout.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('channel', ['propio', 'rappi', 'pedidosya', 'telefono', 'whatsapp'])
                ->default('propio')
                ->after('dining_option');
            $table->boolean('needs_approval')
                ->default(false)
                ->after('channel');
            $table->timestamp('dispatched_at')
                ->nullable()
                ->after('needs_approval');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['channel', 'needs_approval', 'dispatched_at']);
        });
    }
};
