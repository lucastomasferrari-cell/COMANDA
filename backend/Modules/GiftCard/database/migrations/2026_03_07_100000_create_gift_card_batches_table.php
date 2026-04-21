<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gift_card_batches', function (Blueprint $table) {
            $table->id();
            $table->createdBy();
            $table->branch();
            $table->json('name');
            $table->string('prefix')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('value', 18, 4);
            $table->string('currency', 3);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_batches');
    }
};
