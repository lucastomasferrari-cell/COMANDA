<?php

namespace Modules\Pos\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Order\Transformers\Api\V1\Kitchen\KitchenOrderResource;
use Modules\Pos\Services\KitchenViewer\KitchenViewerServiceInterface;
use Modules\Support\ApiResponse;
use Throwable;

class KitchenViewerController extends Controller
{
    /**
     * Create a new instance of PosController
     *
     * @param KitchenViewerServiceInterface $service
     */
    public function __construct(protected KitchenViewerServiceInterface $service)
    {
    }

    /**
     * Get Configuration
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function configuration(Request $request): JsonResponse
    {
        return ApiResponse::success($this->service->getConfiguration($request->get('branch_id')));
    }

    /**
     * Get orders
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function orders(Request $request): JsonResponse
    {
        $data = $this->service->getOrders($request->get('branch_id'));

        return ApiResponse::success([
            "orders" => KitchenOrderResource::collection($data["orders"]),
            "last_updated_at" => $data["last_updated_at"]
        ]);
    }

    /**
     * Update Order product status
     *
     * @param Request $request
     * @param int|string $orderId
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateOrderProductStatus(Request $request, int|string $orderId): JsonResponse
    {
        $this->service->moveOrderProductToNextStatus($orderId, $request->get('ids', []));

        return ApiResponse::success(["success" => true]);
    }
}
