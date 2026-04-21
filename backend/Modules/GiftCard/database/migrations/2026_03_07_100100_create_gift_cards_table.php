<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\GiftCard\Enums\GiftCardScope;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\User\Models\User;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->createdBy();
            $table->branch();
            $table->foreignId('gift_card_batch_id')->nullable()->constrained('gift_card_batches')->nullOnDelete();
            $table->string('code')->unique();
            $table->decimal('initial_balance', 18, 4);
            $table->decimal('current_balance', 18, 4);
            $table->string('currency', 3);
            $table->enum('scope', GiftCardScope::values())->default(GiftCardScope::Branch->value)->index();
            $table->foreignIdFor(User::class, "customer_id")->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', GiftCardStatus::values())->default(GiftCardStatus::Active->value)->index();
            $table->dateTime('expiry_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
