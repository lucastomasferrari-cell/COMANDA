<?php

namespace Modules\GiftCard\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\GiftCard\Http\Requests\Api\V1\SaveGiftCardRequest;
use Modules\GiftCard\Services\GiftCard\GiftCardServiceInterface;
use Modules\GiftCard\Transformers\Api\V1\GiftCardResource;
use Modules\Support\ApiResponse;

class GiftCardController extends Controller
{
    /**
     * Create a new instance of GiftCardController.
     *
     * @param GiftCardServiceInterface $service
     */
    public function __construct(protected GiftCardServiceInterface $service)
    {
    }

    /**
     * Display a listing of gift cards.
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
            resource: GiftCardResource::class,
            filters: $request->get('with_filters') ? $this->service->getStructureFilters() : null,
        );
    }

    /**
     * Display the specified gift card.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(int|string $id): JsonResponse
    {
        return ApiResponse::success(body: new GiftCardResource($this->service->show($id)));
    }

    /**
     * Store a newly created gift card in storage.
     *
     * @param SaveGiftCardRequest $request
     * @return JsonResponse
     */
    public function store(SaveGiftCardRequest $request): JsonResponse
    {
        return ApiResponse::created(
            body: new GiftCardResource($this->service->store($request->validated())),
            resource: $this->service->label(),
        );
    }

    /**
     * Update the specified gift card in storage.
     *
     * @param SaveGiftCardRequest $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function update(SaveGiftCardRequest $request, int|string $id): JsonResponse
    {
        return ApiResponse::updated(
            body: new GiftCardResource($this->service->update($id, $request->validated())),
            resource: $this->service->label(),
        );
    }

    /**
     * Remove the specified gift cards from storage.
     *
     * @param string $ids
     * @return JsonResponse
     */
    public function destroy(string $ids): JsonResponse
    {
        return ApiResponse::destroyed(
            destroyed: $this->service->destroy($ids),
            resource: $this->service->label(),
        );
    }

    /**
     * Get form metadata for the gift card form.
     *
     * @return JsonResponse
     */
    public function getFormMeta(): JsonResponse
    {
        return ApiResponse::success($this->service->getFormMeta());
    }

    /**
     * Get the current balance details for the specified gift card.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function balance(int|string $id): JsonResponse
    {
        return ApiResponse::success($this->service->balance($id));
    }
}
