<?php

namespace Tests\Feature\Admin;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->admin()->create(['name' => 'Sam Staff']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ana Architect',
            'email' => 'ana@example.com',
            'role' => User::EDITOR,
            'password' => 'first-pass',
            'password_confirmation' => 'first-pass',
        ], $overrides);
    }

    public function test_only_staff_can_open_or_change_users()
    {
        $architect = User::factory()->editor()->create();

        foreach (['/dashboard/users', '/dashboard/users/add-user', "/dashboard/users/view-user/{$architect->id}", "/dashboard/users/edit-user/{$architect->id}"] as $uri) {
            $this->actingAs($architect)->get($uri)->assertForbidden();
            $this->actingAs($this->staff)->get($uri)->assertOk();
        }

        $this->actingAs($architect)->post('/dashboard/users/add-user', $this->validData())->assertForbidden();
        $this->actingAs($architect)->patch("/dashboard/users/edit-user/{$architect->id}", $this->validData())->assertForbidden();
        $this->actingAs($architect)->delete("/dashboard/users/edit-user/{$this->staff->id}")->assertForbidden();
        $this->assertSame(2, User::count());
    }

    public function test_the_list_shows_roles_and_can_be_filtered()
    {
        User::factory()->editor()->create(['name' => 'Ana Architect', 'email' => 'ana@example.com']);
        User::factory()->editor()->create(['name' => 'Ben Builder']);

        $this->actingAs($this->staff)->get('/dashboard/users')
            ->assertSee('Ana Architect')->assertSee('Sam Staff')->assertSee('Architect')->assertSee('Staff');

        $this->actingAs($this->staff)->get('/dashboard/users?search=ana%40')
            ->assertSee('Ana Architect')->assertDontSee('Ben Builder');

        $this->actingAs($this->staff)->get('/dashboard/users?role=0')
            ->assertSee('Sam Staff')->assertDontSee('Ana Architect');
    }

    public function test_staff_add_an_architect_with_a_password()
    {
        $this->actingAs($this->staff)->post('/dashboard/users/add-user', $this->validData())
            ->assertSessionHasNoErrors();

        $ana = User::where('email', 'ana@example.com')->sole();
        $this->assertTrue($ana->isArchitect());
        $this->assertTrue(Hash::check('first-pass', $ana->password));

        // and the architect can log in with it
        auth()->logout();
        $this->post('/login', ['email' => 'ana@example.com', 'password' => 'first-pass'])->assertRedirect('/dashboard');
    }

    public function test_adding_by_ajax_goes_to_the_new_account()
    {
        $response = $this->actingAs($this->staff)->postJson('/dashboard/users/add-user', $this->validData(['role' => User::ADMIN]))
            ->assertOk();

        $user = User::where('email', 'ana@example.com')->sole();
        $response->assertExactJson(['message' => 'Staff account for Ana Architect created.', 'redirect' => route('userView', $user)]);
        $this->assertTrue($user->isStaff());
    }

    public function test_add_validation()
    {
        $this->actingAs($this->staff)
            ->postJson('/dashboard/users/add-user', ['name' => '', 'email' => $this->staff->email, 'role' => 5, 'password' => 'short', 'password_confirmation' => 'other'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'role', 'password']);

        $this->actingAs($this->staff)
            ->postJson('/dashboard/users/add-user', $this->validData(['password' => '', 'password_confirmation' => '']))
            ->assertJsonValidationErrors('password');

        $this->assertSame(1, User::count());
    }

    public function test_staff_edit_name_email_and_role_and_keep_the_password()
    {
        $ana = User::factory()->editor()->create(['password' => 'keep-this-1']);

        $this->actingAs($this->staff)
            ->patch("/dashboard/users/edit-user/{$ana->id}", $this->validData(['name' => 'Ana B', 'email' => 'anab@example.com', 'role' => User::ADMIN, 'password' => '', 'password_confirmation' => '']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('userView', $ana));

        $ana->refresh();
        $this->assertSame('Ana B', $ana->name);
        $this->assertSame('anab@example.com', $ana->email);
        $this->assertTrue($ana->isStaff());
        $this->assertTrue(Hash::check('keep-this-1', $ana->password));
    }

    public function test_staff_set_a_new_password_and_the_email_may_stay_the_same()
    {
        $ana = User::factory()->editor()->create(['email' => 'ana@example.com']);

        $this->actingAs($this->staff)
            ->patch("/dashboard/users/edit-user/{$ana->id}", $this->validData(['password' => 'second-pass', 'password_confirmation' => 'second-pass']))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('second-pass', $ana->fresh()->password));
    }

    public function test_the_last_staff_account_must_stay_staff()
    {
        $this->actingAs($this->staff)
            ->patchJson("/dashboard/users/edit-user/{$this->staff->id}", $this->validData(['email' => $this->staff->email, 'role' => User::EDITOR, 'password' => '', 'password_confirmation' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role' => 'This is the last staff account, so it must stay Staff.']);

        $this->assertTrue($this->staff->fresh()->isStaff());

        // With a second staff account, the change is allowed
        User::factory()->admin()->create();
        $this->actingAs($this->staff)
            ->patchJson("/dashboard/users/edit-user/{$this->staff->id}", $this->validData(['email' => $this->staff->email, 'role' => User::EDITOR, 'password' => '', 'password_confirmation' => '']))
            ->assertOk();
        $this->assertTrue($this->staff->fresh()->isArchitect());
    }

    public function test_staff_delete_an_architect_and_their_assignments()
    {
        $ana = User::factory()->editor()->create();
        Property::factory()->create()->architects()->attach($ana);

        $this->actingAs($this->staff)
            ->deleteJson("/dashboard/users/edit-user/{$ana->id}")
            ->assertOk()
            ->assertJson(['redirect' => route('users')]);

        $this->assertNull($ana->fresh());
        $this->assertSame(0, DB::table('property_user')->count());
        $this->assertSame(1, Property::count());
    }

    public function test_staff_cant_delete_themselves_or_the_last_staff_account()
    {
        $this->actingAs($this->staff)
            ->deleteJson("/dashboard/users/edit-user/{$this->staff->id}")
            ->assertStatus(422)
            ->assertJson(['message' => 'You can\'t delete your own account.']);

        $this->actingAs($this->staff)
            ->delete("/dashboard/users/edit-user/{$this->staff->id}")
            ->assertSessionHas('error', 'You can\'t delete your own account.');

        $this->assertNotNull($this->staff->fresh());
    }

    public function test_the_view_page_lists_an_architects_properties()
    {
        $ana = User::factory()->editor()->create();
        $ana->properties()->attach(Property::factory()->create(['name' => 'Casa Gaudí']));

        $this->actingAs($this->staff)->get("/dashboard/users/view-user/{$ana->id}")
            ->assertSee('Casa Gaudí')
            ->assertSee('data-confirm="Delete the account of', false);

        // no delete button on your own page
        $this->actingAs($this->staff)->get("/dashboard/users/view-user/{$this->staff->id}")
            ->assertDontSee('data-confirm="Delete the account of', false);
    }

    public function test_a_missing_user_is_a_404()
    {
        $this->actingAs($this->staff)->get('/dashboard/users/view-user/999')->assertNotFound();
    }
}
