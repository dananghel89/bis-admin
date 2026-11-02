# Plan: using the Sufee Admin and Bell themes

Status: **In progress** – 2026-11-02: facts checked against the original downloads and the four license pages (section 2); D1 and D3 decided (personal project; images from Unsplash, logos and favicons your own); Sufee updated to the Colorlib Nov 2018 release with its MIT `LICENSE`, and the unused demo libraries and `flag-icon` removed (steps 2–3, not yet committed). **Open:** Bell footer credit and README credits (steps 1–2), remove the unused `cover.jpg`, decision D2
Created: 2026-11-02

> This is a technical checklist based on the published license texts, not legal advice.

## 1. Order

1. [project-review-fix-plan.md](project-review-fix-plan.md)
2. **This plan**
3. [github-migration-plan.md](github-migration-plan.md) (already done; the repo has been public since 2026-10-22)
4. [laravel-13-upgrade-plan.md](laravel-13-upgrade-plan.md)
5. [finish-project-plan.md](finish-project-plan.md)

Because the repo is **already public**, steps 1 and 2 of section 5 (the Bell footer credit and the Sufee license notice) shouldn't wait for the review plan. They can go first, as one commit.

## 2. Facts (checked 2026-11-02)

### Which releases the project uses

| Theme | In the project | Original download (kept outside the repo) | Result of the comparison |
|---|---|---|---|
| **Bell** (landing page) | `public/landing/` | `Downloads/webs/bell-master`, the old release at [github.com/technext/bell](https://github.com/technext/bell) (demo: [technext.github.io/bell](https://technext.github.io/bell/)); BootstrapMade's current Bell is a newer version | **Same release.** 31 of 35 `lib/` files are identical. Bootstrap was updated from 4.0.0-beta.2 to 4.0.0. `css/style.css` (58 lines) and `js/custom.js` (14 lines) carry your own changes |
| **Sufee** (admin) | `public/admin/` (`css/`, `js/`, `fonts/`) | `Downloads/webs/sufee-master` (Colorlib, Nov 2018, Bootstrap 4.1.3, `assets/` + `vendors/` layout) | **An earlier Sufee release** (Bootstrap 4.0.0-beta.3, flat `css/js/fonts` layout). `js/main.js` is identical. Both copies carry the same `style.css` banner |

### What the sources say

| Source | What it says |
|---|---|
| Bell `readme.txt` (download) | "Theme Name: Bell · Author: BootstrapMade.com". No license text; `technext/bell` has no LICENSE file either |
| Bell `index.html` footer (download) | `<!-- All the links in the footer should remain intact. You can delete the links only if you purchased the pro version. Licensing information: https://bootstrapmade.com/license/ -->` followed by `<a href="https://bootstrapmade.com/">Bootstrap Templates</a> by BootstrapMade` |
| [bootstrapmade.com/license](https://bootstrapmade.com/license/) (Free License) | "You must keep the footer credit line as is". The free version may be used in personal projects, but "you cannot use the free version to create websites for clients or charge for your work". "None of the licenses allow for the reselling or mass redistribution of the templates". Public repositories aren't mentioned. Removing the credit needs the paid **Pro License** |
| [themewagon.com/license](https://themewagon.com/license/) | Free themes can be used for commercial and client work, **but** "Template authors have different licensing regarding attribution link removal". For the credit, ThemeWagon points to the original author's license. Its theme pages credit **BootstrapMade** for Bell and **Colorlib** for Sufee |
| Sufee `LICENSE` (download) | **MIT License**, "Copyright (c) 2018 Aigars Silkalns" (Colorlib's founder). The notice must be included "in all copies or substantial portions" |
| Sufee `README.md` (download) | "Sufee is licensed under The MIT License (MIT)… But you always need to state that **Colorlib is the original author** of this template." Author: Colorlib |
| Sufee `style.css` banner (download **and** project) | "Theme Name: Sufee-Admin · Author: jewel_theme · License: GNU General Public License v2 or later". This is an older label that Colorlib kept in its own MIT release |
| [colorlib.com/wp/licence](https://colorlib.com/wp/licence/) | Colorlib's **current** licence for its template products: footer credit removal allowed, client use allowed, but **no public repositories** of template source files and no redistribution |

### What follows

- **Sufee is MIT.** The release ships its own `LICENSE`, and a license that comes with a release is what applies to that copy; Colorlib's current general licence doesn't take it back. The MIT conditions are: keep the copyright and permission notice, and, from the README, name Colorlib. **The project has neither today:** no Sufee `LICENSE` file, and no Colorlib mention.
- **Bell needs its footer credit.** It is **missing**: the project footer only says "© {year} BIS - All Rights Reserved".
- **Bell in a public repo** isn't forbidden in writing, and ThemeWagon itself publishes this exact release in the public `technext/bell` repo. But BootstrapMade bans "mass redistribution", so it's your call (D2).

**How this project is used (D1, decided 2026-11-02):** a **personal** project, never for a client or paid work. The source is public on GitHub (`dananghel89/bis-admin`), and the site will run at `demo.dananghel.com` as a personal project on `dananghel.com`. That's personal use, which the free Bell license allows (with the credit). The code is `"license": "proprietary"` (decided 2026-11-02): your own code is "all rights reserved", and the themes keep their own licenses.

## 3. Required vs optional

| Item | Required? | Proposal |
|---|---|---|
| **Bell footer credit** | **Yes** (BootstrapMade Free License, and the theme's own footer comment). **Missing** | Restore the original line in `includes/landing/footer.blade.php`, in the theme's own words: `<a href="https://bootstrapmade.com/">Bootstrap Templates</a> by BootstrapMade` |
| **Sufee MIT notice** | **Yes** (MIT: keep the copyright and permission notice). **Missing** | Copy the download's `LICENSE` unchanged to `public/admin/LICENSE` |
| **Sufee: name Colorlib** as the original author | **Yes** (README condition). **Missing** | README "Credits" section |
| Keep the `style.css` banners (Sufee and Bell) | Yes, keep notices | Unchanged. Don't edit or remove theme banners |
| README "Credits" section | Required for Sufee, recommended for Bell | "Admin theme: Sufee by Colorlib (MIT, see `public/admin/LICENSE`)" and "Landing theme: Bell by BootstrapMade (free license, footer credit kept)", with links |
| Bell for client or paid work | **Not allowed** with the free version | D1 |
| Bell in a public repo | Not addressed by the license | D2 |
| **Unused demo libraries** `public/admin/js/lib/**` and `public/admin/css/lib/**`, 4.4 MB (charts, maps, Google Maps, DataTables, pdfmake…) | No. They're never loaded, and 35 of their 63 JS/CSS files carry no notice of their own | Remove them, as agency-cms removed `public/demo`. Not needed in the history |
| `flag-icon.min.css` | No. It's loaded on every admin page, but points to `../../images/flags/…`, which doesn't exist; no view uses flags | Remove the `<link>` and the file |
| Library notices in the files that are used (Bootstrap, jQuery, Font Awesome, Superfish, Stellar…) | Yes, and already met | Keep the vendor files as they are |
| Images `landing/img/cover.jpg` (unused), `parallax-bg.jpg` (used) | No: **Unsplash** photos (D3). The [Unsplash License](https://unsplash.com/license) allows free use, including commercial, without required attribution | Keep `parallax-bg.jpg`. Remove `cover.jpg` only because it's unused. A "Photos: Unsplash" line in the README credits is optional |
| BIS logos (`landing/img/logo.png`, `logo-nav.png`) and the favicons (`landing/img/favicon.png`, `admin/img/favicon.png`) | No: **your own** brand assets (D3), made back then with Shopify's logo maker, which no longer exists | Keep them unchanged |
| Google Fonts (3 `fonts.googleapis.com` links) | No (privacy best practice) | Self-host during the Laravel 13 upgrade, with Vite/Fontsource, as in agency-cms |
| Project license | No | Already decided: `"license": "proprietary"` in `composer.json`, with no `LICENSE` file in the root |

## 4. Decisions needed

| ID | Question | Options |
|---|---|---|
| ~~D1~~ | Will BIS Admin be a live site for a client or paid work? | **Decided 2026-11-02: no.** Personal project, published at `demo.dananghel.com`; the free Bell license is fine |
| D2 | Keep **Bell** in the **public** repo? | **Keep it, with the credit restored** (recommended: ThemeWagon publishes the same release publicly). Or ask BootstrapMade. Or replace the landing page with an MIT theme |
| ~~D3~~ | Where do `parallax-bg.jpg`, `cover.jpg`, the BIS logos and the favicons come from? | **Decided 2026-11-02:** the two photos are from Unsplash; the BIS logos and favicons are your own (made with Shopify's former logo maker). All are kept; `cover.jpg` is removed only because it's unused |

## 5. Steps

1. **Bell credit** (required): restore the original credit line in the landing footer. Check `/` in the browser.
2. **Sufee notice** (required): add `public/admin/LICENSE` (the MIT text, © 2018 Aigars Silkalns) and the README "Credits" section naming Colorlib, and BootstrapMade for Bell.
3. **Remove the unused demo libraries** `public/admin/js/lib/` and `public/admin/css/lib/`, plus `flag-icon.min.css`. Then do a browser check of every admin page (menu, dropdowns, alerts, forms, login).
4. Carry out the decisions: remove the unused `cover.jpg` (D3); D2 is still open.
5. Update `CLAUDE.md`: the themes, their licenses and versions, and that theme banners, `public/admin/LICENSE` and vendor files stay unchanged.
6. Commits, with your approval: steps 1–2 as `docs: credit Bell and Sufee theme authors`; step 3 as `chore: remove unused Sufee demo libraries`.

The original downloads in `Downloads/webs/` aren't added to the repo; they're only the reference for this plan.

## 6. Final state

| Phase | Commit | Result |
|---|---|---|
| Sufee update (2026-11-02) | not yet committed | `public/admin/` now holds only what the pages load, from Colorlib's Nov 2018 release (`Downloads/webs/sufee-master`), in the release's own layout: `assets/css/style.css`, `assets/js/main.js`, and `vendors/` (Bootstrap 4.1.3, jQuery 2.2.4, Popper, Font Awesome 4.7.0, Themify Icons), each with its `LICENSE`/`README`. Sufee's MIT `LICENSE` and `README.md` are in `public/admin/`. Your custom rules moved unchanged to `public/css/bis-admin.css`, loaded last. Removed: the old release (`css/`, `js/`, `fonts/`, 4.4 MB of unused demo libraries, `normalize.css`, `flag-icon`, `cs-skin-elastic`, `plugins.js`, `avatar.png`). `favicon.png` is kept (D3). Checked: every page renders with only the new assets, all files return 200 on `bis-admin.test`, and no CSS class used by the views lost its styling. **Browser check by you pending** |

## 7. Sources

- Bell (BootstrapMade): https://bootstrapmade.com/bell-free-bootstrap-4-template/ · license: https://bootstrapmade.com/license/
- Bell, old release used here: https://github.com/technext/bell · https://technext.github.io/bell/ · ThemeWagon: https://themewagon.com/themes/free-bootstrap-4-multipurpose-template-bell/
- Sufee (Colorlib): https://colorlib.com/polygon/sufee/index.html · https://github.com/puikinsh/sufee-admin-dashboard · ThemeWagon: https://themewagon.com/themes/free-bootstrap-4-html5-admin-dashboard-template-sufee/
- Licenses: https://themewagon.com/license/ · https://colorlib.com/wp/licence/
