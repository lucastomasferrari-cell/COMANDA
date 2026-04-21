<?php

namespace Modules\Cart\Http\Controllers\Api\V1;

use Darryldecode\Cart\Exceptions\InvalidItemException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cart\Facades\Cart;
use Modules\Cart\Http\Requests\Api\V1\StoreCartItemActionRequest;
use Modules\Cart\Http\Requests\Api\V1\StoreCartItemRequest;
use Modules\Core\Http\Controllers\Controller;
use Modules\Support\ApiResponse;

class CartItemController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCartItemRequest $request
     * @return JsonResponse
     * @throws InvalidItemException
     */
    public function store(StoreCartItemRequest $request): JsonResponse
    {
        Cart::store(
            $request->product_id,
            $request->qty,
            $request->options ?? [],
        );

        return ApiResponse::success(Cart::instance());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $cartId
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $cartId, string $id): JsonResponse
    {
        if (!isset(Cart::get($id)['attributes']['loyalty_gift']['id'])) {
            Cart::updateQuantity($id, $request->qty ?: 1);
        }

        return ApiResponse::success(Cart::instance());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $cartId
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $cartId, string $id): JsonResponse
    {
        Cart::remove($id);

        return ApiResponse::success(Cart::instance());
    }


    /**
     * Store action
     *
     * @param StoreCartItemActionRequest $request
     * @param string $cartId
     * @param string $id
     * @return JsonResponse
     */
    public function storeAction(StoreCartItemActionRequest $request, string $cartId, string $id): JsonResponse
    {
        if (!isset(Cart::get($id)['attributes']['loyalty_gift']['id'])) {
            Cart::storeAction($id, $request->action, $request->qty);
        }

        return ApiResponse::success(Cart::instance());
    }

    /**
     * Remove action
     *
     * @param Request $request
     * @param string $cartId
     * @param string $id
     * @return JsonResponse
     */
    public function destroyAction(Request $request, string $cartId, string $id): JsonResponse
    {
        Cart::deleteAction($id, $request->action);

        return ApiResponse::success(Cart::instance());
    }

}
