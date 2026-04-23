<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de payment methods configurables por el admin. El enum
     * Modules\Payment\Enums\PaymentMethod sigue existiendo como tipo
     * base (se guarda en payments.method por compat con el vendor).
     *
     * Diseño MVP:
     * - Cada row tiene un `type` que referencia el enum value (cash,
     *   card, bank_transfer, mobile_wallet, gift_card, other).
     * - `impacts_cash` determina si el método suma al arqueo de caja
     *   efectivo. Default true para type=cash, false para los demás.
     * - Múltiples rows pueden compartir `type` (ej 2 "mobile_wallet":
     *   Mercado Pago + Modo). El reporte agrupa por type por ahora
     *   (no hay payment_method_id FK en payments todavía — scope
     *   parking: agregarlo cuando se necesite distinguir integraciones).
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 50);
            $table->boolean('impacts_cash')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
