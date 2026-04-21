<?php

namespace Modules\Printer\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Printer\Http\Requests\Api\V1\SavePrintAgentRequest;
use Modules\Printer\Services\Agent\PrintAgentServiceInterface;
use Modules\Printer\Transformers\Api\V1\PrintAgentResource;
use Modules\Support\ApiResponse;

class PrintAgentController extends Controller
{
    /**
     * Create a new instance of PrintAgentController
     *
     * @param PrintAgentServiceInterface $service
     */
    public function __construct(protected PrintAgentServiceInterface $service)
    {
    }

    /**
     * This method retrieves and returns a list of print agent models.
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
            resource: PrintAgentResource::class,
            filters: $request->get('with_filters')
                ? $this->service->getStructureFilters()
                : null
        );
    }

    /**
     * This method retrieves and returns a single print agent model based on the provided identifier.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(
            body: new PrintAgentResource($this->service->show($id))
        );
    }

    /**
     * This method stores the provided data into storage for the print agent model.
     *
     * @param SavePrintAgentRequest $request
     * @return JsonResponse
     */
    public function store(SavePrintAgentRequest $request): JsonResponse
    {
        return ApiResponse::created(
            body: new PrintAgentResource($this->service->store($request->validated())),
            resource: $this->service->label()
        );
    }

    /**
     * This method updates the provided data for the print agent model.
     *
     * @param SavePrintAgentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(SavePrintAgentRequest $request, int $id): JsonResponse
    {
        return ApiResponse::updated(
            body: new PrintAgentResource($this->service->update($id, $request->validated())),
            resource: $this->service->label()
        );
    }

    /**
     * This method deletes the print agent model based on the provided ids.
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
}
