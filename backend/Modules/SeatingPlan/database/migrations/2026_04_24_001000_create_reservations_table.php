<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3.A.bis — esqueleto para sistema de reservas PASE (Fase 2).
 *
 * PASE es una app separada del POS donde el host/maître gestiona reservas
 * y waitlist. El POS de COMANDA solo RECIBE el contexto: qué mesa tiene
 * reserva próxima, con quién, para cuántos. Cuando el host marca
 * "arrived" en PASE, la reserva se convierte en orden dine_in con datos
 * precargados.
 *
 * Esta migration deja la tabla lista para que PASE escriba; el UI admin
 * para crear/editar reservas NO se construye en este sprint (es Fase 2
 * dedicada). Solo queda:
 *   - Migration + seeder demo.
 *   - Badge en el plano cuando una mesa tiene reserva próxima (commit 11).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            // Mesa asignada a priori. Puede ser null si la reserva es
            // "walk-in future" sin mesa fija todavía — el host la asigna
            // al momento del arrival.
            $table->foreignId('table_id')->nullable()
                ->constrained('tables')->nullOnDelete();
            // Cliente registrado (opcional). Si no existe, usar guest_name
            // + guest_phone como string libre.
            $table->foreignId('customer_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_phone')->nullable();
            $table->unsignedSmallInteger('party_size')->default(1);
            $table->timestamp('reserved_for')->index();
            $table->enum('status', [
                'pending',      // creada, no confirmada todavía
                'confirmed',    // confirmada por el host (email/llamada)
                'arrived',      // cliente llegó → el POS crea orden dine_in
                'no_show',      // pasada la hora, no apareció
                'cancelled',    // cancelada (por cliente o restaurante)
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('branch_id')->nullable()
                ->constrained('branches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Query típico del badge en el plano: "reservas próximas
            // (status confirmed/pending) para cualquier mesa en las
            // próximas 2hs". Índice por (table_id, reserved_for) optimiza.
            $table->index(['table_id', 'reserved_for', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
