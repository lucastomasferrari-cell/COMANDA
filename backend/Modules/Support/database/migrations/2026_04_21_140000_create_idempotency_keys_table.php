<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 128);
            $table->string('scope', 128)->default('anonymous');
            $table->string('method', 10);
            $table->string('path', 255);
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->json('response_headers')->nullable();
            $table->mediumText('response_body')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['key', 'scope', 'method', 'path'], 'uniq_idempotency_key_scope_method_path');
            $table->index('expires_at', 'idx_idempotency_expires');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
