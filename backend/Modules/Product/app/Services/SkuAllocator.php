<?php

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Allocator centralizado para SKUs auto-generados secuenciales.
 *
 * Vive en Product module porque es la entidad principal que los usa,
 * pero es invocado también por Category, Menu y Option a través de sus
 * creating hooks. Entity type válidos: 'products' | 'categories' |
 * 'menus' | 'options'. El row en sku_counters se crea en la migration
 * 2026_04_23_001006 — si no existe, algo rompió la migration y tiramos
 * excepción en vez de auto-curar silenciosamente.
 */
final class SkuAllocator
{
    /**
     * Devuelve el próximo SKU para la entidad. Thread-safe vía
     * lockForUpdate: si dos requests crean productos simultáneamente,
     * uno espera al otro y cada uno recibe un valor distinto.
     */
    public static function next(string $entityType): string
    {
        return DB::transaction(function () use ($entityType) {
            $row = DB::table('sku_counters')
                ->where('entity_type', $entityType)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                throw new RuntimeException("SkuAllocator: no counter row for '$entityType'. Run migrations.");
            }

            $value = (int)$row->next_value;

            DB::table('sku_counters')
                ->where('entity_type', $entityType)
                ->update([
                    'next_value' => $value + 1,
                    'updated_at' => now(),
                ]);

            return (string)$value;
        });
    }

    /**
     * Guard para el update de las 4 entidades con sku_locked. Si el
     * modelo está lockeado y el payload incluye un sku distinto del
     * actual, tiramos 422 con un error i18n del módulo.
     *
     * Se llama desde los services (no los FormRequests) porque el
     * FormRequest no tiene acceso natural al modelo hidratado, y
     * duplicar la query en cada FormRequest es peor que hacerla una
     * vez en el service.
     */
    public static function assertNotLocked(Model $model, array $data, string $messageKey): void
    {
        if (!array_key_exists('sku', $data)) {
            return;
        }
        if (!($model->getAttribute('sku_locked') ?? false)) {
            return;
        }
        if ($model->getAttribute('sku') === $data['sku']) {
            return;
        }

        $message = __($messageKey);
        throw new HttpResponseException(
            new JsonResponse(
                data: [
                    'message' => $message,
                    'errors' => ['sku' => [$message]],
                ],
                status: 422,
            ),
        );
    }
}
