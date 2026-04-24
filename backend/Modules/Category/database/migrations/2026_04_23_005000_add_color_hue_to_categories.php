<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 1.B — Colores por categoría configurables via hue.
 *
 * Agregamos smallInteger color_hue (0-360) en paralelo al color hex
 * existente. Convive con color (hex): el frontend del POS lo usa para
 * generar placeholders de producto con saturación/luminosidad fijas
 * (solo el hue es configurable), y el admin puede setearlos con un
 * slider simple en vez de full color picker.
 *
 * Nullable: categorías sin hue explícito heredan el default coral del
 * frontend (hue 12).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->smallInteger('color_hue')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('color_hue');
        });
    }
};
