<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Loyalty\Models\LoyaltyGift;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_discounts', function (Blueprint $table) {
            $table->foreignIdFor(LoyaltyGift::class)
                ->nullable()
                ->after('order_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_discounts', function (Blueprint $table) {
            $table->dropForeignIdFor(LoyaltyGift::class);
            $table->dropColumn('loyalty_gift_id');
        });
    }
};
