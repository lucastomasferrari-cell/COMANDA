<?php

namespace Modules\SeatingPlan\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Http\Controllers\Controller;
use Modules\Support\ApiResponse;

/**
 * Sprint 3.A.bis — endpoint read-only para que el POS viewer muestre badges
 * de reserva próxima en el plano de mesas.
 *
 * PASE (Fase 2) va a tener su propio set completo de endpoints CRUD + UI
 * admin de reservas. Este controller es el mínimo necesario para que el
 * badge del plano funcione hoy con las reservas sembradas por
 * DemoArSeeder.
 */
class ReservationController extends Controller
{
    /**
     * Reservas próximas en el horizonte inmediato (2hs por default).
     * Filtro: status ∈ {pending, confirmed}, reserved_for entre ahora y
     * ahora+2hs, table_id no-null (las sin mesa se manejan en PASE).
     *
     * Safeguard: si la tabla `reservations` todavía no fue migrada (ej.
     * deploy mid-rollout o un fresh install que no corrió el seeder),
     * respondemos lista vacía en vez de 500. El POS trata las reservas
     * como feature secundario y no puede caerse por falta de data.
     */
    public function upcoming(): JsonResponse
    {
        if (! Schema::hasTable('reservations')) {
            return ApiResponse::success([]);
        }

        $rows = DB::table('reservations')
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNotNull('table_id')
            ->where('reserved_for', '>=', now())
            ->where('reserved_for', '<=', now()->addHours(2))
            ->whereNull('deleted_at')
            ->orderBy('reserved_for')
            ->get([
                'id',
                'table_id',
                'guest_name',
                'guest_phone',
                'party_size',
                'reserved_for',
                'status',
                'notes',
            ]);

        return ApiResponse::success($rows);
    }
}
