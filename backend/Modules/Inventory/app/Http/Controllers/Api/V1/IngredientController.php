<?php

namespace Modules\Inventory\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Inventory\Http\Requests\Api\V1\SaveIngredientRequest;
use Modules\Inventory\Services\Ingredient\IngredientServiceInterface;
use Modules\Inventory\Transformers\Api\V1\IngredientResource;
use Modules\Support\ApiResponse;

class IngredientController extends Controller
{
    /**
     * Create a new instance of IngredientController
     *
     * @param IngredientServiceInterface $service
     */
    public function __construct(protected IngredientServiceInterface $service)
    {
    }

    /**
     * This method retrieves and returns a list of Ingredient models.
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
            resource: IngredientResource::class,
            filters: $request->get('with_filters')
                ? $this->service->getStructureFilters()
                : null
        );
    }

    /**
     * This method retrieves and returns a single Ingredient model based on the provided identifier.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(
            body: new IngredientResource($this->service->show($id))
        );
    }

    /**
     * This method stores the provided data into storage for the Ingredient model.
     *
     * @param SaveIngredientRequest $request
     * @return JsonResponse
     */
    public function store(SaveIngredientRequest $request): JsonResponse
    {
        return ApiResponse::created(
            body: new IngredientResource($this->service->store($request->validated())),
            resource: $this->service->label()
        );
    }

    /**
     * This method updates the provided data for the Ingredient model.
     *
     * @param SaveIngredientRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(SaveIngredientRequest $request, int $id): JsonResponse
    {
        return ApiResponse::updated(
            body: new IngredientResource($this->service->update($id, $request->validated())),
            resource: $this->service->label()
        );
    }

    /**
     * This method deletes the Ingredient model based on the provided ids.
     *
     * @param string $ids
     * @return JsonResponse
     */
    public function destroy(string $ids): JsonResponse
    {
        return ApiResponse::destroyed(
            destroyed: $this->service->destroy($ids),
            resource: $this->service->label()
        );
    }

    /**
     * Get form meta
     *
     * @return JsonResponse
     */
    public function getFormMeta(): JsonResponse
    {
        return ApiResponse::success($this->service->getFormMeta());
    }
}
