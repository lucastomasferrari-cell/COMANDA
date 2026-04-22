<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Open Items: permite que un order_product represente un item "al vuelo"
 * cargado por el cajero con nombre y precio arbitrarios, sin product_id.
 *
 * - product_id pasa a nullable + FK nullOnDelete (preserva data historica
 *   incluso si el producto catalogo se borra).
 * - custom_name, custom_price, custom_description son nullable; un
 *   CHECK en service valida que (product_id) XOR (custom_name + custom_price).
 */
return new class extends Migration {
    public function up(): void
    {
        // Paso 1: dropear la FK vieja (cascadeOnDelete) — no podemos
        // alterar una FK existente sin dropearla.
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // Paso 2: hacer product_id nullable.
        Schema::table('order_products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });

        // Paso 3: re-crear FK con nullOnDelete.
        Schema::table('order_products', function (Blueprint $table) {
            $table
                ->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete();
        });

        // Paso 4: columnas de open item.
        Schema::table('order_products', function (Blueprint $table) {
            $table->string('custom_name', 255)->nullable()->after('product_id');
            $table->decimal('custom_price', 18, 4)->nullable()->after('custom_name');
            $table->text('custom_description')->nullable()->after('custom_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn(['custom_name', 'custom_price', 'custom_description']);
        });

        // Convertir product_id de vuelta a NOT NULL solo si no hay registros
        // con NULL — si hay, abortamos para no perder data.
        $nullCount = DB::table('order_products')->whereNull('product_id')->count();
        if ($nullCount === 0) {
            Schema::table('order_products', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
            Schema::table('order_products', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->nullable(false)->change();
            });
            Schema::table('order_products', function (Blueprint $table) {
                $table
                    ->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->cascadeOnDelete();
            });
        }
    }
};
