<?php

namespace Modules\SeatingPlan\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\SeatingPlan\Http\Requests\Api\V1\TableAssignWaiterRequest;
use Modules\SeatingPlan\Http\Requests\Api\V1\TableMergeRequest;
use Modules\SeatingPlan\Services\TableViewer\TableViewerServiceInterface;
use Modules\SeatingPlan\Transformers\Api\V1\TableShowViewerResource;
use Modules\SeatingPlan\Transformers\Api\V1\TableViewerResource;
use Modules\Support\ApiResponse;
use Modules\User\Enums\DefaultRole;
use Modules\User\Models\User;
use Throwable;

class TableViewerController extends Controller
{
    /**
     * Create a new instance of TableViewerController
     *
     * @param TableViewerServiceInterface $service
     */
    public function __construct(protected TableViewerServiceInterface $service)
    {
    }

    /**
     * This method retrieves and returns a list of TableViewer models.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $branchId = $user->assignedToBranch()
            ? $user->branch_id
            : $request->input('branch_id');
        $filters = [
            'search' => trim((string)$request->input('search', '')),
            'floors' => collect($request->input('floors', []))->filter()->values()->all(),
            'zones' => collect($request->input('zones', []))->filter()->values()->all(),
            'statuses' => collect($request->input('statuses', []))->filter()->values()->all(),
        ];

        $data = $this->service->get($branchId, $filters);

        return ApiResponse::success([
            "tables" => TableViewerResource::collection($data['tables']),
            "filters" => [
                "floors" => $data['floors'],
                "zones" => $data['zones'],
                "statuses" => $data['statuses'],
            ]
        ]);
    }

    /**
     * This method retrieves and returns a single Table model based on the provided identifier.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $table = $this->service->show($id);
        return ApiResponse::success(
            body: [
                'table' => new TableShowViewerResource($table),
                'meta' => [
                    "waiters" => User::list($table->branch_id, DefaultRole::Waiter)
                ]
            ]
        );
    }

    /**
     * Table merged
     *
     * @param TableMergeRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function merge(TableMergeRequest $request, int $id): JsonResponse
    {
        $this->service->merge($id, $request->validated());

        return ApiResponse::success(message: __("seatingplan::messages.table_merge_successfully"));
    }

    /**
     * Assign waiter
     *
     * @param TableAssignWaiterRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function assignWaiter(TableAssignWaiterRequest $request, int $id): JsonResponse
    {
        $this->service->assignWaiter($id, $request->validated());

        return ApiResponse::success(message: __("seatingplan::messages." . ($request->waiter_id ? "assigned_waiter_successfully" : "unassigned_waiter_successfully")));
    }

    /**
     * Make as available
     *
     * @param int $id
     * @return JsonResponse
     */
    public function makeAsAvailable(int $id): JsonResponse
    {
        $this->service->makeAsAvailable($id);

        return ApiResponse::success(message: __("seatingplan::messages.table_make_available_successfully"));
    }

    /**
     * Split table
     *
     * @param int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function splitTable(int $id): JsonResponse
    {
        $this->service->splitTable($id);

        return ApiResponse::success(message: __("seatingplan::messages.split_success"));
    }

    /**
     * Get merge table meta
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getMergeMeta(int $id): JsonResponse
    {
        return ApiResponse::success($this->service->getMergeMeta($id));
    }
}
