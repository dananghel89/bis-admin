<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_changes_name_and_email()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/dashboard/settings', ['name' => 'Renamed', 'email' => 'renamed@example.com'])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Renamed', $user->name);
        $this->assertSame('renamed@example.com', $user->email);
    }

    public function test_email_must_be_unique_but_may_stay_the_same()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->patch('/dashboard/settings', ['name' => $user->name, 'email' => $user->email])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->patch('/dashboard/settings', ['name' => $user->name, 'email' => $other->email])
            ->assertSessionHasErrors('email');
    }

    public function test_user_changes_password_with_confirmation()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/dashboard/settings', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'new-secret-1',
                'password_confirmation' => 'other-value',
            ])
            ->assertSessionHasErrors('password');

        $this->actingAs($user)
            ->patch('/dashboard/settings', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'new-secret-1',
                'password_confirmation' => 'new-secret-1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-secret-1', $user->fresh()->password));
    }

    public function test_new_password_needs_at_least_8_characters()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/dashboard/settings', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'short12',
                'password_confirmation' => 'short12',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_blank_password_keeps_the_old_one()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/dashboard/settings', ['name' => 'Same', 'email' => $user->email, 'password' => ''])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
