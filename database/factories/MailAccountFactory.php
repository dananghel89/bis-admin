<?php

namespace Database\Factories;

use App\Models\MailAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailAccount>
 */
class MailAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fake()->safeEmail(),
            'username' => null,
            'password' => 'mail-secret',
            'imap_host' => 'imap.example.test',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'smtp_host' => 'smtp.example.test',
            'smtp_port' => 465,
            'smtp_encryption' => 'ssl',
        ];
    }
}
