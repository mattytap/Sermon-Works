=== Sermon Works ===
Contributors: mattytap
Tags: church, sermon, podcast, preaching, audio
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 3.0-rc6
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sola fide. Fully featured. A maintained restoration of Sermon Manager — publish, organise, and podcast sermons on a WordPress site.

== Description ==

Sermon Works lets a church publish sermons online with the structure they actually need: speakers, series, topics, books of the Bible, and service types — all as proper WordPress taxonomies, all searchable, all filterable. Add audio and video, attach handouts (PDF, DOC, PPT, anything), embed video from YouTube or Vimeo, and ship a fully-featured iTunes-compatible podcast feed for the whole library or for any individual series, speaker, topic, or book.

Sermon Works is a maintained restoration of [Sermon Manager](https://wordpress.org/plugins/sermon-manager-for-wordpress/) by WP for Church, which has been unmaintained for over five years and has known unpatched security issues, including three publicly-disclosed CVEs. Existing Sermon Manager installs can switch to Sermon Works as a drop-in replacement: the database schema, custom post type (`wpfc_sermon`), taxonomies (`wpfc_preacher`, `wpfc_sermon_series`, etc.), and all `wpfc_*` option keys are unchanged.

= What Sermon Works preserves =

* Same shortcodes (`[sermons]`, `[sermon_images]`, `[list_podcasts]`, `[list_sermons]`, `[latest_series]`, `[sermon_sort_fields]`)
* Same custom post type and taxonomies — existing sermon data carries over without import
* Same template files in `views/` — existing theme overrides continue to work
* Same Bib.ly Bible-reference integration
* Same iTunes-compatible podcast feeds

= What's new in Sermon Works =

* Active security maintenance, with [private vulnerability reporting](https://github.com/mattytap/Sermon-Works/security) for responsible disclosure
* Modernised for PHP 8.1+ and WordPress 6.0+
* Bundled outdated third-party dependencies removed or upgraded (CMB2, wp-background-processing, the entry-views polyfill, taxonomy-images)
* Continuing development on [GitHub](https://github.com/mattytap/Sermon-Works)

= Popular shortcodes =

* `[sermons]` — list the 10 most recent sermons
* `[sermons per_page="20"]` — list the 20 most recent sermons
* `[sermon_images]` — list all sermon series with associated image in a grid
* `[list_podcasts]` — list available podcast services with large buttons
* `[list_sermons]` — list all series or speakers in a simple unordered list
* `[latest_series]` — display information about the latest sermon series, including the image, title, and description
* `[sermon_sort_fields]` — dropdown selections to navigate to sermons by series or speaker

== Installation ==

1. Use the "Add New" button in the Plugins section of your WordPress dashboard. Search for "Sermon Works".
2. Activate the plugin.
3. Add a sermon through the dashboard.
4. Display sermons on the front end via shortcode (`[sermons]` in any page or post), or by visiting `https://yourdomain.example/sermons` (with pretty permalinks enabled) or `https://yourdomain.example/?post_type=wpfc_sermon` (without).

= Migrating from Sermon Manager =

If you have an existing Sermon Manager install, install and activate Sermon Works, then deactivate Sermon Manager. All your sermons, speakers, series, topics, and settings carry over without further action — the two plugins use the same database schema. Don't run both at once; the schema overlap will cause conflicts.

= Migrating from Sermon Browser or Series Engine =

Sermon Works inherits Sermon Manager's import functionality for these older sermon plugins. Once Sermon Works is activated, the importer is available under Tools → Import.

== Frequently Asked Questions ==

= How do I display sermons on the front end? =

Use the shortcode `[sermons]` in any page or post, or visit `https://yourdomain.example/sermons` (with pretty permalinks enabled) or `https://yourdomain.example/?post_type=wpfc_sermon` (without).

= How do I create a menu link to the sermons archive? =

Go to Appearance → Menus. In "Custom Links" add `https://yourdomain.example/?post_type=wpfc_sermon` as the URL and "Sermons" as the label. Click "Add to Menu".

= I'm coming from Sermon Manager. Will my sermons survive? =

Yes. Sermon Works uses the same database schema, custom post type, taxonomies, and option keys as Sermon Manager. Activate Sermon Works, deactivate Sermon Manager, and existing sermons keep working. Don't run both at once — the overlapping schema will conflict.

= How do I report a bug or request a feature? =

[GitHub Issues](https://github.com/mattytap/Sermon-Works/issues). For security issues, please use [private vulnerability reporting](https://github.com/mattytap/Sermon-Works/security/advisories/new) rather than public issues.

= Is there a paid version or commercial support? =

No. Sermon Works is GPLv2 free software with no paid tier, no premium add-ons, and no commercial support. Bug fixes, feature requests, and translation contributions are welcome through GitHub.

== Screenshots ==

1. Sermon Details
2. Sermon Files

== Credits ==

Sermon Works is a restoration of [Sermon Manager for WordPress](https://wordpress.org/plugins/sermon-manager-for-wordpress/), originally by WP for Church (Jason Westbrook and contributors). The full upstream contributor list is recorded in [CONTRIBUTORS.md](https://github.com/mattytap/Sermon-Works/blob/main/CONTRIBUTORS.md). Translations were originally contributed by GITNE (German, Polish), Gilles Pilloud (French), and the Dutch translation behind v2.15.13.

== Changelog ==

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

Tier 2 substantive Plugin Check items (file system operations, prepared SQL audits, enqueued scripts, output escaping) are deferred to a future release; see [`.restoration/plugin-check-summary.md`](https://github.com/mattytap/Sermon-Works/blob/main/.restoration/plugin-check-summary.md) for the full triage.

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

For Sermon Manager release history (2.13 through 2.15.16, dating from 2015–2018), see [`changelog.txt`](https://github.com/mattytap/Sermon-Works/blob/main/changelog.txt) in the plugin directory.

== Upgrade Notice ==

= 3.0 =

Plugin renamed from Sermon Manager to Sermon Works; database and content unchanged. Migrate by activating Sermon Works and deactivating Sermon Manager. Includes fixes for three CVEs filed against the upstream Sermon Manager codebase.
