# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environment

Laravel 13 admin app ("BIS Admin") on PHP 8.4, served locally by WAMP at http://bis-admin.test with MySQL database `bis_admin`. Upgraded from Laravel 5.8 / PHP 7.1 on 2026-11-09 (the last 5.8 commit is `13cccce`).

- **Use the plain `php` (8.4.26) and the global `composer` (2.10).** `php71` is only for the old 5.8 code.
- **Tests use an in-memory SQLite database** (`phpunit.xml` sets `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`; `tests/Unit/TestDatabaseTest.php` guards it), so they never touch `bis_admin`. Still, never put test data into `bis_admin` by hand.
- **Tables are InnoDB** (migration `2026_11_04_000000_convert_tables_to_innodb`, and `'engine' => 'InnoDB'` in `config/database.php`). WAMP's MySQL 8.3 defaults to MyISAM, which is why they weren't before.
- **Cache, session and queue are `file`, `file` and `sync`** in `.env`. Laravel 13 defaults them to `database`, and this database has no `cache`, `sessions` or `jobs` tables.
- **`laravel/pao`** (a dev dependency) prints test results as one JSON line when it detects an AI agent. For PHPUnit's normal output, run it with the agent variables unset, e.g. `env -u CLAUDECODE -u CLAUDE_CODE -u AI_AGENT php vendor/bin/phpunit`.

## Commands

```bash
composer install                                  # install PHP dependencies
php artisan migrate                               # create the tables
php -d xdebug.mode=off artisan test               # run all tests (xdebug makes them several times slower)
php artisan test tests/Feature/ContactFormTest.php   # one test file (tests: Unit/, Feature/Auth/, Feature/Admin/, Feature/*Test.php)
php artisan test --filter test_editor_adds_an_info   # one test method (names are test_*, no annotations)
vendor/bin/pint --test                            # code style report (laravel preset); the code is not pint-formatted, so don't mass-reformat
php artisan route:list --except-vendor            # list the app's routes
```

Front-end assets are not built and there is no `package.json`. The views load ready-made theme files with `asset(...)`:
- `public/admin/` is the **Sufee 3.2.1** admin theme (Colorlib, MIT; Bootstrap 5.3, Font Awesome 7, dark mode), built once outside the repo with its own `npm run build` (source in `~/Downloads/webs/sufee-admin-dashboard-3.2.1`). Only the built `assets/` (no source maps, no legacy-browser chunks), `images/`, `themify-icons/`, `LICENSE` and `README.md` are copied. The file names are hashed: `includes/admin/head.blade.php` names `main-*.css` and the entry `main-*.js`, so update them after a rebuild. The theme's demo loads its sidebar/header/breadcrumb at runtime from `data-partial` placeholders; here the Blade layouts render them (`includes/admin/sidebar|header`, breadcrumb in `layouts/admin`) and the theme script only wires them up. The active menu item comes from `@section('page', '...')` (`body[data-page]` matched to `.sidebar .nav-link[data-page]`). Used by the admin and auth layouts (`layouts/auth` is the theme's `page-login.html` card).
- **Forms** use the Blade components in `resources/views/components/form/` (`<x-form.ajax>`, `<x-form.input>`, `<x-form.textarea>`, `<x-form.select>`, `<x-form.error>`): every form is sent by `public/js/ajax-form.js`, and each field has an always-rendered `.invalid-feedback[data-error-for]` that both the server (normal post) and the script (422 JSON) fill. Controllers finish a save with `$this->done($request, $url, $message)` (base `Controller`): it flashes `success` and answers `{message, redirect}` to ajax or redirects otherwise. The layouts show flashed `success`/`error` (`includes/admin/alerts`). `LoginController::authenticated()` and `ResetPasswordController::sendResetResponse()` answer ajax with `{redirect}` too.
- `public/landing/` is the **Bell** theme (BootstrapMade, free license; its footer credit must stay).
- Project-specific admin styles go in `public/css/bis-admin.css`, which loads after the theme. Keep theme files, their banners and their `LICENSE`/`README` files unchanged (details in the local `docs/theme-licensing-plan.md`).

## Architecture

**Bootstrapping.** Laravel 11+ structure: no HTTP/console kernels and only `AppServiceProvider` (`bootstrap/providers.php`). `bootstrap/app.php` sends logged-in users who open a guest page to `/dashboard` (`redirectUsersTo`); guests who open an `auth` page go to `route('login')`.

**Routing.** `routes/web.php` defines the landing pages and the contact form post, and `require`s `routes/auth.php` (login, password reset) and `routes/admin.php` (everything under `/dashboard`, behind the `auth` middleware). Routes use the `[Controller::class, 'method']` syntax. There are no API routes.

**Auth.** The three controllers in `app/Http/Controllers/Auth` use the `laravel/ui` traits (`AuthenticatesUsers`, `SendsPasswordResetEmails`, `ResetsPasswords`). There is **no public sign-up** (decided 2026-11-24): staff create the accounts, so don't add register routes back. The base `Controller` is empty, so controllers can't call `$this->middleware()`: `guest` is applied in `routes/auth.php`, with `logout` outside that group. The password broker uses the old `password_resets` table (`config/auth.php`), and it also allows only one reset link per email per 60 seconds.

**Site settings and contact form.** Site title, tagline and contact email are `config('project.site_title')`, `config('project.tagline')` and `config('project.contact_email')` in `config/project.php`, from `SITE_TITLE`, `SITE_TAGLINE` and `CONTACT_EMAIL` in `.env`. There is no `options` table any more (dropped 2026-11-24). The landing contact form posts to `ContactController@store` (`route('contact')`, `ContactRequest`): it saves a `Contact` row, then emails staff the `ContactReceived` mailable (a plain-text view, `emails.contactReceived`); a mail failure is only `report()`ed, so the message is never lost. A hidden `website` field is a honeypot: when it's filled in, the controller acts as if it worked and saves nothing. The form is sent with ajax by `public/js/ajax-form.js` (vanilla JS, no jQuery, so the admin can reuse it): a `<form data-ajax-form>` is posted with `fetch` and `Accept: application/json`; the controller answers `{message}` (or `{message, redirect}`), Laravel answers 422 with `errors`, and the script puts each error in the element with `data-error-for="<field>"` and adds `is-invalid` to the field. Without JS the form posts normally: errors and the success message go back to `/#contact`. Both paths use the Bell styles `#sendmessage`, `#errormessage` and `.validation` (shown with `.show`, from `data-error-class`); keep those elements always rendered, empty, so the script can fill them. `CONTACT_ADDRESS` and `CONTACT_PHONE` are optional and left out of the page when empty. The global helpers in `app/Helpers.php` (autoloaded through composer's `files`) are wrapped in `function_exists` guards; keep them, because tools that autoload `app/*.php` as classes (Laravel Extra Intellisense) would otherwise fatally redeclare them.

**Roles and permissions.** Two roles (decided 2026-11-24): `App\Models\User` stores an integer `role`, `ADMIN = 0` shown as **Staff** and `EDITOR = 1` shown as **Architect** (`ROLE_LABELS`, `roleLabel()`, `isStaff()`, `isArchitect()`); keep the admin/editor names in code. `getRole()` returns `'admin'|'editor'`, or `''` for anything else, which gets no permissions. All authorization is `Gate::define` abilities in `AppServiceProvider::defineGates()` (no policies): staff only `manageUsers`, `readContacts`, `manageProperties` (add/edit/delete/publish/assign/upload); both roles `viewProperties`, `updateWorkStatus`, `useMailbox`, `editProfile`. Routes enforce them with `->middleware('can:...')`; views use `@can` only to show or hide UI. **Which properties an architect sees is enforced in the query**, never only in Blade: `Property::visibleTo($user)` gives staff everything and architects the *published* properties assigned to them **or with no architect assigned**; drafts are staff only. Every property, plan and license query for a page goes through it (anything else is a 404); `$property->isVisibleTo($user)` checks one.

**Abuse limits.** `POST /contact` and `POST /password/email` use the named rate limiters `throttle:contact` and `throttle:password-email` (3 per 10 minutes per IP, defined in `AppServiceProvider::defineRateLimiters()`). The `contact` limiter's response redirects back to the form with an error instead of a bare 429 page. Keep one named limiter per form: a plain `throttle:3,10` keys guests by IP only, so all such routes would share one counter.

**First admin and demo data.** Staff (admin) accounts are created with `php artisan app:create-admin` (`App\Console\Commands\CreateAdmin`; asks for name, email and a secret password, `--name`/`--email` may be options; the password never is). The seeders never create admins: `php artisan db:seed` runs `ArchitectSeeder` (6 editors, password `ArchitectSeeder::PASSWORD`) and `PropertySeeder` (20 properties around `config('project.map_center')`, Barcelona, with drafts, unassigned ones, status history, plans and licenses; each file is its own small PDF from `Database\Seeders\DemoPdf` on the private disk).

**Models.** Eloquent models live in `App\Models`. `Property` is the center (`STATUSES` draft/published, `WORK_STATUSES` pending/in_progress/done; `architects()` many-to-many to users through `property_user`; `plans()`, `licenses()`, `statusChanges()` newest first). `Plan` and `License` use the `Concerns\HasPdfFile` trait: the PDF is on the private `local` disk (`storage/app/private`) at `file_path`, `file_name` is the download name, and deleting the record deletes the file; `Property::deleting` deletes its plans' and licenses' files because the database cascade skips model events. `License` has `issued_at`/`expires_at`, `isExpired()`, `isExpiringSoon()` and `scopeNeedsAttention()` (within `EXPIRING_DAYS` = 30). `PropertyStatusChange` logs who changed a work status, from, to and a note. `MailAccount` (one per user) stores the IMAP/SMTP settings for Alerts with an `encrypted` password cast. `Contact` is a landing form message. The tables are created by the `0001_01_01_*` migrations (the old 2014/2018 ones were replaced on 2026-11-24 and the database rebuilt; backup in `~/Downloads/backups/bis-admin/bis_admin-backup-20261124.sql`). Real foreign keys, InnoDB. Factories are classes (`Property::factory()->draft()`, `License::factory()->expired()`/`expiringSoon()`, `User::factory()->admin()`/`editor()`).

**Admin sections.** Being rebuilt around properties (local finish plan, Part 2). Until each section lands, Infos, Alerts, Plans, Licenses, Users and Messages show "TODO: coming soon" pages.

**Views.** Three layouts in `resources/views/layouts/` (`admin`, `auth`, `landing`), each with matching partials in `resources/views/includes/<layout>/`. View names are camelCase per section, e.g. `admin.infos.infosIndex`, `admin.infos.infoEdit`; route names follow the same pattern (`infos`, `infoAdd`, `infoEditSave`, ...).

**Facades.** PHP files import the facades they use (`use Illuminate\Support\Facades\Route;`, etc.) so Intelephense resolves them, since no IDE helper package is installed. Blade views use the framework's default facade aliases (e.g. `Auth::user()`), which is fine at runtime.

**Tests.** `assertSee()`/`assertDontSee()` escape their text since Laravel 7, so the tests pass `false` when they look for raw HTML. Use `assertStringContainsString()`/`assertStringNotContainsString()` on strings; PHPUnit 12's `assertContains()` only takes arrays.

## Conventions

Commit messages are one line in the form `type: description` (e.g. `fix: ...`, `chore: ...`, `refactor: ...`).

The plans in `docs/` (review, theme licensing, GitHub migration, Laravel 13 upgrade, finish project) are **local working notes, gitignored on purpose**: never `git add -f` them, and don't link to them or quote their finding IDs (B05, R01, …) in tracked files such as the README, code comments or tests.

The UI language is **English only** (decided 2026-11-02). `AccessTest::test_admin_pages_are_english_only` fails on Spanish text in any admin page. Don't introduce `lang/` translation files.
