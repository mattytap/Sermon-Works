# Drop-in compatibility audit, Sermon Works `main` vs Sermon Manager 2.30.0

Audit started 2026-05-09. Stage 1 only — classification, no remediation code.

## Why 2.30.0 is the canonical baseline

2.30.0 is the version actually shipped to WordPress.org users (SVN at <https://plugins.svn.wordpress.org/sermon-manager-for-wordpress/tags/>; SVN root Last-Modified 26 September 2024). 2.15.16 is the GitHub-only version Sermon Works forked from and was never published to WP.org. WP-for-Church's web presence (`wpforchurch.com`) is now 404, so 2.30.0 is also where the original Sermon Manager line ends.

Real users sitting on Sermon Manager today have 2.30.0 (or an earlier WP.org-shipped tag — 2.16.3, 2.17.x, 2.18.0, 2.19.0, 2.20.0). When they switch to Sermon Works, the relevant comparison surface is what 2.30.0 emitted, not what 2.15.16 emitted.

## Snapshots

- `.restoration/upstream-2.30.0-snapshot/2.30.0/sermon-manager-for-wordpress/` — what real users have. Primary reference for this audit.
- `.restoration/upstream-2.30.0-snapshot/2.15.15/sermon-manager-for-wordpress/` — last 2.15.x version published to SVN. Useful for "what was on WP.org before the 11 post-2.15 versions started landing".
- `.restoration/upstream-2.15.16-snapshot/` — partial GitHub-baseline (the file Sermon Works forked from). Less load-bearing here.

File-level scope of the 2.15.15 → 2.30.0 gap (per `diff -rq`): 1 added (`README.md`), 0 removed, 45 modified. Concentrated in admin classes, core classes, function libraries, 7 view templates, vendor partial, Plyr asset bundle, plus `readme.txt` + `changelog.txt` + `sermons.php`.

## Bucket scheme

For every divergence found:

1. **Sermon Works has it, 2.30.0 doesn't.** Our addition was a justified security fix or modernisation. Document, don't change.
2. **Both have it, but in different shapes.** Classify why (bug fix, security, modernisation, evolution); decide whether to align or document.
3. **2.30.0 has it, Sermon Works doesn't.** Cherry-pick candidate for 3.0.1.
4. **2.30.0 has it, Sermon Works doesn't, but cherry-picking conflicts with our security or modernisation work.** Document the gap with rationale.

## Surfaces

1. CPT registration (`wpfc_sermon`)
2. Taxonomies (`wpfc_preacher`, `wpfc_sermon_series`, `wpfc_bible_book`, `wpfc_sermon_topics`, `wpfc_service_type`)
3. Meta and option keys (`wpfc_*` / `sermon_*`)
4. Capabilities and roles
5. REST API exposure
6. Shortcodes (handlers + emitted DOM)
7. CSS class names emitted
8. View templates in `views/`
9. `the_content` filter usage
10. wp-admin UI surfaces

## Project-wide notes (apply across all surfaces)

- **Text-domain rename.** Sermon Works strings use `'sermon-works'`; 2.30.0 uses `'sermon-manager-for-wordpress'`. Deliberate rename shipped earlier in the restoration (commit `1b68ac8` and four siblings) for the WP.org submission. Justified by the new plugin slug; not a Bucket 3 candidate. Mentioned once here so per-surface tables don't repeat it.

---

## Surface 1: CPT registration (`wpfc_sermon`)

Compared `includes/class-sm-post-types.php` line by line. The two `register_post_type('wpfc_sermon', ...)` argument arrays are byte-identical apart from the text domain (project-wide note above) and one entry in `supports`.

| ID | Bucket | Location | Description | User-visible effect | Action |
|---|---|---|---|---|---|
| F1.1 | 3 | `includes/class-sm-post-types.php:316–327` | 2.30.0's `supports` array includes `'editor'` (line 327); Sermon Works' does not. | On 2.30.0, the WP block editor is enabled for `wpfc_sermon` and the post body is editable; on Sermon Works, sermons have no body field unless a workaround mu-plugin re-adds `'editor'` (the maintainer's legacy site has exactly that). | Cherry-pick: add `'editor'` to the supports array. One-line edit. |

Identical between the two trees: `public`, `show_ui`, `capability_type='wpfc_sermon'`, `capabilities` array, `map_meta_cap`, `publicly_queryable`, `exclude_from_search=false`, `show_in_menu`, `menu_icon='dashicons-sermon-manager'`, `hierarchical=false`, `rewrite` (slug + with_front=false), `query_var`, `show_in_nav_menus`, `show_in_rest=true`, `has_archive`, all 19 label strings (modulo text domain), and the rest of the `supports` items (`title, thumbnail, publicize, wpcom-markdown, comments, entry-views, elementor, excerpt, revisions, author`).

The `'editor'` addition between 2.15.15 and 2.30.0 is a single-line append in the `supports` array. No accompanying changes to capabilities, REST exposure, or rewrite rules. Cherry-pick risk: very low — adding `'editor'` only enables the body field; existing sermons stored under 2.15.16-baseline have no `post_content` populated, so they render unchanged on the front-end (templates that read `the_content` will simply emit nothing if `post_content` is empty). New sermons created post-cherry-pick can have body text. Block-editor vs classic-editor choice is a separate WP-level concern (handled by `use_block_editor_for_post_type` filter or `Disable Gutenberg` plugin).

---

## Surface 2: Taxonomies

`register_taxonomy` calls live only in `includes/class-sm-post-types.php` in both trees. Five taxonomies registered, in the same order, with the same arguments. Compared line by line.

| Taxonomy | Lines (both trees) | Result |
|---|---|---|
| `wpfc_preacher` | 56–97 | Identical except text domain |
| `wpfc_sermon_series` | 99–133 | Identical except text domain |
| `wpfc_sermon_topics` | 135–169 | Identical except text domain |
| `wpfc_bible_book` | 171–205 | Identical except text domain |
| `wpfc_service_type` | 207–248 | Identical except text domain |

All five share: `hierarchical=false`, `show_ui=true`, `query_var=true`, `show_in_rest=true`, `rewrite` slug from `sm_get_permalink_structure()`, `with_front=false`, `capabilities` array using `'manage_wpfc_categories'` for all four operations.

**No Bucket 3 candidates from this surface.**

Note for the future-state read: 2.30.0 keeps `wpfc_sermon_series` as `hierarchical=false`. Series-with-parent is therefore not supported in either tree. Outside the scope of a drop-in audit.

---

## Surface 3: Meta and option keys

Inventoried `update_post_meta`, `update_option`, `register_meta`, `register_setting`, `register_rest_field` calls in both trees (`**/*.php`, excluding vendor where vendor parity is a Bucket 1 concern handled separately). `register_meta` and `register_rest_field` only appear inside the bundled CMB2 vendor in both trees — no project-level REST-meta registration on either side.

### Meta keys

| ID | Bucket | Location (2.30.0) | Description | User-visible effect | Action |
|---|---|---|---|---|---|
| F3.1 | 3 | `sermons.php:961–962` (the `update_multiple_sermon_meta_data` function) | 2.30.0 introduces meta key `sermon_notes_multiple`. When a user has multiple notes attachments (the value of `sermon_notes` is an array), 2.30.0 copies the array to `sermon_notes_multiple` and clears `sermon_notes` to `''`. Triggered on `edit_post` and on every singular-sermon front-end view. | Migrated installs have notes data under `sermon_notes_multiple`; un-migrated installs have it under `sermon_notes`. | Cherry-pick is conditional on adopting the upstream multi-notes feature (which is a CMB2-config change covered in Surface 10). The migration function is dead code without the feature. |
| F3.2 | 3 | `sermons.php:968–969` (same function) | Sister key `sermon_bulletin_multiple`. Same pattern. | Same. | Same. |
| F3.3 | 3 | `includes/sm-core-functions.php:954` (the `update_sermon_posts` function) | 2.30.0 introduces meta key `post_content_backup`. When the legacy `sermon_description` meta value differs from `post_content`, 2.30.0 backs up `post_content` to `post_content_backup` and overwrites `post_content` with `sermon_description`. Triggered only by AJAX endpoint `wp_ajax_sync_sermon_data` (admin-side button, surface 10). | Sites that ran the sync have a populated `post_content` (rendered by `the_content`) and a `post_content_backup` meta; sites that didn't are unchanged. | Cherry-pick paired with the `'editor'` `supports` addition (F1.1) — the migration is the upstream answer to "what populates the body field when editor support is enabled". Without F1.1, this is dead code on Sermon Works. |

### Option keys

| ID | Bucket | Description | Action |
|---|---|---|---|
| F3.4 | 1 | Sermon Works writes the option `sm_term_image_migrated_to_meta` (`includes/sm-update-functions.php:507`) — set after the term-image migrator (#28) copied taxonomy-images data to core `term_meta` under `sm_term_image_id`. | Document. |
| F3.5 | 1 | Sermon Works does not read or write the option `sermon_image_plugin` (the bundled taxonomy-images vendor's option). 2.30.0 still reads/writes it via that vendor at `includes/vendor/taxonomy-images/taxonomy-images.php:220`, `:580`, `:655`, plus the importers. | Document. The term-image work (#28) removed the vendor and migrated reads to core `term_meta`. Sites upgrading from 2.30.0 → Sermon Works run the migrator (`sm_update_3_0_term_image_to_term_meta` in `sm-update-functions.php:478`) on first activation; original `sermon_image_plugin` is left in place for rollback safety. |

### Equal across both trees

All 20+ existing meta keys (`_wpfc_sermon_size`, `_wpfc_sermon_duration`, `sermon_audio_id`, `sermon_audio`, `sermon_video`, `sermon_video_link`, `sermon_date`, `sermon_date_auto`, `sermon_description`, `sermon_notes`, `sermon_bulletin`, `bible_passage`, `bible_passages_start`, `bible_passages_end`, `wpfc_service_type`, `Views`, `sm_files`, plus WP-standard `_wp_attached_file`, `_wp_attachment_metadata`, `_thumbnail_id`) are written from byte-identical sites in both trees. All `wp_sm_updater_*_done` flags and the `sermonmanager_*` settings keys are identical.

---

## Surface 4: Capabilities and roles

`add_cap` and `capability_type` references appear in only two files in each tree: `includes/class-sm-roles.php` and `includes/class-sm-post-types.php`. Surface 1 already established that the per-CPT/taxonomy `capabilities` arrays are byte-identical. `class-sm-roles.php` was diffed against 2.30.0; semantic differences are:

| Difference | Bucket | Notes |
|---|---|---|
| Line endings: 2.30.0 LF vs main CRLF | n/a | Windows-tooling artefact; not a code divergence. |
| PHPDoc package and inline comment text: "Sermon Manager" → "Sermon Works" | 1 | Branding sweep across PHPDoc and inline comments. |
| `defined( 'ABSPATH' ) || exit;` guard added on line 9 | 1 | Plugin Check ABSPATH-guard pass. |

The 14 capability strings (`read_wpfc_sermon`, 4 × `edit_*_wpfc_sermon[s]`, 4 × `delete_*_wpfc_sermon[s]`, `publish_wpfc_sermons`, `read_private_wpfc_sermons`, `manage_wpfc_categories`, `manage_wpfc_sm_settings`, `edit_others_wpfc_sermons`, `delete_others_wpfc_sermons`) and their assignment logic to `administrator`/`editor`/`author` are byte-identical. `capability_type='wpfc_sermon'` and `map_meta_cap=true` are identical on the CPT.

**No Bucket 3 candidates from this surface.**

---

## Surface 5: REST API exposure

`register_rest_route` calls appear only inside `includes/vendor/CMB2/` in both trees (covered by the CMB2 vendor upgrade #15, not a project-level REST surface). No custom REST endpoints are registered outside vendor in either tree.

The project-level REST surface is `includes/class-sm-api.php`, which hooks `rest_wpfc_sermon_collection_params`, `rest_prepare_wpfc_sermon`, `rest_wpfc_sermon_query`, and `rest_insert_wpfc_sermon` filters/actions on the WP-core CPT REST endpoints. Diffed line by line (CRLF/LF noise in the diff aside).

| ID | Bucket | Location | Description | User-visible effect | Action |
|---|---|---|---|---|---|
| F5.1 | 1 | `includes/class-sm-api.php:51–69` | Sermon Works adds a per-key sanitisation switch in `save_custom_data()`: `esc_url_raw` for URL keys (`sermon_audio`, `sermon_video_url`, `sermon_bulletin`), `sanitize_text_field` for `sermon_audio_duration` and `bible_passage`, `wp_kses_post` (or raw for `unfiltered_html` users) for `sermon_description` and `sermon_video_embed`, `absint` for `sermon_date`. 2.30.0 stores REST input verbatim. | REST clients writing crafted strings get them sanitised at storage; XSS-via-REST closed. | Document — security fix for issues `#16`–`#18`, `#23`–`#24`. |
| F5.2 | 4 | `includes/class-sm-api.php:34–43` (key list) and `:154`, `:170` (response composition) | `sermon_description` is **active** on Sermon Works' REST surface (write side accepts it; read side returns it). On 2.30.0 it is **commented out** in all three places. Coupled to the editor-support change: 2.30.0 added `'editor'` to `supports` (F1.1) and an AJAX sync (F3.3 / `wp_ajax_sync_sermon_data`) that copies `sermon_description` to `post_content`. With editor support enabled, 2.30.0 wants REST clients to write the body via `post_content` (WP-standard), not via the legacy `sermon_description` meta. | A 2.30.0 site that ran the AJAX sync and then edited a sermon body via the block editor stores those edits in `post_content`. Switching to Sermon Works, the front-end reads `sermon_description` (unchanged) — those post-sync editor edits are not displayed. Sites that never ran the sync are unaffected. | Bucket 4. If F1.1 is cherry-picked, document the gap. Cherry-picking the comment-outs would regress Sermon Works' REST surface (REST clients currently write `sermon_description` and our templates read it). |
| F5.3 | 1 | `includes/class-sm-api.php:11`, `:38` (PHPDoc) | Branding strings: "Sermon Manager API" → "Sermon Works API". | None at the API surface. | Document — PHPDoc branding sweep. |

The four hook points (`rest_wpfc_sermon_collection_params`, `rest_prepare_wpfc_sermon`, `rest_wpfc_sermon_query`, `rest_insert_wpfc_sermon`), plus `fix_ordering()`, `modify_query_params()`, and the rest of `add_custom_data()` (audio resolution, views, video, bulletin, thumbnail, sermon_date) are byte-identical between trees.

---

## Surface 6: Shortcodes

**Registered shortcodes:**

| Shortcode | Sermon Works | 2.30.0 | Notes |
|---|---|---|---|
| `[list_podcasts]` | ✓ | ✓ | Handler diverges — Bucket 1 fixes (see below) |
| `[list_sermons]` / `[list-sermons]` | ✓ / ✓ | ✓ / ✓ | Handler diverges — Bucket 1 fix |
| `[sermons]` / `[sermons_sm]` | ✓ / ✓ | ✓ / ✓ | Handler diverges — Bucket 1 fixes |
| `[sermon_images]` / `[sermon-images]` | ✓ / ✓ | ✓ / ✓ | Handler diverges — Bucket 1 cleanup + fixes |
| `[latest_series]` | ✓ | ✓ | Handler diverges — Bucket 1 (term-meta migration) |
| `[sermon_sort_fields]` | ✓ | ✓ | Identical |
| `[sermon-views]` | ✓ (native, `includes/sm-views-functions.php:101`) | ✓ (vendor, `includes/vendor/entry-views.php:39`) | Bucket 1 — issues `#35`/`#36` removed the vulnerable vendor and reimplemented natively |
| `[latest_sermon]` | ✗ | ✓ (registered twice, lines 42 + 67 of `class-sm-shortcodes.php`) | **Bucket 4 candidate** — see F6.1 |

### Findings

| ID | Bucket | Location | Description | User-visible effect | Action |
|---|---|---|---|---|---|
| F6.1 | 4 | `class-sm-shortcodes.php:42, 67` (registration) and `:795–921` (handler) in 2.30.0 | 2.30.0 ships a `[latest_sermon]` shortcode (registered twice — once in `init_shortcodes()`, once in `legacy_shortcodes()` — WP silently overwrites). Handler is largely a copy-paste of `display_sermons` with three visible defects: (a) `$query_args['orderby'] = $args['post_date']` reads a non-existent key — the `orderby` shortcode attribute is silently ignored; (b) outputs duplicate `id="wpfc-sermons-latest"` on a nested `<div>` — invalid HTML; (c) writes shortcode attribute values to static class properties `SermonManager::$image / $title / $description` — persists across requests in long-running PHP. Default behaviour (no atts): list 10 sermons, ordered ASC by `post_modified` (intent likely DESC by date — the bug at (a) defeats this). | A user who wrote `[latest_sermon]` into a page on 2.30.0 sees a list of recently-modified sermons; on Sermon Works they see the literal text `[latest_sermon]` (or empty if WP strips unknown shortcodes). | **Document the gap, recommend `[sermons per_page=N order=DESC orderby=date]` as the equivalent.** Cherry-picking would import three known bugs and a static-property side effect; doing it cleanly would mean rewriting the handler, which is feature work outside drop-in scope. Bucket 4. |
| F6.2 | 1 | `class-sm-shortcodes.php:132` (`display_podcasts_list`); `:199` (`display_sermons_list`); `:457` (`display_images`); `:585` (`display_sermons` title_wrapper) | Multiple output-escaping security fixes in shortcode handlers: `esc_html()` on label content in `[list_podcasts]` `<a>`; `esc_html__()` on the "Invalid list parameter" error string in `[list_sermons]` and `[sermon_images]` (XSS-via-shortcode-attribute closed); `sanitize_text_field` + strict `in_array` on `title_wrapper` in `[sermons]` (defence-in-depth). | Crafted shortcode attributes can no longer inject script; otherwise no output change. | Document — security fixes from the audit pass. |
| F6.3 | 1 | `class-sm-shortcodes.php:737` (`display_latest_series_image` helper) | Sermon Works reads `sm_term_image_id` term meta directly; 2.30.0 calls the bundled `sermon_image_plugin_get_associations()` from the removed `taxonomy-images` vendor. | None on a fully-migrated site. | Document — issue `#28`. |
| F6.4 | 1 | `class-sm-shortcodes.php:91–115` (`display_podcasts_list`); `:434–504` (`display_images`) | Code-quality cleanup: removed dead `$display` variable, redundant `if($args['include'])` guards, redundant `error_log` debug residue, redundant `$show_description` / `$hide_title` locals (consolidated into `$args[...]`). The `show_desc='yes'/'no'` shortcode attribute is now mapped to `show_description` consistently. Behavioural shift: `hide_title='no'` is now read as falsy (matches existing pattern). | None on well-formed shortcode usage. Edge case: 2.30.0 accepted `hide_title='no'` as truthy (string, not equal to `'yes'`); Sermon Works treats it as falsy. Likely matches user intent. | Document. |

### Equal across both trees

Six existing shortcode handlers' top-level structure (param parsing, `shortcode_atts` defaults, query construction, output template invocation) is the same. `[sermon_sort_fields]` is byte-identical aside from text domain.

---

## Surface 7: CSS class names emitted

Diffed `class="..."` strings emitted across `views/` between 2.30.0 and main. After eliminating Bucket-1 escape-wrapping noise (every dynamic `class="...$x..."` now `esc_attr( $x )` per the output-escape sweep), only one new emitted class set survives: 2.30.0's Twenty Twenty-Four theme branch (see F7.1 below).

| ID | Bucket | Location | Description | Action |
|---|---|---|---|---|
| F7.1 | 3 (with caveat) | `views/partials/content-sermon-wrapper-start.php:49–50` (2.30.0) | 2.30.0 added a `case 'twentytwentyfour':` branch to the theme-specific wrapper, opening `<div class="wp-block-group has-global-padding is-layout-constrained wp-block-group-is-layout-constrained"><div id="primary" class="content-area"><main id="main" class="site-main wpfc-sermon-container wpfc-twentytwentyfour ...">`. Sermon Works has no `twentytwentyfour` case; the Twenty Twenty-Four installs fall through to a generic `default:` case. **Caveat:** 2.30.0's matching `content-sermon-wrapper-end.php` does NOT have a corresponding `case 'twentytwentyfour':` close — the wrapping `<div class="wp-block-group">` is opened but not closed via the partial (close happens via theme template chain). | Cherry-pick paired with adding a matching `twentytwentyfour` close case in `content-sermon-wrapper-end.php`. The upstream is unbalanced; Sermon Works should ship a balanced pair. Bucket 3 with feature work. |

The full "themes-with-named-wrapper-classes" set (Avada, Beaver Builder, Beaver Theme, Brandon, Genesis, Hueman, Maranatha, Morgan, NativeChurch, OceanWP, Salient, Saved, Twenty Eleven, Twenty Twelve, Twenty Thirteen, Twenty Fourteen, Twenty Fifteen, Twenty Sixteen, Twenty Seventeen, Twenty Eighteen, Twenty Nineteen, Twenty Twenty, Twenty Twenty-One, Twenty Twenty-Two, Twenty Twenty-Three, BeTheme, Divi) is otherwise identical between trees.

---

## Surface 8: View templates

`diff -rq` finds 14 view files differ. Per-file substantive content beyond the known Bucket B output-escape sweep:

| File | Substantive change? | Bucket |
|---|---|---|
| `archive-wpfc_sermon.php` | `phpcs:ignore` comments, ABSPATH guard | 1 only |
| `single-wpfc_sermon.php` | `phpcs:ignore` comments, ABSPATH guard | 1 only |
| `partials/content-sermon-archive.php` | ABSPATH guard, removed `if(SermonManager::$image == 'no')` static-property branch (paired with F6.1 `[latest_sermon]` removal), Bucket-B esc on image markup | 1 only |
| `partials/content-sermon-single.php` | ABSPATH guard, removed dead `is_plugin_active` lookup | 1 only |
| `partials/content-sermon-attachments.php` | **F8.2 — see below** | **4** |
| `partials/content-sermon-filtering.php` | ABSPATH guard, gated partial-load-error to `manage_options` / `WP_DEBUG` (was leaking diagnostics to anonymous visitors), Bucket-B esc | 1 only |
| `partials/content-sermon-wrapper-start.php` | **F7.1 — Twenty Twenty-Four branch missing**, plus Bucket-B esc | 3 + 1 |
| `partials/content-sermon-wrapper-end.php` | `wp_kses_post()` wrapping on `$navigation_content`, `the_content`, and the `sm_templates_wrapper_end` filter return | 1 only |
| `taxonomy-wpfc_*.php` (all 5) | `phpcs:ignore` comments, ABSPATH guard | 1 only |
| `wpfc-podcast-feed.php` | **F8.3** Inlined `sanitize_title($terms)` (no behavioural change — `sanitize_title` is the binding sanitiser); `<itunes:explicit>false</itunes:explicit>` → `<itunes:explicit>no</itunes:explicit>` (Apple-spec-correct value) | 1 only |

### F8.2 — multi-attachments front-end render gap

| ID | Bucket | Location | Description | User-visible effect | Action |
|---|---|---|---|---|---|
| F8.2 | 4 | `views/partials/content-sermon-attachments.php` (whole file) | 2.30.0 reads BOTH `sermon_notes` AND `sermon_notes_multiple` (plus the `_bulletin` pair), iterates the array if multiple, renders one `<a>` per attachment. Sermon Works reads only the singular keys. Coupled to F3.1 / F3.2 (the meta-key migration writes the array to `_multiple` and clears the singular). | A 2.30.0 site that ran the multi-attachments migration has data in `sermon_notes_multiple` and an empty `sermon_notes`. Sermon Works' template reads `sermon_notes` (empty) and renders no Notes link at all. **Drop-in compat is broken** for those sites. | Bucket 4. The minimum-viable cherry-pick is a view-template-only read: if `sermon_notes` is empty, fall back to `sermon_notes_multiple` and iterate. That restores rendering without importing the admin-side multi-attachments feature. Recommended for 3.0.1. |

---

## Surface 9: `the_content` filter usage

Only one `add_filter( 'the_content', ... )` registration exists in either tree, in `includes/sm-template-functions.php:43`.

| ID | Bucket | Location | Description | User-visible effect | Action |
|---|---|---|---|---|---|
| F9.1 | 4 | `includes/sm-template-functions.php:43` | Sermon Works has `add_filter( 'the_content', 'add_wpfc_sermon_content' );` ACTIVE; 2.30.0 has the same line **commented out** (`//add_filter(...)`). The `the_excerpt` filter remains active in both. `add_wpfc_sermon_content` prepends the audio player, video block, scripture passage and "preached on…" markup onto post body output. | 2.30.0 moved the audio/video/passage rendering from the `the_content` filter into the `content-sermon-single.php` template inline (templates lines 80+ render the media block directly). Switching from 2.30.0 to Sermon Works re-enables filter-based rendering: audio/video markup appears wherever `the_content` is called (including in non-sermon contexts that include sermon excerpts via custom queries — e.g., a homepage block calling `the_content` on a sermon post). 2.30.0 sites with theme overrides for either path may render differently. | Bucket 4. The two rendering models are not directly compatible; cherry-picking the comment-out alone would silently disable Sermon Works' rendering on themes that don't override the single-sermon template. Document the gap. Investigation before any cherry-pick. |

The companion `add_filter( 'the_excerpt', 'add_wpfc_sermon_content' );` is identical in both trees (gated on `disable_the_excerpt` option).

---

## Surface 10: wp-admin UI surfaces

`diff -rq` finds 19 admin files differ. The bulk is the output-escape sweep, Plugin Check fixes, and the PHPDoc/branding pass. Substantive new admin UI in 2.30.0:

| ID | Bucket | Location (2.30.0) | Description | User-visible effect | Action |
|---|---|---|---|---|---|
| F10.1 | 4 | `includes/admin/views/html-admin-settings.php:53–57` (button + nonce field), `:148–164` (inline jQuery AJAX) | 2.30.0 adds a "Sync Now" button on the Sermon Settings page. Click → `confirm()` dialog → AJAX → `wp_ajax_sync_sermon_data` → `update_sermon_posts()` (F3.3 in `sm-core-functions.php:930–965`). Iterates every `wpfc_sermon` post and overwrites `post_content` with `sermon_description` (with `post_content_backup`). Three quality issues: (a) endpoint has no `current_user_can('manage_options')` check beyond nonce — any authenticated user with a valid nonce can trigger; (b) `console.log` debug residue in inline JS; (c) no rollback UI despite the backup meta key. | Sermon Works has no equivalent button, no AJAX endpoint, and no migration. Tied to the editor-support cluster (F1.1, F3.3, F5.2, F9.1). | Bucket 4. Cherry-pick of the button + endpoint would also pull in the migration code, which has the quality issues above. Recommend documenting the gap; if the editor-support cluster is later cherry-picked, write the migration cleanly rather than copy-paste from upstream. |
| F10.2 | 4 | `includes/admin/sm-cmb-functions.php:131–140` (notes), `:151–160` (bulletin) | 2.30.0 adds two CMB2 fields per attachment type: "Multiple Sermon Notes" (`id=sermon_notes_multiple`, type `file_list`) and "Multiple Bulletin" (`id=sermon_bulletin_multiple`, type `file_list`). Sermon Works keeps only the Single fields (`type=file`). | 2.30.0 admins can attach multiple PDFs per sermon; Sermon Works admins can only attach one. Drop-in render gap is F8.2 (front-end template doesn't read the array). | Bucket 4. Minimum-viable answer is render-only (F8.2 cherry-pick); fuller answer also adds the two `file_list` fields here. The cherry-pick is straightforward — two `add_field` calls. |
| F10.3 | 1 | `includes/admin/class-sm-admin-post-types.php:82` | 2.30.0 has `//add_filter( 'enter_title_here', ... );` commented out; Sermon Works has it enabled. The filter sets a custom "Enter sermon title" placeholder on the edit-sermon screen. | Cosmetic — 2.30.0 shows WP default "Add Title"; Sermon Works shows the custom placeholder. | Document — restoration of upstream's earlier behaviour. |
| F10.4 | 1 | `includes/admin/sm-term-image-functions.php` (new file in main only) | Sermon Works ships an admin metabox for series/preacher images (#28), replacing the removed `taxonomy-images` vendor's UI. | None on a fully-migrated site. | Document — issue `#28` phase C. |
| F10.5 | 1 | Multiple admin files | `date()` → `wp_date()` (timezone-aware), `wp_kses_post()` and `wp_kses()` wrapping on `apply_filters` returns and `$data` echoes. Examples: `class-sm-admin-post-types.php` "preached-date" column, the `sm_sermon_filters` apply_filters return. | None on well-formed input. | Document — Bucket B sweep + Bucket A modernisation. |

### Settings tabs

Tabs registered in `class-sm-admin-settings.php` and `class-sm-settings-page.php` are identical in both trees: General, Display, Podcast, Verse, Debug. No new tab in 2.30.0. The Sync button (F10.1) is rendered inside the General tab via `html-admin-settings.php`, not as a separate tab.

### Importers and exporters

`class-sm-import-sb.php`, `class-sm-import-se.php`, `class-sm-import-sm.php`, `class-sm-export-sm.php` differ between trees but the substantive changes are the `wxr_cdata` registration on export plus `parse_url`/`unlink` swaps and `taxonomy-images` removals on import. No new importer/exporter UI in 2.30.0.

---

## Cross-cluster summary

The substantive 2.15.15 → 2.30.0 changes break into four feature clusters plus the line-by-line Bucket-1 fixes Sermon Works has already shipped.

### Cluster A — Editor support

Five interlocking changes: F1.1 (add `'editor'` to `supports`), F3.3 (`post_content_backup` migration), F5.2 (`sermon_description` REST commented out), F9.1 (`the_content` filter commented out), F10.1 ("Sync Now" admin button). Coherent on 2.30.0 because 2.30.0 swaps the rendering model from `sermon_description` (read by the_content filter) to `post_content` (rendered as a normal post body). Cherry-picking the full cluster onto Sermon Works is a major template-and-render change; cherry-picking subsets risks broken rendering.

**Conservative cherry-pick:** F1.1 alone, without the other four. Adds `'editor'` to supports → editor pane appears in sermon admin; `the_content` filter still prepends audio/video/passage; `sermon_description` still drives front-end rendering; no migration. Result: users can start populating `post_content` for new sermons but the data model and rendering stay on `sermon_description`. Templates that read `the_content` would emit both the editor body AND the filter's audio/video block — that is the existing Sermon Works rendering behaviour, the addition is just that the body field is now editable.

### Cluster B — Multi-attachments (notes and bulletin)

Four interlocking changes: F3.1 / F3.2 (new `_multiple` meta keys + on-view migration), F8.2 (view template reads array), F10.2 (CMB2 `file_list` admin fields). 

**Minimum-viable cherry-pick:** F8.2 alone. View-template-only read: if `sermon_notes_multiple` is non-empty, render the array; else fall back to `sermon_notes`. Restores drop-in compat for 2.30.0 sites that ran the multi-attachments migration without importing the admin-side feature. ~10 LOC.

**Fuller cherry-pick:** F8.2 + F10.2. Adds the two `file_list` CMB2 fields so Sermon Works admins can also create new multi-attachments. ~30 LOC. Recommend if multi-attachments is genuinely useful to maintainers; skip if drop-in render is the only goal.

### Cluster C — Twenty Twenty-Four theme support

Single change: F7.1 (wrapper-start case + balanced wrapper-end close). Upstream ships unbalanced (no end case); Sermon Works should ship balanced. ~10 LOC across two view templates. Niche — only matters if a user is on the Twenty Twenty-Four core theme and currently sees broken wrapper styling.

### Cluster D — `[latest_sermon]` shortcode

Single change: F6.1. Cherry-picking would import three known bugs (orderby silently ignored, duplicate HTML id, static-property side effects). Recommend documenting the gap and pointing users at `[sermons per_page=N order=DESC orderby=date]` as the equivalent.

---

## Bucket 3 — cherry-pick into 3.0.1 (recommended order)

| # | ID | Change | Cost | Risk | Justification |
|---|---|---|---|---|---|
| 1 | F8.2 | View template reads `sermon_notes_multiple` and `sermon_bulletin_multiple` with fallback to singular | ~10 LOC, single-file, view-template-only | Very low | Restores drop-in compat for 2.30.0 sites that ran the multi-attachments migration; data is currently invisible on Sermon Works. Top priority. |
| 2 | F1.1 (alone) | Append `'editor'` to `supports` array | 1 LOC | Very low | Enables the editor pane on sermon admin without changing the rendering model; lets users start using `post_content` for new sermons. |
| 3 | F10.2 | Add "Multiple Sermon Notes" + "Multiple Bulletin" CMB2 `file_list` fields | ~30 LOC | Low | Lets Sermon Works admins create multi-attachments themselves, not just render existing 2.30.0 data. Pair with #1 for a coherent feature. |
| 4 | F7.1 | Twenty Twenty-Four wrapper (start case + balanced end case) | ~10 LOC, two files | Low | Niche but trivial. Can defer if 3.0.1 scope tightens. |

Suggested commit shape: one commit per item, each tagged `2.30.0 cherry-pick: <description>` in the commit message body, referencing this audit.

## Bucket 4 — documented gaps (rationale required in README/readme.txt)

| ID | Gap on Sermon Works | Why we don't cherry-pick |
|---|---|---|
| F5.2 / F9.1 (Cluster A subset) | The 2.30.0 swap of "sermon_description body filter" → "post_content body" is not implemented. Sermon Works renders body via `sermon_description` + `the_content` filter (the upstream pre-2.30.0 model). | Switching rendering models is invasive; partial cherry-picks risk breaking either path. F1.1 alone is the conservative subset shipped via Bucket 3 #2. |
| F3.3 / F10.1 (Cluster A subset) | "Sync Now" button + `update_sermon_posts` migration are not implemented. Sites switching from 2.30.0 with editor edits in `post_content` will see Sermon Works rendering `sermon_description` instead. | The upstream migration has quality issues (missing capability check, debug residue, no rollback UI). If maintainers want this feature, write the migration cleanly rather than copy-paste. |
| F6.1 | `[latest_sermon]` shortcode is not provided. | Upstream handler has three known bugs and uses static-property side-effects. Recommend `[sermons per_page=N order=DESC orderby=date]` as the documented substitute. |

## Cross-cutting Bucket 1 (Sermon Works divergences from 2.30.0 that are justified)

These are documented for completeness — none require action; they are surfaced so the README/readme.txt rewrite can describe Sermon Works' positive divergences from 2.30.0 honestly.

- **Stored-XSS hardening on REST insert** (F5.1): `esc_url_raw` / `wp_kses_post` / `absint` per-key sanitisation on `rest_insert_wpfc_sermon`. Closes `#16`–`#18`, `#23`–`#24` family.
- **Stored-XSS hardening on shortcodes** (F6.2): `esc_html()` on `[list_podcasts]` `<a>` content, `esc_html__()` on shortcode error messages, strict `in_array` + sanitised `title_wrapper` in `[sermons]`.
- **`taxonomy-images` vendor removed** (F3.4 / F3.5 / F6.3 / F10.4): migration to core `term_meta` under `sm_term_image_id`, runs on first activation; original `sermon_image_plugin` option preserved for rollback.
- **`entry-views.php` vendor removed** (Cluster around `[sermon-views]`): closed CVE-2025-12368 / CVE-2025-63002; reimplemented as ~30 LOC native counter at `includes/sm-views-functions.php`.
- **CMB2 v2.2.3.1 → v2.11.0** (`#15`): contemporary CMB2 features and security fixes.
- **wp-background-processing 1.0.1 → 1.4.0** (`#27`): closed POI in batch deserialisation; subclass `allowed_batch_data_classes = false`.
- **PHP 8.1 floor + WP 6.0 floor** in plugin header.
- **Output-escape sweep** (~317 sites across 32 files): every `<?php echo $foo; ?>` in views and admin classes now passes through context-appropriate escaper. Sermon Works' Plugin Check baseline is zero `OutputNotEscaped` errors.
- **Code-quality cleanup**: ABSPATH guards, dead-link removal (`wpforchurch.com`, `sermonmanager.pro`), PHPDoc / branding sweep `Sermon Manager` → `Sermon Works` across 47 sites, `date()` → `wp_date()`, `parse_url` → `wp_parse_url`, `strip_tags` → `wp_strip_all_tags`, etc.
- **Text domain rename** `sermon-manager-for-wordpress` → `sermon-works` (387 call-sites, paired with WP.org slug).

## What this means for the 3.0.1 release

If maintainer signs off on the Bucket 3 list above, the suggested 3.0.1 cut is:

1. Four cherry-pick commits in the order shown.
2. Single `release: bump to 3.0.1` commit (sermons.php Version, readme.txt Stable tag, new `= 3.0.1 =` changelog stanza, optional Upgrade Notice referring 2.30.0 users to the multi-attachments restoration).
3. README and readme.txt narrative refresh: replace "abandoned since 2019" with "actively maintained on WP.org through late 2024", narrow drop-in claim to "drop-in for the wpfc_sermon CPT, wpfc_* taxonomies, and option keys; cherry-picked 2.30.0 surface beyond that as listed below; full 2.30.0 parity not promised", reference WP.org SVN as the canonical source.
4. Annotated `v3.0.1` tag, dist ZIP via `bin/build-release-zip.ps1 -Ref v3.0.1`, GitHub Release published (NOT prerelease — second non-prerelease in the line).
5. SUPERSEDED-banner the `v3.0` release notes pointing at v3.0.1 (link-only edit, rationale text noting the 2.30.0 cherry-picks).
6. WP.org tactic depends on whether the reviewer email arrives before the cut. If not yet: reply to the original submission acknowledgement asking the reviewer to consider 3.0.1 (attach new ZIP). If reviewer has approved 3.0: ship 3.0.1 to SVN immediately and bump `Stable Tag`.

Estimated total effort: ~50 LOC across 5 files for the four cherry-picks; 1 doc commit; 1 release commit; ~30 minutes mechanical work on dist + Release.
