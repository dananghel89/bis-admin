<?php

use App\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the first admin from config('project.seed_admin') if that email isn't taken.
     * An existing user is left exactly as it is, so seeding twice is safe.
     *
     * @return void
     */
    public function run()
    {
        $admin = config('project.seed_admin');

        User::firstOrCreate(['email' => $admin['email']], [
            'name' => $admin['name'],
            'password' => bcrypt($admin['password']),
            'role' => User::ADMIN,
        ]);
    }
}
