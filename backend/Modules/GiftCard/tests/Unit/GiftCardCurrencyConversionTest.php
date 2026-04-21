<?php

namespace Modules\GiftCard\Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Services\GiftCard\GiftCardServiceInterface;
use Modules\Order\Models\Order;
use Modules\Support\Money;
use Tests\TestCase;

class GiftCardCurrencyConversionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->instance('setting', new class
        {
            public function get(string $key, mixed $default = null): mixed
            {
                return match ($key) {
                    'default_currency' => 'JOD',
                    'default_locale' => 'en',
                    default => $default,
                };
            }

            public function set(array $data): array
            {
                return $data;
            }
        });

        Schema::dropIfExists('currency_rates');
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency')->unique();
            $table->decimal('rate', 18, 8)->nullable();
            $table->timestamps();
        });

        \DB::table('currency_rates')->insert([
            ['currency' => 'JOD', 'rate' => 1],
            ['currency' => 'USD', 'rate' => 1.41],
            ['currency' => 'EUR', 'rate' => 0.77],
        ]);

        Cache::flush();
    }

    public function test_convert_supports_default_currency_to_default_currency(): void
    {
        $giftCard = new GiftCard(['currency' => 'JOD']);

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 10.125, 'JOD', 1),
            10.125,
            'JOD',
        );
    }

    public function test_convert_supports_same_non_default_currency(): void
    {
        $giftCard = new GiftCard(['currency' => 'USD']);

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 10.0, 'USD', 1.41),
            10.0,
            'USD',
        );
    }

    public function test_convert_ignores_different_rate_when_order_currency_matches_gift_card_currency(): void
    {
        $giftCard = new GiftCard(['currency' => 'USD']);

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 10.0, 'USD', 9.99),
            10.0,
            'USD',
        );
    }

    public function test_convert_supports_non_default_to_non_default_through_default_currency(): void
    {
        $giftCard = new GiftCard(['currency' => 'EUR']);

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 14.1, 'USD', 1.41),
            7.7,
            'EUR',
        );
    }

    public function test_convert_supports_foreign_currency_to_default_currency(): void
    {
        $giftCard = new GiftCard(['currency' => 'JOD']);

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 10.0, 'USD', 1.41),
            7.092,
            'JOD',
        );
    }

    public function test_convert_supports_default_currency_to_foreign_currency(): void
    {
        $giftCard = new GiftCard(['currency' => 'USD']);

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 10.0, 'JOD', 1),
            14.1,
            'USD',
        );
    }

    public function test_convert_uses_override_currency_rate_when_provided(): void
    {
        $giftCard = new GiftCard(['currency' => 'EUR']);

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 15.0, 'USD', 1.41),
            8.19,
            'EUR',
        );

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 15.0, 'USD', 1.5),
            7.7,
            'EUR',
        );
    }

    public function test_convert_rounds_to_target_gift_card_currency_precision(): void
    {
        $giftCard = new GiftCard(['currency' => 'EUR']);

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 1.0, 'USD', 1.41),
            0.55,
            'EUR',
        );
    }

    public function test_convert_supports_small_fractional_amounts(): void
    {
        $giftCard = new GiftCard(['currency' => 'USD']);

        $this->assertMoneyEquals(
            $this->convertForGiftCard($giftCard, 0.005, 'JOD', 1),
            0.01,
            'USD',
        );
    }

    public function test_convert_can_be_called_from_order_context_values(): void
    {
        $order = new Order([
            'currency' => 'USD',
            'currency_rate' => 1.41,
        ]);

        $giftCard = new GiftCard(['currency' => 'EUR']);

        $this->assertMoneyEquals(
            $this->convertForOrder($order, $giftCard, 14.1),
            7.7,
            'EUR',
        );
    }

    protected function convertForOrder(Order $order, GiftCard $giftCard, float $amount): Money
    {
        return $this->convertForGiftCard(
            giftCard: $giftCard,
            amount: $amount,
            orderCurrency: $order->currency,
            orderCurrencyRate: $order->currency_rate,
        );
    }

    protected function convertForGiftCard(
        GiftCard $giftCard,
        float $amount,
        string $orderCurrency,
        float $orderCurrencyRate,
    ): Money {
        return app(GiftCardServiceInterface::class)->convertOrderAmountToGiftCardAmount(
            giftCard: $giftCard,
            amount: $amount,
            orderCurrency: $orderCurrency,
            orderCurrencyRate: $orderCurrencyRate,
        );
    }

    protected function assertMoneyEquals(Money $money, float $amount, string $currency): void
    {
        $this->assertSame($currency, $money->currency());
        $this->assertEqualsWithDelta($amount, $money->amount(), 0.0000001);
    }
}
