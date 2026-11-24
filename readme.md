# BIS Admin

**Building Information System**: an architecture web application for documentation, plans and licenses.

BIS Admin has a public landing page with a contact form, user accounts with roles, and an admin dashboard where editors publish information for subscribers. It is built with Laravel 13 on PHP 8.4.

> **Status:** work in progress. The landing page, its contact form, accounts and **Infos** work end to end. The **Alerts**, **Plans**, **Licenses**, **Users** and **Messages** sections only show their pages so far.

## Features

| Area | What works |
|---|---|
| Landing | Front page, Terms of Service page, and a contact form that saves each message and emails it to the contact address |
| Accounts | Login, logout and password reset. There is no public sign-up: accounts are created by staff |
| Settings | Every user can change their own name, email and password |
| Infos | Editors create, edit and delete infos marked *public* or *private*; subscribers read public infos |
| Alerts, Plans, Licenses, Users, Messages | Pages only; no saving yet |

## Roles

Every user has one role, stored as a number in the `users.role` column:

| Role | Value | Can access |
|---|---|---|
| Admin | `0` | Users, Messages, own Settings |
| Editor | `1` | Create, edit and delete Infos, Alerts, Plans and Licenses; Messages; own Settings |
| Subscriber | `2` | View Infos (public ones only), Alerts, Plans and Licenses; Messages; own Settings |

There is no public sign-up; the first staff account is created with `php artisan app:create-admin` (see step 4 below).

## Requirements

- PHP **8.3 or newer** (developed on 8.4), with the `pdo_mysql` extension, and `pdo_sqlite` for the tests
- MySQL
- [Composer](https://getcomposer.org) 2

The project needs no front-end build step: the admin and landing themes are ready-made files in `public/admin` and `public/landing`.

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
4. Create the tables, then the first staff (admin) account. The command asks for the name, email and password:
   ```bash
   php artisan migrate
   php artisan app:create-admin
   ```
5. Optional, for a demo: fill the database with 6 architects and 20 properties around Barcelona, with plans, licenses and small sample PDFs. All the demo architects use the password `architect-demo`; the seeder prints their emails.
   ```bash
   php artisan db:seed
   ```
6. Point a virtual host at the `public/` folder (`.env.example` uses `http://bis-admin.test`), or run `php artisan serve`. Then log in with the admin account from step 4.

## Configuration

Site settings are read from `config/project.php`, which takes them from `.env`:

| `.env` key | Purpose |
|---|---|
| `SITE_TITLE`, `SITE_TAGLINE` | Used in page titles, headers and the footer |
| `CONTACT_EMAIL` | Shown on the landing page; receives the contact form messages |
| `CONTACT_ADDRESS`, `CONTACT_PHONE` | Optional; shown on the landing page when set (use `
` in the address for a line break) |
| `MAIL_*` | The SMTP server the site sends email through (contact form, password reset). `MAIL_MAILER=log` writes the emails to `storage/logs` instead |

After changing `.env` on a server that caches its config, run `php artisan config:cache` again.

The contact form accepts 3 messages per 10 minutes from one IP, ignores bots that fill in a hidden field, and saves every message in the `contacts` table even when the email can't be sent. It is sent with ajax (`public/js/ajax-form.js`), so errors and the success message appear without a page reload; without JavaScript it posts normally.

## Tests

```bash
php artisan test
php artisan test --filter test_editor_adds_an_info     # a single test
```

The tests run on an in-memory SQLite database (set in `phpunit.xml`), so they never touch the database in `.env`. The feature tests cover login, password reset, each role's access to every admin page, Infos, Settings, the landing pages and the contact form.

## Project structure

| Path | Contents |
|---|---|
| `routes/web.php` | Landing routes; loads `routes/auth.php` and `routes/admin.php` |
| `routes/admin.php` | Dashboard routes under `/dashboard`, protected by `auth` and per-page `can:` permissions |
| `app/Providers/AppServiceProvider.php` | Defines every permission (Gate ability) for each role, and the contact form and password-reset rate limits |
| `config/project.php` | Site title, tagline, contact email and the first admin's login |
| `app/Http/Controllers/Admin` | Dashboard controllers |
| `app/Models` | Eloquent models (`User`, `Property`, `Plan`, `License`, `PropertyStatusChange`, `MailAccount`, `Contact`) |
| `app/Helpers.php` | Global helpers: `get_post_statuses()`, `get_gravatar()` |
| `resources/views` | Blade views, with `admin`, `auth` and `landing` layouts |

## Built with

- [Laravel 13](https://laravel.com/docs/13.x), with [laravel/ui](https://github.com/laravel/ui) for the login and password reset controllers
- The Sufee 3.2.1 admin theme (Bootstrap 5.3, Font Awesome 7, light and dark mode) in `public/admin`, and the Bell landing theme (Bootstrap 4 and jQuery) in `public/landing`; see Credits
- A small script, `public/js/ajax-form.js`, that sends every form without a page reload and shows the validation errors under the fields
- [Gravatar](https://gravatar.com) for user avatars

## Credits

- **Admin theme:** [Sufee](https://github.com/puikinsh/sufee-admin-dashboard) by [Colorlib](https://colorlib.com), the original author. MIT License; see [`public/admin/LICENSE`](public/admin/LICENSE). Version 3.2.1, built once from its source with `npm install && npm run build`; `public/admin` holds the built `assets/` (Bootstrap, Font Awesome, Chart.js and the other libraries it bundles, each under its own license), `images/` and `themify-icons/`. To update it, build the new release and copy those folders again, then change the two hashed file names in `resources/views/includes/admin/head.blade.php`.
- **Landing theme:** [Bell](https://bootstrapmade.com/bell-free-bootstrap-4-template/) by [BootstrapMade](https://bootstrapmade.com), used under the [BootstrapMade free license](https://bootstrapmade.com/license/). Its footer credit is kept as required.
- **Photos:** the landing page background images are from [Unsplash](https://unsplash.com) ([Unsplash License](https://unsplash.com/license)).

The project's own code is not open source (`"license": "proprietary"` in `composer.json`). The themes and libraries above remain under their own licenses.
