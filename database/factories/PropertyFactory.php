<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Properties in and around the map center (config('project.map_center'), Barcelona).
 *
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $center = config('project.map_center');
        $local = fake('es_ES');

        return [
            'name' => fake()->randomElement(['Casa', 'Edificio', 'Residencial', 'Torre', 'Villa', 'Can']).' '.$local->lastName(),
            'address' => $local->streetName().', '.fake()->numberBetween(1, 250),
            'city' => 'Barcelona',
            'postal_code' => '080'.str_pad((string) fake()->numberBetween(1, 42), 2, '0', STR_PAD_LEFT),
            'latitude' => round($center['lat'] + fake()->randomFloat(5, -0.035, 0.035), 7),
            'longitude' => round($center['lng'] + fake()->randomFloat(5, -0.05, 0.05), 7),
            'description' => fake()->paragraphs(2, true),
            'status' => 'published',
            'work_status' => fake()->randomElement(array_keys(Property::WORK_STATUSES)),
        ];
    }

    /**
     * Only staff can see it.
     */
    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'published']);
    }

    /**
     * Not on the map.
     */
    public function withoutLocation(): static
    {
        return $this->state(fn () => ['latitude' => null, 'longitude' => null]);
    }
}
