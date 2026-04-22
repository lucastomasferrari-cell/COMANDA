<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Void de items y órdenes con trazabilidad.
 *
 * - void_reasons: catalogo editable por el dueño. applies_to separa
 *   razones de item de razones de orden (pueden solaparse pero el
 *   cajero no ve razones de orden cuando anula un solo item).
 * - order_products: voided_at / voided_by / void_reason_id /
 *   void_note. Delete fisico queda prohibido — los queries del cart
 *   filtran por voided_at IS NULL.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('void_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->json('name');
            $table->enum('applies_to', ['item', 'order', 'both'])->default('item');
            $table->boolean('requires_manager_approval')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('status');
            $table->foreignId('voided_by')->nullable()->after('voided_at')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('void_reason_id')->nullable()->after('voided_by')
                ->constrained('void_reasons')->nullOnDelete();
            $table->text('void_note')->nullable()->after('void_reason_id');

            $table->index('voided_at', 'order_products_voided_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropForeign(['void_reason_id']);
            $table->dropIndex('order_products_voided_at_idx');
            $table->dropColumn(['voided_at', 'voided_by', 'void_reason_id', 'void_note']);
        });
        Schema::dropIfExists('void_reasons');
    }
};
