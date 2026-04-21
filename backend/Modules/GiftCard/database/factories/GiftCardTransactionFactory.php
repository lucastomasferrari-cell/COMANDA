<?php

namespace Modules\GiftCard\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\GiftCard\Enums\GiftCardTransactionType;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Models\GiftCardTransaction;
use Modules\User\Models\User;

class GiftCardTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = GiftCardTransaction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(GiftCardTransactionType::values());
        $amount = $this->faker->randomFloat(4, 5, 150);
        $balanceBefore = $this->resolveBalanceBefore($type, $amount);

        return [
            'created_by' => User::query()->inRandomOrder()->value('id'),
            'gift_card_id' => GiftCard::query()->inRandomOrder()->value('id') ?? GiftCard::factory(),
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->resolveBalanceAfter($type, $amount, $balanceBefore),
            'currency' => fn(array $attributes) => GiftCard::query()->find($attributes['gift_card_id'])?->currency ?? 'JOD',
            'exchange_rate' => null,
            'amount_in_order_currency' => null,
            'order_currency' => null,
            'order_id' => null,
            'branch_id' => fn(array $attributes) => GiftCard::query()->find($attributes['gift_card_id'])?->branch_id,
            'notes' => $this->faker->optional()->sentence(),
            'transaction_at' => now()->subDays($this->faker->numberBetween(0, 60)),
        ];
    }

    /**
     * Assign the transaction to a specific gift card.
     */
    public function forGiftCard(int|GiftCard $giftCard): static
    {
        return $this->state(function () use ($giftCard) {
            $giftCardModel = $giftCard instanceof GiftCard
                ? $giftCard
                : GiftCard::query()->findOrFail($giftCard);

            return [
                'gift_card_id' => $giftCardModel->id,
                'currency' => $giftCardModel->currency,
                'branch_id' => $giftCardModel->branch_id,
            ];
        });
    }

    /**
     * Create a purchase transaction.
     */
    public function purchase(): static
    {
        return $this->forType(GiftCardTransactionType::Purchase);
    }

    /**
     * Create a redeem transaction.
     */
    public function redeem(): static
    {
        return $this->forType(GiftCardTransactionType::Redeem);
    }

    /**
     * Create a refund transaction.
     */
    public function refund(): static
    {
        return $this->forType(GiftCardTransactionType::Refund);
    }

    /**
     * Create a recharge transaction.
     */
    public function recharge(): static
    {
        return $this->forType(GiftCardTransactionType::Recharge);
    }

    /**
     * Create an adjustment transaction.
     */
    public function adjustment(): static
    {
        return $this->forType(GiftCardTransactionType::Adjustment);
    }

    /**
     * Create an expire transaction.
     */
    public function expire(): static
    {
        return $this->forType(GiftCardTransactionType::Expire);
    }

    /**
     * Add order-currency conversion metadata to the transaction.
     */
    public function withOrderCurrency(?float $exchangeRate = null, ?string $orderCurrency = null): static
    {
        return $this->state(function (array $attributes) use ($exchangeRate, $orderCurrency) {
            $rate = $exchangeRate ?? $this->faker->randomFloat(8, 0.5, 1.5);
            $amount = (float) $attributes['amount'];

            return [
                'exchange_rate' => $rate,
                'amount_in_order_currency' => round($amount * $rate, 4),
                'order_currency' => $orderCurrency ?? $this->faker->randomElement(['JOD', 'USD', 'EUR']),
            ];
        });
    }

    /**
     * Create a transaction with a fixed balance transition.
     */
    public function withBalances(float|int|string $amount, float|int|string $balanceBefore, float|int|string $balanceAfter): static
    {
        return $this->state(fn() => [
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
        ]);
    }

    /**
     * Create a transaction for the provided type and matching balance direction.
     */
    protected function forType(GiftCardTransactionType $type): static
    {
        return $this->state(function () use ($type) {
            $amount = $this->faker->randomFloat(4, 5, 150);
            $balanceBefore = $this->resolveBalanceBefore($type->value, $amount);

            return [
                'type' => $type->value,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $this->resolveBalanceAfter($type->value, $amount, $balanceBefore),
            ];
        });
    }

    /**
     * Resolve a valid previous balance for the given transaction type.
     */
    protected function resolveBalanceBefore(string $type, float $amount): float
    {
        $transactionType = GiftCardTransactionType::from($type);

        return match ($transactionType) {
            GiftCardTransactionType::Redeem,
            GiftCardTransactionType::Expire => $this->faker->randomFloat(4, $amount, $amount + 300),
            default => $this->faker->randomFloat(4, 0, 300),
        };
    }

    /**
     * Resolve the resulting balance after applying the transaction amount.
     */
    protected function resolveBalanceAfter(string $type, float $amount, float $balanceBefore): float
    {
        $transactionType = GiftCardTransactionType::from($type);

        return match ($transactionType) {
            GiftCardTransactionType::Redeem,
            GiftCardTransactionType::Expire => round($balanceBefore - $amount, 4),
            default => round($balanceBefore + $amount, 4),
        };
    }
}
