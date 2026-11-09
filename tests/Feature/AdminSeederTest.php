<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_one_admin_from_the_settings()
    {
        // A fresh install gets its first admin from db:seed
        config([
            'project.seed_admin.name' => 'Site Admin',
            'project.seed_admin.email' => 'owner@example.com',
            'project.seed_admin.password' => 'chosen-secret',
        ]);

        $this->seed();

        $admin = User::where('email', 'owner@example.com')->first();
        $this->assertNotNull($admin);
        $this->assertSame('Site Admin', $admin->name);
        $this->assertSame(User::ADMIN, (int) $admin->role);
        $this->assertTrue(Hash::check('chosen-secret', $admin->password));
    }

    public function test_seeding_twice_keeps_one_admin_and_its_password()
    {
        config(['project.seed_admin.email' => 'owner@example.com', 'project.seed_admin.password' => 'first']);
        $this->seed();

        config(['project.seed_admin.password' => 'second']);
        $this->seed();

        $this->assertSame(1, User::where('email', 'owner@example.com')->count());
        $this->assertTrue(Hash::check('first', User::where('email', 'owner@example.com')->value('password')));
    }

    public function test_defaults_are_neutral()
    {
        $this->assertSame('admin@example.com', config('project.seed_admin.email'));
        $this->assertSame('password', config('project.seed_admin.password'));
    }
}
