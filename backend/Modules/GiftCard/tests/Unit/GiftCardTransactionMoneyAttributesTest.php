<?php

namespace Modules\GiftCard\Tests\Unit;

use Modules\GiftCard\Models\GiftCardTransaction;
use Modules\Support\Money;
use Tests\TestCase;

class GiftCardTransactionMoneyAttributesTest extends TestCase
{
    public function test_amount_in_order_currency_is_cast_to_money_using_order_currency(): void
    {
        $transaction = new GiftCardTransaction([
            'currency' => 'USD',
            'amount' => 10,
            'balance_before' => 20,
            'balance_after' => 10,
            'amount_in_order_currency' => 7.092,
            'order_currency' => 'JOD',
        ]);

        $this->assertInstanceOf(Money::class, $transaction->amount_in_order_currency);
        $this->assertSame('JOD', $transaction->amount_in_order_currency->currency());
        $this->assertEquals(7.092, $transaction->amount_in_order_currency->amount());
    }

    public function test_amount_in_order_currency_returns_null_without_order_currency(): void
    {
        $transaction = new GiftCardTransaction([
            'currency' => 'USD',
            'amount' => 10,
            'balance_before' => 20,
            'balance_after' => 10,
            'amount_in_order_currency' => 7.092,
            'order_currency' => null,
        ]);

        $this->assertNull($transaction->amount_in_order_currency);
    }
}
