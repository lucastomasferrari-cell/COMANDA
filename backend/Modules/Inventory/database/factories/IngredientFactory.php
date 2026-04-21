<?php

namespace Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\Ingredient;

class IngredientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Ingredient::class;

    /**
     *
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'cost_per_unit' => $this->faker->randomFloat(3, 0.01, 10),
            'alert_quantity' => $this->faker->numberBetween(1, 20),
            'current_stock' => $this->faker->randomFloat(2, 0, 100),
            'is_returnable' => $this->faker->boolean(),
        ];
    }
}

