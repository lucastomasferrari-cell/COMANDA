<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pending approvals: registro de acciones sensibles que se ejecutaron
 * SIN aprobación del manager (por no estar disponible en el momento).
 * El admin las revisa al día siguiente; si pasan 7 días sin revisar,
 * se marcan como expired y se incluyen en el mail del dueño.
 *
 * - user_id: quién disparó la acción.
 * - action: string 64 (mismo namespace que AuditLog.action).
 * - related_model / related_id: polymorphic a la entidad tocada.
 * - details: JSON con snapshot de la acción (parametros, contexto).
 * - status: pending / approved / rejected / expired.
 * - reviewed_by / reviewed_at: del admin.
 * - expires_at: default now + 7 días.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('pending_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('action', 64);
            $table->string('related_model', 191);
            $table->unsignedBigInteger('related_id');
            $table->json('details')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])
                ->default('pending');
            $table->foreignId('reviewed_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['related_model', 'related_id']);
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_approvals');
    }
};
