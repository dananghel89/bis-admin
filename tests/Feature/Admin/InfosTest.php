<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use App\Http\Models\Info;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InfosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\User
     */
    protected $editor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->editor = factory(User::class)->states('editor')->create();
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
        factory(Info::class)->create(['title' => 'Taken']);

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
        $info = factory(Info::class)->create(['title' => 'Same title']);

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
        $info = factory(Info::class)->create();

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
        $author = factory(User::class)->states('editor')->create(['name' => 'Ana Architect']);
        factory(Info::class)->create(['title' => 'Plan review', 'author' => $author->id]);

        $this->actingAs($this->editor)
            ->get('/dashboard/infos')
            ->assertSee('Plan review')
            ->assertSee('Ana Architect');
    }

    public function test_list_is_newest_first_and_loads_authors_in_one_query()
    {
        // B15
        factory(Info::class)->create(['title' => 'Older info', 'created_at' => now()->subDays(2)]);
        factory(Info::class)->create(['title' => 'Newest info', 'created_at' => now()]);
        factory(Info::class, 3)->create(['created_at' => now()->subDays(5)]);

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
        // B16
        $info = factory(Info::class)->create(['content' => "Line one\nLine two <script>alert(1)</script>"]);

        $this->actingAs($this->editor)
            ->get("/dashboard/infos/view-info/{$info->id}")
            ->assertSee('Line one<br />')
            ->assertSee('&lt;script&gt;')
            ->assertDontSee('<script>alert(1)</script>');
    }

    public function test_subscriber_cannot_add_edit_or_delete()
    {
        $subscriber = factory(User::class)->create();
        $info = factory(Info::class)->create();

        $this->actingAs($subscriber)->post('/dashboard/infos/add-info', ['title' => 'X', 'status' => 'public'])->assertForbidden();
        $this->actingAs($subscriber)->patch("/dashboard/infos/edit-info/{$info->id}", ['title' => 'X', 'status' => 'public'])->assertForbidden();
        $this->actingAs($subscriber)->delete("/dashboard/infos/edit-info/{$info->id}")->assertForbidden();

        $this->assertNotNull(Info::find($info->id));
    }

    public function test_status_must_be_public_or_private()
    {
        // B05
        $info = factory(Info::class)->create();

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
        // B06: privacy is enforced by the controller, not only by the view
        $subscriber = factory(User::class)->create();
        $private = factory(Info::class)->states('private')->create(['content' => 'SECRET-CONTENT']);
        $public = factory(Info::class)->create();

        $this->actingAs($subscriber)->get("/dashboard/infos/view-info/{$private->id}")
            ->assertNotFound()
            ->assertDontSee('SECRET-CONTENT');
        $this->actingAs($subscriber)->get("/dashboard/infos/view-info/{$public->id}")->assertOk();
        $this->actingAs($this->editor)->get("/dashboard/infos/view-info/{$private->id}")->assertOk();
    }

    public function test_delete_asks_for_confirmation()
    {
        // B09
        $info = factory(Info::class)->create();

        $this->actingAs($this->editor)
            ->get("/dashboard/infos/edit-info/{$info->id}")
            ->assertSee("return confirm('Delete this info?')");
    }

    public function test_subscriber_does_not_see_private_infos_in_the_list()
    {
        factory(Info::class)->create(['title' => 'Public note']);
        factory(Info::class)->states('private')->create(['title' => 'Private note']);

        $this->actingAs(factory(User::class)->create())
            ->get('/dashboard/infos')
            ->assertSee('Public note')
            ->assertDontSee('Private note');
    }
}
