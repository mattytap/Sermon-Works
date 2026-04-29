# Stream B execution plan

Stream A (the 21-issue security-fix run, commits `b911e6f`..`84805ae`) shipped in session 8. Stream B is the major-vendor-surgery work the original session-8 plan deliberately deferred. This doc scopes each item, documents migration strategy, names operational-verification risks, and sequences execution.

**Ordering principle:** smallest blast radius first; data-migration items bundled with the surgery that creates them.

**Smoke-test pattern (session-12 update):** Stream B items ship on code review + diff + `php -l` + PHPCS. LocalWP smoke-tests bundle into the pre-launch UAT alongside the 17 deferred Stream A `awaiting-smoke-test` issues.

**Order revised (session-13):** #35/#36 swapped ahead of #28. Under the smoke-test-deferral pattern, ordering by blast radius matters more — #35/#36 is smaller by every metric (zero data migration, ~30 LOC replacement, no new admin UI) and closes two outstanding public CVEs. #28's data-migration step lands closer to the pre-launch UAT when it ships second.

| # | Issue | Item | Status |
|---|---|---|---|
| 1 | #27 | wp-background-processing 1.0.1 → 1.4.0 full upgrade | ✓ Shipped session 12 |
| 2 | #35 + #36 | entry-views.php removal + post-meta + template_redirect counter | ✓ Shipped session 13 |
| 3 | #28 | taxonomy-images library removal + term-meta migration | ✓ Shipped session 13 |
| 4 | #15 | Bundled CMB2 upgrade (currently 7+ years stale) | ✓ Shipped session 14 |

All four items are landed-but-not-flipped-public territory: per the public-flip gates in `CLAUDE.md`, none of these block visibility-flip on its own — but the two CVE-bearing ones (#35, #36) and #15 are pre-WP.org-submission gates because of `Plugin Check` lint blockers and the abandoned-vendor signal.

---

## 1. #27 — wp-background-processing 1.0.1 → 1.4.0 full upgrade ✓

**Status: shipped session 12.** Two commits:

- `4be678c` — `chore(vendor): upgrade wp-background-processing 1.0.1 -> 1.4.0`. Verbatim upstream 1.4.0 with three hand-edits to preserve `SM_WP_*` class names (so we never inherit a colliding plugin's stale 1.0.x copy). Bundled `license-wp-background-processing.txt`. Drops the session-8 `:271` hand-edit since upstream now ships the canonical `maybe_unserialize()` static + `$allowed_batch_data_classes` property.
- `6bb6219` — `refactor(updater): always use SM-prefixed vendor classes; harden against deserialisation; remove obsolete debug toggle`. Replaces the 28-line conditional class-aliasing dance with a 6-line include guard. Adds `protected $allowed_batch_data_classes = false` on `SM_Background_Updater` (subclass-property-precedence at upstream lines 100-104 ensures this beats the constructor default). Removes the `in_house_background_update` "Force Background Updates" debug toggle (its only purpose was to force the now-removed conditional branch).

The session-8 minimum-viable POI fix (`84805ae`) closed the security exposure; the two commits above complete the audit's recommended remediation. Issue tagged `awaiting-smoke-test`; smoke-test bundles with the pre-launch UAT.

**Locked design decisions worth preserving** (in case they recur in #28 / #35 / #15):

- **SM-prefixed classes inside the vendor file, not aliased.** The original "alias `SM_WP_*` ↔ `WP_*` based on whether another plugin loaded `WP_*` first" pattern was actively dangerous with a 7-year API gap — we'd inherit incompatible `task()` semantics from a colliding 1.0.x copy. Renaming inside the vendor file (cleaner than aliasing) aligns with upstream's own README guidance ("highly recommended to prefix wrap the library class files").
- **Subclass-side property override for `$allowed_batch_data_classes`** rather than vendor hand-edit. Upstream provides the canonical hook; using it keeps the vendor file pristine for future upgrades.
- **Defence-in-depth `task()` allow-list guard preserved.** `0 === strpos( $callback, 'sm_update_' )` is belt-and-braces on top of the unserialise hardening; closes the deserialise-to-arbitrary-callable path even if a future regression weakens the property guard.

---

## 2. #35 + #36 — Bundled entry-views.php removal ✓

**Status: shipped session 13.** Two commits:

- `a12fe83` — `refactor(views): replace bundled entry-views.php with WP-native counter`. Creates `includes/sm-views-functions.php` (~30 LOC of executable code, ~70 LOC including docblocks) with a server-side `template_redirect` counter, the `[sermon-views]` shortcode (now `esc_html()`-escaping `before`/`after` attrs), and the public `wpfc_entry_views_get()` function the admin sermon-list "views" column at `includes/admin/class-sm-admin-post-types.php:131` calls. Switches `sermons.php:110` to load the new file. Drop-in compat preserved on function names + `Views` meta key + `wpfc_entry_views_meta_key` / `sm_views_add_view` filters + `entry-views` post-type-support gate. Adds two free guards: `is_feed()` and `is_preview()` filtered out of counter increments.
- `76d740b` — `chore(vendor): remove now-unused bundled entry-views.php`. Deletes the 150-LOC vendor file from the working tree. The CVE-bearing AJAX registrations stopped firing the moment the prior commit switched the include; this commit is just dead-file cleanup.

Both CVEs closed:

- **CVE-2025-12368** (stored XSS via shortcode `before`/`after` attrs) — closed by `esc_html()` on both attrs in the rewritten `wpfc_entry_views_get()`.
- **CVE-2025-63002** (unauthenticated state-change via `wp_ajax_nopriv_wpfc_entry_views`) — closed by deleting the AJAX handlers entirely. Counter is now server-side via `template_redirect`, no AJAX surface.

Issues `#35` and `#36` tagged `awaiting-smoke-test`; smoke-test bundles with the pre-launch UAT per the deferral pattern.

**Plan-time inaccuracies caught at execution** (preserved for future reference):

- The plan said "~150 LOC vendor"; actual was 150 LOC of `entry-views.php` ✓
- The plan flagged a possibly-separate `assets/js/entry-views.js`; in fact the JS was inline-injected via `wpfc_entry_views_load_scripts()` at `:147` of the vendor file. Removing the vendor file removed the JS automatically — no separate asset to delete.
- The plan said the loader was at "`sermons.php` (location to be confirmed)"; it was at `:110`.
- The plan didn't flag the `class-sm-admin-post-types.php:131` admin caller. Caught by grep before writing; preserved by keeping `wpfc_entry_views_get()` as a public function with the same signature.

### Original plan (preserved for reference)

[The original surface inventory + strategy + risk + checklist sections below are kept verbatim from the locked plan; they describe the work that the two commits above executed.]

### TL;DR

Replace 150 LOC of unmaintained Justin Tadlock library with ~30 LOC of WP-native code: a `template_redirect` view counter writing to post meta + a `[sermon-views]` shortcode reading the same. Closes both CVEs (CVE-2025-12368 stored XSS at `:114` and CVE-2025-63002 missing-auth state-change at `:80-93`) by deleting the vulnerable code wholesale.

### Surface

- `includes/vendor/entry-views.php` — 150 LOC. Hooks at `:41-44`:
  - `template_redirect` → `wpfc_entry_views_load` (counter trigger)
  - `wp_ajax_wpfc_entry_views` / `wp_ajax_nopriv_wpfc_entry_views` → `wpfc_entry_views_update_ajax` (the unauth-state-change CVE)
- Shortcode `[sermon-views]` registered in the same file.
- View counter writes `post_meta` key `Views` (capital V — the existing key, must preserve for upgrade compat).
- Front-end JS at `assets/js/entry-views.js` (need to confirm — possibly bundled with the library) hits the AJAX endpoint.

### Data model

**Current:** post meta `Views` (integer count) updated via AJAX from front-end view.

**Target:** unchanged — same `Views` post meta. Replacement counter writes to the same key. Migration is **zero-data-movement** because the storage is already WP-native; only the writer/reader code changes.

### Strategy

1. Replace `wpfc_entry_views_load()` with a server-side counter that increments `Views` on `template_redirect` for `is_singular( 'wpfc_sermon' )` views. No AJAX needed — increments happen server-side.
2. Replace `[sermon-views]` shortcode with a ~10-line read of `get_post_meta( get_the_ID(), 'Views', true )` plus the same `before` / `after` attribute support — but with `esc_html()` on both attrs (closes CVE-2025-12368 by escaping the attacker-controllable surface).
3. Drop the `wp_ajax_*` handlers entirely (closes CVE-2025-63002 by deleting the vulnerable surface).
4. Drop the front-end JS (no longer needed since counting moves server-side).
5. Remove `includes/vendor/entry-views.php` directory.
6. Update the loader / require chain to drop the include.

The replacement code is well under 30 LOC and the audit log's "~20 lines of core post-meta plus a `template_redirect` counter" estimate is accurate.

### Trade-off (server-side vs JS counter)

The original library used JS+AJAX to defer view counting until after page render — useful for caching plugins (counters skip cached responses, keeping the count accurate per actual viewer not per origin-fetch). Server-side counter increments on every `template_redirect`, which means a CDN-cached page that never hits PHP doesn't bump the counter, but a per-request PHP run does double-count if the cache layer mis-keys.

Realistic call: server-side is simpler, ships now, and a future enhancement could re-add a JS-based counter if Sermon Works is run on a heavy-traffic site that needs it. Most church-tech sites don't.

### Risk

- **View-count drift on caching-plugin sites.** Documented above. Mitigation: changelog note + UAT smoke-test against whatever caching plugin (if any) the validation site uses.
- **Shortcode behaviour change.** Existing posts containing `[sermon-views before="..." after="..."]` will render the same way post-fix EXCEPT that `<script>`-bearing attrs (which were the CVE) now render escaped. Legitimate usage unaffected.

### Execution checklist

- [ ] Write the replacement counter + shortcode in (e.g.) `includes/sm-views-functions.php`
- [ ] Register the new shortcode at the same `[sermon-views]` slug
- [ ] Hook the counter on `template_redirect` for `wpfc_sermon` singular
- [ ] Confirm post meta key `Views` is preserved (upgrade compat)
- [ ] Remove `includes/vendor/entry-views.php`
- [ ] Remove the include / require line for it (location to be confirmed during execution)
- [ ] Remove any `assets/js/entry-views*.js` if separately bundled
- [ ] Tag `#35` and `#36` `awaiting-smoke-test`; close on pre-launch UAT

### UAT smoke-test (deferred)

- [ ] Existing `Views` meta values preserved on every sermon post post-upgrade
- [ ] Front-end view of a sermon increments the count (compare before/after `wp_postmeta` row)
- [ ] `[sermon-views]` shortcode renders the count
- [ ] `[sermon-views before='<script>alert(1)</script>']` renders escaped, no script execution (CVE-2025-12368 closed)
- [ ] No AJAX endpoint at `?action=wpfc_entry_views` (returns 0/404 — CVE-2025-63002 closed)
- [ ] If validation site has a caching plugin: verify count behaviour matches the changelog note

---

## 3. #28 — Bundled taxonomy-images library removal ✓

**Status: shipped session 13.** Four commits, one per phase:

- `78468f4` — `feat(updater): migrator for sermon_image_plugin option -> term_meta (#28 Phase A)`. Adds `sm_update_2160_migrate_term_images()` in `sm-update-functions.php`, registered in `SM_Install::$db_updates` against new version `2.16.0`. Also bumps the plugin header `Version:` and the `readme.txt` `Stable tag:` to 2.16.0 — the version delta is what triggers `SM_Install::check_version()` to queue the new updater on the next admin pageload. Ships independently safe: the legacy option-side reads/writes still work, the migrator just pre-populates `wp_termmeta.sm_term_image_id` in parallel.
- `d9b0c5b` — `refactor: read/write term-image associations via term_meta (#28 Phase B)`. Cuts the two consumer call sites (`get_sermon_series_image_url`, `get_latest_series_image_id`) and the five import/export sites over from the legacy option to `get_term_meta()` / `update_term_meta()`. Net 26 LOC deleted, 8 LOC added — term_meta is genuinely simpler than the option-array indirection.
- `ec52fba` — `feat(admin): WP-native term-image picker for sermon taxonomies (#28 Phase C)`. New file `includes/admin/sm-term-image-functions.php` (~80 LOC) hooks `{taxonomy}_edit_form_fields` + `_add_form_fields` + `edited_{taxonomy}` + `created_{taxonomy}` for the three sermon taxonomies; renders hidden input + Add Image button + preview + Remove Image button; saves on form submit with nonce + `manage_wpfc_categories` cap check. New file `assets/js/admin/term-image-picker.js` (~30 LOC) handles the standard `wp.media()` modal flow. Translatable modal strings via `wp_localize_script`.
- `51c2ec9` — `chore(vendor): remove bundled taxonomy-images library (#28 Phase D)`. Deletes 11 vendor files (1,886 PHP LOC + assets, 2,513 lines total) and the `sermons.php:109` include line. Strictly removing dead code at this point — Phases A–C made the vendor unreachable.

Issue `#28` tagged `awaiting-smoke-test`; smoke-test bundles with the pre-launch UAT per the deferral pattern.

**Plan-time refinement worth noting** (session-13 vs session-12 lock):

- The locked plan stubbed `sermon_image_plugin_get_associations()` to scan all terms in three taxonomies on every call. Session-13 swapped to direct-rewrite of the two consumer call sites — each only needs one term's meta, so a single `get_term_meta()` per call replaces the full taxonomy scan. The function is deleted with the vendor instead of stubbed. Documented in the "Migration strategy" sub-section's step 3.

**Plan-time inaccuracies caught at execution** (preserved for the next Stream B item to learn from):

- Plan said vendor LOC was 1,313; actual was 1,886 (the original count missed `public-filters.php`'s 573 LOC). Caught + corrected mid-session-13.
- Plan said the include line was `sermons.php:108`; actual was `:109`. Caught + corrected mid-session-13.
- Plan didn't mention the WXR importer's existing partial-migration behaviour at `class-sm-import-sm.php:701`: the importer was already calling `add_term_meta()` directly for `sm_term_image_id` on import (and *also* mirroring to the legacy option). Caught at Phase 0 discovery; meant Phase B's WXR-import refactor was "remove the option mirror" not "switch from option to term_meta".
- Plan didn't flag that the taxonomy custom cap is `manage_wpfc_categories` (registered at `class-sm-post-types.php:43-48`), not the standard `manage_categories`. Caught at Phase 0 discovery; Phase C uses the right cap.
- Plan didn't flag the WP-core nonce sequencing: `edited_<taxonomy>` and `created_<taxonomy>` actions fire AFTER WP core has already verified the edit-tag / add-tag form's nonce. Phase C still adds its own `sm_term_image_nonce` as defence-in-depth (closes the gap if some external code triggers `wp_update_term()` with attacker-controlled `$_POST['sm_term_image_id']`).

**Functional gap from the vendor library that we deliberately don't restore**: the vendor added taxonomy-list-table columns showing image thumbnails next to each term. Replicating adds another ~20 LOC plus column-content-callback wiring. Worth doing as a follow-up enhancement; not blocking for the cutover.

### Original plan (preserved for reference)

[The original surface inventory + migration strategy + risk + checklists below describe the work that the four commits above executed.]

### TL;DR

Replace 1,886 LOC of abandoned-2014 vendor with WordPress core term meta (since 4.4) + a small admin metabox for the three sermon taxonomies. One-shot migrator copies the existing `sermon_image_plugin` option into `term_meta` keyed `sm_term_image_id`. The two consumer call sites (`get_sermon_series_image_url`, `get_latest_series_image_id`) are rewritten to call `get_term_meta()` directly; `sermon_image_plugin_get_associations()` is deleted alongside the vendor.

### Surface

- `includes/vendor/taxonomy-images/` — 1,886 LOC across `taxonomy-images.php` (1,313) and `public-filters.php` (573) + assets (CSS, JS, language files, default.png / blank.png / controls.png).
- `includes/sm-core-functions.php:580` — `get_sermon_series_image_url()` calls `sermon_image_plugin_get_associations()`.
- `includes/class-sm-shortcodes.php:737` — `get_latest_series_image_id()` calls the same function.
- 5 import/export sites read/write the `sermon_image_plugin` option directly:
  - `includes/admin/export/class-sm-export-sm.php:346` — export
  - `includes/admin/import/class-sm-import-sm.php:705`, `:710`, `:1172`, `:1174` — WXR
  - `includes/admin/import/class-sm-import-sb.php:265`, `:267` — SB
  - `includes/admin/import/class-sm-import-se.php:185`, `:187` — SE
- `sermons.php:109` — the `include` line that loads the vendor.

### Data model

**Current:** single WP option `sermon_image_plugin` containing `array<term_id, attachment_id>`. Read everywhere via `get_option( 'sermon_image_plugin' )` or `sermon_image_plugin_get_associations()`.

**Target:** per-term meta row, key `sm_term_image_id`, value `attachment_id`. Read via `get_term_meta( $term_id, 'sm_term_image_id', true )`.

### Migration strategy

1. Add a one-shot updater function `sm_update_2160_migrate_term_images()` in `includes/sm-update-functions.php` (next free `sm_update_<bump>` slot — 2.16.0 placeholder; pick the actual version on execution day):

   ```php
   function sm_update_2160_migrate_term_images() {
       $associations = get_option( 'sermon_image_plugin', array() );
       if ( ! is_array( $associations ) ) {
           return;
       }
       foreach ( $associations as $term_id => $attachment_id ) {
           if ( $term_id && $attachment_id ) {
               update_term_meta( (int) $term_id, 'sm_term_image_id', (int) $attachment_id );
           }
       }
       // Don't delete the option — keep as legacy + rollback path. Mark migrated.
       update_option( 'sm_term_image_migrated_to_meta', 1 );
   }
   ```

2. Register it in `SM_Install::$db_updates` against the version bump.

3. **Rewrite the two consumer call sites to use `get_term_meta()` directly** (session-13 refinement — replaces the original BC-stub plan).

   The original plan stubbed `sermon_image_plugin_get_associations()` to scan all terms in three taxonomies and build an associations array on every call. That made every page load that hits the function expensive on sites with hundreds of preachers/series/topics. Both callers only need a single term's image, so a direct `get_term_meta()` is one query instead of a full taxonomy scan.

   - `includes/sm-core-functions.php:580` — `get_sermon_series_image_url()` already has the term in scope. Replace `sermon_image_plugin_get_associations()` lookup with `get_term_meta( $term_id, 'sm_term_image_id', true )`.
   - `includes/class-sm-shortcodes.php:737` — `get_latest_series_image_id()` same pattern.

   Then delete `sermon_image_plugin_get_associations()` alongside the vendor (no BC stub; the function was `@access private` per its docblock).

4. Replace the 5 import/export `sermon_image_plugin` option reads/writes with term-meta loops. The read pattern becomes a `get_terms` + `get_term_meta` loop; the write pattern becomes `update_term_meta` per association.

5. Add admin metabox for image-per-term (see "Admin UX" below).

6. Remove `includes/vendor/taxonomy-images/` directory.

7. Remove the `include SM_PATH . 'includes/vendor/taxonomy-images/taxonomy-images.php';` line at `sermons.php:109`.

8. Preserve the `sermon_image_plugin` option value in DB indefinitely as a rollback / re-migration source.

### Admin UX (the part the audit log glossed)

**Current:** vendor library hooks `{taxonomy}_edit_form_fields` for `wpfc_sermon_series` / `wpfc_preacher` / `wpfc_sermon_topics`, renders an image-picker UI on the term edit page. Admins click "Set image" → media library opens → choose attachment → save.

**Target:** roughly 80-100 LOC of WP-native admin code:

- `{taxonomy}_edit_form_fields` callback for each of the three taxonomies — renders a `<input type="hidden">` for the attachment ID + an "Add Image" button + a `<img>` preview if set.
- `{taxonomy}_add_form_fields` callback for the new-term page (same shape).
- `edited_{taxonomy}` + `created_{taxonomy}` callbacks — read `$_POST['sm_term_image_id']`, validate, write to term meta.
- Inline JS to wire `wp.media()` to the button. Or a single `assets/js/admin/term-image-picker.js`.
- Nonce + cap check (`manage_categories` or per-taxonomy cap) on the save callback.

This is real work — call it a 4-6 hour task by itself.

### Risk

- **Data loss on existing sites if the migrator has a bug.** This is the headline risk — any site upgrading would lose the term-image associations if the migration loop misreads the option or writes to the wrong meta key. **Mitigation:** keep the `sermon_image_plugin` option in DB (don't delete it), add the `sm_term_image_migrated_to_meta` sentinel, write a `sm_update_2161_re_migrate_term_images()` re-runner that reads from the original option and overwrites term meta — recoverable.
- **Term-edit metabox break.** New taxonomy-image admin UX needs to be tested against each of the three sermon taxonomies on the validation site. Without that, admins can't set images on existing or new terms.
- **Theme integrations break if any third-party caller used `sermon_image_plugin_get_associations()` directly.** The function is `@access private` per its docblock so this is unlikely in practice, but the session-13 refinement deletes the function rather than BC-stubbing it. Flag in upgrade notes; any theme that called it must switch to `get_term_meta( $term_id, 'sm_term_image_id', true )`. Themes that read the `sermon_image_plugin` option directly are unaffected (the option is preserved indefinitely as a rollback path).

### Execution checklist

- [ ] Write `sm_update_2160_migrate_term_images()` in `includes/sm-update-functions.php`
- [ ] Register in `SM_Install::$db_updates`
- [ ] Rewrite `get_sermon_series_image_url()` at `includes/sm-core-functions.php:580` to use `get_term_meta()` directly
- [ ] Rewrite `get_latest_series_image_id()` at `includes/class-sm-shortcodes.php:737` to use `get_term_meta()` directly
- [ ] Delete `sermon_image_plugin_get_associations()` (no BC stub)
- [ ] Replace the 5 import/export option reads/writes with term-meta loops
- [ ] Build the admin metabox (3 taxonomies × edit + add forms × save callback) — separate sub-task
- [ ] Remove `includes/vendor/taxonomy-images/`
- [ ] Remove `sermons.php:109` include line
- [ ] Document the legacy option + the deleted function in upgrade notes

### UAT smoke-test (deferred)

- [ ] **Pre-migration:** install Sermon Works on the validation site with existing term images via the old vendor library. Confirm images display.
- [ ] Trigger the migrator (force-bump db version, or call directly via debug page).
- [ ] **Post-migration:** confirm `wp_termmeta.sm_term_image_id` has the same term→attachment pairs as the old `sermon_image_plugin` option.
- [ ] Confirm series-image still displays on archive + single views.
- [ ] Edit an existing term, change the image via new metabox, save — verify term meta updated.
- [ ] Add a new term with an image — verify term meta written on create.
- [ ] WXR export / SB import / SE import: verify term-image associations round-trip via the new term-meta paths.
- [ ] Confirm `sermon_image_plugin` option still exists in DB (rollback path preserved).

---

## 4. #15 — Bundled CMB2 upgrade ✓

**Status: shipped session 14.** One commit:

- `17b090f` — `chore(vendor): upgrade CMB2 2.2.3.1 -> 2.11.0`. Verbatim swap of bundled v2.2.3.1 with the upstream v2.11.0 tarball. 85 files changed (~14K insertions / ~12K deletions); the LOC churn is the 2.3.0 architecture refactor (new `rest-api/`, `types/`, `shim/` subdirs) finally landing in our tree. Consumer code (`sm-cmb-functions.php` / `class-sm-api.php` / `class-sm-dates-wp.php`) untouched — Phase B not needed.

The locked plan called this "the most-coupled, biggest-blast-radius Stream B item"; the session-13 scope-out reframed it as a likely single-commit swap with optional Phase B follow-up. Execution confirmed the latter framing.

API surface verified stable in changelog walk 2.2.4 → 2.11.0:

- `new_cmb2_box()` / `add_field()` core signatures unchanged
- All 7 in-use field types still core types (`text`, `text_url`, `text_date_timestamp`, `select`, `wysiwyg`, `file`, `textarea_code`)
- `cmb2_admin_init` action present at `bootstrap.php:26`
- `cmb2_sanitize_{$type}` filter still wired at `CMB2_Field.php:507`
- `cmb2_override_{$key}_meta_save` / `cmb2_override_{$key}_meta_remove` filters at `CMB2_Field.php:355` / `:432`

**Two BC-relevant behaviour shifts** surfaced from the changelog walk — both backwards-compatible at the data layer; UAT smoke-test will confirm the visual changes are acceptable:

- **`textarea_code` → CodeMirror editor (since 2.4.0).** Affects the `sermon_video` field. Field still accepts and saves embed code verbatim; rendering switches from plain textarea to a syntax-highlighted code editor. Opt-out is available via `'options' => array( 'disable_codemirror' => true )` if needed; not used.
- **`text_url` defaults URLs to `https://` (since 2.10.0).** Affects the `sermon_video_link` field. Existing values stay as-is; on next save, a bare URL like `youtube.com/watch?v=…` becomes `https://youtube.com/watch?v=…`. Largely a positive — sermon-video links should be HTTPS anyway.

Verification:

- `php -l` clean on all 62 PHP files in the new vendor (PHP 8.5 lint)
- PHPCS unchanged on consumer files (vendor dir excluded via existing `*/vendor/*` pattern)
- Changelog walk confirms our pinned API surface remains stable

Issue `#15` tagged `awaiting-smoke-test`; smoke-test bundles with the pre-launch UAT per the deferral pattern.

**Plan-time inaccuracies caught at execution** (preserved for the historical record — Stream B is now complete with this shipment):

- Plan-doc § 4 said the loader was at `sermons.php:111`; actual is `:122`.
- Plan-doc § 4 dated v2.11.0 as "April 2025"; upstream's CHANGELOG.md header dates it `2.11.0 - 2024-04-02`, while the GitHub Release `published_at` field gives `2025-04-16T01:45:01Z`. The two upstream sources disagree by a year; v2.11.0 remains the tagged release either way.
- Plan-doc § 4 anticipated "~20K LOC" of vendor churn but framed it as a near-zero net diff. The actual diff is ~14K insertions / ~12K deletions across 85 files — closer to a doubling of vendor file count (102 → 364) because the 2.3.0 architecture refactor introduced three new subdirs (`rest-api/`, `types/`, `shim/`). All churn is internal; consumer-facing API stable.

### Original plan (preserved for reference)

[The original surface inventory + strategy + risk + checklist sections below describe the work that the single commit above executed.]

### TL;DR

Replace bundled CMB2 v2.2.3.1 (pre-Aug 2017) with upstream v2.11.0 (April 2025) wholesale. The locked plan called this "the most-coupled, biggest-blast-radius Stream B item"; session-13 scope-out found that's overstated. Vendor LOC churn is large (~20K LOC of internal CMB2), but our consumer surface is tiny (198 LOC across one file, plus 4 filter sites elsewhere) and uses only core CMB2 API patterns that have been stable since 1.x. Most likely shipping shape: **single-commit vendor swap**, with one prepared follow-up commit only if API drift turns up.

### Surface

- **Bundled**: `includes/vendor/CMB2/` — v2.2.3.1, 102 files, 20,540 LOC across PHP/JS/CSS/PNG. Loader at `sermons.php:111` (admin-only, inside `is_admin()` block — front-end blast radius is zero).
- **Live metabox consumer**: `includes/admin/sm-cmb-functions.php` — 198 LOC. Defines 2 metaboxes (`wpfc_sermon_details`, `wpfc_sermon_files`) totalling 9 fields. Registers 1 custom sanitiser (`cmb2_sanitize_text_number`).
- **Indirect consumers** (use CMB2 hooks without defining metaboxes):
  - `includes/class-sm-api.php:96-97` — `cmb2_override_{$key}_meta_remove` / `cmb2_override_{$key}_meta_save` filters (per-key meta-save suppression — REST handles persistence directly, CMB2 only handles admin-form rendering).
  - `includes/class-sm-dates-wp.php:46-47` — same filter pair on `sermon_date` (date-handling module manages persistence via WP date hooks).

### Upstream context (session-13 verification)

- **Latest tag**: v2.11.0 (2025-04-16). PHP 7.4+ minimum (Sermon Works floor is 8.1, so fine). WP tested up to 6.1.
- **Release cadence**: 9 minor releases between 2.2.3 and 2.11.0 over 8 years. Active upstream — not abandoned-vendor territory like taxonomy-images was. Maintainer Justin Sternberg.
- **Published security advisories**: zero on GitHub Security tab, but the 2.11 release notes flag one unstated hardening for `text_datetime_timestamp_timezone` field (DateTime unserialisation). We don't use that field type, so it doesn't apply — but it's a sign of ongoing security maintenance.

### API surface in use (pinned for upgrade verification)

| API | Usage count | Stability across 2.2.3 → 2.11 |
|---|---:|---|
| `new_cmb2_box( array )` with `id`, `title`, `object_types`, `context`, `priority`, `show_names` | 2 boxes | Stable public API since 1.x |
| `$box->add_field( array )` | 9 fields | Stable |
| Field type `text` | 2 | Core type, stable |
| Field type `text_url` | 1 | Core type, stable |
| Field type `text_date_timestamp` | 1 | Core type, stable |
| Field type `select` | 1 | Core type, stable |
| Field type `wysiwyg` | 1 | Core type, stable |
| Field type `file` | 3 | Core type, stable |
| Field type `textarea_code` | 1 | Core type, stable |
| Action `cmb2_admin_init` | 1 | Public API, stable |
| Filter `cmb2_sanitize_text_number` | 1 | Custom-named filter on stable `cmb2_sanitize_<type>` pattern |
| Filters `cmb2_override_{$key}_meta_save` / `cmb2_override_{$key}_meta_remove` | 4 sites | Public, stable since 2.x |

### Strategy — single-commit vendor swap most likely

1. Pull upstream tag `v2.11.0` (or latest tagged release at execution time) from `https://github.com/CMB2/CMB2`.
2. Replace `includes/vendor/CMB2/` wholesale with the upstream tag's contents.
3. Run `php -l` on every PHP file in the new vendor (sanity check on PHP 8.1 compatibility).
4. Run PHPCS on `includes/admin/sm-cmb-functions.php` and the four indirect-consumer filter sites — expect zero new sniff hits since our consumer code is unchanged.
5. Walk the upstream changelog (2.2.5 → 2.11.0) for breaking-change callouts. Specifically scan for: `add_field` argument renames, `cmb2_override_*` filter signature changes, field-type renames. Expected outcome: nothing applies.
6. Commit + push.
7. Tag `#15` `awaiting-smoke-test` for the pre-launch UAT.

### Phase B (only if needed)

If the upstream changelog walk surfaces actual breaks, ship a follow-up commit adapting the consumer code. This phase is structured but probably empty.

### Risk catalogue (session-13 refined)

- **Sermon-edit UX regression on form-submit save.** Each of the 9 fields needs to render correctly and round-trip on the validation site. Includes the Stream-A-hardened meta keys: `sermon_audio`, `sermon_video`, `sermon_video_link`, `sermon_notes`, `sermon_bulletin`, `sermon_description`, `bible_passage`, `sermon_date`, `_wpfc_sermon_duration`, plus the `wpfc_service_type` taxonomy field. **This is the headline UAT smoke-test cost.**
- **JS / CSS / asset path conventions.** CMB2 auto-enqueues its own JS+CSS+images (date-picker, repeater UX, file-upload modal triggers). Upstream may have refactored asset paths. Symptom on the validation site: a metabox renders without styling, or the date-picker doesn't open, or the file-upload modal fails. Mitigation: smoke-test catches it; the locator pattern in upstream's `CMB2_JS::register_scripts()` is the place to look if anything goes wrong.
- **REST + sanitiser interaction.** Stream A's per-key sanitiser at `SM_API::save_custom_data()` (commit `5925f7f`) reads from the same meta keys CMB2 writes. The keys are defined in *our* `add_field()` calls, not in CMB2's source — so the upgrade can't change them. Risk is functionally zero.
- **The `cmb2_override_*` filter contract.** Used in two places to suppress CMB2's automatic meta-save (we hand-roll it via REST + WP date hooks). The filter signature has been stable since 2.x; the docs name them in the public-API list. Risk is low. Verification: the CMB2 docs site (`https://cmb2.io/`) for the `cmb2_override_*` family reference.
- **PHP version compatibility.** Upstream 2.11 declares PHP 7.4+ floor; bundled was 5.4+. Sermon Works floor is 8.1. The new vendor will exercise more recent language features, which is fine.
- **No new attack surface introduced.** CMB2 is admin-only; upgrading doesn't expand the front-end. No new AJAX endpoints, no new REST routes — same shape as bundled.

### Execution checklist

- [ ] Confirm latest upstream tag at execution time (currently v2.11.0 as of 2025-04-16)
- [ ] Pull tagged release tarball; extract into a fresh `includes/vendor/CMB2/`
- [ ] `php -l` on every `*.php` file in the new vendor
- [ ] `vendor/bin/phpcs --standard=phpcs.xml.dist includes/admin/sm-cmb-functions.php includes/class-sm-api.php includes/class-sm-dates-wp.php` — expect no regressions
- [ ] Walk upstream changelog 2.2.5 → 2.11.0 for breaking changes affecting our API surface
- [ ] If breaks found: adapt consumer code as Phase B follow-up commit
- [ ] Update plan-doc § 4 with shipment preamble (commit hash + plan-time inaccuracies caught)
- [ ] Tag `#15` `awaiting-smoke-test` on the tracker; add deferral comment pointing to this plan section

### UAT smoke-test (deferred)

For each field in `sm-cmb-functions.php`, on the validation site:
- [ ] Field renders on the sermon-edit screen without console errors
- [ ] Field accepts input + saves on form submit
- [ ] Saved value displays correctly on re-loading the sermon-edit screen
- [ ] Saved value reads correctly on the public-facing sermon archive / single (where applicable)

Specific checks:
- [ ] `sermon_date`: date picker opens; selected date round-trips; auto-fill behaviour works
- [ ] `wpfc_service_type`: dropdown lists service-type terms; selection round-trips
- [ ] `sermon_audio`, `sermon_notes`, `sermon_bulletin` (all `file`): media-upload modal opens; uploaded file URL stored
- [ ] `sermon_description`: WYSIWYG editor loads; rich-text content round-trips
- [ ] `sermon_video`: textarea_code accepts embed code; saves verbatim
- [ ] `sermon_video_link`: text_url field rejects non-URL input
- [ ] `bible_passage`, `_wpfc_sermon_duration`: plain text fields round-trip
- [ ] REST POST to `/wp/v2/wpfc_sermon` with each meta key still hits Stream A's sanitiser correctly (the cmb2_override_* filters block CMB2 from writing in REST flow; Stream A's REST handler writes instead)

### Net impact

- Vendor LOC churn: ~20K LOC replaced. Net diff in our consumer code: probably zero or near-zero.
- Security gain: 9 versions of upstream development picked up, including unstated hardenings.
- Modernisation gain: Upstream 2.11 supports modern WP (6.1 tested), modern PHP, gutenberg-editor compat improvements, REST API integration enhancements. Future-proofs the admin UX surface.
- Estimated cost: 2-3 hours focused work for Phase A. Phase B (consumer adaptations) only if changelog walk surfaces breaks; expected empty.

---

## Execution dependency notes

Under the session-12 smoke-test-deferral pattern, none of the four items above is blocked by the absence of LocalWP — they ship on code review + diff + `php -l` + PHPCS, and the operational verification bundles into formal pre-launch UAT. The per-item "UAT smoke-test" checklists above describe what that validation pass needs to walk through for each item.

The dependency order is just "smallest blast radius first":

| Item | Why this position |
|---|---|
| #27 (shipped) | Smallest API surface, single security-driven upgrade |
| #35/#36 (next) | 30 LOC replacement, zero data migration, no new admin UI, two CVEs closed |
| #28 | Term-meta migration + new admin UI; data-loss risk if migrator wrong |
| #15 | Largest blast radius; touches every sermon-edit field |

The 17 Stream A `awaiting-smoke-test` issues are walked alongside Stream B items in the same pre-launch UAT — not separately.

---

## Coda

This doc is meant to be picked up cold next session. If anything here looks wrong on second read, treat it as wrong — the plan was sketched in one pass and refined session-by-session as Stream B items execute. When the validation site is up and you're staring at the actual data, validate before executing.

End of plan.
