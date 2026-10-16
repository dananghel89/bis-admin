# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environment

Laravel 5.8 admin app ("BIS Admin"), served locally by WAMP at http://bis-admin.test with MySQL database `bis_admin`.

- **Always use PHP 7.1 via `php71`** (`C:/Wamp/bin/php/php7.1.33`). The `php` on PATH is 8.4 and floods Laravel 5.8 with deprecation output.
- **Use the project's own Composer:** `php71 composer.phar ...` (2.2 LTS, committed to the repo). The global `composer` is 2.10 and requires PHP ≥ 7.2.5.
- **The database cannot roll back.** Every table in `bis_admin` is MyISAM, so `DB::beginTransaction()`/`rollBack()`, `DatabaseTransactions` and `RefreshDatabase` silently do nothing. `phpunit.xml` does not override `DB_CONNECTION`, so any test that touches the DB writes to the real `bis_admin` data. Do not write test data to it; use an sqlite test connection or ask first.

## Commands

```bash
php71 composer.phar install                     # install PHP dependencies
php71 artisan migrate                           # create tables; the options migration also seeds the 4 default options
php71 vendor/bin/phpunit                        # run all tests
php71 vendor/bin/phpunit tests/Feature/ExampleTest.php   # one test file
php71 vendor/bin/phpunit --filter testBasicTest          # one test method
php71 -l path/to/File.php                       # syntax check (no linter is configured)
php71 artisan route:list                        # list routes
php71 artisan ide-helper:generate               # regenerate _ide_helper.php (gitignored) after adding facades/packages
```

Front-end assets are not built. The views load prebuilt static templates from `public/admin/` and `public/landing/` with `asset(...)`. `webpack.mix.js`/`package.json` are the untouched Laravel defaults, and no view uses their output.

## Architecture

**Routing.** `routes/web.php` defines the landing pages and `require`s `routes/auth.php` (login, register, password reset) and `routes/admin.php` (everything under `/dashboard`, behind the `auth` middleware). `routes/api.php` is empty.

**Options and registration.** Site settings live in the key/value `options` table (`site_title`, `tagline`, `email_address`, `membership`) and are read with the global `get_option($name)` helper in `app/Helpers.php`. That file is autoloaded through composer's `files` and caches all options in a `static` once per request. Its functions are wrapped in `function_exists` guards; keep them, because tools that autoload `app/*.php` as classes (Laravel Extra Intellisense) would otherwise fatally redeclare them. The register routes are only registered when `get_option('membership')` is truthy. This happens when the routes file loads, so `route:cache` freezes the setting. `RegisterController` also re-checks membership in `showRegistrationForm()`/`register()`, then delegates to the `RegistersUsers` trait method, aliased as `registerUser`. New users always get `User::SUBSCRIBER`.

**Roles and permissions.** `App\User` stores an integer `role` (`ADMIN = 0`, `EDITOR = 1`, `SUBSCRIBER = 2`); `getRole()` returns `'admin'|'editor'|'subscriber'`. All authorization is `Gate::define` abilities in `AuthServiceProvider` (no policies): `editOptions`, `viewUsers`, `addPosts`, `viewPosts`, `editPosts`, `accessMessages`, etc. Routes enforce them with `->middleware('can:...')`. Views also use `@can`/`@cannot` for page-level access and for filtering. For example, non-editors only see `public` infos, and that filtering is done in the Blade templates, not the controller. `viewPosts` is granted to editors and subscribers only, not admins.

**Models.** Eloquent models live in `App\Http\Models` (`Info`, `Alert`, `Plan`, `License`, `Options`, `Dashboard`), not in `app/` or `app/Models`; `User` is `App\User`. The post-like tables (`infos`, `alerts`, `plans`, `licenses`) share one shape: `title`, `content`, `author` (user id, no foreign key), `status` (`public`/`private`, from `get_post_statuses()`).

**Admin sections.** Only Infos is implemented end to end (`InfoController`, `InfoAddRequest`/`InfoEditRequest`). Alerts, Plans, Licenses, Users and Messages controllers only return their views. Their FormRequests (`AlertRequest`, `PlanRequest`, `LicenseRequest`) still have `authorize()` returning `false` and must be changed when wired up. Controllers usually pass plain arrays (`->toArray()`) to views.

**Views.** Three layouts in `resources/views/layouts/` (`admin`, `auth`, `landing`), each with matching partials in `resources/views/includes/<layout>/`. View names are camelCase per section, e.g. `admin.infos.infosIndex`, `admin.infos.infoEdit`; route names follow the same pattern (`infos`, `infoAdd`, `infoEditSave`, ...).

**Facades.** Route files and some views use facade aliases (`Route::`, `Auth::`) without `use` imports, relying on the aliases in `config/app.php`. Editor tooling resolves them through the generated `_ide_helper.php`.

## Conventions

Commit messages are one line in the form `type: description` (e.g. `fix: ...`, `chore: ...`, `refactor: ...`).
