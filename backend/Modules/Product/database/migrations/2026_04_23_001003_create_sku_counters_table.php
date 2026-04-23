<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sku_counters', function (Blueprint $table) {
            // entity_type es la PK porque un solo contador vive por tipo.
            // Valores esperados: 'products' | 'categories' | 'menus' | 'options'.
            // Cuando se agregue una 5ta entidad con SKU, se inserta una fila
            // nueva aca — no se crean tablas paralelas.
            $table->string('entity_type', 50)->primary();
            $table->unsignedBigInteger('next_value')->default(1);
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sku_counters');
    }
};
