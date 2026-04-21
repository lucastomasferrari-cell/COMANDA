<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('print_agents', function (Blueprint $table) {
            $table->string('api_key')->nullable()->after('name');
            $table->string('host')->nullable()->after('api_key');
            $table->unsignedInteger('port')->nullable()->after('host');
            $table->dropColumn(['agent_id', 'secret', 'last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_agents', function (Blueprint $table) {
            $table->string('agent_id')->unique()->after('id');
            $table->text('secret')->after('name');
            $table->timestamp('last_seen_at')->nullable()->after('secret');
            $table->dropColumn(['api_key', 'host', 'port']);
        });
    }
};
