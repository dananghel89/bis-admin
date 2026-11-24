<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => User::EDITOR,
        ];
    }

    /**
     * Staff: manages properties, users and contact messages.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ADMIN,
        ]);
    }

    /**
     * An architect: sees the properties made visible to them (the default).
     */
    public function editor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::EDITOR,
        ]);
    }
}
