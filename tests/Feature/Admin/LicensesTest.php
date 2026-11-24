<?php

namespace Tests\Feature\Admin;

use App\Models\License;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LicensesTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected User $ana;

    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->staff = User::factory()->admin()->create();
        $this->ana = User::factory()->editor()->create();
        $this->property = Property::factory()->published()->create(['name' => 'Casa Calvet']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validData(array $overrides = []): array
    {
        return array_merge([
            'property_id' => $this->property->id,
            'title' => 'Building permit',
            'number' => 'LIC-2026-01',
            'issued_at' => '2026-01-15',
            'expires_at' => '2030-01-15',
            'file' => UploadedFile::fake()->create('permit.pdf', 80, 'application/pdf'),
        ], $overrides);
    }

    protected function licenseWithFile(?Property $property = null): License
    {
        $license = License::factory()->for($property ?? $this->property)->create(['file_name' => 'permit.pdf']);
        Storage::disk('local')->put($license->file_path, '%PDF-1.4 test');

        return $license;
    }

    public function test_staff_upload_a_license_with_number_and_dates()
    {
        $this->actingAs($this->staff)->postJson('/dashboard/licenses/add-license', $this->validData())
            ->assertOk()
            ->assertJson(['message' => 'License Building permit uploaded.', 'redirect' => route('infoView', $this->property)]);

        $license = License::sole();
        $this->assertSame('LIC-2026-01', $license->number);
        $this->assertSame('2026-01-15', $license->issued_at->format('Y-m-d'));
        $this->assertSame('2030-01-15', $license->expires_at->format('Y-m-d'));
        $this->assertStringStartsWith('licenses/', $license->file_path);
        Storage::disk('local')->assertExists($license->file_path);
    }

    public function test_validation()
    {
        $this->actingAs($this->staff)
            ->postJson('/dashboard/licenses/add-license', $this->validData(['issued_at' => '2026-05-01', 'expires_at' => '2026-04-01', 'file' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain')]))
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'expires_at' => 'The expiry date can\'t be before the issue date.',
                'file' => 'The file must be a PDF.',
            ]);

        $this->assertSame(0, License::count());
    }

    public function test_the_list_flags_expired_and_expiring_licenses_and_sorts_by_expiry()
    {
        License::factory()->for($this->property)->create(['title' => 'Later', 'expires_at' => today()->addYears(2)]);
        License::factory()->for($this->property)->create(['title' => 'Old one', 'expires_at' => today()->subDays(10)]);
        License::factory()->for($this->property)->create(['title' => 'Soon one', 'expires_at' => today()->addDays(5)]);
        License::factory()->for($this->property)->create(['title' => 'Forever', 'expires_at' => null]);

        $this->actingAs($this->ana)->get('/dashboard/licenses')
            ->assertOk()
            ->assertSeeInOrder(['Old one', 'Soon one', 'Later', 'Forever'])
            ->assertSee('Expired')
            ->assertSee('Expires in 5 days')
            ->assertDontSee('Upload License');

        $this->actingAs($this->ana)->get('/dashboard/licenses?state=attention')
            ->assertSee('Old one')->assertSee('Soon one')->assertDontSee('Later')->assertDontSee('Forever');
        $this->actingAs($this->ana)->get('/dashboard/licenses?state=expired')
            ->assertSee('Old one')->assertDontSee('Soon one');
        $this->actingAs($this->ana)->get('/dashboard/licenses?state=valid')
            ->assertSee('Later')->assertSee('Forever')->assertDontSee('Old one');
    }

    public function test_the_list_only_shows_licenses_of_visible_properties()
    {
        $draft = Property::factory()->draft()->create();
        License::factory()->for($this->property)->create(['title' => 'Public license']);
        License::factory()->for($draft)->create(['title' => 'Draft license']);

        $this->actingAs($this->ana)->get('/dashboard/licenses')->assertSee('Public license')->assertDontSee('Draft license');
        $this->actingAs($this->staff)->get('/dashboard/licenses')->assertSee('Draft license');
    }

    public function test_staff_edit_replace_and_delete()
    {
        $license = $this->licenseWithFile();
        $oldPath = $license->file_path;

        $this->actingAs($this->staff)
            ->patchJson("/dashboard/licenses/edit-license/{$license->id}", $this->validData(['title' => 'Renewed permit', 'file' => UploadedFile::fake()->create('renewed.pdf', 50, 'application/pdf')]))
            ->assertOk();

        $license->refresh();
        $this->assertSame('Renewed permit', $license->title);
        $this->assertSame('renewed.pdf', $license->file_name);
        Storage::disk('local')->assertMissing($oldPath);

        $this->actingAs($this->staff)->deleteJson("/dashboard/licenses/edit-license/{$license->id}")->assertOk();
        $this->assertSame(0, License::count());
        Storage::disk('local')->assertMissing($license->file_path);
    }

    public function test_architects_cant_manage_licenses()
    {
        $license = $this->licenseWithFile();

        $this->actingAs($this->ana)->get('/dashboard/licenses/add-license')->assertForbidden();
        $this->actingAs($this->ana)->post('/dashboard/licenses/add-license', $this->validData())->assertForbidden();
        $this->actingAs($this->ana)->patch("/dashboard/licenses/edit-license/{$license->id}", $this->validData())->assertForbidden();
        $this->actingAs($this->ana)->delete("/dashboard/licenses/edit-license/{$license->id}")->assertForbidden();
        $this->assertSame(1, License::count());
    }

    public function test_downloads_follow_the_property_visibility()
    {
        $visible = $this->licenseWithFile();
        $draft = $this->licenseWithFile(Property::factory()->draft()->create());

        $this->actingAs($this->ana)->get("/dashboard/licenses/download/{$visible->id}")->assertOk()->assertDownload('permit.pdf');
        $this->actingAs($this->ana)->get("/dashboard/licenses/download/{$draft->id}")->assertNotFound();
        $this->actingAs($this->staff)->get("/dashboard/licenses/download/{$draft->id}")->assertOk();
    }

    public function test_the_property_page_lists_its_licenses()
    {
        License::factory()->for($this->property)->expired()->create(['title' => 'Fire certificate']);

        $this->actingAs($this->ana)->get("/dashboard/infos/view-info/{$this->property->id}")
            ->assertSee('Fire certificate')
            ->assertSee('Expired');
    }
}
