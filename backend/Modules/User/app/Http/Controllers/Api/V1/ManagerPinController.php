<?php

namespace Modules\User\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Support\ApiResponse;
use Modules\User\Services\Auth\ManagerPinService;

class ManagerPinController extends Controller
{
    public function __construct(protected ManagerPinService $service)
    {
    }

    /**
     * Verifica el PIN de un manager y devuelve un token one-shot
     * valido 5 minutos que el frontend adjunta a la accion sensible
     * (void item, descuento alto, cambio pago, etc).
     *
     * Body:
     *   { "user_id": 12, "pin": "4281", "action_context": "void_item_after_fire" }
     *
     * Responses:
     *   200 { token, expires_in }
     *   401 { message }                  — PIN incorrecto
     *   423 { message }                  — lockout activo (429-like pero con 423 LOCKED)
     *   404 { message }                  — user sin PIN configurado
     */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'pin' => 'required|string|min:4|max:6',
            'action_context' => 'nullable|string|max:128',
        ]);

        $token = $this->service->verify(
            userId: (int) $data['user_id'],
            pin: (string) $data['pin'],
            actionContext: $data['action_context'] ?? null,
        );

        if (is_null($token)) {
            return ApiResponse::errors(
                message: __('user::messages.manager_pin_incorrect'),
                code: 401,
            );
        }

        return ApiResponse::success(
            body: [
                'token' => $token,
                'expires_in' => 300,
            ],
        );
    }

    /**
     * Setea o actualiza el PIN del usuario logueado.
     * Body: { "pin": "4281" }
     */
    public function setSelf(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pin' => 'required|string|min:4|max:6',
        ]);

        $this->service->setPin(auth()->user(), $data['pin']);

        return ApiResponse::success(
            body: ['success' => true],
            message: __('user::messages.manager_pin_saved'),
        );
    }
}
