# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environment

Laravel 13 admin app ("BIS Admin") on PHP 8.4, served locally by WAMP at http://bis-admin.test with MySQL database `bis_admin`. Upgraded from Laravel 5.8 / PHP 7.1 on 2026-11-09 (git tag `pre-laravel-13` marks the last 5.8 commit).

- **Use the plain `php` (8.4.26) and the global `composer` (2.10).** `php71` is only for the old 5.8 code.
- **Tests use an in-memory SQLite database** (`phpunit.xml` sets `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`; `tests/Unit/TestDatabaseTest.php` guards it), so they never touch `bis_admin`. Still, never put test data into `bis_admin` by hand.
- **Tables are InnoDB** (migration `2026_11_04_000000_convert_tables_to_innodb`, and `'engine' => 'InnoDB'` in `config/database.php`). WAMP's MySQL 8.3 defaults to MyISAM, which is why they weren't before.
- **Cache, session and queue are `file`, `file` and `sync`** in `.env`. Laravel 13 defaults them to `database`, and this database has no `cache`, `sessions` or `jobs` tables.
- **`laravel/pao`** (a dev dependency) prints test results as one JSON line when it detects an AI agent. For PHPUnit's normal output, run it with the agent variables unset, e.g. `env -u CLAUDECODE -u CLAUDE_CODE -u AI_AGENT php vendor/bin/phpunit`.

## Commands

```bash
composer install                                  # install PHP dependencies
php artisan migrate                               # create tables; the options migration also seeds the 4 default options
php -d xdebug.mode=off artisan test               # run all tests (xdebug makes them several times slower)
php artisan test tests/Feature/Admin/InfosTest.php   # one test file (tests: Unit/, Feature/Auth/, Feature/Admin/, Feature/*Test.php)
php artisan test --filter test_editor_adds_an_info   # one test method (names are test_*, no annotations)
vendor/bin/pint --test                            # code style report (laravel preset); the code is not pint-formatted, so don't mass-reformat
php artisan route:list --except-vendor            # list the app's routes
```

Front-end assets are not built and there is no `package.json`. The views load prebuilt static templates with `asset(...)`:
- `public/admin/` is the **Sufee** admin theme (Colorlib, MIT, Nov 2018 release), in its original `assets/` + `vendors/` layout. It's used by the admin and auth layouts.
- `public/landing/` is the **Bell** theme (BootstrapMade, free license; its footer credit must stay).
- Project-specific admin styles go in `public/css/bis-admin.css`, which loads after the theme. Keep theme files, their banners and their `LICENSE`/`README` files unchanged (details in the local `docs/theme-licensing-plan.md`).

## Architecture

**Bootstrapping.** Laravel 11+ structure: no HTTP/console kernels and only `AppServiceProvider` (`bootstrap/providers.php`). `bootstrap/app.php` registers the `membership` middleware alias and sends logged-in users who open a guest page to `/dashboard` (`redirectUsersTo`); guests who open an `auth` page go to `route('login')`.

**Routing.** `routes/web.php` defines the landing pages and `require`s `routes/auth.php` (login, register, password reset) and `routes/admin.php` (everything under `/dashboard`, behind the `auth` middleware). Routes use the `[Controller::class, 'method']` syntax. There are no API routes.

**Auth.** The four controllers in `app/Http/Controllers/Auth` use the `laravel/ui` traits (`AuthenticatesUsers`, `RegistersUsers`, `SendsPasswordResetEmails`, `ResetsPasswords`). The base `Controller` is empty, so controllers can't call `$this->middleware()`: `guest` is applied in `routes/auth.php`, with `logout` outside that group. The password broker uses the old `password_resets` table (`config/auth.php`), and it also allows only one reset link per email per 60 seconds.

**Options and registration.** Site settings live in the key/value `options` table (`site_title`, `tagline`, `email_address`, `membership`) and are read with the global `get_option($name)` helper in `app/Helpers.php`. That file is autoloaded through composer's `files`. `get_option()` reads the lazy container singleton `project.options` (registered in `AppServiceProvider::register()`), which loads all options on first use, once per app instance; `OptionsController::optionsEdit` calls `app()->forgetInstance('project.options')` after saving. **Never call `get_option()` while routes or providers load**: that made every `artisan` command, `composer install` included, need MySQL. The helper functions are wrapped in `function_exists` guards; keep them, because tools that autoload `app/*.php` as classes (Laravel Extra Intellisense) would otherwise fatally redeclare them. The register routes always exist and sit behind the `membership` middleware (`EnsureMembershipIsOpen`), which redirects to `front` when the option is off, per request. `RegisterController` is the stock `RegistersUsers` controller; new users always get `User::SUBSCRIBER`.

**Roles and permissions.** `App\Models\User` stores an integer `role` (`ADMIN = 0`, `EDITOR = 1`, `SUBSCRIBER = 2`, cast to `integer`); `getRole()` returns `'admin'|'editor'|'subscriber'`, or `''` for a missing role, which gets no permissions. All authorization is `Gate::define` abilities in `AppServiceProvider::defineGates()` (no policies): `editOptions`, `viewUsers`, `addPosts`, `viewPosts`, `editPosts`, `accessMessages`, etc. Routes enforce them with `->middleware('can:...')`. Views use `@can`/`@cannot` only for showing or hiding UI. **Data access is enforced in the controller/query**, never only in Blade: `Info::visibleTo($user)` gives editors (`editPosts`) every info and everyone else only `public` ones, and `InfoController` uses it for the list and `infoView` (a private info is a 404 for subscribers). Reuse that pattern for Alerts, Plans and Licenses. `viewPosts` is granted to editors and subscribers only, not admins.

**Abuse limits.** `POST /register` and `POST /password/email` use the named rate limiters `throttle:register` and `throttle:password-email` (3 per 10 minutes per IP, defined in `AppServiceProvider::defineRateLimiters()`). Keep one named limiter per form: a plain `throttle:3,10` keys guests by IP only, so all such routes would share one counter.

**First admin.** `php artisan db:seed` runs `Database\Seeders\AdminUserSeeder`, which creates the admin from `config('project.seed_admin')` (`SEED_ADMIN_NAME`/`EMAIL`/`PASSWORD` in `.env`; defaults `admin@example.com`/`password`) and leaves an existing user with that email untouched.

**Models.** Eloquent models live in `App\Models` (`User`, `Info`, `Alert`, `Plan`, `License`, `Options`). The post-like tables (`infos`, `alerts`, `plans`, `licenses`) share one shape: `title`, `content`, `author` (user id, no foreign key), `status` (`public`/`private`, from `get_post_statuses()`). Factories are classes (`User::factory()->admin()`, `->editor()`; `Info::factory()->private()`).

**Admin sections.** Only Infos is implemented end to end (`InfoController`, `InfoAddRequest`/`InfoEditRequest`). Alerts, Plans, Licenses, Users and Messages controllers only return "TODO: coming soon" views, and have no form requests yet. The Messages menu is commented out in `includes/admin/header.blade.php` until Messages is built. Controllers usually pass plain arrays (`->toArray()`) to views; the infos list eager-loads its author as `authorUser` (not `author()`, which is the user-id column), so the view reads `$info['author_user']['name']`.

**Views.** Three layouts in `resources/views/layouts/` (`admin`, `auth`, `landing`), each with matching partials in `resources/views/includes/<layout>/`. View names are camelCase per section, e.g. `admin.infos.infosIndex`, `admin.infos.infoEdit`; route names follow the same pattern (`infos`, `infoAdd`, `infoEditSave`, ...).

**Facades.** PHP files import the facades they use (`use Illuminate\Support\Facades\Route;`, etc.) so Intelephense resolves them, since no IDE helper package is installed. Blade views use the framework's default facade aliases (e.g. `Auth::user()`), which is fine at runtime.

**Tests.** `assertSee()`/`assertDontSee()` escape their text since Laravel 7, so the tests pass `false` when they look for raw HTML. Use `assertStringContainsString()`/`assertStringNotContainsString()` on strings; PHPUnit 12's `assertContains()` only takes arrays.

## Conventions

Commit messages are one line in the form `type: description` (e.g. `fix: ...`, `chore: ...`, `refactor: ...`).

The plans in `docs/` (review, theme licensing, GitHub migration, Laravel 13 upgrade, finish project) are **local working notes, gitignored on purpose**: never `git add -f` them, and don't link to them or quote their finding IDs (B05, R01, …) in tracked files such as the README, code comments or tests.

The UI language is **English only** (decided 2026-11-02). `AccessTest::test_admin_pages_are_english_only` fails on Spanish text in any admin page. Don't introduce `lang/` translation files.
