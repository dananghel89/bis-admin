# Plan: move the repository from Bitbucket to GitHub

Status: **Done** – 2026-10-16: history rewritten and checked (4.3–4.4), pushed to GitHub (4.5), Bitbucket remote removed (4.7). 2026-10-17: identity changed to the noreply address and the 4 oldest messages renamed (4.3, second and third passes). 2026-10-22: repo public (4.8). By 2026-11-02: Bitbucket repo deleted (4.9). 2026-11-02: original-history bundle saved outside the repo (4.9.2), and old objects purged locally (4.7.3). 2026-11-05: repo moved to `~/Develop/Projects`; the bundle is now `~/Downloads/backups/bis-admin/bis-admin-pre-github-20261016.bundle`, kept until the Laravel 13 upgrade is done.
Created: 2026-11-02 (written afterwards, as a record of the migration done 2026-10-16 → 2026-10-22)
Model: this was the **first** migration. agency-cms (2026-10-31 → 2026-11-02) followed the same route in a single rewrite pass: rewrite the history to the GitHub noreply address with one-line messages, push to GitHub, review it as a visitor, make it public, then delete Bitbucket.

## 1. Order and decisions

Plan order in `docs/`:

1. [project-review-fix-plan.md](project-review-fix-plan.md)
2. [theme-licensing-plan.md](theme-licensing-plan.md) (done, 2026-11-02)
3. **This plan**, done ahead of the others because the repo moved before the review was written
4. [laravel-13-upgrade-plan.md](laravel-13-upgrade-plan.md)
5. [finish-project-plan.md](finish-project-plan.md)

| Topic | Decision |
|---|---|
| When | 2026-10-16, before any of the other plans existed |
| From | `https://dananghel89@bitbucket.org/dan-anghel/bis-admin.git` (private, branch `master`) |
| To | `https://github.com/dananghel89/bis-admin.git` (HTTPS) |
| Visibility | **Public**, only after you reviewed it as a visitor. It was created **private** first |
| Commit identity | Every commit (author **and** committer): `Dan Anghel <7040327+dananghel89@users.noreply.github.com>`. Decided 2026-10-17 as your per-host rule: GitHub repos use the noreply address, Bitbucket and other repos use `dev@dananghel.com` |
| Commit messages | One line, `type: description`. The 4 oldest commits were renamed (section 3); the others were already in that style |
| History scrub | In every past commit: `dan.anghel@yandex.com` (options migration seed) and `info@bis.architect` (landing page) → `dev@dananghel.com`, which you chose as the public contact address |
| History purge | None needed; no files were removed from the history |
| Bitbucket | **Deleted** after GitHub was verified (you did this in the browser) |
| Tools | Standard git only (`git filter-branch`, `git bundle`). `gh` and `git filter-repo` aren't installed (there's no Python) and weren't needed. GitHub steps were done in the browser |

### What the history contained (checked 2026-10-16)

- **Commits:** 12 on Bitbucket, plus 3 docs commits added before the move (15 in total), with 4 different author emails:
  - `dan.anghel89@gmail.com` (9 commits)
  - `info@cinthia.space` (2)
  - `dan.anghel@yandex.com` (1)
- **No secrets.** `.env` was never committed, and there were no API keys, tokens or app keys in any version. The only password, `DB_PASSWORD=secret`, was Laravel's default placeholder in `.env.example`.
- **Personal data in file contents:**
  - `dan.anghel@yandex.com` in `database/migrations/2018_10_04_204030_create_options_table.php`
  - `info@bis.architect` in `resources/views/landing/front.blade.php`
  - The landing page also shows a Barcelona address and phone number. You checked them before going public; they're still there (see the review plan, B20).
- **Size:** about 5.4 MB as a bundle.

## 2. Prerequisites

None were needed: the project's own commits were already one-line `type: description`, and no `.env` values were in the code.

Two things were set up along the way and now apply to all repos:
- `~/.gitconfig` includes `~/.gitconfig-github` (the noreply address) for every repo with a GitHub remote, via `includeIf "hasconfig:remote.*.url:https://github.com/**"` and the same rule for `git@github.com:*/**`. The global email is `dev@dananghel.com`.
- `credential.https://github.com.username = dananghel89`, so Git Credential Manager no longer asks which GitHub account to use. The old "Personal Access Token" login was removed.

## 3. New commit messages

| Original | New |
|---|---|
| `first commit` | `feat: add BIS admin app with landing page, auth and dashboard` |
| `update gitignore` | `chore: ignore .htaccess and php.ini` |
| `update readme` | `docs: replace Laravel readme with BIS project description` |
| `Update to Laravel 5.8` | `chore: upgrade Laravel from 5.4 to 5.8` |
| The 11 later commits | unchanged (already in style) |

## 4. Steps (as done)

### 4.1 Pre-flight (local)

1. Working tree clean; one branch, `master`, in sync with Bitbucket.
2. Secret scan over every version of every file (`git grep` over `git rev-list --all`): no secrets found (section 1).
3. **Backup** before every rewrite. The three bundles are in the Claude session's temporary scratchpad:
   - `bis-admin-before-email-rewrite.bundle` (the original history, tip `982c9d2` / `eae4797`)
   - `bis-admin-before-noreply-rewrite.bundle` (tip `5102c21`)
   - `bis-admin-before-message-rewrite.bundle` (tip `226158d`)

### 4.2 ⏸ You: create the empty GitHub repo (browser)

1. Created `dananghel89/bis-admin`, **Private**, with no README, `.gitignore` or license.
2. First push on 2026-10-16: `git remote add github …`, then `git push github master`. It was a normal push of the **original** history; every commit id matched Bitbucket.
3. Settings → Features: **Issues, Wiki, Projects, Discussions, Pull requests** turned off (by 2026-10-22).
4. Account settings → Emails:
   - "Keep my email addresses private" stays **on**.
   - "Block command line pushes that expose my email" was turned **off** on 2026-10-17, while the commits still used `dev@dananghel.com`. With the noreply address it could be on again.
   - `dev@dananghel.com` was added and verified.

### 4.3 Rewrite the history (local) — three passes

agency-cms did all of this in **one** pass. Here it took three, because the decisions came one at a time.

1. **2026-10-16, emails → `dev@dananghel.com`.**
   - `--env-filter` set the author and committer emails.
   - `--index-filter` replaced the two emails inside the two files. It works on the stored blobs, so line endings weren't touched.
2. **2026-10-17, identity → noreply.** `--env-filter` only, setting `7040327+dananghel89@users.noreply.github.com`. The file contents stayed as they were.
3. **2026-10-17, messages.** `--msg-filter` with a map from old commit id to new message, for the 4 commits in section 3.

Every pass kept the original author and commit dates, and used `git filter-branch -- master`.

### 4.4 Verify the rewrite (local, before every push)

Checked after each pass:

1. Same number of commits (15).
2. **Identity:** `git log --format='%an <%ae> | %cn <%ce>' | sort | uniq -c` → one line, 15 commits.
3. **Messages:** only the intended ones changed; one line per commit.
4. **Dates:** unchanged (`%ad %cd` compared with `refs/original`).
5. **Content:** `git diff --quiet <old> <new>` for every commit pair. After pass 1, only the two email lines differed; after passes 2 and 3, there were no differences.
6. **Scrub:** no old email left in any commit or file (`yandex`, `cinthia.space`, `anghel89@gmail`, `bis.architect`).
7. **App:** the app starts and the tests pass.

### 4.5 Switch the remote and push

1. `git remote rename origin bitbucket`, then `git remote rename github origin`, then `git branch -u origin/master`.
2. After each pass: `git push --force-with-lease=master:<previous tip> origin master` (the repo was still private).
3. GitHub `master`: `5102c21` (pass 1) → `226158d` (pass 2) → `6d0ac80` (pass 3). Later commits were normal pushes.

### 4.6 ⏸ You: check on GitHub (still private)

- [x] The commit count matches, and every commit is linked to `dananghel89`
- [x] The dates are original, and the messages are as in section 3
- [x] The README describes BIS Admin

### 4.7 Local cleanup

1. [x] `git remote remove bitbucket` (2026-10-17, the local link only).
2. [x] `git update-ref -d refs/original/refs/heads/master` after each pass.
3. [x] Purged the old objects locally (2026-11-02, after the bundle in 4.9.2 was saved): `git reflog expire --expire=now --all`, then `git gc --prune=now`. Afterwards, the old Bitbucket tip `982c9d2` and the earlier rewrite tips `eae4797`, `5102c21` and `226158d` are gone; `git fsck --unreachable --no-reflogs` finds nothing; the tests pass.
4. [x] `git log --all --format='%ae %ce' | sort -u` → only the noreply address.

### 4.8 ⏸ You: make it public

1. [x] Checked **as a visitor**, through GitHub's public API, on 2026-10-22:
   - `"visibility": "public"`
   - 15 commits, all from the noreply address and linked to `dananghel89`
   - Issues, Wiki, Projects, Discussions and Pull requests all off
2. [x] Settings → General → Danger Zone → **Change visibility → Public** (2026-10-22).
3. `composer.json` got `"name": "dananghel89/bis-admin"`, a description, keywords and `"license": "proprietary"` on 2026-11-02, as in agency-cms.

### 4.9 ⏸ You: delete the Bitbucket repo (browser)

1. [x] **Deleted.** Before deleting, its only branch, `master` (`982c9d2`), was compared with GitHub: all 12 commits were present in the same order, and the content differed only in the two scrubbed email lines (2026-10-22). On 2026-11-02, `git ls-remote` reported that the repo no longer exists. The GitHub URL was entered as the new location.
2. [x] **Original history saved** (2026-11-02): `bis-admin-before-email-rewrite.bundle` was copied next to the project folder, outside the repo, as in agency-cms. On 2026-11-05 the repo moved from `~/Develop/Workspace` to `~/Develop/Projects`, and the bundle moved to the backups folder: `~/Downloads/backups/bis-admin/bis-admin-pre-github-20261016.bundle`. `git bundle verify` reports a complete history: 15 commits, including Bitbucket's tip `982c9d2`. It still contains the old emails, so **delete it once the Laravel 13 upgrade is merged**. The three copies in the session's temporary scratchpad can go at any time.

### 4.10 Docs and follow-up

1. `CLAUDE.md` and the README describe the project as it is on GitHub. The commit identity is automatic; check it with `git config user.email` before the first commit in a new clone.
2. The upgrade plan works on the GitHub `master`.
3. **Incident during the migration (2026-10-16):** test scripts that relied on `DB::rollBack()` left test users and infos in the local database, because its tables are MyISAM. They were removed (with a JSON backup), and the review plan converts the tables to InnoDB (B02).

## 5. Checklist (end state)

- [x] `git remote -v` → only `origin = https://github.com/dananghel89/bis-admin.git`
- [x] All commits use the noreply address and one-line `type: description` messages
- [x] The old emails appear nowhere in the history
- [x] The GitHub repo is public, with Issues, Wiki, Projects, Discussions and Pull requests off
- [x] The Bitbucket repo is deleted
- [x] Old objects purged locally (4.7.3); bundle saved in `~/Downloads/backups/bis-admin/` (4.9.2), kept until the Laravel 13 upgrade is done
- [x] The tests pass on the GitHub `master`

## 6. Rollback

| Situation | Action |
|---|---|
| A rewrite looks wrong | No longer applies: every pass was verified and pushed. To see the original history, fetch it from the bundle: `git fetch ~/Downloads/backups/bis-admin/bis-admin-pre-github-20261016.bundle master:original-history` (into a separate branch, never over `master`) |
| Anything else | GitHub `master` is the only source of truth now. **No force-push without asking** |

## 7. Lessons (used for agency-cms)

- **Decide the final identity first**, then rewrite once: three passes meant three force-pushes and three sets of new commit ids.
- **Set up the per-host email rule** (`includeIf`) before the first commit on a GitHub remote.
- **Check the GitHub email privacy settings before pushing** private-domain commits, since "Block command line pushes" rejects them.
- **Don't verify with test data in a MyISAM database.** Rollbacks do nothing there.
