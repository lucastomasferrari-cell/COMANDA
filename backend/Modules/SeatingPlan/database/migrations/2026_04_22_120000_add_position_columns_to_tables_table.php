<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Columnas de posicion y tamano para el plano visual. Todas nullable o
     * con defaults razonables: mesas viejas siguen funcionando, el frontend
     * detecta NULL en position_x/y y dispara auto-grid al abrir el editor.
     */
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->decimal('position_x', 8, 2)->nullable()->after('shape');
            $table->decimal('position_y', 8, 2)->nullable()->after('position_x');
            $table->decimal('width', 6, 2)->default(80)->after('position_y');
            $table->decimal('height', 6, 2)->default(80)->after('width');
            $table->smallInteger('rotation')->default(0)->after('height');
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y', 'width', 'height', 'rotation']);
        });
    }
};
