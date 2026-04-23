<?php

namespace App\Support;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * Helpers centralizados para el flujo anti-fraude.
 *
 * requireApproval($message, $context) lanza 403 con shape standard
 * que el interceptor del frontend reconoce para activar el PIN
 * dialog. Body: { code: "manager_approval_required", message,
 * context }. Header adicional X-AntiFraud-Action sirve como fallback
 * de detección.
 *
 * Reemplaza los abort(403, __('...')) dispersos que antes devolvían
 * solo { message }, que el frontend no podía distinguir de un 403
 * común.
 */
class AntiFraud
{
    public const CODE_MANAGER_REQUIRED = 'manager_approval_required';

    /**
     * @throws HttpResponseException
     */
    public static function requireApproval(string $message, ?string $context = null): never
    {
        throw new HttpResponseException(
            new JsonResponse(
                data: [
                    'code' => self::CODE_MANAGER_REQUIRED,
                    'message' => $message,
                    'context' => $context,
                ],
                status: 403,
                headers: [
                    'X-AntiFraud-Action' => self::CODE_MANAGER_REQUIRED,
                ],
            ),
        );
    }
}
