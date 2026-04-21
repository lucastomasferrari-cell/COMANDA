<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\User\Enums\CustomerType;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->after('gender');
            $table->text('note')->nullable()->after('birthdate');
            $table->enum('customer_type', CustomerType::values())
                ->default(CustomerType::Regular->value)
                ->after('note');
            $table->string('registration_number')->nullable()->after('customer_type');
            $table->string('vat_tin')->nullable()->after('registration_number');
            $table->json('profile_photo_path')->nullable()->after('vat_tin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'birthdate',
                'note',
                'customer_type',
                'registration_number',
                'vat_tin',
                'profile_photo_path',
            ]);
        });
    }
};
