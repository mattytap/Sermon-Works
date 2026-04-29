# Modernisation pass — discovery findings

Discovery date: 2026-04-28 (session 14, after #15 shipment closed Stream B).

This doc is the audit-style log for the modernisation pass — the "PHP 8.1 floor + current-WP-API surface" priority called out in the resumption-prompt as next-session priority 2 after Stream B closes. Discovery output here; execution decisions live in the per-tier sub-sections.

The pass ships **before** the `readme.txt` rewrite + text-domain rename (priority 3) and **before** pre-launch operational validation (priority 4). Items that overlap with priority 3 (the text-domain literal `sermon-manager-for-wordpress`, the WP.org `Contributors:` line, etc.) are explicitly deferred there and not duplicated here.

---

## Pre-discovery context

What the resumption-prompt named as surface seeds:

- 11 dead `wpforchurch.com` admin links (3 files)
- 2 front-end error messages leaking plugin internals (2 files)
- Dead PHP 5.3 version-check block at `sermons.php:22-46` (translation string at `:38` still says "Sermon Manager")
- Plugin header `Requires at least: 4.5` and `Tested up to: 5.1`
- Scattered `@author WP For Church` PHPDoc tags throughout `includes/`
- File-level `Sermon Manager` references in PHPDoc / inline comments (~30 occurrences)
- #28 follow-up: taxonomy-list-table column with image thumbnails

Discovery confirmed most of these and resized two:

- `@author WP For Church` is in **one** code file (`sermons.php:51`), not "throughout `includes/`"
- `Sermon Manager` PHP-file references are **60+** hits, not ~30 — split between user-visible admin strings and internal-only PHPDoc/comments

PHPCompatibility 9.3.5 with `testVersion 8.1-` reports zero hits on the codebase — suggests no obvious 8.1-syntax incompatibilities at the static-sniff level, but doesn't rule out runtime-level fatals (e.g. session-8's `views/wpfc-podcast-feed.php:165` finding that PHPCS missed). Modernisation here is mostly about removing dead PHP 5.3-era code, replacing WP-deprecated function calls, and updating user-visible branding/links — not about chasing 8.1-syntax fixes that don't seem to exist.

---

## Tier A — user-visible / actively wrong

These are the items that affect what an admin or visitor actually sees today, or that fail under current WP. Ship-priority within the pass.

### A1. WP-deprecated function calls

PHPCS `WordPress.WP.Deprecated*` sniff (run on plugin code, vendor excluded) reports four hits:

| File:line | Call | Deprecated since | Fix |
|---|---|---|---|
| `includes/admin/export/class-sm-export-sm.php:76` | `get_terms( $tax, array( 'get' => 'all' ) )` | WP 4.5.0 | Drop second arg; `get_terms()` returns all by default |
| `includes/admin/export/class-sm-export-sm.php:99` | `seems_utf8( $str )` | WP 6.9.0 | Replace with `wp_is_valid_utf8( $str )` |
| `includes/admin/import/class-sm-import-sm.php:1005` | `wp_upload_bits( $name, 0, $contents )` | WP 2.0.0 | Drop the `0` second arg (it was never used) |
| `includes/admin/sm-admin-functions.php:176` | `term_description( $term_id, $taxonomy )` | WP 4.9.2 | Drop second arg; `$term_id` alone is sufficient |

All four are mechanical fixes. The `seems_utf8` one is in WXR-style sermon export (slug normalisation); the rest are similarly localised. None should change behaviour beyond removing the deprecation notices.

### A2. Front-end error messages leaking plugin internals

Confirmed exactly as flagged in session 11:

- `includes/sm-template-functions.php:717`: `<p><b>Sermon Works</b>: Failed loading partial "<i>$name</i>", file does not exist.</p>` — emitted unconditionally to public visitors when a partial-template lookup fails.
- `views/partials/content-sermon-filtering.php:34`: `<p><b>Sermon Works</b>: Partial "<i>$basename</i>" loaded incorrectly.</p>` — same pattern, fires when a required template variable is unset.

Both tell a public visitor the plugin slug, the partial filename, and the failure mode. **Fix:** gate on `WP_DEBUG` or `current_user_can( 'manage_options' )` — public visitors get nothing, admins/devs see the same message. (Reasonable additional touch: log via `error_log()` regardless, so silent failures still surface in server logs.)

### A3. Dead PHP 5.3 version-check block

`sermons.php:18-46` (technically `:18` is the `// All files must be PHP 5.3 compatible!` comment, then `:22-46` is the runtime check + the `sm_render_php_version_error()` function definition).

Three problems with this block:

1. **Dead under PHP 8.1 floor.** The `if ( version_compare( PHP_VERSION, '5.3.0', '<' ) )` check can never be true — the plugin already declares `Requires PHP: 8.1` in its header.
2. **Translation string at `:38` still says "Sermon Manager"** — deferred from session-11 rebrand because the whole block is dead. Will be deleted with the block.
3. **The leading comment** `// All files must be PHP 5.3 compatible!` directly contradicts `Requires PHP: 8.1` — confusing for any new contributor reading the file.

**Fix:** delete the comment, the runtime check, and the `sm_render_php_version_error()` function in one stroke.

### A4. Plugin-header floors

`sermons.php` plugin header currently reports:

- `Requires at least: 4.5` (WordPress 4.5 was March 2016)
- `Tested up to: 5.1` (WordPress 5.1 was Feb 2019)
- `Requires PHP: 8.1` ✓ (already correct)
- `Version: 2.16.0` ✓ (matches the recent #28 bump)

Pilot LocalWP environment runs WP 6.9.4 + PHP 8.1.29. The pre-launch validation site will be on similarly current WP.

**Fix:** bump `Requires at least` and `Tested up to` to numbers that reflect actually-tested current WP. Pragmatic call: `Requires at least: 6.0` (covers reasonably modern WP without locking out sites that haven't updated to 6.9 yet) and `Tested up to: 6.9` (matches the LocalWP env). Both can be revisited at WP.org submission time.

### A5. Dead `wpforchurch.com` admin links + WP-For-Church-support reference

Confirmed across four files:

- `includes/class-sm-install.php:278` — Plugins-row "Premium support" link to `wpforchurch.com/my/submitticket.php?...`. Visible on every wp-admin Plugins page row for the active plugin.
- `includes/admin/views/html-admin-settings.php:70` — "Sign up today" CTA linking to `wpforchurch.com/wordpress-plugins/sermon-manager/?...` (in the "$49 per year" support block).
- `includes/admin/views/html-admin-settings.php:75` — `wpforchurch.com/my/clientarea.php?...` customer-area link.
- `includes/admin/views/html-admin-settings.php:92,95,99,105` — four `wpforchurch.com/my/knowledgebase/...` links covering Getting Started, Shortcodes, Troubleshooting, and the knowledgebase root.
- `includes/admin/views/html-admin-import-export.php:130,147,171,188` — knowledgebase links for Sermon Browser import (`#sermon-browser`) and Series Engine import (`#series-engine`), each duplicated as a primary link and a "Click here for more details" link.
- `includes/admin/settings/class-sm-settings-debug.php:123` — translation string "Usually used when WP For Church support instructs to do so" (in the Background-updates debug-tab description).

`wpforchurch.com` itself has been domain-parked / non-functional for years, so all 11 links lead nowhere. The settings-debug.php string is just stale narrative pointing at non-existent support.

**Fix:** for each link, decide:

- **Plugins-row "Premium support":** delete entirely (the upsell hook is gone with the upstream's exit).
- **Settings page "$49 per year" support block (lines 65-78 area):** delete entirely; replace with a brief "GitHub Issues" pointer or omit.
- **Settings page knowledgebase links (Getting Started, Shortcodes, Troubleshooting, knowledge base root):** replace with GitHub-side equivalents where they exist (`README.md`, `ROADMAP.md`, `CONTRIBUTORS.md`), or delete the links and keep the surrounding copy.
- **Import-Export knowledgebase links:** delete the `<a>` wrappers; keep the surrounding "Some restrictions apply" copy as bare text.
- **Settings-debug "WP For Church support" string:** reword to "Usually used when GitHub Issues instructs to do so" or just generic "for advanced debugging" copy.

This is mechanical but per-link-judgment-call work. ~12 surgery sites in 4 files.

### A6. `class-sm-install.php:279` — `sermonmanager.pro` Pro-upsell link

Hit found in passing during the `Sermon Manager` grep:

```
'smp' => '<a href="https://sermonmanager.pro/?utm_source=...">Get Sermon Manager Pro</a>'
```

Renders in the Plugins row alongside the `wpforchurch.com` "Premium support" link. `sermonmanager.pro` was the upstream's paid Pro plugin product line — that line is also abandoned alongside the free version, and the `.pro` domain is now parked.

Note the suspicious text-domain in the `__()` calls on this line: `'sermon-manager-pro'` (not `'sermon-manager-for-wordpress'`). This is a cross-text-domain reference originally intended to be filled in by the Pro plugin if it was active; the free plugin would otherwise show "Get Sermon Manager Pro" untranslated. With both plugins dead, the reference is just dead text.

**Fix:** delete the `'smp'` plugin-row link entirely. Tier A because Plugins-row visibility.

---

## Tier B — internal-only / IDE-visible

Items that only show up in editor/IDE views or PHPDoc tooling — they're stale-branding noise but don't affect what users see.

### B1. `@author WP For Church` PHPDoc tag

One occurrence in code: `sermons.php:51`. That's it; the resumption-prompt's "throughout `includes/`" was overstated.

**Fix:** update to `@author Matt Fawcett (restoration); WP For Church (original)` or similar — mirror the plugin-header `Author:` field for consistency.

### B2. File-level `Sermon Manager` PHPDoc / inline-comment references

60+ matches across PHP files. Pattern roughly:

- File-level docblocks: `/** ... Sermon Manager ... */` at the top of class files (e.g. `class-sm-roles.php:3`, `class-sm-autoloader.php:11`, `class-sm-api.php:11`, `class-sm-install.php:93`, `class-sm-dates.php:13`, etc.)
- Inline comments naming the plugin: `// Init Sermon Manager locale.` etc.
- PHPDoc `@param` / method descriptions referencing the plugin name

These are internal-only. They don't appear in any user-facing surface — admin pages, error messages, plugin metadata. Future contributors / IDE tooling would see them.

**Fix:** mechanical rename `Sermon Manager` → `Sermon Works` in PHPDoc-context-only call sites. The trick is **not** touching:

- Translation strings: `__( '...Sermon Manager...', 'sermon-manager-for-wordpress' )` — these are user-visible (Tier A or text-domain rename territory)
- Variable names / class names / function names — all already use `SM_*` / `sm_*` prefixes, no `SermonManager` literal
- The `'sermon-manager-pro'` text-domain (Tier A1's Pro-link)
- Anywhere the original upstream plugin name is referenced for historical context (e.g. the import/export class docblocks "Imports data from another Sermon Manager installation" — that's an interop fact, not branding)

Realistic surface: maybe ~40 PHPDoc/comment sites that should change, ~20 that are interop-context and stay.

### B3. Stale "All files must be PHP 5.3 compatible!" comment in `sermons.php`

Line 18, just above the dead PHP 5.3 check (Tier A3). Bundled with that fix; no separate action needed here.

### B4. `composer.json` `homepage` field

`composer.json:16`: `"homepage": "https://wpforchurch.com/"`. Not user-visible from inside a WP install (composer.json is dev metadata only) but Composer / Packagist do surface it.

**Fix:** update to `https://github.com/mattytap/Sermon-Works`. Bundle with the readme.txt rewrite (priority 3) since both touch package metadata, OR ship in this pass — judgment call.

---

## Tier C — explicitly bundled with priority 3 (readme.txt + text-domain rename)

These are flagged here for completeness but **not** in scope for the modernisation pass. They ship with the WP.org submission prep:

- Text-domain literal `'sermon-manager-for-wordpress'` across all `__()` / `_e()` / `esc_html_e()` / `esc_attr_e()` / `_x()` / etc. call sites (hundreds of them).
- `Text Domain:` plugin-header line in `sermons.php`.
- `Domain Path:` plugin-header line.
- Shipped `.mo` and `.po` files in `languages/` (need rebuilding under new text domain).
- `readme.txt` — the upstream WP.org readme, including `Contributors:`, `Donate link:`, "#1 WordPress Sermon Plugin" copy, knowledge base links, etc.

WP.org guidelines require slug + text-domain match, so these MUST land together.

---

## Tier D — separate enhancement (deferrable)

### D1. #28 follow-up: taxonomy-list-table image column

The bundled `taxonomy-images` library that #28 removed (commits `78468f4`..`51c2ec9`) included a column on each sermon-taxonomy term-list table showing image thumbnails. Phase C deliberately didn't restore it ("Functional gap from the vendor library that we deliberately don't restore" per `.restoration/stream-b-plan.md § 3`).

**Surface:** ~20 LOC of `manage_{taxonomy}_columns` filter + `manage_{taxonomy}_custom_column` action callback for each of the three sermon taxonomies. Could go in `includes/admin/sm-term-image-functions.php` (the file added in #28 Phase C).

**Status:** not blocking the cutover, not blocking WP.org submission. Worth doing as a focused follow-up enhancement *after* the modernisation pass closes. Or punt to post-cutover.

---

## Suggested execution sequencing

Group by thematic shipment, not by tier number, to keep commits coherent. Suggested order — smallest blast radius first, biggest decisions last:

1. **WP-deprecated function calls** (A1 — 4 sites in 3 files) — single commit. Pure mechanical fix, no decisions.

2. **Front-end error-message gates** (A2 — 2 sites in 2 files) — single commit. Plus optional `error_log()` call. Tiny but security-flavoured.

3. **Dead PHP 5.3 block + stale comment** (A3 + B3 — `sermons.php:18-46`) — single commit. Tiny diff, big readability win.

4. **Plugin-header floor bumps** (A4 — `sermons.php` header) — single commit. Two-line change.

5. **Dead `wpforchurch.com` admin links + Pro-upsell + debug-tab string** (A5 + A6 — 4 files, ~13 surgery sites) — likely single commit, possibly split if any sub-decision turns out controversial. Per-link judgment calls but mostly "delete or replace with GitHub-side equivalent."

6. **`@author` tag + composer.json homepage** (B1 + B4 — 2 sites) — single commit, tiny.

7. **PHPDoc / inline-comment branding sweep** (B2 — ~40 sites across many files) — single commit. Largest diff in the pass but lowest blast radius. Worth doing in one go so the codebase reads consistently afterwards. Skipped: interop-context references and translation strings.

Estimated total: 6–7 commits, ~2–3 hours focused work depending on how many of the wpforchurch.com link decisions need a back-and-forth.

**Out of scope for this pass:**
- Tier C (readme.txt + text-domain rename) — bundled with priority 3.
- Tier D (#28 taxonomy-images column) — separate enhancement.
- Anything new that emerges from pre-launch operational validation or a runtime fatal during smoke-testing.

---

## Plan-time risks worth pre-flagging

- **Translation-string protection.** During the PHPDoc/comment branding sweep (Tier B2), any `Edit replace_all` for `Sermon Manager` → `Sermon Works` MUST not touch translation strings (those are user-visible text and tied to the existing `sermon-manager-for-wordpress` text domain). Per the session-8 lesson on `Edit replace_all` not word-boundary-respecting, **prefer per-line targeted Edits** or use longer context-anchoring patterns.

- **`'sermon-manager-pro'` text-domain literal.** The `class-sm-install.php:279` line uses a different text domain than the rest of the plugin. Tier A6 deletes that line entirely so the text-domain literal goes with it — but if the deletion gets re-scoped, the text-domain reference must NOT be globally renamed.

- **Interop-context references.** The import classes (`class-sm-import-sm.php`, `class-sm-import-sb.php`, `class-sm-import-se.php`) have docblocks like "Imports data from another Sermon Manager installation" that describe an actual interop fact (the import format is the upstream Sermon Manager export format, and this is what the user reads to know what the importer accepts). These should stay as "Sermon Manager" — they're naming the source, not the receiving plugin.

- **`Sermon Manager Pro` references in `html-admin-settings.php:48-52`.** A whole "Sermon Manager Pro" upsell block at the top of the settings page beyond the `wpforchurch.com` links. ~10-15 LOC. Tier A territory (user-visible upsell to dead product) but slipped past the initial Tier A5 catalog. **Add to A5 scope.** Delete the whole upsell block.
