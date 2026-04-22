<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega 2 columnas de tracking al ciclo de vida de la orden:
 * - bill_requested_at: el cajero (o el cliente via un flujo futuro)
 *   marca que pidió la cuenta. Se usa para resaltar la mesa en rojo
 *   en el plano visual y disparar impresion fiscal automatica.
 * - paused_at: la orden se pone en espera — la mesa queda libre
 *   visualmente, pero la orden persiste y se reabre con resume.
 *
 * Nullables — los flujos las ignoran si estan NULL.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('bill_requested_at')->nullable()->after('payment_at');
            $table->timestamp('paused_at')->nullable()->after('bill_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['bill_requested_at', 'paused_at']);
        });
    }
};
