<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data: architects, then properties with their plans and licenses.
     * The admin is not seeded: create it with `php artisan app:create-admin`.
     */
    public function run(): void
    {
        $this->call([
            ArchitectSeeder::class,
            PropertySeeder::class,
        ]);
    }
}
