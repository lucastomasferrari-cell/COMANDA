<?php

namespace Modules\GiftCard\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Branch\Models\Branch;
use Modules\GiftCard\Enums\GiftCardScope;
use Modules\GiftCard\Enums\GiftCardStatus;
use Modules\GiftCard\Models\GiftCard;
use Modules\GiftCard\Models\GiftCardBatch;
use Modules\GiftCard\Services\GiftCardCode\GiftCardCodeServiceInterface;
use Modules\User\Models\User;

class GiftCardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = GiftCard::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $branch = Branch::query()->inRandomOrder()->first();
        $initialBalance = $this->faker->randomFloat(4, 10, 500);

        return [
            'created_by' => User::query()->inRandomOrder()->value('id'),
            'branch_id' => $branch?->id,
            'gift_card_batch_id' => null,
            'code' => null,
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'currency' => $branch?->currency ?? 'JOD',
            'scope' => GiftCardScope::Branch->value,
            'customer_id' => $this->faker->boolean(35) ? User::query()->inRandomOrder()->value('id') : null,
            'status' => GiftCardStatus::Active->value,
            'expiry_date' => $this->faker->boolean(70) ? now()->addDays($this->faker->numberBetween(30, 365)) : null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create a branch-scoped gift card.
     */
    public function branchScoped(?int $branchId = null): static
    {
        return $this->state(function () use ($branchId) {
            $branch = $branchId
                ? Branch::query()->find($branchId)
                : Branch::query()->inRandomOrder()->first();

            return [
                'branch_id' => $branch?->id ?? Branch::factory(),
                'currency' => $branch?->currency ?? 'JOD',
                'scope' => GiftCardScope::Branch->value,
            ];
        });
    }

    /**
     * Create a globally scoped gift card.
     */
    public function global(): static
    {
        return $this->state(fn() => [
            'branch_id' => null,
            'scope' => GiftCardScope::Global->value,
            'currency' => 'JOD',
        ]);
    }

    /**
     * Assign the gift card to a customer.
     */
    public function forCustomer(int|User|null $customer = null): static
    {
        return $this->state(fn() => [
            'customer_id' => $customer instanceof User
                ? $customer->id
                : ($customer ?? User::query()->inRandomOrder()->value('id') ?? User::factory()),
        ]);
    }

    /**
     * Remove the customer assignment.
     */
    public function withoutCustomer(): static
    {
        return $this->state(fn() => ['customer_id' => null]);
    }

    /**
     * Create a gift card that expires soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn() => [
            'status' => GiftCardStatus::Active->value,
            'expiry_date' => now()->addDays($this->faker->numberBetween(1, 7)),
        ]);
    }

    /**
     * Create a gift card without any expiry date.
     */
    public function noExpiry(): static
    {
        return $this->state(fn() => ['expiry_date' => null]);
    }

    /**
     * Link the gift card to a batch and align branch, currency, and generated code.
     */
    public function linkedToBatch(int|GiftCardBatch|null $batch = null): static
    {
        return $this->state(function () use ($batch) {
            $giftCardBatch = match (true) {
                $batch instanceof GiftCardBatch => $batch,
                is_int($batch) => GiftCardBatch::query()->find($batch),
                default => GiftCardBatch::query()->inRandomOrder()->first(),
            };

            if (!$giftCardBatch) {
                return [
                    'gift_card_batch_id' => GiftCardBatch::factory(),
                ];
            }

            return [
                'gift_card_batch_id' => $giftCardBatch->id,
                'branch_id' => $giftCardBatch->branch_id,
                'currency' => $giftCardBatch->currency,
                'scope' => is_null($giftCardBatch->branch_id)
                    ? GiftCardScope::Global->value
                    : GiftCardScope::Branch->value,
                'code' => app(GiftCardCodeServiceInterface::class)->generate($giftCardBatch->prefix),
            ];
        });
    }

    /**
     * Set specific balance values for the card.
     */
    public function withBalance(float|int|string $initialBalance, float|int|string $currentBalance): static
    {
        return $this->state(fn() => [
            'initial_balance' => $initialBalance,
            'current_balance' => $currentBalance,
        ]);
    }

    /**
     * Create an active gift card with remaining balance.
     */
    public function active(): static
    {
        $initialBalance = $this->faker->randomFloat(4, 25, 500);
        $currentBalance = $this->faker->randomFloat(4, 1, $initialBalance);

        return $this->state(fn() => [
            'initial_balance' => $initialBalance,
            'current_balance' => $currentBalance,
            'status' => GiftCardStatus::Active->value,
            'expiry_date' => now()->addDays(rand(30, 365)),
        ]);
    }

    /**
     * Create a fully used gift card.
     */
    public function used(): static
    {
        $initialBalance = $this->faker->randomFloat(4, 10, 300);

        return $this->state(fn() => [
            'initial_balance' => $initialBalance,
            'current_balance' => 0,
            'status' => GiftCardStatus::Used->value,
            'expiry_date' => now()->addDays(rand(7, 120)),
        ]);
    }

    /**
     * Create an expired gift card.
     */
    public function expired(): static
    {
        $initialBalance = $this->faker->randomFloat(4, 10, 300);
        $currentBalance = $this->faker->randomFloat(4, 0, $initialBalance);

        return $this->state(fn() => [
            'initial_balance' => $initialBalance,
            'current_balance' => $currentBalance,
            'status' => GiftCardStatus::Expired->value,
            'expiry_date' => now()->subDays(rand(1, 90)),
        ]);
    }

    /**
     * Create a disabled gift card.
     */
    public function disabled(): static
    {
        $initialBalance = $this->faker->randomFloat(4, 10, 300);
        $currentBalance = $this->faker->randomFloat(4, 0, $initialBalance);

        return $this->state(fn() => [
            'initial_balance' => $initialBalance,
            'current_balance' => $currentBalance,
            'status' => GiftCardStatus::Disabled->value,
        ]);
    }

    /**
     * Create a disabled gift card that still holds remaining balance.
     */
    public function disabledWithBalance(): static
    {
        $initialBalance = $this->faker->randomFloat(4, 20, 300);
        $currentBalance = $this->faker->randomFloat(4, 1, $initialBalance);

        return $this->state(fn() => [
            'initial_balance' => $initialBalance,
            'current_balance' => $currentBalance,
            'status' => GiftCardStatus::Disabled->value,
        ]);
    }
}
