# BIS Admin

**Building Information System**: an architecture web application for documentation, plans and licenses.

BIS Admin has a public landing page, user accounts with roles, and an admin dashboard where editors publish information for subscribers. It is built with Laravel 5.8.

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

People who register themselves become **subscribers**. There is no admin screen for changing roles yet, so the first admin has to be set directly in the database (see step 6 below).

## Requirements

- PHP **7.1.3 – 7.x**. PHP 8 is not supported by Laravel 5.8.
- MySQL
- Composer. This repo includes `composer.phar` (Composer 2.2 LTS), which still runs on PHP 7.1. Current Composer releases need PHP 7.2.5 or newer.

The front-end needs no build step: the admin and landing templates are prebuilt files in `public/admin` and `public/landing`.

## Installation

1. Clone the repository and install the PHP dependencies:
   ```bash
   php composer.phar install
   ```
2. Create your environment file and app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Create an empty MySQL database (named `bis_admin` by default) and set the `DB_*` values in `.env`.
4. Create the tables. This also adds the default site options, with Membership turned on:
   ```bash
   php artisan migrate
   ```
5. Point a virtual host at the `public/` folder (`.env.example` uses `http://bis-admin.test`), or run `php artisan serve`. Then open `/register` and create your account.
6. Make that account an admin by setting its role to `0`:
   ```sql
   UPDATE users SET role = 0 WHERE email = 'you@example.com';
   ```

If more than one PHP version is installed, run `php` and `composer.phar` with PHP 7.1–7.x, for example `php7.1 composer.phar install`.

## Configuration

Admins manage these settings from **Dashboard → Options**. They are stored in the `options` table:

| Option | Purpose |
|---|---|
| Site Title, Tagline | Used in page titles, headers and the footer |
| Email Address | Contact address (saved, but not shown on the site yet) |
| Membership | When on, anyone can register at `/register`. When off, `/register` redirects to the home page. |

The register routes are set up when the routes file loads. If you cache routes with `php artisan route:cache`, run it again after changing Membership.

## Tests

```bash
php vendor/bin/phpunit
php vendor/bin/phpunit --filter testBasicTest     # a single test
```

`phpunit.xml` doesn't override the database connection, so any test that uses the database runs against the one configured in `.env`. Point it at a separate test database before writing database tests.

## Project structure

| Path | Contents |
|---|---|
| `routes/web.php` | Landing routes; loads `routes/auth.php` and `routes/admin.php` |
| `routes/admin.php` | Dashboard routes under `/dashboard`, protected by `auth` and per-page `can:` permissions |
| `app/Providers/AuthServiceProvider.php` | Defines every permission (Gate ability) for each role |
| `app/Http/Controllers/Admin` | Dashboard controllers |
| `app/Http/Models` | Eloquent models (`Info`, `Alert`, `Plan`, `License`, `Options`); `User` is in `app/User.php` |
| `app/Helpers.php` | Global helpers: `get_option()`, `get_post_statuses()`, `get_gravatar()` |
| `resources/views` | Blade views, with `admin`, `auth` and `landing` layouts |

## Built with

- [Laravel 5.8](https://laravel.com/docs/5.8)
- Admin dashboard and landing page HTML templates (Bootstrap and jQuery), stored in `public/admin` and `public/landing`
- [Gravatar](https://gravatar.com) for user avatars
