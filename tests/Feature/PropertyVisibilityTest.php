<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property::visibleTo(): staff see everything; an architect sees published
 * properties assigned to them, or with no architect at all.
 */
class PropertyVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_who_sees_which_property()
    {
        $staff = User::factory()->admin()->create();
        $ana = User::factory()->editor()->create();
        $ben = User::factory()->editor()->create();

        $draft = Property::factory()->draft()->create();
        $draftForAna = Property::factory()->draft()->create();
        $draftForAna->architects()->attach($ana);
        $open = Property::factory()->published()->create();
        $anas = Property::factory()->published()->create();
        $anas->architects()->attach($ana);
        $shared = Property::factory()->published()->create();
        $shared->architects()->attach([$ana->id, $ben->id]);
        $bens = Property::factory()->published()->create();
        $bens->architects()->attach($ben);

        $ids = fn (User $user) => Property::visibleTo($user)->orderBy('id')->pluck('id')->all();

        $this->assertSame([$draft->id, $draftForAna->id, $open->id, $anas->id, $shared->id, $bens->id], $ids($staff));
        $this->assertSame([$open->id, $anas->id, $shared->id], $ids($ana));
        $this->assertSame([$open->id, $shared->id, $bens->id], $ids($ben));

        $this->assertTrue($bens->isVisibleTo($ben));
        $this->assertFalse($bens->isVisibleTo($ana));
        $this->assertFalse($draftForAna->isVisibleTo($ana));
    }

    public function test_a_user_without_a_known_role_sees_nothing()
    {
        Property::factory()->published()->create();

        $this->assertSame(0, Property::visibleTo(User::factory()->create(['role' => 9]))->count());
    }

    public function test_assigning_an_architect_hides_the_property_from_the_others()
    {
        $ana = User::factory()->editor()->create();
        $ben = User::factory()->editor()->create();
        $property = Property::factory()->published()->create();

        $this->assertTrue($property->isVisibleTo($ben));

        $property->architects()->attach($ana);

        $this->assertFalse($property->isVisibleTo($ben));
        $this->assertTrue($property->isVisibleTo($ana));
    }
}
