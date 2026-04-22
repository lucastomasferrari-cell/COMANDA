<?php

namespace Modules\Order\Services\Order;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;
use Modules\Printer\Enum\PrintContentType;
use Throwable;

interface OrderServiceInterface
{
    /**
     * Label for the resource.
     *
     * @return string
     */
    public function label(): string;

    /**
     * Model for the resource.
     *
     * @return string
     */
    public function model(): string;

    /**
     * Get a new instance of the model.
     *
     * @return Order
     */
    public function getModel(): Order;


    /**
     * Display a listing of the resource.
     *
     * @param array $filters
     * @param array $sorts
     * @return LengthAwarePaginator
     */
    public function get(array $filters = [], array $sorts = []): LengthAwarePaginator;

    /**
     * Show the specified resource.
     *
     * @param int|string $id
     * @param array|null $with
     * @return Order
     * @throws ModelNotFoundException
     */
    public function show(int|string $id, ?array $with = null): Order;

    /**
     * Get structure filters for frontend
     *
     * @return array
     */
    public function getStructureFilters(): array;

    /**
     * Cancel order
     *
     * @param int|string $id
     * @param array $data
     * @return void
     * @throws Throwable
     */
    public function cancel(int|string $id, array $data): void;

    /**
     * Refund order
     *
     * @param int|string $id
     * @param array $data
     * @return void
     * @throws Throwable
     */
    public function refund(int|string $id, array $data): void;

    /**
     * Init edit order
     *
     * @param int|string $id
     * @return array
     * @throws Throwable
     */
    public function initEdit(int|string $id): array;

    /**
     * Get specific resource
     *
     * @param int|string $id
     * @param bool $withBranch
     * @return Order|Builder|EloquentCollection|array;
     * @throws ModelNotFoundException
     */
    public function findOrFail(int|string $id, bool $withBranch = false): Order|Builder|EloquentCollection|array;

    /**
     * Get update status meta
     *
     * @param int|string $id
     * @return array
     */
    public function getUpdateStatusMeta(int|string $id): array;

    /**
     * Get active orders
     *
     * @param int|null $branchId
     * @return Collection
     */
    public function activeOrders(?int $branchId = null, array $filters = []): Collection;

    /**
     * Get upcoming orders
     *
     * @param int|null $branchId
     * @return Collection
     */
    public function upcomingOrders(?int $branchId = null, array $filters = []): Collection;

    /**
     * Move to next status
     *
     * @param string|int $id
     * @return OrderStatus
     * @throws Throwable
     */
    public function moveToNextStatus(string|int $id): OrderStatus;

    /**
     * Kitchen move to next status
     *
     * @param string|int $id
     * @return OrderStatus
     * @throws Throwable
     */
    public function kitchenMoveToNextStatus(string|int $id): OrderStatus;

    /**
     * Preview print
     *
     * @param string|int $id
     * @param PrintContentType $type
     * @param int|null $kitchenId
     * @param int|null $specificId
     * @param string $contentType
     * @return array
     * @throws Throwable
     */
    public function previewPrint(
        string|int       $id,
        PrintContentType $type,
        ?int             $kitchenId = null,
        ?int             $specificId = null,
        string           $contentType = 'html'
    ): array;

    /**
     * Get print meta
     *
     * @param string|int $id
     * @param int|null $branchId
     * @param int|null $registerId
     * @return array
     * @throws Throwable
     */
    public function printMeta(string|int $id, ?int $branchId = null, ?int $registerId = null): array;

    /**
     * Agrega un custom / open item a la orden existente. Solo disponible
     * en edit mode (la orden ya tiene id). Recalcula subtotal / total /
     * due_amount al terminar.
     *
     * @param int|string $id
     * @param array $data {custom_name, custom_price, custom_description?, quantity}
     * @return Order
     */
    public function addCustomProduct(int|string $id, array $data): Order;

    /**
     * Marca que el cliente pidio la cuenta. Setea bill_requested_at y
     * dispara OrderBillRequested (que eventualmente imprime el ticket).
     *
     * @param int|string $id
     * @return Order
     */
    public function requestBill(int|string $id): Order;

    /**
     * Poner la orden en espera. La mesa queda available, la orden
     * persiste y aparece con icono pausa en ActiveOrdersPanel.
     *
     * @param int|string $id
     * @return Order
     */
    public function pauseOrder(int|string $id): Order;

    /**
     * Reanudar una orden pausada. Si la mesa esta available, vuelve a
     * occupied. Si otra orden esta activa en la misma mesa, el
     * listener no fuerza el cambio.
     *
     * @param int|string $id
     * @return Order
     */
    public function resumeOrder(int|string $id): Order;

    /**
     * Anula (void) un item de una orden. No hace DELETE fisico.
     *
     * @param int|string $orderId
     * @param int $orderProductId
     * @param array $data {void_reason_id, void_note}
     * @return Order
     */
    public function voidOrderProduct(int|string $orderId, int $orderProductId, array $data): Order;
}
