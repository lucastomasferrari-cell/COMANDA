<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PIN corto (4-6 digitos) para que managers/admins autoricen
 * acciones sensibles desde el POS sin loguearse con password full.
 *
 * - nullable: no todos los users tienen PIN (cajeros no lo requieren).
 * - bcrypt: hasheamos con Hash::make al asignar; verify con Hash::check.
 * - lockout_until: timestamp hasta el cual el user no puede intentar
 *   PIN tras N fallos. Se limpia al exito.
 * - pin_failed_attempts: contador que rompe el rate limit en 3.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('manager_pin_hash', 255)->nullable()->after('password');
            $table->timestamp('manager_pin_lockout_until')->nullable()->after('manager_pin_hash');
            $table->unsignedTinyInteger('manager_pin_failed_attempts')->default(0)->after('manager_pin_lockout_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'manager_pin_hash',
                'manager_pin_lockout_until',
                'manager_pin_failed_attempts',
            ]);
        });
    }
};
