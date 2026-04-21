<?php

namespace Modules\Cart\Http\Controllers\Api\V1;

use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Illuminate\Http\JsonResponse;
use Modules\Cart\Facades\Cart;
use Modules\Core\Http\Controllers\Controller;
use Modules\Order\Enums\OrderType;
use Modules\Support\ApiResponse;

class CartOrderTypeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param string $cartId
     * @param OrderType $type
     * @return JsonResponse
     * @throws InvalidConditionException
     */
    public function store(string $cartId, OrderType $type): JsonResponse
    {
        Cart::addOrderType($type);

        return ApiResponse::success(Cart::instance());
    }

    /**
     * Destroy resource's.
     *
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        Cart::removeOrderType();

        return ApiResponse::success(Cart::instance());
    }
}
