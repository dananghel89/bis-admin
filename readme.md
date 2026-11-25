# BIS Admin

**Building Information System**: a web application where architects check the information, plans and licenses of their properties.

Staff add the properties, upload their plans and licenses as PDF files, place them on a map and choose which architects see them. Architects open the properties made visible to them, download the PDFs and report the progress of their work. Every user also has a mailbox (**Alerts**) for their own email account. A public landing page explains the service and has a contact form. It is built with Laravel 13 on PHP 8.4.

## Features

| Area | What it does |
|---|---|
| Landing | Front page, Terms of Service, and a contact form that saves each message and emails it to the contact address |
| Accounts | Login, logout and password reset. There is no public sign-up: staff create every account |
| Dashboard | Staff: counts of properties, architects, unread contact messages and licenses to check, plus the latest work status changes. Architects: their properties by work status and their licenses to check |
| Infos (properties) | List with search and filters, a page per property with its map, plans, licenses and work status history. Staff add, edit and delete properties, set the location on a map, publish them and assign architects |
| Work status | Pending, in progress or done, changed by staff or by an architect who sees the property, with an optional note; every change is kept |
| Plans | A map of the properties and the list of plan PDFs; staff upload, replace and delete them |
| Licenses | License PDFs with number, issue and expiry dates; expired ones and those expiring within 30 days are flagged |
| Alerts | Each user's own mailbox over IMAP and SMTP: inbox, sent, read, write, reply and delete (to the account's Trash folder; no attachments yet). Nothing is stored in the app: every page reads the mail server, so mail sent or deleted in another email program shows here too. HTML email is shown in a sandbox with remote images blocked |
| Contacts | Staff read and delete the contact form messages; the menu shows how many are unread |
| Users | Staff add, edit and delete accounts, set passwords and roles, and can fill in a user's mail settings |
| Settings | Every user changes their name, email and password, and sets up their mailbox |

Every form is sent with ajax: errors show under the fields without a page reload, and the forms still work without JavaScript.

## Roles

Every user has one role, stored as a number in the `users.role` column:

| Role | Value | Can do |
|---|---|---|
| Staff | `0` (`User::ADMIN`) | Everything: properties, plans, licenses, users, contact messages, their own mailbox |
| Architect | `1` (`User::EDITOR`) | See the published properties assigned to them, and the published ones with no architect assigned; download their PDFs; change their work status; use their own mailbox |

Drafts are visible to staff only. A property, plan or license an architect can't see is a 404 for them, also when they type its URL.

## Requirements

- PHP **8.3 or newer** (developed on 8.4), with the `pdo_mysql`, `openssl` and `fileinfo` extensions, and `pdo_sqlite` for the tests
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
| `CONTACT_ADDRESS`, `CONTACT_PHONE` | Optional; shown on the landing page when set (write `\n` in the address for a line break) |
| `MAP_CENTER_LAT`, `MAP_CENTER_LNG`, `MAP_ZOOM` | Where the maps open when there is nothing to show (default Barcelona) |
| `MAIL_*` | The SMTP server the site itself sends email through (contact form, password reset). `MAIL_MAILER=log` writes those emails to `storage/logs` instead |

After changing `.env` on a server that caches its config, run `php artisan config:cache` again.

- **Contact form:** 3 messages per 10 minutes from one IP; bots that fill in a hidden field are ignored; every message is saved in the `contacts` table even when the email can't be sent.
- **PDF uploads:** plans and licenses are PDF only, up to 20 MB, or less when PHP's `upload_max_filesize` / `post_max_size` are lower (WAMP's default is 2 MB); the form shows the real limit. They are stored in `storage/app/private` and only downloaded through the app.
- **Alerts:** each user's IMAP/SMTP settings are stored per user, with the password encrypted with `APP_KEY` (changing the key means re-entering them). A copy of each sent email is saved in the account's Sent folder, except on Gmail and Outlook, which do that themselves.
- **Maps:** [Leaflet](https://leafletjs.com) with [OpenStreetMap](https://www.openstreetmap.org) tiles; no API key needed.

## Tests

```bash
php artisan test
php artisan test --filter test_staff_add_a_property_with_its_location_and_architects     # a single test
```

The tests run on an in-memory SQLite database (set in `phpunit.xml`), so they never touch the database in `.env`, and they use fake IMAP/SMTP servers, so they never connect to a real mailbox. The feature tests cover each role's access to every page, the visibility of properties, plans and licenses per architect, every form (also as JSON, the way the ajax script sends them), uploads and downloads, the mailbox, the seeders, the admin command and the landing pages.

## Project structure

| Path | Contents |
|---|---|
| `routes/web.php` | Landing routes and the contact form; loads `routes/auth.php` and `routes/admin.php` |
| `routes/admin.php` | Dashboard routes under `/dashboard`, protected by `auth` and per-page `can:` permissions |
| `app/Providers/AppServiceProvider.php` | Every permission (Gate ability) for each role, Bootstrap 5 pagination, and the contact form and password-reset rate limits |
| `config/project.php` | Site title, tagline, contact details and the map center |
| `app/Http/Controllers/Admin` | Dashboard controllers (properties, plans, licenses, alerts, contacts, users, settings) |
| `app/Models` | Eloquent models (`User`, `Property`, `Plan`, `License`, `PropertyStatusChange`, `MailAccount`, `Contact`) |
| `app/Services/Mailbox` | The Alerts mailbox: IMAP and SMTP for one user's account |
| `app/Console/Commands/CreateAdmin.php` | `php artisan app:create-admin` |
| `database/seeders` | The demo data |
| `resources/views` | Blade views, with `admin`, `auth`, `landing` and `error` layouts, and the form components in `components/form` |
| `public/js` | `ajax-form.js` (forms) and `property-map.js` (maps) |

## Built with

- [Laravel 13](https://laravel.com/docs/13.x), with [laravel/ui](https://github.com/laravel/ui) for the login and password reset controllers
- [ImapEngine](https://github.com/DirectoryTree/ImapEngine) (`directorytree/imapengine-laravel`) for reading mail over IMAP, and Symfony Mailer (part of Laravel) for sending it
- The Sufee 3.2.1 admin theme (Bootstrap 5.3, Font Awesome 7, light and dark mode) in `public/admin`, and the Bell landing theme (Bootstrap 4 and jQuery) in `public/landing`; see Credits
- [Leaflet](https://leafletjs.com) 1.9.4 in `public/vendor/leaflet`, with OpenStreetMap tiles
- [Gravatar](https://gravatar.com) for user avatars

## Credits

- **Admin theme:** [Sufee](https://github.com/puikinsh/sufee-admin-dashboard) by [Colorlib](https://colorlib.com), the original author. MIT License; see [`public/admin/LICENSE`](public/admin/LICENSE). Version 3.2.1, built once from its source with `npm install && npm run build`; `public/admin` holds the built `assets/` (Bootstrap, Font Awesome, Chart.js and the other libraries it bundles, each under its own license), `images/` and `themify-icons/`. To update it, build the new release and copy those folders again, then change the two hashed file names in `resources/views/includes/admin/head.blade.php` and `resources/views/layouts/error.blade.php`.
- **Landing theme:** [Bell](https://bootstrapmade.com/bell-free-bootstrap-4-template/) by [BootstrapMade](https://bootstrapmade.com), used under the [BootstrapMade free license](https://bootstrapmade.com/license/). Its footer credit is kept as required.
- **Maps:** [Leaflet](https://leafletjs.com) by Volodymyr Agafonkin and contributors, BSD 2-Clause License; see [`public/vendor/leaflet/LICENSE`](public/vendor/leaflet/LICENSE). Map data © [OpenStreetMap](https://www.openstreetmap.org/copyright) contributors.
- **Photos:** the landing page background images are from [Unsplash](https://unsplash.com) ([Unsplash License](https://unsplash.com/license)).

The project's own code is not open source (`"license": "proprietary"` in `composer.json`). The themes and libraries above remain under their own licenses.
