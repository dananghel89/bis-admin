<?php

namespace Tests\Feature\Admin;

use App\Models\Plan;
use App\Models\Property;
use App\Models\User;
use App\Support\PdfUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlansTest extends TestCase
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
        $this->property = Property::factory()->published()->create(['name' => 'Casa Vicens']);
    }

    protected function pdf(string $name = 'ground-floor.pdf', int $kb = 100): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kb, 'application/pdf');
    }

    protected function planWithFile(?Property $property = null): Plan
    {
        $plan = Plan::factory()->for($property ?? $this->property)->create(['file_name' => 'site.pdf']);
        Storage::disk('local')->put($plan->file_path, '%PDF-1.4 test');

        return $plan;
    }

    public function test_the_page_shows_a_map_of_visible_properties_and_their_plans()
    {
        $hidden = Property::factory()->draft()->create(['name' => 'Hidden Draft']);
        Plan::factory()->for($this->property)->create(['title' => 'Visible plan']);
        Plan::factory()->for($hidden)->create(['title' => 'Hidden plan']);

        $html = $this->actingAs($this->ana)->get('/dashboard/plans')
            ->assertOk()
            ->assertSee('Visible plan')
            ->assertDontSee('Hidden plan')
            ->assertDontSee('Upload Plan')
            ->getContent();

        preg_match('/data-markers="([^"]*)"/', $html, $match);
        $markers = json_decode(html_entity_decode($match[1]), true);
        $this->assertSame(['Casa Vicens'], array_column($markers, 'title'));
        $this->assertSame(route('infoView', $this->property), $markers[0]['url']);

        $this->actingAs($this->staff)->get('/dashboard/plans')->assertSee('Hidden plan')->assertSee('Upload Plan');
    }

    public function test_the_list_can_be_filtered_by_property()
    {
        $other = Property::factory()->create();
        Plan::factory()->for($this->property)->create(['title' => 'Plan A']);
        Plan::factory()->for($other)->create(['title' => 'Plan B']);

        $this->actingAs($this->staff)->get("/dashboard/plans?property={$other->id}")->assertSee('Plan B')->assertDontSee('Plan A');
    }

    public function test_staff_upload_a_plan()
    {
        $this->actingAs($this->staff)
            ->postJson('/dashboard/plans/add-plan', ['property_id' => $this->property->id, 'title' => 'Ground floor', 'file' => $this->pdf()])
            ->assertOk()
            ->assertJson(['redirect' => route('infoView', $this->property)]);

        $plan = Plan::sole();
        $this->assertSame('ground-floor.pdf', $plan->file_name);
        $this->assertSame(102400, $plan->file_size);
        $this->assertSame($this->staff->id, $plan->uploaded_by);
        $this->assertStringStartsWith('plans/', $plan->file_path);
        Storage::disk('local')->assertExists($plan->file_path);
    }

    public function test_only_pdfs_are_accepted_and_one_is_required()
    {
        $this->actingAs($this->staff)
            ->postJson('/dashboard/plans/add-plan', ['property_id' => $this->property->id, 'title' => 'Bad', 'file' => UploadedFile::fake()->image('photo.jpg')])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file' => 'The file must be a PDF.']);

        $this->actingAs($this->staff)
            ->postJson('/dashboard/plans/add-plan', ['property_id' => 999, 'title' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['property_id', 'title', 'file']);

        $this->assertSame(0, Plan::count());
    }

    public function test_a_pdf_over_the_limit_is_refused()
    {
        $tooBig = PdfUpload::maxKilobytes() + 1;

        $this->actingAs($this->staff)
            ->postJson('/dashboard/plans/add-plan', ['property_id' => $this->property->id, 'title' => 'Huge', 'file' => $this->pdf('huge.pdf', $tooBig)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file' => 'The PDF can be at most ' . PdfUpload::maxForHumans() . '.']);
    }

    public function test_upload_limits_read_php_sizes()
    {
        $this->assertSame(2048, PdfUpload::toKilobytes('2M'));
        $this->assertSame(512, PdfUpload::toKilobytes('512K'));
        $this->assertSame(1048576, PdfUpload::toKilobytes('1G'));
        $this->assertSame(1024, PdfUpload::toKilobytes('1048576'));
        $this->assertLessThanOrEqual(PdfUpload::MAX_KILOBYTES, PdfUpload::maxKilobytes());
    }

    public function test_staff_edit_a_plan_and_replace_its_file()
    {
        $plan = $this->planWithFile();
        $oldPath = $plan->file_path;

        $this->actingAs($this->staff)
            ->patchJson("/dashboard/plans/edit-plan/{$plan->id}", ['property_id' => $this->property->id, 'title' => 'New title'])
            ->assertOk();
        $this->assertSame('New title', $plan->fresh()->title);
        Storage::disk('local')->assertExists($oldPath);

        $this->actingAs($this->staff)
            ->patchJson("/dashboard/plans/edit-plan/{$plan->id}", ['property_id' => $this->property->id, 'title' => 'New title', 'file' => $this->pdf('v2.pdf')])
            ->assertOk();

        $plan->refresh();
        $this->assertSame('v2.pdf', $plan->file_name);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($plan->file_path);
    }

    public function test_staff_delete_a_plan_and_its_file()
    {
        $plan = $this->planWithFile();

        $this->actingAs($this->staff)->deleteJson("/dashboard/plans/edit-plan/{$plan->id}")->assertOk();

        $this->assertSame(0, Plan::count());
        Storage::disk('local')->assertMissing($plan->file_path);
    }

    public function test_architects_cant_upload_edit_or_delete()
    {
        $plan = $this->planWithFile();

        $this->actingAs($this->ana)->get('/dashboard/plans/add-plan')->assertForbidden();
        $this->actingAs($this->ana)->get("/dashboard/plans/edit-plan/{$plan->id}")->assertForbidden();
        $this->actingAs($this->ana)->post('/dashboard/plans/add-plan', ['property_id' => $this->property->id, 'title' => 'X', 'file' => $this->pdf()])->assertForbidden();
        $this->actingAs($this->ana)->delete("/dashboard/plans/edit-plan/{$plan->id}")->assertForbidden();

        $this->assertSame(1, Plan::count());
    }

    public function test_downloads_follow_the_property_visibility()
    {
        $visible = $this->planWithFile();
        $bens = Property::factory()->published()->create();
        $bens->architects()->attach(User::factory()->editor()->create());
        $hidden = $this->planWithFile($bens);

        $this->actingAs($this->ana)->get("/dashboard/plans/download/{$visible->id}")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload('site.pdf');

        $this->actingAs($this->ana)->get("/dashboard/plans/download/{$hidden->id}")->assertNotFound();
        $this->actingAs($this->staff)->get("/dashboard/plans/download/{$hidden->id}")->assertOk();
    }

    public function test_guests_cant_download()
    {
        $plan = $this->planWithFile();

        $this->get("/dashboard/plans/download/{$plan->id}")->assertRedirect('/login');
    }

    public function test_a_missing_file_is_a_404()
    {
        $plan = Plan::factory()->for($this->property)->create();

        $this->actingAs($this->staff)->get("/dashboard/plans/download/{$plan->id}")->assertNotFound();
    }

    public function test_the_property_page_lists_its_plans()
    {
        Plan::factory()->for($this->property)->create(['title' => 'Roof plan']);

        $this->actingAs($this->ana)->get("/dashboard/infos/view-info/{$this->property->id}")
            ->assertSee('Roof plan')
            ->assertDontSee('Upload Plan');

        $this->actingAs($this->staff)->get("/dashboard/infos/view-info/{$this->property->id}")
            ->assertSee(route('planAdd', ['property' => $this->property->id]), false);

        $this->actingAs($this->staff)->get("/dashboard/plans/add-plan?property={$this->property->id}")
            ->assertSee('<option value="' . $this->property->id . '" selected>', false);
    }
}
