<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo architects (editors). All of them use the password below.
 */
class ArchitectSeeder extends Seeder
{
    public const PASSWORD = 'architect-demo';

    public const COUNT = 6;

    public function run(): void
    {
        $architects = User::factory()->editor()->count(self::COUNT)->create(['password' => self::PASSWORD]);

        $this->command?->info(self::COUNT.' demo architects, password "'.self::PASSWORD.'":');
        $this->command?->line('  '.$architects->pluck('email')->implode(', '));
    }
}
