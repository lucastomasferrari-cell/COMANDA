<?php

namespace Modules\Payment\Http\Controllers\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Controller;
use Modules\Payment\Http\Requests\Api\V1\SavePaymentMethodRequest;
use Modules\Payment\Services\PaymentMethodItem\PaymentMethodItemService;
use Modules\Payment\Transformers\Api\V1\PaymentMethodItemResource;
use Modules\Support\ApiResponse;

class PaymentMethodItemController extends Controller
{
    public function __construct(protected PaymentMethodItemService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::pagination(
            paginator: $this->service->get(
                filters: $request->get('filters', []),
                sorts: $request->get('sorts', []),
            ),
            resource: PaymentMethodItemResource::class,
            filters: $request->get('with_filters')
                ? $this->service->getStructureFilters()
                : null,
        );
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(
            body: new PaymentMethodItemResource($this->service->show($id)),
        );
    }

    public function store(SavePaymentMethodRequest $request): JsonResponse
    {
        $this->service->store($request->validated());
        return ApiResponse::created(resource: $this->service->label());
    }

    public function update(int $id, SavePaymentMethodRequest $request): JsonResponse
    {
        $this->service->update($id, $request->validated());
        return ApiResponse::updated(body: null, resource: $this->service->label());
    }

    public function destroy(string $id): JsonResponse
    {
        $destroyed = $this->service->destroy($id);
        return ApiResponse::destroyed($destroyed, $this->service->label());
    }

    public function formMeta(): JsonResponse
    {
        return ApiResponse::success(body: $this->service->getFormMeta());
    }

    /**
     * Endpoint para consumo dashboard + PASE futuro:
     * GET /v1/payment-methods/report?from=YYYY-MM-DD&to=YYYY-MM-DD
     *
     * Response shape:
     * { body: [{method_id, method_name, method_type, total_amount,
     *           transactions_count}, ...] }
     */
    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->get('from')) : now()->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->get('to')) : now()->endOfDay();

        return ApiResponse::success(body: $this->service->report($from, $to));
    }
}
