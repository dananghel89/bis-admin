<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyStatusChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyStatusChange>
 */
class PropertyStatusChangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'from_status' => 'pending',
            'to_status' => 'in_progress',
            'note' => fake()->optional()->sentence(),
        ];
    }
}
