<?php

namespace Modules\User\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\Controller;
use Modules\Support\ApiResponse;
use Modules\User\Http\Requests\Api\V1\LoginRequest;
use Modules\User\Services\Auth\AuthServiceInterface;
use Modules\User\Transformers\Api\V1\AuthResource;

class AuthController extends Controller
{
    /**
     * Create a new instance of AuthController
     *
     * @param AuthServiceInterface $service
     */
    public function __construct(protected AuthServiceInterface $service)
    {
    }

    /**
     * User store authentication
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->service->login($request->validated());

        return ApiResponse::success([
            "user" => new AuthResource($data['user']),
            "token" => $data['token']
        ]);
    }

    /**
     * Destroy user authentication
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        return ApiResponse::success(
            body: ["success" => $this->service->logout()],
            message: __('auth.logout')
        );
    }

    /**
     * This API helps the frontend guarantee that the token is valid and return user data.
     *
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        return ApiResponse::success(['user' => new AuthResource(auth()->user())]);
    }
}
