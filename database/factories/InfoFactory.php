<?php

namespace Database\Factories;

use App\Models\Info;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Info>
 */
class InfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(4),
            'content' => fake()->paragraph(),
            'author' => User::factory()->editor(),
            'status' => 'public',
        ];
    }

    /**
     * Only editors can see it.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'private',
        ]);
    }
}
