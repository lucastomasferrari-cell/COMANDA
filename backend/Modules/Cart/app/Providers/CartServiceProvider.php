<?php

namespace Modules\Cart\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Cart\Cart;
use Modules\Cart\Storages\CartDBStorage;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(Cart::class, function ($app) {
            $sessionKey = request()->route('cartId') ?: auth()->id();
            return new Cart(
                new CartDBStorage(),
                $app['events'],
                'cart',
                "cart_$sessionKey",
                config("cart.cart")
            );
        });

        $this->app->alias(Cart::class, 'cart');
    }
}
