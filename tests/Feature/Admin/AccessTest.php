<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use App\Http\Models\Info;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every admin page and the roles that may open it, matching the Gates in AuthServiceProvider.
     *
     * @param int $infoId
     * @return array
     */
    protected function pages($infoId)
    {
        $all = ['admin', 'editor', 'subscriber'];
        $admin = ['admin'];
        $editor = ['editor'];
        $readers = ['editor', 'subscriber'];

        return [
            '/dashboard' => $all,
            '/dashboard/settings' => $all,
            '/dashboard/options' => $admin,

            '/dashboard/users' => $admin,
            '/dashboard/users/add-user' => $admin,
            '/dashboard/users/view-user/1' => $admin,
            '/dashboard/users/edit-user/1' => $admin,

            '/dashboard/infos' => $readers,
            '/dashboard/infos/add-info' => $editor,
            "/dashboard/infos/view-info/{$infoId}" => $readers,
            "/dashboard/infos/edit-info/{$infoId}" => $editor,

            '/dashboard/alerts' => $readers,
            '/dashboard/alerts/add-alert' => $editor,
            '/dashboard/alerts/view-alert/1' => $readers,
            '/dashboard/alerts/edit-alert/1' => $editor,

            '/dashboard/plans' => $readers,
            '/dashboard/plans/add-plan' => $editor,
            '/dashboard/plans/view-plan/1' => $readers,
            '/dashboard/plans/edit-plan/1' => $editor,

            '/dashboard/licenses' => $readers,
            '/dashboard/licenses/add-license' => $editor,
            '/dashboard/licenses/view-license/1' => $readers,
            '/dashboard/licenses/edit-license/1' => $editor,

            '/dashboard/messages' => $all,
            '/dashboard/messages/sent' => $all,
            '/dashboard/messages/send-message' => $all,
            '/dashboard/messages/view-message/1' => $all,
        ];
    }

    public function test_each_role_can_open_exactly_its_pages()
    {
        $users = [
            'admin' => factory(User::class)->states('admin')->create(),
            'editor' => factory(User::class)->states('editor')->create(),
            'subscriber' => factory(User::class)->create(),
        ];
        $info = factory(Info::class)->create();

        foreach ($this->pages($info->id) as $uri => $allowed) {
            foreach ($users as $role => $user) {
                $status = $this->actingAs($user)->get($uri)->getStatusCode();
                $expected = in_array($role, $allowed) ? 200 : 403;

                $this->assertSame($expected, $status, "{$role} GET {$uri}");
            }
        }
    }

    public function test_menu_has_no_fake_unread_badge_and_no_messages_entry()
    {
        // B11: Messages isn't built yet, so its menu (with a hard-coded "10" badge) is hidden
        $html = $this->actingAs(factory(User::class)->create())->get('/dashboard')->getContent();

        $this->assertNotContains('badge-success', $html);
        $this->assertNotContains(route('messages'), $html);
    }

    public function test_guests_are_sent_to_login_from_every_admin_page()
    {
        foreach (array_keys($this->pages(1)) as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }
    }
}
