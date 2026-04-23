<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * El vendor creó un unique compuesto (sku, menu_id) que permitía el
     * mismo SKU en 2 menús distintos. Lo cambiamos a unique GLOBAL porque
     * integraciones delivery (Rappi, PedidosYa, Uber Eats) esperan SKU
     * único por restaurante, no por menú.
     *
     * Este migration solo hace el drop. El unique global se agrega al
     * final (2026_04_23_001006) después del backfill, para que no choque
     * con filas pre-existentes en NULL / con duplicados cross-menu.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique(['sku', 'menu_id']);
        });
    }
};
