<?php

namespace Modules\Pos\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Pos\Services\PosViewer\PosViewerServiceInterface;
use Modules\Support\ApiResponse;

class PosViewerController extends Controller
{
    /**
     * Create a new instance of PosController
     *
     * @param PosViewerServiceInterface $service
     */
    public function __construct(protected PosViewerServiceInterface $service)
    {
    }

    /**
     * Get pos configuration
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function configuration(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->getConfiguration($request->get('branch_id'))
        );
    }

    /**
     * Get menu items
     *
     * @param string $cartId
     * @param int $menuId
     * @return JsonResponse
     */
    public function menuItems(string $cartId, int $menuId): JsonResponse
    {
        return ApiResponse::success($this->service->getMenuItems($menuId));
    }
}
