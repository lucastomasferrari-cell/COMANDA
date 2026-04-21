<?php

namespace Modules\ActivityLog\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ActivityLog\Services\ActivityLog\ActivityLogServiceInterface;
use Modules\ActivityLog\Transformers\Api\V1\ActivityLogResource;
use Modules\ActivityLog\Transformers\Api\V1\ActivityLogShowResource;
use Modules\Core\Http\Controllers\Controller;
use Modules\Support\ApiResponse;

class ActivityLogController extends Controller
{
    /**
     * Create a new instance of ActivityLogController
     *
     * @param ActivityLogServiceInterface $service
     */
    public function __construct(protected ActivityLogServiceInterface $service)
    {
    }

    /**
     * Display a listing of the resource.
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
            resource: ActivityLogResource::class,
            filters: $request->get('with_filters')
                ? $this->service->getStructureFilters()
                : null
        );
    }

    /**
     * Show the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(
            body: new ActivityLogShowResource($this->service->show($id))
        );
    }
}
