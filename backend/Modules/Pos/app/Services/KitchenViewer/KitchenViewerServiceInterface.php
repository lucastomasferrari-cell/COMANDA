<?php

namespace Modules\Pos\Services\KitchenViewer;

use Throwable;

interface KitchenViewerServiceInterface
{
    /**
     * Get configuration
     *
     * @param int|null $branchId
     * @return array
     */
    public function getConfiguration(?int $branchId = null): array;

    /**
     * Get orders
     *
     * @param int|null $branchId
     * @return array
     */
    public function getOrders(?int $branchId = null): array;

    /**
     * Move order products to next status
     *
     * @param int|string $orderId
     * @param array|int $ids
     * @return void
     * @throws Throwable
     */
    public function moveOrderProductToNextStatus(int|string $orderId, array|int $ids): void;
}
