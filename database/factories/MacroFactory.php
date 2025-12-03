<?php

namespace Database\Factories;

use App\Models\Macro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Macro>
 */
class MacroFactory extends Factory
{
    protected $model = Macro::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(2, true)),
            'content' => $this->faker->sentences(2, true),
            'scope' => $this->faker->randomElement(['personal', 'global']),
            'created_by' => $this->faker->numberBetween(1, 5),
        ];
    }
}
