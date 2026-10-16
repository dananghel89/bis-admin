# Plan: Upgrade to Laravel 13 on PHP 8.4

## Goal

Move BIS Admin from **Laravel 5.8 on PHP 7.1** to **Laravel 13 on PHP 8.4**, the PHP version already installed in WAMP (`C:\Wamp\bin\php\php8.4.26`). The app should keep working the same way and keep its existing data.

**Why Laravel 13:** it is the current major release and requires PHP 8.3 or newer. It supports PHP 8.3–8.5, gets bug fixes until Q3 2027 and security fixes until Q1 2028. Laravel 12 also runs on PHP 8.4, but its security support ends in February 2027.

Sources: [Laravel 13 release notes](https://laravel.com/docs/13.x/releases.md), [Laravel 13 released (Laravel News)](https://laravel-news.com/laravel-13-released).

## Approach: start from a fresh Laravel 13 project and move the code in

Laravel 5.8 to 13 is eight major versions. Upgrading one version at a time (6 → 7 → … → 13) means eight rounds of dependency changes and upgrade guides. This app is small: 9 admin controllers, 5 auth controllers, 6 models, 7 form requests, 45 Blade views and 8 migrations. It is much less work to create a clean Laravel 13 project and copy the app's own code into it, fixing each part as it moves.

Do all the work on a branch, `upgrade/laravel-13`. `master` stays on 5.8 until the new version passes the checklist at the end.

## Step 0 — Prepare

1. **Back up the database:**
   ```bash
   mysqldump -u root bis_admin > bis_admin-backup.sql
   ```
2. **Convert the tables to InnoDB.** Every table is currently MyISAM, so transactions and rollbacks silently do nothing. Laravel 13's test helpers rely on transactions.
   ```sql
   ALTER TABLE users ENGINE=InnoDB;      ALTER TABLE password_resets ENGINE=InnoDB;
   ALTER TABLE options ENGINE=InnoDB;    ALTER TABLE infos ENGINE=InnoDB;
   ALTER TABLE alerts ENGINE=InnoDB;     ALTER TABLE plans ENGINE=InnoDB;
   ALTER TABLE licenses ENGINE=InnoDB;   ALTER TABLE migrations ENGINE=InnoDB;
   ```
3. **Note the `APP_KEY`** in the current `.env`. Reusing it in the new project keeps existing login sessions valid. Passwords are hashed with bcrypt, so they work either way.
4. **Check the global Composer:** `composer --version` should report 2.x and PHP 8.4. Once the upgrade is done, the `composer.phar` in the repo is no longer needed.

## Step 1 — Create the Laravel 13 project

Create it in a separate folder, then copy it into the branch:

```bash
composer create-project laravel/laravel:^13.0 ../bis-admin-13
```

Then add the packages this app needs:

| Package | Action | Why |
|---|---|---|
| `laravel/ui` `^4.6` | add | Contains the `AuthenticatesUsers`, `RegistersUsers`, `SendsPasswordResetEmails` and `ResetsPasswords` traits the auth controllers use. Laravel removed them from the framework in 6.0. v4.6.3 supports Laravel 13 ([Packagist](https://packagist.org/packages/laravel/ui)). |
| `barryvdh/laravel-ide-helper` | add as dev, latest version | Generates `_ide_helper.php` for VS Code / Intelephense |
| `fideloper/proxy` | drop | Built into Laravel since 9 |
| `beyondcode/laravel-dump-server` | drop | Not used |
| `fzaninotto/faker` | drop | Abandoned; the new project already includes `fakerphp/faker` |
| `phpunit/phpunit`, `nunomaduro/collision`, `mockery/mockery` | use the new project's versions | |

## Step 2 — Move the code in

Laravel 11 introduced a much smaller project structure: no `Kernel.php` files, and fewer providers and config files. The table below says where each part of the current app goes.

| Current location | New location | Changes needed |
|---|---|---|
| `app/Helpers.php` | `app/Helpers.php` | Add to `autoload.files` in `composer.json`. No code changes. |
| `app/User.php`, `app/Http/Models/*` | `app/Models/` (namespace `App\Models`) | Recommended: matches Laravel's convention and Laravel Extra Intellisense finds them there. Update every `use` statement and `config/auth.php`. Keep `User`'s `role` constants and `getRole()`. |
| `app/Http/Controllers/Admin/*`, `Auth/*` | same folders | See **Controllers** below. |
| `app/Http/Requests/*` | same folder | `SettingsRequest`: replace `Input::get('password')` with `$this->input('password')`; the `Input` facade was removed in Laravel 6. `InfoEditRequest`: replace `$this->id` with `$this->route('id')`. |
| `AuthServiceProvider` (all `Gate::define` calls) | `AppServiceProvider::boot()` | Laravel 11+ has no `AuthServiceProvider` by default. Remove the empty `$policies` array. |
| `AppServiceProvider` `Schema::defaultStringLength(191)` | remove | Only needed for MySQL < 5.7.7 / MariaDB < 10.2.2. |
| `app/Http/Middleware/*` | delete | All are the stock 5.8 versions, now built into the framework. Set the "already logged in" redirect in `bootstrap/app.php`: `$middleware->redirectUsersTo('/dashboard')`. |
| `routes/web.php`, `auth.php`, `admin.php` | same files | See **Routes** below. `routes/api.php` is empty: don't install API routes. |
| `routes/channels.php`, `console.php` | drop `channels.php`; keep `console.php` only if it gains commands | Broadcasting isn't used. |
| `database/migrations/2014_*`, `2018_*` | `database/migrations/` | See **Database** below. |
| `database/seeds/DatabaseSeeder.php` | `database/seeders/` | The folder was renamed and seeders are namespaced since Laravel 8. Use the new project's file. |
| `database/factories/UserFactory.php` | use the new project's class-based factory | Add `role` to its definition. |
| `resources/views/**` | same | Blade is compatible. `csrf_field()`, `method_field()`, `@can` and `@cannot` all still work. |
| `public/admin`, `public/landing`, `favicon.ico` | same | Copy as is. |
| `.vscode/settings.json` | update | Remove the `LaravelExtraIntellisense.phpCommand` override; the default `php` (8.4) is now correct. |

### Controllers

- **`$this->middleware(...)` in constructors no longer works.** In Laravel 11+, the base `Controller` is an empty class. `LoginController`, `RegisterController`, `ForgotPasswordController`, `ResetPasswordController` and `VerificationController` all call it. Remove those constructors and apply `guest` / `auth` in the route files instead (see below).
- If a controller ever calls `$this->authorize()` or `$this->validate()`, add `use AuthorizesRequests, ValidatesRequests;` to the base `Controller`. The current code uses neither: it relies on form requests and `can:` middleware.
- `RegisterController` keeps its `register as protected registerUser` trait alias. Replace `bcrypt()` with `Hash::make()`; both still work, so this is optional.

### Routes

- **Change every string controller reference to the class syntax.** Laravel 8 dropped the automatic `App\Http\Controllers` prefix, so `'Admin\InfoController@index'` no longer resolves:
  ```php
  use App\Http\Controllers\Admin\InfoController;

  Route::get('/infos', [InfoController::class, 'index'])->name('infos')->middleware('can:viewPosts');
  ```
- Group the guest-only auth routes under `Route::middleware('guest')`, keeping `logout` outside that group.
- **Recommended:** replace the `if (get_option('membership'))` around the register routes with a small middleware (for example `EnsureMembershipIsOpen`) on those two routes. The routes then always exist, `php artisan route:cache` keeps working when the option changes, and the checks in `RegisterController` become unnecessary.

### Database

Keep the existing database and its data. In the new project:

1. **Delete** the new project's default migrations (`0001_01_01_000000_create_users_table`, `…_create_cache_table`, `…_create_jobs_table`) and **copy in the 8 existing migrations**. The `migrations` table already records them as run, so nothing runs again.
2. The new project's `.env` defaults to `SESSION_DRIVER=database`, `CACHE_STORE=database` and `QUEUE_CONNECTION=database`, which need tables this database doesn't have. Either set them to `file`, `file` and `sync` (the current behavior), or add the `sessions`, `cache` and `jobs` migrations with `php artisan make:session-table`, `make:cache-table` and `make:queue-table`, then run `php artisan migrate`.
3. **Password reset table:** Laravel 10+ uses `password_reset_tokens`. Either rename the table with a migration, or set `'table' => 'password_resets'` under `passwords.users` in `config/auth.php`.
4. In `config/database.php`, set `'engine' => 'InnoDB'` on the `mysql` connection so new tables are never MyISAM again.

### Environment (`.env`)

Use the new project's `.env.example` and copy over the values for `APP_NAME`, `APP_URL`, `APP_KEY` and `DB_*`. Renamed keys:

| Old | New |
|---|---|
| `MAIL_DRIVER` | `MAIL_MAILER` (use `log` while developing) |
| `CACHE_DRIVER` | `CACHE_STORE` |
| `BROADCAST_DRIVER` | `BROADCAST_CONNECTION` |

## Step 3 — Tests

- In `phpunit.xml`, set `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` so tests never touch `bis_admin`.
- Add feature tests for the behavior that must survive the upgrade:
  - login and logout
  - registration with Membership on, and the redirect with it off
  - password reset request (mail driver `array`)
  - each role's access to Options, Users, Infos and Settings
  - Infos create, edit and delete as an editor
  - subscribers can't see private infos
  - 404 for a missing info

Run them with `php artisan test`.

## Step 4 — Switch WAMP and the tooling to PHP 8.4

1. In WAMP, switch the `bis-admin.test` virtual host or Apache to PHP 8.4.26.
2. Regenerate the IDE helper with `php artisan ide-helper:generate`.
3. Update `README.md` and `CLAUDE.md`: PHP 8.4, the global `composer`, no `php71`, the new commands, and the models in `App\Models`.
4. Remove `composer.phar` from the repository.

## Verification checklist (before merging into `master`)

- [ ] `composer install` and `php artisan migrate` run cleanly on PHP 8.4, with no deprecation warnings.
- [ ] `php artisan route:list` and `php artisan route:cache` both work.
- [ ] `php artisan test` passes.
- [ ] Manual check at http://bis-admin.test:
  - landing page and TOS
  - login and logout
  - register, with Membership on and off
  - password reset email (in the log)
  - Settings: change name and password
  - Options: save all four
  - Infos: list, add, view, edit, delete
  - a subscriber can't see private infos or open Options and Users
- [ ] The existing user and info from the old database still show up and the user can log in.

## Risks and notes

- **The `laravel/ui` auth traits are legacy code.** They are maintained, but Laravel's current recommendation is Fortify or a starter kit. Keeping `laravel/ui` makes this upgrade a code move instead of a rewrite. Replacing the auth layer can be a separate, later task.
- **Laravel 11+ rehashes passwords at login** when the bcrypt cost changes (default 12). This happens automatically and needs no action.
- **Rough effort:** 1–2 days of focused work, most of it on Routes and Controllers plus testing. This is an estimate, not a measurement.
