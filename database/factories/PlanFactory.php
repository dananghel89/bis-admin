<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Plans whose file_path points at no real file; tests that need one put it there.
 *
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->randomElement(['Ground floor', 'First floor', 'Second floor', 'Roof', 'Basement', 'Site plan', 'Elevations', 'Sections', 'Electrical', 'Plumbing']);

        return [
            'property_id' => Property::factory(),
            'title' => $title,
            'description' => fake()->optional()->sentence(),
            'file_path' => 'plans/'.Str::random(40).'.pdf',
            'file_name' => Str::slug($title).'.pdf',
            'file_size' => fake()->numberBetween(50_000, 5_000_000),
        ];
    }
}
