# Project review and fix plan

Status: **Done** – Phase A done 2026-11-04 (afa3a17: DB backup `../bis_admin-backup-20261104.sql`, B01, B02, B03, B04); Phase B done 2026-11-04 (f991ec2, 41 tests); Phase C done 2026-11-04 (6e52208, B05–B12); Phase D done 2026-11-04 (311156e, B13–B19; B20 left to the backlog, 58 tests); `/tos` moved to `/terms-of-service` (d3f08ca); Phase E done 2026-11-04 (R01 fixed in 3ef747f, 60 tests); Phase F done 2026-11-04. Next: [laravel-13-upgrade-plan.md](laravel-13-upgrade-plan.md)
Created: 2026-11-02

Plan order in `docs/`:

1. **This plan**
2. [theme-licensing-plan.md](theme-licensing-plan.md) (done, 2026-11-02)
3. [github-migration-plan.md](github-migration-plan.md) (already done, 2026-10-16 → 2026-10-22)
4. [laravel-13-upgrade-plan.md](laravel-13-upgrade-plan.md)
5. [finish-project-plan.md](finish-project-plan.md)

Two parts of the upgrade plan move into this plan, because both make every later step safer:
- converting the tables to InnoDB (its Step 0.2)
- a SQLite test database and a real test suite (its Step 3)

## 1. Scope and decisions

| Topic | Decision |
|---|---|
| Stack during this plan | Unchanged: Laravel 5.8, PHP **7.1** (`php71`), PHPUnit 7.5 |
| What gets fixed | Everything that is **wrong**: bugs, security issues, broken config, dead or misleading UI, dead code |
| Unfinished features | **Not built here.** They are made to *fail cleanly* (hidden menu entries, no fake numbers) and listed in the backlog (section 6), which [finish-project-plan.md](finish-project-plan.md) covers |
| Database | Local. The current data is 1 user and 1 info; back it up before Phase A. Schema changes go into **new migrations**, so the existing data is kept |
| Way of working | Every fix starts with a failing test, then the code change, then a green suite. Each phase is one commit, made with your approval |
| Commit messages | One line, `type: description` (`fix:`, `feat:`, `test:`, `refactor:`, `chore:`, `docs:`, `style:`) |

## 2. Environment rules

- PHP: **`php71`** for every command. Composer: **`php71 composer.phar`**.
- > ⛔ **Do not add or run database tests until step A2 is done.** Today `phpunit.xml` has no test database, so tests use the real `bis_admin` MySQL database. Its tables are MyISAM, so transactions and rollbacks silently do nothing: any test that writes data leaves it behind (findings B01 and B02).
- Back up the database before Phase A: `mysqldump -u root bis_admin > ../bis_admin-backup-YYYYMMDD.sql`.
- Never verify a fix by writing test data to `bis_admin`. Use the SQLite test database from step A2.

## 3. Findings

How each finding was confirmed:
- **Reproduced:** confirmed by running the real app on 2026-11-02 against an in-memory SQLite database, with the `array` mail driver. No MySQL data was touched.
- **Code:** certain from reading the code.
- **Browser:** must be confirmed in the browser during the fix.

Four suspicions were checked and **dropped**, because they turned out not to be bugs:
- Editing an info without changing its title passes the `unique` rule.
- A logged-in user opening `/login` is correctly sent to `/dashboard`.
- No view prints raw HTML (`{!! !!}` is never used), so there is no XSS through Blade output.
- Every form that sends data includes a CSRF token. The landing contact form is disabled; see B20.

### High

| ID | Area | Location | Problem | Confirmed by |
|---|---|---|---|---|
| B01 | Tests | `phpunit.xml` | No `DB_CONNECTION` / `DB_DATABASE` override, so every test runs against the real `bis_admin` MySQL database. Any database test would write to it, or wipe it with `RefreshDatabase` | Code |
| B02 | Database | all 8 tables, `config/database.php:56` (`'engine' => null`) | Every table is **MyISAM**, so there are no transactions and no foreign keys. `DB::transaction()`, `DatabaseTransactions` and rollbacks silently do nothing | Reproduced (information_schema) |
| B03 | Boot | `routes/auth.php:14`, `app/Helpers.php` (`get_option`) | Loading the routes reads the `options` table. As a result, **every `artisan` command fails when MySQL is down**, including `package:discover`, which `composer install` runs, so the project can't even be installed without a database | Reproduced |
| B04 | Auth › Register | `routes/auth.php:14-19`, `get_option()` `static` cache | Membership is decided **once, when the routes load**. Consequences: `route:cache` freezes it; a fresh database or a test database (no `options` table yet at boot) silently turns registration off; and the `static` cache keeps the empty value for the whole PHP process. In tests, the register routes are never active | Reproduced |

### Medium

| ID | Area | Location | Problem | Confirmed by |
|---|---|---|---|---|
| B05 | Infos › Status | `InfoAddRequest`, `InfoEditRequest` (`'status' => 'required'`) | Any status is accepted. An info saved as `whatever` doesn't appear in a subscriber's list, but **its content is readable at its URL**. The page only hides content when the status is exactly `private` | Reproduced |
| B06 | Infos › Visibility | `InfoController::infoView`, `admin/infos/infoView.blade.php:10-20` | Privacy is enforced only in Blade. A subscriber opening a private info's URL gets **HTTP 200**, and the controller has already loaded the whole record. It should be a 404 from the controller. The list filter is also done in Blade (`infosIndex.blade.php:25-49`) | Reproduced |
| B07 | Options | `OptionsController::optionsEdit` | Saving the Options form sets **every option row that isn't on the form** to `'0'`. An option named `logo` was overwritten with `'0'`. It also runs one `find()` query per option | Reproduced |
| B08 | Security › Mail | `ForgotPasswordController`, `routes/auth.php` (`password.email`) | Password-reset requests aren't throttled: 6 requests in a row sent **6 emails**. The same applies to `POST /register` when Membership is on (spam accounts) | Reproduced |
| B09 | Infos › Delete | `admin/infos/infoEdit.blade.php:93-115` | **One click** on Delete permanently deletes the info. There's no confirmation | Code |
| B10 | Setup › First admin | `database/seeds/DatabaseSeeder.php` (empty), `database/factories/UserFactory.php` (no `role`) | A fresh install has **no way to get an admin**: registration only creates subscribers, and Users management isn't built. The first admin has to be set by hand in SQL | Code |
| B11 | Admin › Menu | `includes/admin/header.blade.php:15-38` | The Messages menu shows a hard-coded **"10"** unread badge for every user, for a feature that doesn't exist. Its pages say "TODO: coming soon" | Reproduced |
| B12 | Mail | `.env.example:26-31` | Mail defaults to Mailtrap SMTP with `null` credentials, so on a fresh install "Forgot password" fails, or sends nowhere, until mail is configured | Browser |

### Low

| ID | Area | Location | Problem | Confirmed by |
|---|---|---|---|---|
| B13 | UI › Language | `includes/admin/header.blade.php`, 17 admin views (`@section('title')`) | Mixed languages: menus, list titles and add titles are **Spanish** ("Todas Las Informaciones", "Añadir Plan", "Bandeja De Entrada"), while edit and view pages, buttons and messages are **English** | Code |
| B14 | UI › Copy | `admin/{alerts,plans,licenses}/*.blade.php:13` | Copy-paste text: "You are not allowed to access **info**" on 11 Alerts, Plans and Licenses pages | Code |
| B15 | Infos › List | `InfoController::index`, `infosIndex.blade.php:55` | Passes `$user = User::class` to the view and runs `$user::find()` **once per row** (N+1 queries). `Info::all()` has no ordering | Code |
| B16 | Infos › View | `infoView.blade.php:40` | `{{ $info['content'] }}` drops the line breaks typed in the 10-row textarea | Code |
| B17 | Auth › Passwords | `RegisterController`, `SettingsRequest`, `ResetPasswordController` | Minimum password length is 6 | Code |
| B18 | Dead code | various | `use Illuminate\Http\Request;` unused in 6 admin controllers; unused `MustVerifyEmail` import in `User.php`; `VerificationController` has no routes; Gate `registerUser` is never used; empty `Dashboard` model; `AlertRequest`, `PlanRequest` and `LicenseRequest` (`authorize()` returns `false`) are unused; the `@cannot` branches in admin views are unreachable because the routes already apply `can:`; `resources/js`, `resources/sass`, `package.json` and `webpack.mix.js` are unused Laravel defaults | Code |
| B19 | Deprecated API | `SettingsRequest.php:28` | `Input::get()`. The `Input` facade is removed in Laravel 6 and would block the upgrade | Code |
| B20 | Landing | `landing/front.blade.php:215-232`, `landing/tos.blade.php` | The contact form is a disabled `<fieldset>` with no action or CSRF token. Texts are lorem ipsum. The address and phone number are hard-coded. The `email_address` option isn't used anywhere | Code |

The PHP files pass `php71 -l`, every route name used in the views exists, and every view the code references exists.

## 4. Phases

### Phase A – Safety first (no behaviour change)

1. Work directly on `master`: commit and push each phase only after you confirm it (I show the files and message first). Back up the database (section 2).
2. **B01:** in `phpunit.xml`, add `<server name="DB_CONNECTION" value="sqlite"/>` and `<server name="DB_DATABASE" value=":memory:"/>`. From now on, running tests is safe.
3. **B02:** a new migration that runs `ALTER TABLE … ENGINE=InnoDB` for the 8 tables (MySQL only; skipped on SQLite), and `'engine' => 'InnoDB'` in `config/database.php`. Check with `SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA='bis_admin'`.
4. **B03 + B04:** stop reading options when the routes load.
   - Always register `GET`/`POST /register` and remove the `if (get_option('membership'))` block.
   - Add middleware `EnsureMembershipIsOpen` (alias `membership`) on both routes: when Membership is off, redirect to `front`. Remove the duplicate checks in `RegisterController`, so the trait alias can go too.
   - Replace the `static` cache in `get_option()` with a container singleton (`app('project.options')`), loaded on first use. `OptionsController::optionsEdit` calls `app()->forgetInstance('project.options')` after saving. A new app instance, as in each test, starts fresh.
   - Check: with an unreachable database port (`DB_PORT=1`), `php71 artisan inspire` and `package:discover` work. `php71 artisan route:list` shows the register routes. The WAMP site behaves exactly as before.

### Phase B – Test suite for the current, correct behaviour

1. Keep `tests/Unit/ExampleTest.php`; replace `tests/Feature/ExampleTest.php` with the tests below.
2. Factories: `UserFactory` gets `role` (default `SUBSCRIBER`) with `admin` and `editor` states; add `InfoFactory`.
3. Conventions: `test_*` method names, `RefreshDatabase`, and no annotations where possible, so the tests port to PHPUnit 11 during the upgrade with minimal changes.
4. Write the tests below. In this phase they cover only behaviour that is **already correct**, so they pass.

| Test | Covers |
|---|---|
| `Feature/Auth/LoginTest` | `/login` renders; a correct login → `/dashboard`; a wrong password fails; a guest opening `/dashboard` → `/login`; a logged-in user opening `/login` → `/dashboard`; logout |
| `Feature/Auth/RegisterTest` | Membership on: the form renders, a valid sign-up creates a **subscriber** and logs in; `terms` is required. Membership off: `/register` → `/` (works after A4) |
| `Feature/Auth/PasswordResetTest` | Forgot-password renders; a request sends `ResetPassword`; the reset screen renders; a valid token resets the password |
| `Feature/Admin/AccessTest` | Role × page matrix (admin, editor, subscriber, guest) for every admin route: 200 / 403 / redirect, matching the Gates in `AuthServiceProvider` |
| `Feature/Admin/InfosTest` | Editor: add (validation, duplicate title rejected), edit (same title allowed), delete; unknown id → 404; the list shows the author's name |
| `Feature/Admin/SettingsTest` | Change name and email (unique, ignoring yourself); change password (confirmation required); blank password keeps the old one |
| `Feature/Admin/OptionsTest` | Admin saves the 4 options; unchecking Membership stores `'0'`; validation |
| `Feature/OptionHelperTest` | `get_option()` returns the DB value, `''` for an unknown name, and works with no `options` table |
| `Feature/LandingTest` | `/` and `/terms-of-service` render with `site_title` in `<title>` |

5. `php71 vendor/bin/phpunit` → all green.

### Phase C – Functional and security fixes (High/Medium)

Each item: add a failing test → fix → green.

| ID | Test first | Fix |
|---|---|---|
| B05 | `InfosTest`: status `whatever` → validation error | `'status' => 'required|in:' . implode(',', array_keys(get_post_statuses()))` in both requests |
| B06 | `InfosTest`: a subscriber opening a private info → **404**, and not listed | Add `Info::scopeVisibleTo(User $user)` (editors see everything, others only `public`). `index` and `infoView` use it, with `firstOrFail()`. Remove the Blade filtering in `infosIndex` and `infoView` |
| B07 | `OptionsTest`: an option that isn't on the form keeps its value | Update only the 4 known names (`site_title`, `tagline`, `email_address`, `membership`) with `Options::updateOrCreate(['name' => …], ['value' => …])` |
| B08 | `PasswordResetTest`/`RegisterTest`: the 4th request within 10 minutes from the same IP → 429, and no mail or user | `->middleware('throttle:3,10')` on `password.email` and `POST /register` |
| B09 | – (UI) + **browser check** | `onclick="return confirm('Delete this info?')"` before submitting `#delete-info` |
| B10 | `AdminSeederTest`: `db:seed` creates one admin from `.env`, and is safe to run twice | `AdminUserSeeder` using `SEED_ADMIN_NAME`, `SEED_ADMIN_EMAIL` (default `admin@example.com`) and `SEED_ADMIN_PASSWORD` (default `password`) with `firstOrCreate`; call it from `DatabaseSeeder`. Document the keys in `.env.example`; put your real values only in your local `.env`. Update the README's "make yourself admin" step |
| B11 | `AccessTest`: the dashboard HTML has no unread badge | Remove the hard-coded `10` badge. Hide the Messages menu until Messages exists (backlog) |
| B12 | – (config) | `.env.example`: `MAIL_DRIVER=log` for development, with a comment on how to switch to SMTP |

`php71 vendor/bin/phpunit` → green after every row.

### Phase D – Low-priority fixes and cleanup

| ID | Fix |
|---|---|
| B13 | **Decided 2026-11-02: everything in English.** Translate the Spanish menu entries, page titles (`@section('title')`) and messages, e.g. "Todas Las Informaciones" → "All Infos", "Añadir Plan" → "Add Plan", "Bandeja De Entrada" → "Inbox", "Ajustes" → "Settings", "Opciones" → "Options". No `lang/` files are needed. Add an `AccessTest` assertion that the admin menu contains no Spanish labels |
| B14 | Correct the "not allowed" texts (alerts, plans, licenses), or remove them together with the dead `@cannot` branches (B18). **Done:** removed with the 20 unreachable `@cannot` blocks |
| B15 | `Info::author()` (`belongsTo(User::class, 'author')`) and `Info::with('author')->latest()->get()`. The view uses `$info->author->name ?? '-'`; stop passing `User::class`. **Done as `authorUser()`:** `author` is already the column name, so a relation called `author()` would never be used |
| B16 | `{!! nl2br(e($info->content)) !!}` |
| B17 | `min:8` for new passwords (register, settings, reset). **Reset** already required 8 in Laravel 5.8's `ResetsPasswords`; register and settings changed |
| B18 | Remove the dead code and unused imports listed in B18. Leave `resources/js`, `resources/sass` and the npm files in place until the upgrade (the upgrade plan decides) |
| B19 | `$this->input('password')` instead of `Input::get('password')` |
| B20 | No code change here: listed in the backlog. Only make sure the disabled form can't be submitted (it can't today) |

### Phase E – Verification

1. `php71 vendor/bin/phpunit` → all green.
2. `php71 artisan view:cache` → OK, then `php71 artisan view:clear`.
3. `php71 artisan route:list`: same routes as before, plus the throttle and `membership` middleware.
4. With MySQL stopped: `php71 artisan inspire` works (B03).
5. Browser smoke test on `http://bis-admin.test`:
   - **Landing:** `/`, `/terms-of-service`.
   - **Auth:** login; register with Membership on and off; forgot/reset password (mail in `storage/logs`); logout.
   - **Admin, as each role:** menu entries match the role, with no fake badge; Infos list/add/edit/delete, with a confirmation on delete; a subscriber gets 404 on a private info; Settings; Options (save, Membership toggle).

**Done 2026-11-04** on 3ef747f: 60 tests green; `view:cache` OK; 43 routes, with `membership` and `throttle.route` on register and `password.email`; `artisan inspire` and `route:list` work with the database unreachable (`DB_PORT=1`). The browser smoke test found R01 (section 5), fixed.

### Phase F – Wrap up

1. Update `CLAUDE.md`:
   - tests run on SQLite in-memory (safe)
   - tables are InnoDB
   - `get_option()` uses the lazy `project.options` singleton
   - registration is controlled by the `membership` middleware
   - the admin seeder and its `.env` keys
   - remove the fixed quirks (MyISAM warning, route-time membership)
2. Update the upgrade plan: Step 0.2 (InnoDB) and Step 3 (tests) are **done by this plan**.
3. Final review of all commits since the start of the plan, with your approval. Then the upgrade plan can start.

**Done 2026-11-04:** `CLAUDE.md` already covered point 1 (updated phase by phase); the upgrade and finish plans now mark what this plan delivered; the backlog below is brought up to date with B11/B18.

## 5. Bugs discovered while implementing

Add any new findings here (ID, location, problem, decision). Fix them only if they are small and you approve; otherwise move them to the backlog.

| ID | Location | Problem | Decision |
|---|---|---|---|
| R01 | `resources/views/auth/passwords/reset.blade.php` | The reset form showed `1` in the email field: `{{ $email or old('email') }}` is the Blade `or` syntax removed in Laravel 5.7, so it ran as PHP's boolean `or` | Fixed in 3ef747f with `??`, plus a test. No other view used `or` |

## 6. Backlog (unfinished features – not part of this plan)

These items are planned in [finish-project-plan.md](finish-project-plan.md), which runs after the Laravel 13 upgrade.

| Feature | Notes |
|---|---|
| Users management | List, add, edit, delete and role change for admins; the Gates `addUsers`, `editUsers`, `deleteUsers` exist |
| Alerts, Plans, Licenses | Same table shape as Infos; build on a shared base. Their unused request classes were removed (B18); new ones are needed |
| Messages | No table, model or routes yet; the menu is hidden by B11 |
| Admins and posts | Admins get 403 on Infos, Alerts, Plans and Licenses (`viewPosts` excludes them). Decide whether that's intended |
| Dashboard | Only a welcome text; counters per role |
| Landing page | Real texts instead of lorem ipsum, a working contact form sent to the `email_address` option (with throttle and CSRF), real or removed address and phone |
| Email verification | The unwired `VerificationController` was removed (B18); decide if new users must verify |
| Error pages | 403/404/500 in the site layout |
| Front-end assets | Laravel Mix defaults are unused; decide during the upgrade (keep the static templates, or move to Vite) |

## 7. Relation to the other plans

- **Upgrade plan:** this plan delivers its Step 0.2 (InnoDB) and Step 3 (SQLite tests and the test suite). It also removes the `Input` facade (B19) and route-time options (B03/B04), both of which would otherwise block or complicate the upgrade. The tests written here (`test_*` names, few annotations) become the safety net for every upgrade step.
- **Completion plan:** its Milestone 1 items **first admin**, **access filtering out of the views**, **author relationship** and **status validation** are done here (B10, B06, B15, B05). Its "Decisions needed" list gains B13 (language).
