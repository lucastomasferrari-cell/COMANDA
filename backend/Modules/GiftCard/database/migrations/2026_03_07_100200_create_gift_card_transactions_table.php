<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\Order\Models\Order;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gift_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->createdBy();
            $table->branch();
            $table->uuid()->unique();
            $table->foreignId('gift_card_id')->constrained('gift_cards')->cascadeOnDelete();
            $table->enum('type', GiftCardTransactionType::values())->index();
            $table->decimal('amount', 18, 4);
            $table->decimal('balance_before', 18, 4);
            $table->decimal('balance_after', 18, 4);
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 18, 8)->nullable();
            $table->decimal('amount_in_order_currency', 18, 4)->nullable();
            $table->string('order_currency', 3)
                ->nullable();
            $table->foreignIdFor(Order::class)->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('pos_terminal_id')->nullable(); // TODO: Check this
            $table->text('notes')->nullable();
            $table->timestamp('transaction_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_transactions');
    }
};
