<?php

namespace Database\Factories;

use App\Models\InternalNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternalNote>
 */
class InternalNoteFactory extends Factory
{
    protected $model = InternalNote::class;

    public function definition(): array
    {
        return [
            'agent_id' => $this->faker->numberBetween(1, 5),
            'ticket_code' => 'TKT-'.$this->faker->unique()->numerify('####'),
            'content' => $this->faker->paragraph(),
        ];
    }
}
