<?php

namespace Modules\GiftCard\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Branch\Models\Branch;
use Modules\GiftCard\Models\GiftCardBatch;
use Modules\User\Models\User;

class GiftCardBatchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = GiftCardBatch::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $branch = Branch::query()->inRandomOrder()->first();

        return [
            'created_by' => User::query()->inRandomOrder()->value('id'),
            'branch_id' => $branch?->id,
            'name' => [
                'en' => 'Gift Card Batch ' . $this->faker->unique()->bothify('??##'),
                'ar' => 'دفعة بطاقات هدايا ' . $this->faker->unique()->numerify('###'),
            ],
            'prefix' => strtoupper($this->faker->unique()->lexify('GC??')),
            'quantity' => $this->faker->numberBetween(5, 25),
            'value' => $this->faker->randomFloat(4, 10, 500),
            'currency' => $branch?->currency ?? 'JOD',
        ];
    }

    /**
     * Create a batch assigned to a branch.
     */
    public function withBranch(?int $branchId = null): static
    {
        return $this->state(function () use ($branchId) {
            $branch = $branchId
                ? Branch::query()->find($branchId)
                : Branch::query()->inRandomOrder()->first();

            return [
                'branch_id' => $branch?->id ?? Branch::factory(),
                'currency' => $branch?->currency ?? 'JOD',
            ];
        });
    }

    /**
     * Create a batch without branch assignment.
     */
    public function global(): static
    {
        return $this->state(fn() => [
            'branch_id' => null,
            'currency' => 'JOD',
        ]);
    }

    /**
     * Create a small batch for limited campaigns or manual testing.
     */
    public function smallBatch(): static
    {
        return $this->state(fn() => [
            'quantity' => $this->faker->numberBetween(3, 10),
        ]);
    }

    /**
     * Create a large batch for marketing campaigns.
     */
    public function largeBatch(): static
    {
        return $this->state(fn() => [
            'quantity' => $this->faker->numberBetween(50, 250),
        ]);
    }

    /**
     * Create a higher-value batch.
     */
    public function highValue(): static
    {
        return $this->state(fn() => [
            'value' => $this->faker->randomFloat(4, 100, 500),
        ]);
    }
}
