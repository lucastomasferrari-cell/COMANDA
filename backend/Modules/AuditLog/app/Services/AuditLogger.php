<?php

namespace Modules\AuditLog\Services;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;
use Modules\AuditLog\Models\AuditLog;

/**
 * Interfaz estática para registrar acciones criticas. Uso:
 *
 *   AuditLogger::log('void_item', $orderProduct, [
 *       'reason' => 'Cliente devolvió',
 *       'old_values' => $orderProduct->getOriginal(),
 *       'new_values' => $orderProduct->getAttributes(),
 *       'approved_by' => $managerId,
 *       'parent_id' => $parentAuditId,
 *       'is_fiscal' => false,
 *   ]);
 *
 * Captura automáticamente user_id (auth), IP, user agent y parseo del
 * device. Si la invocación ocurre fuera de un request HTTP (ej. job,
 * command artisan), user_id y metadata.ip quedan null — OK, el log
 * sigue registrando la acción.
 *
 * No expone update/delete. Para cleanup ver CleanupAuditLogs command.
 */
class AuditLogger
{
    public static function log(
        string $action,
        Model $auditable,
        array $details = [],
    ): AuditLog {
        $payload = [
            'user_id' => $details['user_id'] ?? self::currentUserId(),
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),
            'action' => $action,
            'reason' => $details['reason'] ?? null,
            'old_values' => $details['old_values'] ?? null,
            'new_values' => $details['new_values'] ?? null,
            'metadata' => array_merge(
                self::captureMetadata(),
                $details['metadata'] ?? [],
            ),
            'approved_by' => $details['approved_by'] ?? null,
            'parent_id' => $details['parent_id'] ?? null,
            'is_fiscal' => (bool) ($details['is_fiscal'] ?? false),
            'created_at' => now(),
        ];

        return AuditLog::create($payload);
    }

    /**
     * Captura del contexto HTTP. Fuera de un request devuelve null
     * en los campos que no apliquen.
     */
    protected static function captureMetadata(): array
    {
        $request = request();
        if (!$request) {
            return [];
        }

        $userAgent = $request->userAgent();
        $agent = new Agent();
        $agent->setUserAgent($userAgent ?? '');

        return [
            'ip' => $request->ip(),
            'user_agent' => $userAgent,
            'device' => [
                'platform' => $agent->platform() ?: null,
                'browser' => $agent->browser() ?: null,
                'device_type' => $agent->isDesktop()
                    ? 'desktop'
                    : ($agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'unknown')),
            ],
        ];
    }

    protected static function currentUserId(): ?int
    {
        return auth()->check() ? auth()->id() : null;
    }
}
