<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the first admin from config('project.seed_admin') if that email isn't taken.
     * An existing user is left exactly as it is, so seeding twice is safe.
     */
    public function run(): void
    {
        $admin = config('project.seed_admin');

        User::firstOrCreate(['email' => $admin['email']], [
            'name' => $admin['name'],
            'password' => Hash::make($admin['password']),
            'role' => User::ADMIN,
        ]);
    }
}
