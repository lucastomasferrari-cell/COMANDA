<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * audit_logs: append-only. No hay updated_at, el Model bloquea updates.
 *
 * Campos clave:
 * - auditable_type + auditable_id: polymorphic. La entidad tocada.
 * - action: string (ej: void_item, discount_applied, order_reopened).
 *   Usamos string no enum para que cada bloque del sprint pueda sumar
 *   acciones nuevas sin tocar migration.
 * - approved_by: si la accion requirio manager passcode o override, acá
 *   va el user_id del aprobador. user_id es el que disparo, approved_by
 *   el que autorizo.
 * - parent_id: acciones anidadas. Si un reopen dispara sub-logs, todos
 *   apuntan al reopen como padre.
 * - is_fiscal: impide cleanup automatico. Todo lo que toca facturacion
 *   AFIP (refunds, creditos, etc.) se marca true.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('auditable_type', 191);
            $table->unsignedBigInteger('auditable_id');
            $table->string('action', 64);
            $table->text('reason')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_fiscal')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('parent_id')
                ->references('id')
                ->on('audit_logs')
                ->nullOnDelete();

            $table->index(['auditable_type', 'auditable_id'], 'audit_logs_auditable_idx');
            $table->index('user_id', 'audit_logs_user_idx');
            $table->index('action', 'audit_logs_action_idx');
            $table->index('created_at', 'audit_logs_created_at_idx');
            $table->index('is_fiscal', 'audit_logs_fiscal_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
