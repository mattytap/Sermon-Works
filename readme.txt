=== Mattytap Sermons ===
Contributors: mattytap
Tags: church, sermon, podcast, preaching, audio
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 3.1-rc2
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Faithful to the source. A maintained restoration of Sermon Manager — publish, organise, and podcast sermons on a WordPress site.

== Description ==

Mattytap Sermons lets a church publish sermons online with the structure they actually need: speakers, series, topics, books of the Bible, and service types — all as proper WordPress taxonomies, all searchable, all filterable. Add audio and video, attach handouts (PDF, DOC, PPT, anything), embed video from YouTube or Vimeo, and ship a fully-featured iTunes-compatible podcast feed for the whole library or for any individual series, speaker, topic, or book.

Mattytap Sermons is a maintained restoration of [Sermon Manager](https://wordpress.org/plugins/sermon-manager-for-wordpress/) by WP for Church. The original plugin was last published to WordPress.org in September 2024 (version 2.30.0); the upstream maintainer's web presence is no longer reachable, and several publicly-disclosed CVEs remain unpatched in 2.30.0 and earlier. Mattytap Sermons is the successor line. Existing Sermon Manager installs (including 2.30.0) can switch to Mattytap Sermons as a drop-in replacement: the database schema, custom post type (`wpfc_sermon`), taxonomies (`wpfc_preacher`, `wpfc_sermon_series`, etc.), all `wpfc_*` option keys, six core shortcodes, and view templates are preserved. Multi-attachments for notes and bulletin, post-body editor support, and Twenty Twenty-Four theme wrapper are cherry-picked from upstream 2.30.0.

= What Mattytap Sermons preserves =

* Same shortcodes (`[sermons]`, `[sermon_images]`, `[list_podcasts]`, `[list_sermons]`, `[latest_series]`, `[sermon_sort_fields]`)
* Same custom post type and taxonomies — existing sermon data carries over without import
* Same template files in `views/` — existing theme overrides continue to work
* Same Bib.ly Bible-reference integration
* Same iTunes-compatible podcast feeds

= What's new in Mattytap Sermons =

* Active security maintenance, with [private vulnerability reporting](https://github.com/mattytap/Mattytap-Sermons/security) for responsible disclosure
* Modernised for PHP 8.1+ and WordPress 6.0+
* Bundled outdated third-party dependencies removed or upgraded (CMB2, wp-background-processing, the entry-views polyfill, taxonomy-images)
* Continuing development on [GitHub](https://github.com/mattytap/Mattytap-Sermons)

= Popular shortcodes =

* `[sermons]` — list the 10 most recent sermons
* `[sermons per_page="20"]` — list the 20 most recent sermons
* `[sermon_images]` — list all sermon series with associated image in a grid
* `[list_podcasts]` — list available podcast services with large buttons
* `[list_sermons]` — list all series or speakers in a simple unordered list
* `[latest_series]` — display information about the latest sermon series, including the image, title, and description
* `[sermon_sort_fields]` — dropdown selections to navigate to sermons by series or speaker

== Installation ==

1. Use the "Add New" button in the Plugins section of your WordPress dashboard. Search for "Mattytap Sermons".
2. Activate the plugin.
3. Add a sermon through the dashboard.
4. Display sermons on the front end via shortcode (`[sermons]` in any page or post), or by visiting `https://yourdomain.example/sermons` (with pretty permalinks enabled) or `https://yourdomain.example/?post_type=wpfc_sermon` (without).

= Migrating from Sermon Manager =

If you have an existing Sermon Manager install, install and activate Mattytap Sermons, then deactivate Sermon Manager. All your sermons, speakers, series, topics, and settings carry over without further action — the two plugins use the same database schema. Don't run both at once; the schema overlap will cause conflicts.

= Migrating from Sermon Browser or Series Engine =

Mattytap Sermons inherits Sermon Manager's import functionality for these older sermon plugins. Once Mattytap Sermons is activated, the importer is available under Tools → Import.

== Frequently Asked Questions ==

= How do I display sermons on the front end? =

Use the shortcode `[sermons]` in any page or post, or visit `https://yourdomain.example/sermons` (with pretty permalinks enabled) or `https://yourdomain.example/?post_type=wpfc_sermon` (without).

= How do I create a menu link to the sermons archive? =

Go to Appearance → Menus. In "Custom Links" add `https://yourdomain.example/?post_type=wpfc_sermon` as the URL and "Sermons" as the label. Click "Add to Menu".

= I'm coming from Sermon Manager. Will my sermons survive? =

Yes. Mattytap Sermons uses the same database schema, custom post type, taxonomies, and option keys as Sermon Manager. Activate Mattytap Sermons, deactivate Sermon Manager, and existing sermons keep working. Don't run both at once — the overlapping schema will conflict.

= How do I report a bug or request a feature? =

[GitHub Issues](https://github.com/mattytap/Mattytap-Sermons/issues). For security issues, please use [private vulnerability reporting](https://github.com/mattytap/Mattytap-Sermons/security/advisories/new) rather than public issues.

= Is there a paid version or commercial support? =

No. Mattytap Sermons is GPLv2 free software with no paid tier, no premium add-ons, and no commercial support. Bug fixes, feature requests, and translation contributions are welcome through GitHub.

== Screenshots ==

1. Sermon Details
2. Sermon Files

== Credits ==

Mattytap Sermons is a restoration of [Sermon Manager for WordPress](https://wordpress.org/plugins/sermon-manager-for-wordpress/), originally by WP for Church (Jason Westbrook and contributors). The full upstream contributor list is recorded in [CONTRIBUTORS.md](https://github.com/mattytap/Mattytap-Sermons/blob/main/CONTRIBUTORS.md). Translations were originally contributed by GITNE (German, Polish), Gilles Pilloud (French), and the Dutch translation behind v2.15.13.

== Changelog ==

= 3.1-rc2 =

Three regressions surfaced during 3.1-rc1 LocalWP canary verification.

* Plyr 3.5+ removed `embed.setCurrentTime()` from the YouTube embed surface; the seek-on-load handler in `assets/js/plyr.js` was still calling it directly after setting `instance.currentTime` via Plyr's high-level API. Dropped the redundant embed-direct call: the high-level setter handles seek normalisation across HTML5, YouTube, and Vimeo in 3.7.x.
* The F-escape sweep's `sm_template_allowed_html()` helper didn't whitelist `<source>` as an allowed child of `<audio>` and `<video>`, so every `<source>` child was stripped silently when the audio/video render passed through `wp_kses()`. Audio and video elements rendered as empty shells with no playable source. Added `<source>` with `src`, `type`, `media`, `srcset`.
* The same helper covered `<select>` for the `onchange` attribute only and didn't whitelist `<option>`, `<form>`, or the rest of `<select>`'s attribute set. The sermon archive sort widget and the five taxonomy templates rendered as bare `<select onchange="...">` shells with no `<option>` tags. Added the form-element family.

= 3.1-rc1 =

First release-candidate of the renamed line. Sermon Works has been renamed to Mattytap Sermons under the WordPress.org slug `mattytap-sermons` following plugin-team trademark guidance against the prior name. Strapline updated to "Faithful to the source." Same caretaker, same codebase, same migration path; existing 3.0.x installs upgrade in place.

This release also closes the ten technical items the WordPress.org reviewer's automated pre-review flagged against the 3.0.2 submission:

* Removed the `load_plugin_textdomain` call (WordPress.org auto-loads translations since WP 4.6).
* Replaced PHP 8.2-deprecated `utf8_encode` with `mb_convert_encoding` in the WXR exporter's UTF-8 fallback branch.
* Added `sanitize_text_field( wp_unslash() )` on the defence-in-depth `$_REQUEST['_wpnonce']` read in the settings save-handler.
* Added `esc_url_raw( wp_unslash() )` on the `$_SERVER['REQUEST_URI']` read in the import form action.
* Removed the unpaired `ob_start()` at `admin_init` priority 1. The other twelve `ob_start()` sites in the codebase are properly paired with `ob_end_flush()` and continue to work as before.
* Annotated 45 reviewer-flagged sites carrying legacy upstream `wpfc_*` / `wp_sm_*` / `sm_*` prefixes with `phpcs:ignore` plus rationale comments. The prefixes themselves are preserved deliberately for drop-in compatibility with Sermon Manager.
* Applied `wp_kses()` and friends at 40 sites across 11 view templates in `views/`. A small helper `sm_template_allowed_html()` extends `wp_kses_allowed_html('post')` for Plyr `data-*` attributes and audio/video element attributes the default kses table strips. Five genuinely-unfixable sites (CDATA-wrapped strings and attribute-fragment echoes) are left annotated with rationale.
* Wrapped editor-pane content in `wp_kses_post()` in the `the_content` filter callback. The `[list_podcasts]` shortcode is now internationalised via `__()` / `sprintf()` and its return value wrapped in `wp_kses_post()` so static scanners see an explicit escaper at the function boundary.
* Consolidated the Plyr enqueue path in `sermons.php`: replaced two `wp_localize_script` calls with `wp_add_inline_script`; dropped `maybe_print_cloudflare_plyr` and its manual `<script data-cfasync="false">` echo in favour of a `script_loader_tag` filter that injects the attribute on the two Plyr handles. Hoisted the dashboard "At a Glance" widget's inline `<style>` block to `wp_add_inline_style` against the dashicons handle. The two `UNCODE.initHeader()` inline-script sites in the Uncode theme branch remain inline with `phpcs:ignore` plus rationale: those calls fire at a mid-body DOM position the Uncode theme's header initialisation depends on.
* Refreshed bundled vendor libraries: Plyr 3.4.7 → 3.7.8 (verbatim swap from the upstream npm tarball; no JS-API breaking changes; the 3.7.x line includes preview-thumbnail, focus-visible, and border-radius CSS updates which are absorbed automatically because we ship the precompiled CSS from the same release). wp-color-picker-alpha 2.1.3 → 3.0.4 (verbatim swap from upstream `main`; the 3.0.x line is a full rewrite that drops the WordPress 5.5-removed `wpColorPickerL10n` global dependency. No runtime delta in this plugin because the script is dead code: no CMB2 colorpicker field in the codebase sets `'alpha' => true`).

Drop-in compatibility ethic preserved: the `wpfc_sermon` custom post type, the `wpfc_preacher` and `wpfc_sermon_series` taxonomies, and all `wpfc_*` option keys are unchanged. Existing Sermon Manager (or 3.0.x Sermon Works) installs continue to migrate without data loss.

= 3.0.2 =

Editor-pane render fix following the 3.0.1 cherry-pick of `'editor'` into the `wpfc_sermon` post type's `supports` array. In 3.0.1 the WordPress block (or classic) editor pane was visible on the edit-sermon screen but content typed there did not reach the front-end: the `add_wpfc_sermon_content` callback that drives the `the_content` filter for sermon posts replaced the post body entirely with the sermon-template render, discarding `post_content`. Site administrators reaching for the editor pane first (the natural place for any WordPress user) would publish a sermon and see their typed content vanish on the public page.

3.0.2 closes this. The callback now preserves the incoming `post_content` and, on singular sermon views where it is non-empty, appends it after the sermon-template render in a `<div class="wpfc-sermon-editor-content">` wrapper that themes can target. Existing sermons (whose body content lives in the `sermon_description` post meta key, not in `post_content`) are unaffected: their `post_content` is empty, the conditional skip fires, and the front-end render is byte-identical to 3.0.1. New sermons authored via the editor pane now render their content alongside the legacy `sermon_description` rendering.

No security delta against 3.0.1; no API surface changes; no data migration. Single-file change in `includes/sm-template-functions.php`.

= 3.0.1 =

Drop-in compatibility cherry-picks from upstream Sermon Manager 2.30.0 (the last WP.org-shipped version of the original plugin), informed by a 10-surface compatibility audit against the 2.30.0 baseline (`.restoration/DROP-IN-AUDIT.md`):

* Multi-attachments for notes and bulletin: the front-end view template now renders the `sermon_notes_multiple` and `sermon_bulletin_multiple` arrays alongside the singular keys. Existing 2.30.0 sites that ran the upstream multi-attachments migration have data in the `_multiple` keys; this restores their visibility on the front-end. Two new `file_list` CMB2 fields ("Sermon Notes (multiple)", "Bulletin (multiple)") are added to the Sermon Files metabox so Sermon Works admins can also create new multi-attachments themselves.
* Post-body editor support: `'editor'` is added to the `wpfc_sermon` post type's `supports` array, enabling the WordPress block (or classic) editor on the edit-sermon screen. The Sermon Works rendering model continues to use `sermon_description` plus the `the_content` filter; existing sermons render unchanged. New sermons created post-3.0.1 can populate `post_content` via the editor pane in addition to `sermon_description`.
* Twenty Twenty-Four theme wrapper: the theme-specific archive wrapper now has a balanced `twentytwentyfour` case opening `<div class="wp-block-group has-global-padding is-layout-constrained ..."><div id="primary"><main class="site-main wpfc-sermon-container wpfc-twentytwentyfour ...">` and the matching close in the wrapper-end partial. Upstream 2.30.0 ships unbalanced (no matching close); Sermon Works ships balanced.

Three upstream changes from 2.30.0 are deliberately not cherry-picked, with rationale documented in `.restoration/DROP-IN-AUDIT.md`:

* The full editor-support cluster (REST surface change, `the_content` filter disablement, AJAX `update_sermon_posts` migration with "Sync Now" button): the upstream rendering-model swap from `sermon_description` to `post_content` is invasive, partial cherry-picks risk broken rendering, and the upstream migration has quality issues (missing capability check, debug residue, no rollback UI). Sermon Works keeps the sermon_description-based rendering model.
* The `[latest_sermon]` shortcode: the upstream handler has three known bugs (orderby attribute silently ignored due to a typo in query-args assembly, duplicate HTML id on a nested div, static-property side effects that persist across requests). Use `[sermons per_page="N" order="DESC" orderby="date"]` for the same effect.

Framing correction in README and readme.txt: the original Sermon Manager line was actively maintained on WordPress.org through September 2024 (last published version 2.30.0). The "abandoned since 2019" framing in pre-3.0.1 docs was based on the GitHub state and missed the WP.org line.

= 3.0 =

3.0 stable cut. No runtime changes against rc6 — this entry promotes the release-candidate cycle to stable, capping the restoration arc that began in rc1.

= 3.0-rc6 =

CI gate clearance for WordPress.org submission, no runtime changes against rc5. The Plugin Check workflow now passes cleanly on `main`. The WXR exporter port (`includes/admin/export/class-sm-export-sm.php`) is excluded at workflow level: it is a near-verbatim derivative of WordPress core's `wp-admin/includes/export.php`, and the project's `phpcs.xml.dist` registration of `wxr_cdata` as `customAutoEscapedFunctions` is honoured by local PHPCS but not by the Plugin Check action. Targeted `phpcs:ignore` annotations carrying per-site rationale clear the remaining submission-blocking sniffs: the file-system reads in the PNG/JPEG dimension sniffers (`sm_get_png_dimensions`, `sm_get_jpeg_dimensions`), the inline Cloudflare-bypass `<script>` tag in the Plyr enqueuer, and the transient-cleanup direct-DB call. The audit-deferred warning tail (REST nonce annotations, superglobal-input sanitisation, db-caching, footer-flag enqueues) rides this submission with `ignore-warnings: true` on the action's pass/fail logic; warnings still surface in the JSON report for review.

= 3.0-rc5 =

WordPress.org submission-ready cut, held back behind one more RC for maintainer verification before the 3.0 stable tag flips. Two work strands gating the stable tag are now closed.

Plugin Check "Bucket B" output-escaping sweep (317 sites across 28 files): every `echo` and `<?= ?>` site that the WordPress.org reviewer ruleset flagged as `WordPress.Security.EscapeOutput.OutputNotEscaped` now uses a context-appropriate escaper. `esc_html` for text content between tags, `esc_attr` for HTML attribute values, `esc_url` for URLs, `(int)` cast for numeric IDs, `wp_kses_post` for already-built HTML fragments, `esc_textarea` for textarea content, and `esc_xml`-equivalent (`esc_html` plus CDATA wrapping where appropriate) for XML output in the WXR exporter and the iTunes podcast feed. Where `wp_kses_post` would strip needed markup (audio/video player iframes, Uncode theme integration), per-line `phpcs:ignore` annotations carry the rationale rather than weakening the sniff. The WXR exporter also picks up `wxr_cdata` as a registered custom-auto-escaped function in the project's PHPCS config.

Pre-3.0 small fixes pulled forward from the pre-submission audit:

* Fixed a runtime PHP 8.0+ warning (`foreach() argument must be of type array|object, string given`) in `sm-core-functions.php:663`. The `apply_filters( 'sermon-images-get-the-terms', '', ... )` default value was an empty string; foreach over a string fails on PHP 8.0+. Default coerced to `array()` with a belt-and-braces `(array)` cast on the result.
* Added the `defined( 'ABSPATH' ) || exit;` guard to `includes/class-sm-roles.php` (missed in the rc4 sweep).
* Fixed two PHP 8.2 deprecation warnings: `"[${time}]"` -> `"[{$time}]"` in the Sermon Browser and Sermon Manager importers. Plugin floor stays at PHP 8.1 where this isn't deprecated, but WordPress.org test environments cover 8.2 and 8.3.
* Bug fix in the recent-sermons widget admin form: textarea contents for `before_widget` / `after_widget` were being echoed as kses-stripped HTML, which renders as visible literal text in a textarea. Now `esc_textarea`, which entity-encodes for the textarea context. Round-trip edit workflow now matches what the user typed at save.

= 3.0-rc4 =

WordPress.org Plugin Check sweep, "Bucket A" mechanical fixes (~70 sites across 10 commits):

* Plugin header: added `License: GPLv2` and `License URI` lines for WP.org guideline compliance.
* Direct file access protection: added `defined( 'ABSPATH' ) or die;` guards to 11 view templates (`views/*.php`).
* Replaced `date()` with `gmdate()` (12 sites) or `wp_date()` (2 user-facing admin column sites) in 9 files. PHP `date()` is affected by `date_default_timezone_set()` and unsafe inside WordPress. Incidentally corrects a latent bug in the RSS feed (`pubDate` and `lastBuildDate` previously claimed `+0000` UTC while passing server-local timestamps).
* Replaced `parse_url()` with `wp_parse_url()` (8 sites). WP wrapper suppresses warnings on malformed URLs.
* Replaced `strip_tags()` with `wp_strip_all_tags()` (7 sites). WP wrapper additionally strips script/style tag contents.
* Replaced `@unlink()` with `wp_delete_file()` (6 sites in the WXR importer). WP wrapper respects the `wp_delete_file` filter.
* Replaced `rand()` with `wp_rand()` (1 site). WP wrapper produces better random numbers.
* i18n: added the `'sermon-works'` text-domain argument to 10 `__()` / `esc_html__()` calls that previously omitted it (Plugin Check `MissingArgDomain`).
* i18n: corrected 9 calls in the WXR importer that used `'wordpress-importer'` instead of `'sermon-works'` as the text-domain (Plugin Check `TextDomainMismatch`).
* i18n: added `/* translators: */` comments above 3 placeholder strings (Plugin Check `MissingTranslatorsComment`).

Tier 2 substantive Plugin Check items (file system operations, prepared SQL audits, enqueued scripts, output escaping) are deferred to a future release; see [`.restoration/plugin-check-summary.md`](https://github.com/mattytap/Mattytap-Sermons/blob/main/.restoration/plugin-check-summary.md) for the full triage.

= 3.0-rc3 =

Bug fix for installs on case-sensitive filesystems (Linux hosts including most production WordPress hosting):

* Rename bundled `includes/vendor/CMB2/includes/CMB2_hookup.php` to `CMB2_Hookup.php` so CMB2's class autoloader can find the file. Without this, activating Sermon Works on a Linux host produced a fatal "class CMB2_Hookup not found" error. The casing inconsistency was inherited from the upstream CMB2 v2.11.0 vendor bundle and only manifested on case-sensitive filesystems; Windows and macOS users would not have noticed.

= 3.0-rc2 =

Salvaged bug-fix PRs from the upstream open-PR backlog, ingested with original authors preserved as git commit authors:

* Fix shortcode pagination on static front page (upstream PR [#274](https://github.com/WP-for-Church/Sermon-Manager/pull/274) by [@brianfreytag](https://github.com/brianfreytag)).
* Defensive guard around the import-message cleanup loop in `sermons.php` (upstream PR [#292](https://github.com/WP-for-Church/Sermon-Manager/pull/292) by [@tstephen](https://github.com/tstephen)). Prevents a PHP 8.x fatal when `_sm_import_se_messages` / `_sm_import_sb_messages` options are non-array.
* Add French Bible versions (Louis Segond 1910, Segond 21) to the verse-popup settings dropdown (upstream PR [#264](https://github.com/WP-for-Church/Sermon-Manager/pull/264) by [@rjorel](https://github.com/rjorel)).

= 3.0-rc1 — Sermon Works restoration =

This release renames the plugin from Sermon Manager to Sermon Works (text domain `sermon-works`) and ships the in-place restoration completed across early 2026:

* Security: 25 issues resolved from a focused audit, including three CVEs filed against the upstream Sermon Manager codebase — CVE-2025-12368 (stored XSS in `[sermon-views]`), CVE-2025-63002 (unauthenticated missing authorisation), and CVE-2025-63000 (stored XSS in `[list_sermons]` / `[sermon_images]`). All three sinks have been removed or hardened in Sermon Works.
* Modernised: PHP 8.1+ and WordPress 6.0+ floor; deprecated WordPress function calls replaced with current equivalents; dead PHP 5.3 compatibility paths and stale upstream marketing links removed.
* Bundled vendor cleanup: CMB2 upgraded v2.2.3.1 → v2.11.0; wp-background-processing upgraded 1.0.1 → 1.4.0; bundled `entry-views.php` polyfill removed (replaced with a small WordPress-native counter); `taxonomy-images` library removed (migrated to core term_meta + a dedicated metabox).
* Database, shortcodes, post types, taxonomies, and template files unchanged — existing Sermon Manager installs migrate as a drop-in replacement.

= Older releases =

For Sermon Manager release history (2.13 through 2.15.16, dating from 2015–2018), see [`changelog.txt`](https://github.com/mattytap/Mattytap-Sermons/blob/main/changelog.txt) in the plugin directory.

== Upgrade Notice ==

= 3.1-rc2 =

Three rc1 playback and widget regressions fixed: YouTube embed seek-on-load no longer throws after the Plyr 3.7 upgrade; `<audio>` / `<video>` elements again include their `<source>` children; the sermon archive sort widget renders its `<option>` tags. No data migration.

= 3.1-rc1 =

Plugin renamed from Sermon Works to Mattytap Sermons under the WordPress.org slug `mattytap-sermons`. Database, content, post type, taxonomies, and front-end render unchanged. Migrate by activating Mattytap Sermons and deactivating Sermon Works. Also closes ten technical items raised by the WordPress.org plugin reviewer's automated pre-review of 3.0.2.

= 3.0.2 =

Editor-pane render fix following 3.0.1. Content typed into the WordPress editor on the edit-sermon screen now renders on the front-end alongside the existing `sermon_description` rendering. Existing sermons unaffected; no data migration.

= 3.0.1 =

Drop-in compatibility cherry-picks from upstream 2.30.0: multi-attachments rendering for notes and bulletin (closes a render gap on sites that ran the upstream multi-attachments migration), post-body editor support, and Twenty Twenty-Four theme wrapper. Database and front-end render unchanged for existing sites.

= 3.0 =

Plugin renamed from Sermon Manager to Sermon Works; database and content unchanged. Migrate by activating Sermon Works and deactivating Sermon Manager. Includes fixes for three CVEs filed against the upstream Sermon Manager codebase.
