# BIS Admin

**Building Information System**: an architecture web application for documentation, plans and licenses.

BIS Admin has a public landing page, user accounts with roles, and an admin dashboard where editors publish information for subscribers. It is built with Laravel 13 on PHP 8.4.

> **Status:** work in progress. Accounts, site options and **Infos** work end to end. The **Alerts**, **Plans**, **Licenses**, **Users** and **Messages** sections only show their pages so far.

## Features

| Area | What works |
|---|---|
| Landing | Front page and Terms of Service page |
| Accounts | Login, logout, password reset, and self-registration when *Membership* is on |
| Settings | Every user can change their own name, email and password |
| Options | Admins edit the site title, tagline, contact email, and whether anyone can register |
| Infos | Editors create, edit and delete infos marked *public* or *private*; subscribers read public infos |
| Alerts, Plans, Licenses, Users, Messages | Pages only; no saving yet |

## Roles

Every user has one role, stored as a number in the `users.role` column:

| Role | Value | Can access |
|---|---|---|
| Admin | `0` | Options, Users, Messages, own Settings |
| Editor | `1` | Create, edit and delete Infos, Alerts, Plans and Licenses; Messages; own Settings |
| Subscriber | `2` | View Infos (public ones only), Alerts, Plans and Licenses; Messages; own Settings |

People who register themselves become **subscribers**. There is no admin screen for changing roles yet; the first admin is created by the database seeder (see step 4 below).

## Requirements

- PHP **8.3 or newer** (developed on 8.4), with the `pdo_mysql` extension, and `pdo_sqlite` for the tests
- MySQL
- [Composer](https://getcomposer.org) 2

The front-end needs no build step: the admin and landing templates are prebuilt files in `public/admin` and `public/landing`.

## Installation

1. Clone the repository and install the PHP dependencies:
   ```bash
   composer install
   ```
2. Create your environment file and app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Create an empty MySQL database (named `bis_admin` by default) and set the `DB_*` values in `.env`.
4. Choose the first admin's login in `.env`: `SEED_ADMIN_NAME`, `SEED_ADMIN_EMAIL` and `SEED_ADMIN_PASSWORD`. The defaults are `admin@example.com` / `password`; never keep them on a public server.
5. Create the tables and the first admin. The migrations also add the default site options, with Membership turned on:
   ```bash
   php artisan migrate --seed
   ```
   Seeding again later is safe: an existing user with that email is left unchanged.
6. Point a virtual host at the `public/` folder (`.env.example` uses `http://bis-admin.test`), or run `php artisan serve`. Then log in with the admin account from step 4.

## Configuration

Admins manage these settings from **Dashboard → Options**. They are stored in the `options` table:

| Option | Purpose |
|---|---|
| Site Title, Tagline | Used in page titles, headers and the footer |
| Email Address | Contact address (saved, but not shown on the site yet) |
| Membership | When on, anyone can register at `/register`. When off, `/register` redirects to the home page. |

Membership is checked on every request, so a change takes effect at once, also with cached routes (`php artisan route:cache`).

## Tests

```bash
php artisan test
php artisan test --filter test_editor_adds_an_info     # a single test
```

The tests run on an in-memory SQLite database (set in `phpunit.xml`), so they never touch the database in `.env`. The feature tests cover login, registration, password reset, each role's access to every admin page, Infos, Settings, Options and the landing pages.

## Project structure

| Path | Contents |
|---|---|
| `routes/web.php` | Landing routes; loads `routes/auth.php` and `routes/admin.php` |
| `routes/admin.php` | Dashboard routes under `/dashboard`, protected by `auth` and per-page `can:` permissions |
| `app/Providers/AppServiceProvider.php` | Defines every permission (Gate ability) for each role, and the sign-up and password-reset rate limits |
| `app/Http/Controllers/Admin` | Dashboard controllers |
| `app/Models` | Eloquent models (`User`, `Info`, `Alert`, `Plan`, `License`, `Options`) |
| `app/Helpers.php` | Global helpers: `get_option()`, `get_post_statuses()`, `get_gravatar()` |
| `resources/views` | Blade views, with `admin`, `auth` and `landing` layouts |

## Built with

- [Laravel 13](https://laravel.com/docs/13.x), with [laravel/ui](https://github.com/laravel/ui) for the login, registration and password reset controllers
- The Sufee and Bell themes (Bootstrap 4 and jQuery), stored in `public/admin` and `public/landing`; see Credits
- [Gravatar](https://gravatar.com) for user avatars

## Credits

- **Admin theme:** [Sufee](https://github.com/puikinsh/sufee-admin-dashboard) by [Colorlib](https://colorlib.com), the original author. MIT License; see [`public/admin/LICENSE`](public/admin/LICENSE). Its bundled libraries keep their own licenses in `public/admin/vendors/`.
- **Landing theme:** [Bell](https://bootstrapmade.com/bell-free-bootstrap-4-template/) by [BootstrapMade](https://bootstrapmade.com), used under the [BootstrapMade free license](https://bootstrapmade.com/license/). Its footer credit is kept as required.
- **Photos:** the landing page background images are from [Unsplash](https://unsplash.com) ([Unsplash License](https://unsplash.com/license)).

The project's own code is not open source (`"license": "proprietary"` in `composer.json`). The themes and libraries above remain under their own licenses.
