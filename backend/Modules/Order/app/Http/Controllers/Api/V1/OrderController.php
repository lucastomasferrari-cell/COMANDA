<?php

namespace Modules\Order\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Branch\Models\Branch;
use Modules\Cart\Facades\Cart;
use Modules\Core\Http\Controllers\Controller;
use Modules\Order\Enums\OrderPaymentStatus;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\OrderType;
use Modules\Order\Http\Requests\Api\V1\CancelOrRefundOrderRequest;
use Modules\Order\Http\Requests\Api\V1\OrderPaymentRequest;
use Modules\Order\Http\Requests\Api\V1\SaveOrderRequest;
use Modules\Order\Http\Requests\Api\V1\StoreCustomOrderProductRequest;
use Modules\Order\Services\Order\OrderServiceInterface;
use Modules\Order\Services\OrderPayment\OrderPaymentServiceInterface;
use Modules\Order\Services\SaveOrder\SaveOrderServiceInterface;
use Modules\Order\Transformers\Api\V1\ActiveOrderResource;
use Modules\Order\Transformers\Api\V1\OrderResource;
use Modules\Order\Transformers\Api\V1\ShowOrderResource;
use Modules\Printer\Enum\PrintContentType;
use Modules\Support\ApiResponse;
use Throwable;

class OrderController extends Controller
{
    /**
     * Create a new instance of OrderCreateController
     *
     * @param OrderServiceInterface $service
     * @param SaveOrderServiceInterface $saveOrderService
     * @param OrderPaymentServiceInterface $paymentService
     */
    public function __construct(
        protected OrderServiceInterface        $service,
        protected SaveOrderServiceInterface    $saveOrderService,
        protected OrderPaymentServiceInterface $paymentService,
    )
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
            resource: OrderResource::class,
            filters: $request->get('with_filters')
                ? $this->service->getStructureFilters()
                : null
        );
    }

    /**
     * This method cancel order
     *
     * @param CancelOrRefundOrderRequest $request
     * @param int|string $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function cancel(CancelOrRefundOrderRequest $request, int|string $id): JsonResponse
    {
        $this->service->cancel($id, $request->validated());

        return ApiResponse::success(
            body: ["success" => true],
            message: __('admin::messages.resources_cancelled', ['resource' => $this->service->label()])
        );
    }

    /**
     * This method Refund order
     *
     * @param CancelOrRefundOrderRequest $request
     * @param int|string $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function refund(CancelOrRefundOrderRequest $request, int|string $id): JsonResponse
    {
        $this->service->refund($id, $request->validated());

        return ApiResponse::success(body: ["success" => true], message: __('order::messages.order_refunded'));
    }

    /**
     * This method edit order
     *
     * @param string $cartId
     * @param int|string $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function edit(string $cartId, int|string $id): JsonResponse
    {
        return ApiResponse::success(body: [
            ...$this->service->initEdit($id),
            "cart" => Cart::instance()
        ]);
    }

    /**
     * This method retrieves and returns a single Payment model based on the provided identifier.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(int|string $id): JsonResponse
    {
        return ApiResponse::success(
            body: new ShowOrderResource($this->service->show($id))
        );
    }

    /**
     * This method stores the provided data into storage for the Order model.
     *
     * @param SaveOrderRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(SaveOrderRequest $request): JsonResponse
    {
        $order = $this->saveOrderService->create($request->validated());

        return ApiResponse::created(
            body: ["order_id" => $order->reference_no, "cart" => Cart::instance()],
            resource: $this->service->label()
        );
    }

    /**
     * This method update the provided data into storage for the Order model.
     *
     * @param SaveOrderRequest $request
     * @param string $cartId
     * @param string|int $id
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(SaveOrderRequest $request, string $cartId, string|int $id): JsonResponse
    {
        $order = $this->saveOrderService->update($id, $request->validated());

        return ApiResponse::updated(
            body: ["order_id" => $order->reference_no, "cart" => Cart::instance()],
            resource: $this->service->label()
        );
    }

    /**
     * Get add payment meta
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function getPaymentMeta(int|string $id): JsonResponse
    {
        return ApiResponse::success($this->paymentService->getPaymentMeta($id));
    }

    /**
     * Get update status meta
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function getUpdateStatusMeta(int|string $id): JsonResponse
    {
        return ApiResponse::success($this->service->getUpdateStatusMeta($id));
    }

    /**
     * Order add payment
     *
     * @param OrderPaymentRequest $request
     * @param int|string $orderId
     * @return JsonResponse
     * @throws Throwable
     */
    public function storePayment(OrderPaymentRequest $request, int|string $orderId): JsonResponse
    {
        $this->paymentService->storePayment($orderId, $request->validated());

        return ApiResponse::success(message: __("order::messages.order_paid_successfully"));
    }

    /**
     * Get active orders
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function activeOrders(Request $request): JsonResponse
    {
        $user = auth()->user();
        $branchId = $request->input('branch_id');
        $filters = $this->normalizeDrawerFilters($request);
        $branch = $user->assignedToBranch()
            ? $user->branch
            : (!is_null($branchId) ? Branch::where('id', $branchId)->first() : $user->effective_branch);

        return ApiResponse::success(
            [
                "orders" => ActiveOrderResource::collection($this->service->activeOrders($branch->id, $filters)),
                "filters" => [
                    "statuses" => OrderStatus::toArrayTrans([
                        OrderStatus::Refunded->value,
                        OrderStatus::Cancelled->value,
                        OrderStatus::Merged->value,
                        OrderStatus::Served->value,
                    ]),
                    "order_types" => array_values(
                        array_filter(
                            OrderType::toArrayTrans(),
                            fn($orderType) => $orderType['id'] != OrderType::DineIn->value && in_array($orderType['id'], $branch->order_types)
                        )
                    ),
                    "payment_statuses" => OrderPaymentStatus::toArrayTrans()
                ]
            ]
        );
    }

    /**
     * Get upcoming orders
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upcomingOrders(Request $request): JsonResponse
    {
        $user = auth()->user();
        $branchId = $request->input('branch_id');
        $filters = $this->normalizeDrawerFilters($request);
        $branch = $user->assignedToBranch()
            ? $user->branch
            : (!is_null($branchId) ? Branch::where('id', $branchId)->first() : $user->effective_branch);


        return ApiResponse::success(
            [
                "orders" => ActiveOrderResource::collection($this->service->upcomingOrders($branch->id, $filters)),
                "filters" => [
                    "statuses" => OrderStatus::toArrayTrans([
                        OrderStatus::Refunded->value,
                        OrderStatus::Cancelled->value,
                        OrderStatus::Merged->value,
                        OrderStatus::Completed->value,
                        OrderStatus::Served->value,
                        OrderStatus::Ready->value,
                        OrderStatus::Preparing->value,
                    ]),
                    "order_types" => array_values(
                        array_filter(
                            OrderType::toArrayTrans(),
                            fn($orderType) => in_array($orderType['id'], [OrderType::Catering->value, OrderType::PreOrder->value]) && in_array($orderType['id'], $branch->order_types)
                        )
                    ),
                    "payment_statuses" => OrderPaymentStatus::toArrayTrans()
                ]
            ]
        );
    }

    /**
     * Move to next status
     *
     * @param int|string $orderId
     * @return JsonResponse
     * @throws Throwable
     */
    public function moveToNextStatus(int|string $orderId): JsonResponse
    {
        $newStatus = $this->service->moveToNextStatus($orderId);

        return ApiResponse::success(message: __("order::messages.order_update_status_to_successfully", ['status' => $newStatus->trans()]));
    }

    /**
     * Preview print
     *
     * @param Request $request
     * @param int|string $orderId
     * @param PrintContentType $type
     * @return JsonResponse|Response
     * @throws Throwable
     */
    public function previewPrint(Request $request, int|string $orderId, PrintContentType $type): JsonResponse|Response
    {
        $contentType = $request->input('content_type', 'html');
        $preview = $this->service->previewPrint(
            $orderId,
            $type,
            $request->kitchen_id,
            $request->specific_id,
            $contentType
        );

        if ($request->input('response_type', 'json') !== 'file') {
            return ApiResponse::success($preview);
        }

        $filename = "order-$orderId-$type->value";
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        [$content, $mimeType, $extension] = match ($contentType) {
            'image' => [
                base64_decode($preview['content'], true) ?: $preview['content'],
                'image/png',
                'png',
            ],
            'pdf' => [
                base64_decode($preview['content'], true) ?: $preview['content'],
                'application/pdf',
                'pdf',
            ],
            default => [
                $preview['content'],
                'text/html; charset=UTF-8',
                'html',
            ],
        };

        return response(
            $content,
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => $disposition . '; filename="' . $filename . '.' . $extension . '"',
            ]
        );
    }

    /**
     * Get print meta
     *
     * @param Request $request
     * @param int|string $orderId
     * @return JsonResponse
     * @throws Throwable
     */
    public function printMeta(Request $request, int|string $orderId): JsonResponse
    {
        return ApiResponse::success(
            $this->service->printMeta(
                $orderId,
                $request->input('branch_id'),
                $request->input('register_id'),
            )
        );
    }

    /**
     * Agrega un custom / open item a una orden existente. Solo en edit
     * mode (la orden ya fue enviada o esta en curso). En create mode el
     * user agrega items al cart con el flujo normal; el endpoint cart
     * equivalent queda como ticket futuro.
     *
     * @param StoreCustomOrderProductRequest $request
     * @param int|string $orderId
     * @return JsonResponse
     */
    public function storeCustomProduct(StoreCustomOrderProductRequest $request, int|string $orderId): JsonResponse
    {
        $order = $this->service->addCustomProduct($orderId, $request->validated());

        return ApiResponse::created(
            body: new ShowOrderResource($order),
            resource: __('order::attributes.orders.custom_product')
        );
    }

    /**
     * Cliente pidio la cuenta. Setea bill_requested_at y dispara
     * evento para impresion fiscal (stub hoy).
     */
    public function requestBill(int|string $orderId): JsonResponse
    {
        $this->service->requestBill($orderId);

        return ApiResponse::success(
            body: ["success" => true],
            message: __("order::messages.bill_requested_successfully"),
        );
    }

    /**
     * Pausa la orden. La mesa queda libre visualmente, la orden persiste.
     */
    public function pauseOrder(int|string $orderId): JsonResponse
    {
        $this->service->pauseOrder($orderId);

        return ApiResponse::success(
            body: ["success" => true],
            message: __("order::messages.order_paused_successfully"),
        );
    }

    /**
     * Reanuda una orden pausada.
     */
    public function resumeOrder(int|string $orderId): JsonResponse
    {
        $this->service->resumeOrder($orderId);

        return ApiResponse::success(
            body: ["success" => true],
            message: __("order::messages.order_resumed_successfully"),
        );
    }

    protected function normalizeDrawerFilters(Request $request): array
    {
        return [
            'search' => trim((string)$request->input('search', '')),
            'types' => $this->normalizeFilterValues($request->input('types', [])),
            'statuses' => $this->normalizeFilterValues($request->input('statuses', [])),
            'payment_statuses' => $this->normalizeFilterValues($request->input('payment_statuses', [])),
        ];
    }

    protected function normalizeFilterValues(mixed $values): array
    {
        return collect($values)
            ->filter(fn($value) => !is_null($value) && $value !== '')
            ->values()
            ->all();
    }
}
