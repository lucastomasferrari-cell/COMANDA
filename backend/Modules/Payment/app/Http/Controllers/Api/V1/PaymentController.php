<?php

namespace Modules\Payment\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Payment\Services\Payment\PaymentServiceInterface;
use Modules\Payment\Transformers\Api\V1\PaymentResource;
use Modules\Support\ApiResponse;

class PaymentController extends Controller
{
    /**
     * Create a new instance of PaymentController
     *
     * @param PaymentServiceInterface $service
     */
    public function __construct(protected PaymentServiceInterface $service)
    {
    }

    /**
     * This method retrieves and returns a list of Payment models.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::pagination(
            paginator: $this->service->get(
                filters: $request->get('filters', []),
                sorts: $request->get('sorts', []),
            ),
            resource: PaymentResource::class,
            filters: $request->get('with_filters')
                ? $this->service->getStructureFilters()
                : null
        );
    }

    /**
     * This method retrieves and returns a single Payment model based on the provided identifier.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(
            body: new PaymentResource($this->service->show($id))
        );
    }
}
