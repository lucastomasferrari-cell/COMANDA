<?php

namespace Modules\GiftCard\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\GiftCard\Services\GiftCardTransaction\GiftCardTransactionServiceInterface;
use Modules\GiftCard\Transformers\Api\V1\GiftCardTransactionResource;
use Modules\Support\ApiResponse;

class GiftCardTransactionController extends Controller
{
    /**
     * Create a new instance of GiftCardTransactionController.
     *
     * @param GiftCardTransactionServiceInterface $service
     */
    public function __construct(protected GiftCardTransactionServiceInterface $service)
    {
    }

    /**
     * Display a listing of gift card transactions.
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
            resource: GiftCardTransactionResource::class,
            filters: $request->get('with_filters') ? $this->service->getStructureFilters() : null,
        );
    }

    /**
     * Display the specified gift card transaction.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(int|string $id): JsonResponse
    {
        return ApiResponse::success(body: new GiftCardTransactionResource($this->service->show($id)));
    }
}
