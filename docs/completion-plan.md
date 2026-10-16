# Plan: Complete the missing parts of BIS Admin

## Where the project stands

| Area | State |
|---|---|
| Landing page, Terms of Service | Pages exist. Most text is still lorem ipsum, and the contact form is disabled. |
| Login, logout, password reset, registration | Working. Registration depends on the *Membership* option. |
| Settings (own profile) | Working |
| Options | Working, but the *Email Address* option isn't used anywhere |
| Infos | Working end to end: list, add, view, edit, delete, public/private |
| Dashboard | Only shows a "Welcome … You are logged in!" message |
| Users | Pages only, showing "TODO: coming soon". No save, update or delete routes. |
| Alerts | Pages only. The `sendAlerts` and `receiveAlerts` permissions exist, and the header already has an alerts menu for subscribers. |
| Plans | Pages only |
| Licenses | Pages only |
| Messages (inbox, sent, send, view) | Pages only. No table, model or routes. |

The permissions (Gates) for all of these already exist in `AuthServiceProvider`. The missing pieces are the controllers' save logic, routes, migrations and the views' content.

## Do the Laravel 13 upgrade first

Build the new features **after** [the Laravel 13 upgrade](upgrade-laravel-13-plan.md). Otherwise every new controller, route and test gets written in Laravel 5.8 style and then rewritten. The upgrade also converts the database to InnoDB and gives the tests their own sqlite database. Both are needed before writing tests for the new features.

## Decisions needed before starting

These questions change what gets built, so they need an answer first:

1. **Plans and Licenses: text only, or files?** The landing page talks about "Digital Plans" and "documentation, plans & licenses". If a plan is really a drawing (PDF or DWG) and a license a scanned document, they need file uploads, storage and downloads. That is more than a copy of Infos.
2. **What is an Alert?** The permissions suggest editors *send* alerts and subscribers *receive* them, with a menu in the header. Should an alert be a post like an info, or a notification with an unread count, sent to everyone or to chosen users?
3. **Messages: between whom?** Any user to any user, or only between subscribers and editors/admins? With attachments?
4. **Should admins see Infos, Alerts, Plans and Licenses?** Today `viewPosts` excludes admins.
5. **Language:** the Messages inbox title is Spanish ("Bandeja De Entrada") and the rest is English. Pick one language, or plan for translations (`lang/` files).
6. **Email verification:** `VerificationController` exists but isn't used. Should new users have to verify their email?

## Work plan

Each milestone is one or more small commits, in the repo's `type: description` style, with feature tests.

### Milestone 1 — Foundations

- **Create the first admin.** Add an Artisan command, e.g. `php artisan bis:make-admin {email}`, that gives an existing user role `0`. Today this can only be done in the database.
- **Move access filtering out of the views.** Non-editors are blocked from private infos only by `@cannot` blocks in Blade. Add a query scope such as `Info::visibleTo($user)` and use it in the controllers, so a subscriber can't open a private info by guessing its URL.
- **Real author relationship.** Add `author()` (`belongsTo(User::class, 'author')`) to the post models and load it with `with('author')`. This replaces `$user::find(...)` inside `infosIndex.blade.php`, which runs one query per row.
- **Pagination** on every list page (`->paginate(20)`).
- **One shared base for post-like sections.** Infos, Alerts, Plans and Licenses use identical tables (`title`, `content`, `author`, `status`). Pull the Infos controller logic and its two form requests into a shared base controller or trait plus one form request, so Alerts, Plans and Licenses don't become three copies of the same code.
- **Fix the form requests** `AlertRequest`, `PlanRequest` and `LicenseRequest`. Their `authorize()` returns `false`, which blocks every request with a 403. Return `true` (permissions are already checked by the `can:` middleware) and add rules.

### Milestone 2 — Users management (admin)

- **List:** name, email, role and registration date.
- **Add:** name, email, role and initial password, or send a password reset link.
- **View and edit:** change name, email and role.
- **Delete.**
- **New routes:** `POST /dashboard/users/add-user`, `PATCH /dashboard/users/edit-user/{id}`, `DELETE /dashboard/users/edit-user/{id}`. Use the existing `addUsers`, `editUsers` and `deleteUsers` permissions; `deleteUsers` isn't used by any route yet.
- **Safety rules:** an admin can't delete themselves or remove the last admin.
- **Tests:** only admins have access, and the role change and the last-admin rule work.

### Milestone 3 — Alerts, Plans, Licenses

- Build on the shared base from Milestone 1. Each section gets save, update and delete routes, like Infos.
- Change the views from "TODO: coming soon" to real list, add, view and edit pages, based on the Infos views.
- **Plans and Licenses:** add uploads if decision 1 says files.
  - Store them with Laravel's `Storage` on a private disk.
  - Serve downloads through a controller that checks permission and visibility.
  - Validate file types and size.
- **Alerts:** build according to decision 2. If they are notifications, use Laravel notifications with the `database` channel. Show the unread count in the existing header menu (`@can('receiveAlerts')` in `includes/admin/header.blade.php`) and add a "mark as read" action.

### Milestone 4 — Messages

- **New `messages` table:** `id`, `sender_id`, `recipient_id`, `subject`, `body`, `read_at`, timestamps, plus foreign keys to `users`.
- **New `Message` model and routes:** inbox, sent, send (form plus `POST`) and view (marks the message as read).
- Unread count in the header.
- **Access rules:** users only see messages they sent or received. Apply decision 3 for who can message whom.
- **Tests:** a user can't read someone else's message, and the unread count updates.

### Milestone 5 — Dashboard, options and landing page

- **Dashboard:** counts per role, for example number of infos, alerts and plans, unread messages, and new users for admins.
- **Use the *Email Address* option:**
  - Enable the landing page contact form, validate it, and send it to that address with a Mailable.
  - Add rate limiting (`throttle` middleware).
  - Add a CSRF token; the form doesn't have one today.
- **Replace the landing page's lorem ipsum** (features, about, steps) with real text, and check the Terms of Service text.
- **Membership:** if the upgrade plan's `EnsureMembershipIsOpen` middleware isn't done yet, do it here.

### Milestone 6 — Finish

- Error pages (`403`, `404`, `500`) that use the site layout.
- Consistent success and error flash messages on every form.
- Fix the `@param boole` typo in `app/Helpers.php` (should be `bool`).
- Update `README.md` (Features and Status) and `CLAUDE.md` as each section lands.

## Definition of done

- No page shows "TODO: coming soon".
- Every section has list, add, view, edit and delete working, with the role permissions enforced by routes and controllers, not only by views.
- Feature tests cover access per role and the main actions of each section, and `php artisan test` passes.
- The README's Features table has no "pages only" rows.
