<?php

namespace Modules\Menu\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Menu\Http\Requests\Api\V1\SaveOnlineMenuRequest;
use Modules\Menu\Services\OnlineMenu\OnlineMenuServiceInterface;
use Modules\Menu\Transformers\Api\V1\OnlineMenuResource;
use Modules\Support\ApiResponse;

class OnlineMenuController extends Controller
{
    /**
     * Create a new instance of OnlineMenuController
     *
     * @param OnlineMenuServiceInterface $service
     */
    public function __construct(protected OnlineMenuServiceInterface $service)
    {
    }

    /**
     * This method retrieves and returns a list of OnlineMenu models.
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
            resource: OnlineMenuResource::class,
            filters: $request->get('with_filters')
                ? $this->service->getStructureFilters(
                    auth()->user()->assignedToBranch()
                        ? auth()->user()->branch_id
                        : $request->get('filters')['branch_id'] ?? null
                )
                : null
        );
    }

    /**
     * This method retrieves and returns a single OnlineMenu model based on the provided identifier.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(
            body: new OnlineMenuResource($this->service->show($id))
        );
    }

    /**
     * This method stores the provided data into storage for the OnlineMenu model.
     *
     * @param SaveOnlineMenuRequest $request
     * @return JsonResponse
     */
    public function store(SaveOnlineMenuRequest $request): JsonResponse
    {
        return ApiResponse::created(
            body: new OnlineMenuResource($this->service->store($request->validated())),
            resource: $this->service->label()
        );
    }

    /**
     * This method updates the provided data for the OnlineMenu model.
     *
     * @param SaveOnlineMenuRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(SaveOnlineMenuRequest $request, int $id): JsonResponse
    {
        return ApiResponse::updated(
            body: new OnlineMenuResource($this->service->update($id, $request->validated())),
            resource: $this->service->label()
        );
    }

    /**
     * This method deletes the OnlineMenu model based on the provided ids.
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
     * @param Request $request
     * @return JsonResponse
     */
    public function getFormMeta(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->getFormMeta(
                auth()->user()->assignedToBranch()
                    ? auth()->user()->branch_id
                    : $request->get('branch_id')
            )
        );
    }

    /**
     * Get online menu data
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function getMenu(string $slug): JsonResponse
    {
        return ApiResponse::success($this->service->getMenu($slug));
    }
}
