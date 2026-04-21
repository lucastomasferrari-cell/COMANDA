<?php

namespace Modules\Loyalty\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Loyalty\Http\Requests\Api\V1\SaveLoyaltyCustomerRequest;
use Modules\Loyalty\Services\LoyaltyCustomer\LoyaltyCustomerServiceInterface;
use Modules\Loyalty\Transformers\Api\V1\LoyaltyCustomerResource;
use Modules\Support\ApiResponse;

class LoyaltyCustomerController extends Controller
{
    /**
     * Create a new instance of LoyaltyCustomerController
     *
     * @param LoyaltyCustomerServiceInterface $service
     */
    public function __construct(protected LoyaltyCustomerServiceInterface $service)
    {
    }

    /**
     * This method retrieves and returns a list of LoyaltyCustomer models.
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
            resource: LoyaltyCustomerResource::class,
            filters: $request->get('with_filters')
                ? $this->service->getStructureFilters($request->get('filters')['loyalty_program_id'] ?? null)
                : null
        );
    }

    /**
     * This method retrieves and returns a single LoyaltyCustomer model based on the provided identifier.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(
            body: new LoyaltyCustomerResource($this->service->show($id))
        );
    }
}
