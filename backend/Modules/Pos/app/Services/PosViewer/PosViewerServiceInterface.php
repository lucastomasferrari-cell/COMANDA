<?php

namespace Modules\Pos\Services\PosViewer;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface PosViewerServiceInterface
{
    /**
     * Get POS Screen configuration
     *
     * @param int|null $branchId
     * @return array
     */
    public function getConfiguration(?int $branchId = null): array;

    /**
     * Get Menu categories and products
     *
     * @param int $menuId
     * @return array
     */
    public function getMenuItems(int $menuId): array;

    /**
     * Get tree categories
     *
     * @param int $menuId
     * @return AnonymousResourceCollection
     */
    public function getCategories(int $menuId): AnonymousResourceCollection;

    /**
     * Get products
     *
     * @param int $menuId
     * @return AnonymousResourceCollection
     */
    public function getProducts(int $menuId): AnonymousResourceCollection;
}
