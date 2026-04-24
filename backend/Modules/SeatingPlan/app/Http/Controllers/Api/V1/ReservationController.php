<?php

namespace Modules\SeatingPlan\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
     */
    public function upcoming(): JsonResponse
    {
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
