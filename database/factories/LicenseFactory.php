<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Licenses whose file_path points at no real file; tests that need one put it there.
 *
 * @extends Factory<License>
 */
class LicenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->randomElement(['Building permit', 'Occupancy license', 'Activity license', 'Renovation permit', 'Energy certificate', 'Fire safety certificate']);
        $issued = fake()->dateTimeBetween('-3 years', '-1 month');

        return [
            'property_id' => Property::factory(),
            'title' => $title,
            'number' => strtoupper(fake()->bothify('LIC-####-??')),
            'issued_at' => $issued,
            'expires_at' => fake()->dateTimeBetween('+2 months', '+4 years'),
            'file_path' => 'licenses/'.Str::random(40).'.pdf',
            'file_name' => Str::slug($title).'.pdf',
            'file_size' => fake()->numberBetween(50_000, 3_000_000),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => today()->subDays(fake()->numberBetween(1, 200))]);
    }

    /**
     * Expires within License::EXPIRING_DAYS.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn () => ['expires_at' => today()->addDays(fake()->numberBetween(1, License::EXPIRING_DAYS))]);
    }
}
