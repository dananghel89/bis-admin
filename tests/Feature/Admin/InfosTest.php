<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;
use App\Models\Info;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InfosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Models\User
     */
    protected $editor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->editor = User::factory()->editor()->create();
    }

    public function test_editor_adds_an_info()
    {
        $response = $this->actingAs($this->editor)->post('/dashboard/infos/add-info', [
            'title' => 'Site meeting',
            'content' => 'Monday at 9',
            'status' => 'public',
        ]);

        $info = Info::where('title', 'Site meeting')->first();
        $this->assertNotNull($info);
        $this->assertSame($this->editor->id, (int) $info->author);
        $response->assertRedirect(route('infoEdit', $info->id));
    }

    public function test_title_is_required_and_unique()
    {
        Info::factory()->create(['title' => 'Taken']);

        $this->actingAs($this->editor)
            ->post('/dashboard/infos/add-info', ['content' => 'x', 'status' => 'public'])
            ->assertSessionHasErrors('title');

        $this->actingAs($this->editor)
            ->post('/dashboard/infos/add-info', ['title' => 'Taken', 'status' => 'public'])
            ->assertSessionHasErrors('title');

        $this->assertSame(1, Info::count());
    }

    public function test_editor_edits_an_info_keeping_its_title()
    {
        $info = Info::factory()->create(['title' => 'Same title']);

        $this->actingAs($this->editor)
            ->patch("/dashboard/infos/edit-info/{$info->id}", [
                'title' => 'Same title',
                'content' => 'Updated content',
                'status' => 'private',
            ])
            ->assertSessionHasNoErrors();

        $info->refresh();
        $this->assertSame('Updated content', $info->content);
        $this->assertSame('private', $info->status);
    }

    public function test_editor_deletes_an_info()
    {
        $info = Info::factory()->create();

        $this->actingAs($this->editor)
            ->delete("/dashboard/infos/edit-info/{$info->id}")
            ->assertRedirect(route('infos'));

        $this->assertNull(Info::find($info->id));
    }

    public function test_unknown_info_returns_404()
    {
        $this->actingAs($this->editor)->get('/dashboard/infos/view-info/999')->assertNotFound();
        $this->actingAs($this->editor)->get('/dashboard/infos/edit-info/999')->assertNotFound();
    }

    public function test_list_shows_the_authors_name()
    {
        $author = User::factory()->editor()->create(['name' => 'Ana Architect']);
        Info::factory()->create(['title' => 'Plan review', 'author' => $author->id]);

        $this->actingAs($this->editor)
            ->get('/dashboard/infos')
            ->assertSee('Plan review', false)
            ->assertSee('Ana Architect', false);
    }

    public function test_list_is_newest_first_and_loads_authors_in_one_query()
    {
        Info::factory()->create(['title' => 'Older info', 'created_at' => now()->subDays(2)]);
        Info::factory()->create(['title' => 'Newest info', 'created_at' => now()]);
        Info::factory(3)->create(['created_at' => now()->subDays(5)]);

        \DB::enableQueryLog();
        $html = $this->actingAs($this->editor)->get('/dashboard/infos')->getContent();
        $userQueries = array_filter(\DB::getQueryLog(), function ($query) {
            return stripos($query['query'], 'from "users"') !== false;
        });

        $this->assertLessThan(strpos($html, 'Older info'), strpos($html, 'Newest info'));
        $this->assertCount(1, $userQueries, 'authors must be eager-loaded, not queried per row');
    }

    public function test_content_keeps_line_breaks_and_stays_escaped()
    {
        $info = Info::factory()->create(['content' => "Line one\nLine two <script>alert(1)</script>"]);

        $this->actingAs($this->editor)
            ->get("/dashboard/infos/view-info/{$info->id}")
            ->assertSee('Line one<br />', false)
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_subscriber_cannot_add_edit_or_delete()
    {
        $subscriber = User::factory()->create();
        $info = Info::factory()->create();

        $this->actingAs($subscriber)->post('/dashboard/infos/add-info', ['title' => 'X', 'status' => 'public'])->assertForbidden();
        $this->actingAs($subscriber)->patch("/dashboard/infos/edit-info/{$info->id}", ['title' => 'X', 'status' => 'public'])->assertForbidden();
        $this->actingAs($subscriber)->delete("/dashboard/infos/edit-info/{$info->id}")->assertForbidden();

        $this->assertNotNull(Info::find($info->id));
    }

    public function test_status_must_be_public_or_private()
    {
        $info = Info::factory()->create();

        $this->actingAs($this->editor)
            ->post('/dashboard/infos/add-info', ['title' => 'Odd', 'status' => 'whatever'])
            ->assertSessionHasErrors('status');

        $this->actingAs($this->editor)
            ->patch("/dashboard/infos/edit-info/{$info->id}", ['title' => $info->title, 'status' => 'whatever'])
            ->assertSessionHasErrors('status');

        $this->assertSame(0, Info::where('status', 'whatever')->count());
    }

    public function test_subscriber_gets_404_for_a_private_info()
    {
        // Privacy is enforced by the controller, not only by the view
        $subscriber = User::factory()->create();
        $private = Info::factory()->private()->create(['content' => 'SECRET-CONTENT']);
        $public = Info::factory()->create();

        $this->actingAs($subscriber)->get("/dashboard/infos/view-info/{$private->id}")
            ->assertNotFound()
            ->assertDontSee('SECRET-CONTENT', false);
        $this->actingAs($subscriber)->get("/dashboard/infos/view-info/{$public->id}")->assertOk();
        $this->actingAs($this->editor)->get("/dashboard/infos/view-info/{$private->id}")->assertOk();
    }

    public function test_delete_asks_for_confirmation()
    {
        $info = Info::factory()->create();

        $this->actingAs($this->editor)
            ->get("/dashboard/infos/edit-info/{$info->id}")
            ->assertSee("return confirm('Delete this info?')", false);
    }

    public function test_subscriber_does_not_see_private_infos_in_the_list()
    {
        Info::factory()->create(['title' => 'Public note']);
        Info::factory()->private()->create(['title' => 'Private note']);

        $this->actingAs(User::factory()->create())
            ->get('/dashboard/infos')
            ->assertSee('Public note', false)
            ->assertDontSee('Private note', false);
    }
}
