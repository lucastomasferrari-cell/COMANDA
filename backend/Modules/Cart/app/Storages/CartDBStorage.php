<?php

namespace Modules\Cart\Storages;

use Darryldecode\Cart\CartCollection;
use Modules\Cart\Models\Cart;

class CartDBStorage
{
    /**
     * Retrieve the cart data for a given key.
     *
     * @param string $key
     * @return CartCollection|array
     */
    public function get(string $key): CartCollection|array
    {
        $cart = Cart::query()->find($key);

        if (!$cart || empty($cart->data)) {
            return [];
        }

        // Wrap the data in a CartCollection if it exists, otherwise a simple Collection
        return new CartCollection(items: $cart->data);
    }

    /**
     * Determine if a cart exists for the given key.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Cart::query()->whereKey($key)->exists();
    }

    /**
     * Store or update the cart data for a given key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function put(string $key, mixed $value): void
    {
        Cart::query()
            ->updateOrCreate(
                ['id' => $key],
                ['data' => $value]
            );
    }
}
