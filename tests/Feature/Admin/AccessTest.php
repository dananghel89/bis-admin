<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every admin GET page and the roles that may open it (AppServiceProvider::defineGates).
     * 'admin' is Staff, 'editor' is Architect.
     *
     * @return array<string, list<string>>
     */
    protected function pages(): array
    {
        $all = ['admin', 'editor'];
        $staff = ['admin'];

        return [
            '/dashboard' => $all,
            '/dashboard/settings' => $all,
            '/dashboard/alerts' => $all,
            '/dashboard/infos' => $all,
            '/dashboard/plans' => $all,
            '/dashboard/licenses' => $all,
            '/dashboard/users' => $staff,
        ];
    }

    /**
     * @return array<string, User>
     */
    protected function users(): array
    {
        return [
            'admin' => User::factory()->admin()->create(),
            'editor' => User::factory()->editor()->create(),
        ];
    }

    public function test_each_role_can_open_exactly_its_pages()
    {
        $users = $this->users();

        foreach ($this->pages() as $uri => $allowed) {
            foreach ($users as $role => $user) {
                $status = $this->actingAs($user)->get($uri)->getStatusCode();
                $expected = in_array($role, $allowed) ? 200 : 403;

                $this->assertSame($expected, $status, "{$role} GET {$uri}");
            }
        }
    }

    public function test_a_user_without_a_known_role_can_open_nothing_but_the_dashboard()
    {
        $user = User::factory()->create(['role' => 7]);

        $this->assertSame('', $user->getRole());

        foreach (array_keys($this->pages()) as $uri) {
            $this->actingAs($user)->get($uri)->assertStatus($uri === '/dashboard' ? 200 : 403);
        }
    }

    public function test_admin_pages_are_english_only()
    {
        // The UI is English only (decided 2026-11-02)
        $spanish = [
            'Añadir', 'Todos Los', 'Todas Las', 'Informaciones', 'Información', 'Avisos', 'Planos', 'Licencias',
            'Usuarios', 'Opciones', 'Ajustes', 'Mensajes', 'Bandeja', 'Enviar', 'Administración',
            'Datos De Sistema', 'Cerrar Sesión',
        ];
        $users = $this->users();

        foreach ($this->pages() as $uri => $allowed) {
            foreach ($allowed as $role) {
                $html = $this->actingAs($users[$role])->get($uri)->getContent();

                foreach ($spanish as $word) {
                    $this->assertStringNotContainsString($word, $html, "{$role} GET {$uri} contains '{$word}'");
                }
            }
        }
    }

    public function test_the_menu_shows_each_role_its_sections()
    {
        $users = $this->users();

        $staff = $this->actingAs($users['admin'])->get('/dashboard')->getContent();
        $this->assertStringContainsString('href="' . route('users') . '"', $staff);
        $this->assertStringContainsString('href="' . route('infos') . '"', $staff);

        $architect = $this->actingAs($users['editor'])->get('/dashboard')->getContent();
        $this->assertStringNotContainsString('href="' . route('users') . '"', $architect);
        $this->assertStringContainsString('href="' . route('infos') . '"', $architect);
        $this->assertStringContainsString('href="' . route('alerts') . '"', $architect);
    }

    public function test_roles_have_staff_and_architect_labels()
    {
        $users = $this->users();

        $this->assertSame('Staff', $users['admin']->roleLabel());
        $this->assertSame('Architect', $users['editor']->roleLabel());
        $this->actingAs($users['editor'])->get('/dashboard/settings')->assertSee('value="Architect"', false);
    }

    public function test_guests_are_sent_to_login_from_every_admin_page()
    {
        foreach (array_keys($this->pages()) as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }
    }
}
