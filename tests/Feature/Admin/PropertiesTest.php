<?php

namespace Tests\Feature\Admin;

use App\Models\Plan;
use App\Models\Property;
use App\Models\PropertyStatusChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertiesTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected User $ana;

    protected User $ben;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->admin()->create();
        $this->ana = User::factory()->editor()->create(['name' => 'Ana Architect']);
        $this->ben = User::factory()->editor()->create(['name' => 'Ben Builder']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Casa Batlló',
            'address' => 'Passeig de Gràcia, 43',
            'postal_code' => '08007',
            'city' => 'Barcelona',
            'latitude' => '41.3916',
            'longitude' => '2.1649',
            'description' => "Line one\nLine two",
            'status' => 'published',
            'architects' => [$this->ana->id],
        ], $overrides);
    }

    public function test_the_list_shows_each_user_only_the_visible_properties()
    {
        Property::factory()->published()->create(['name' => 'Open House']);
        Property::factory()->draft()->create(['name' => 'Secret Draft']);
        Property::factory()->published()->create(['name' => 'Bens House'])->architects()->attach($this->ben);

        $this->actingAs($this->ana)->get('/dashboard/infos')
            ->assertOk()
            ->assertSee('Open House')
            ->assertDontSee('Secret Draft')
            ->assertDontSee('Bens House')
            ->assertDontSee('Add Property');

        $this->actingAs($this->staff)->get('/dashboard/infos')
            ->assertSee('Open House')->assertSee('Secret Draft')->assertSee('Bens House')->assertSee('Add Property');
    }

    public function test_the_list_actions_follow_the_role()
    {
        $house = Property::factory()->published()->create();

        $this->actingAs($this->ana)->get('/dashboard/infos')
            ->assertSee(route('infoView', $house), false)
            ->assertDontSee(route('infoEdit', $house), false)
            ->assertDontSee(route('infoDelete', $house).'"', false);

        $this->actingAs($this->staff)->get('/dashboard/infos')
            ->assertSee(route('infoEdit', $house), false)
            ->assertSee('action="'.route('infoDelete', $house).'"', false);
    }

    public function test_the_list_can_be_searched_and_filtered()
    {
        Property::factory()->create(['name' => 'Casa Milà', 'work_status' => 'done']);
        Property::factory()->create(['name' => 'Torre Glòries', 'work_status' => 'pending']);
        Property::factory()->draft()->create(['name' => 'Villa Draft']);

        $this->actingAs($this->staff)->get('/dashboard/infos?search=Mil')->assertSee('Casa Milà')->assertDontSee('Torre Glòries');
        $this->actingAs($this->staff)->get('/dashboard/infos?work_status=pending')->assertSee('Torre Glòries')->assertDontSee('Casa Milà');
        $this->actingAs($this->staff)->get('/dashboard/infos?status=draft')->assertSee('Villa Draft')->assertDontSee('Casa Milà');
    }

    public function test_an_architect_opens_only_visible_property_pages()
    {
        $open = Property::factory()->published()->create();
        $draft = Property::factory()->draft()->create();
        $bens = Property::factory()->published()->create();
        $bens->architects()->attach($this->ben);

        $this->actingAs($this->ana)->get("/dashboard/infos/view-info/{$open->id}")->assertOk();
        $this->actingAs($this->ana)->get("/dashboard/infos/view-info/{$draft->id}")->assertNotFound();
        $this->actingAs($this->ana)->get("/dashboard/infos/view-info/{$bens->id}")->assertNotFound();
        $this->actingAs($this->ben)->get("/dashboard/infos/view-info/{$bens->id}")->assertOk();
        $this->actingAs($this->staff)->get("/dashboard/infos/view-info/{$draft->id}")->assertOk();
        $this->actingAs($this->staff)->get('/dashboard/infos/view-info/999')->assertNotFound();
    }

    public function test_only_staff_can_add_edit_or_delete()
    {
        $property = Property::factory()->published()->create();

        foreach (['/dashboard/infos/add-info', "/dashboard/infos/edit-info/{$property->id}"] as $uri) {
            $this->actingAs($this->ana)->get($uri)->assertForbidden();
            $this->actingAs($this->staff)->get($uri)->assertOk();
        }

        $this->actingAs($this->ana)->post('/dashboard/infos/add-info', $this->validData())->assertForbidden();
        $this->actingAs($this->ana)->patch("/dashboard/infos/edit-info/{$property->id}", $this->validData())->assertForbidden();
        $this->actingAs($this->ana)->delete("/dashboard/infos/edit-info/{$property->id}")->assertForbidden();
        $this->assertSame(1, Property::count());
    }

    public function test_staff_add_a_property_with_its_location_and_architects()
    {
        $response = $this->actingAs($this->staff)->postJson('/dashboard/infos/add-info', $this->validData())->assertOk();

        $property = Property::sole();
        $response->assertExactJson(['message' => 'Property Casa Batlló added.', 'redirect' => route('infoView', $property)]);
        $this->assertSame('Passeig de Gràcia, 43', $property->address);
        $this->assertSame(41.3916, $property->latitude);
        $this->assertSame('published', $property->status);
        $this->assertSame('pending', $property->work_status);
        $this->assertSame($this->staff->id, $property->created_by);
        $this->assertSame([$this->ana->id], $property->architects->pluck('id')->all());
    }

    public function test_add_validation()
    {
        $this->actingAs($this->staff)
            ->postJson('/dashboard/infos/add-info', ['name' => '', 'address' => '', 'city' => '', 'latitude' => '95', 'status' => 'gone', 'architects' => [$this->staff->id]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address', 'city', 'latitude', 'longitude', 'status', 'architects.0']);

        $this->assertSame(0, Property::count());
    }

    public function test_staff_edit_a_property_and_clear_its_architects()
    {
        $property = Property::factory()->draft()->create();
        $property->architects()->attach([$this->ana->id, $this->ben->id]);

        $data = $this->validData(['name' => 'Renamed', 'latitude' => '', 'longitude' => '']);
        unset($data['architects']); // nothing chosen in the multi-select

        $this->actingAs($this->staff)->patch("/dashboard/infos/edit-info/{$property->id}", $data)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('infoView', $property));

        $property->refresh();
        $this->assertSame('Renamed', $property->name);
        $this->assertTrue($property->isPublished());
        $this->assertFalse($property->hasLocation());
        $this->assertSame(0, $property->architects()->count());
    }

    public function test_staff_delete_a_property_with_its_files()
    {
        Storage::fake('local');
        $property = Property::factory()->create();
        $plan = Plan::factory()->for($property)->create();
        Storage::disk('local')->put($plan->file_path, 'pdf');

        $this->actingAs($this->staff)->deleteJson("/dashboard/infos/edit-info/{$property->id}")
            ->assertOk()
            ->assertJson(['redirect' => route('infos')]);

        $this->assertSame(0, Property::count() + Plan::count());
        Storage::disk('local')->assertMissing($plan->file_path);
    }

    public function test_the_page_shows_details_safely()
    {
        $property = Property::factory()->published()->create(['name' => 'Casa <b>Bold</b>', 'description' => "Line one\n<script>alert(1)</script>"]);
        $property->architects()->attach($this->ana);

        $this->actingAs($this->ana)->get("/dashboard/infos/view-info/{$property->id}")
            ->assertSee('Casa &lt;b&gt;Bold&lt;/b&gt;', false)
            ->assertSee('Line one<br />', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('data-map', false)
            ->assertDontSee('Delete', false); // no staff buttons for architects

        $this->actingAs($this->staff)->get("/dashboard/infos/view-info/{$property->id}")
            ->assertSee('Ana Architect')
            ->assertSee('data-confirm="Delete Casa &lt;b&gt;Bold&lt;/b&gt; with all its plans and licenses?', false);
    }

    public function test_an_architect_changes_the_work_status_with_a_note()
    {
        $property = Property::factory()->published()->create(['work_status' => 'pending']);
        $property->architects()->attach($this->ana);

        $this->actingAs($this->ana)
            ->patchJson("/dashboard/infos/work-status/{$property->id}", ['work_status' => 'in_progress', 'note' => 'Started the survey'])
            ->assertOk()
            ->assertJson(['message' => 'Work status changed to In progress.']);

        $this->assertSame('in_progress', $property->fresh()->work_status);

        $change = PropertyStatusChange::sole();
        $this->assertSame([$this->ana->id, 'pending', 'in_progress', 'Started the survey'], [$change->user_id, $change->from_status, $change->to_status, $change->note]);

        $this->actingAs($this->staff)->get("/dashboard/infos/view-info/{$property->id}")
            ->assertSee('Started the survey')
            ->assertSee('Ana Architect');
    }

    public function test_the_work_status_must_change_and_be_valid()
    {
        $property = Property::factory()->published()->create(['work_status' => 'done']);

        $this->actingAs($this->staff)->patchJson("/dashboard/infos/work-status/{$property->id}", ['work_status' => 'done'])
            ->assertStatus(422)->assertJsonValidationErrors(['work_status' => 'Choose a status different from the current one.']);
        $this->actingAs($this->staff)->patchJson("/dashboard/infos/work-status/{$property->id}", ['work_status' => 'finished'])
            ->assertStatus(422);

        $this->assertSame(0, PropertyStatusChange::count());
    }

    public function test_an_architect_cant_change_a_property_they_dont_see()
    {
        $bens = Property::factory()->published()->create(['work_status' => 'pending']);
        $bens->architects()->attach($this->ben);
        $draft = Property::factory()->draft()->create(['work_status' => 'pending']);

        $this->actingAs($this->ana)->patchJson("/dashboard/infos/work-status/{$bens->id}", ['work_status' => 'done'])->assertNotFound();
        $this->actingAs($this->ana)->patchJson("/dashboard/infos/work-status/{$draft->id}", ['work_status' => 'pending'])->assertNotFound();

        $this->assertSame(0, PropertyStatusChange::count());
    }

    public function test_the_header_search_looks_for_properties()
    {
        $this->actingAs($this->ana)->get('/dashboard')
            ->assertSee('action="' . route('infos') . '"', false)
            ->assertSee('id="searchToggle"', false);
    }
}
