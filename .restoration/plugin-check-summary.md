# Plugin Check findings summary — v3.0-rc2

**Run:** GitHub Actions workflow `plugin-check.yml`, run ID 25207310684, 2026-05-01.
**Plugin Check Action:** `WordPress/plugin-check-action@v1`.
**Source artefact:** `.restoration/plugin-check/plugin-check-results.txt` (gitignored).

## Headline

- **1,335 total findings** across 53 files
  - **395 ERRORs** (WP.org-flagged; reviewers may block submission)
  - **940 WARNINGs** (most are accepted noise; some legitimate)
- Concentration: **5 files account for 54%** of findings (`content-sermon-wrapper-start.php`, `wpfc-podcast-feed.php`, `class-sm-export-sm.php`, `content-sermon-wrapper-end.php`, `class-sm-admin-settings.php`).

## ERROR breakdown (submission-blockers, 395 total)

| Count | Code | Notes |
|---:|---|---|
| 316 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | The big one. Mostly in view templates where `<?php echo $var ?>` should be `<?php echo esc_html( $var ) ?>` (or the appropriate escaper). |
| 14 | `WordPress.DateTime.RestrictedFunctions.date_date` | `date()` to `gmdate()`. Mechanical. |
| 11 | `missing_direct_file_access_protection` | Add `defined( 'ABSPATH' ) or die;` guard. Mechanical. |
| 10 | `WordPress.WP.I18n.MissingArgDomain` | Add `'sermon-works'` text-domain arg to `__()`/`_e()`/etc. |
| 9 | `WordPress.WP.I18n.TextDomainMismatch` | Wrong domain (e.g. `'wordpress-importer'`). Easy. |
| 8 | `parse_url_parse_url` | `parse_url()` to `wp_parse_url()`. |
| 7 | `strip_tags_strip_tags` | `strip_tags()` to `wp_strip_all_tags()`. |
| 6 | `unlink_unlink` | `unlink()` to `wp_delete_file()`. |
| 3 | `MissingTranslatorsComment` | Add `/* translators: ... */` to placeholder strings. |
| 3 | `file_system_operations_fread` | Use `WP_Filesystem` API. Less mechanical. |
| 2 | `WordPress.DB.PreparedSQL.NotPrepared` | SQL injection guard. Real concern. |
| 2 | `file_system_operations_fopen` | Use `WP_Filesystem` API. |
| 1 | `plugin_header_no_license` | Add `License: GPLv2` etc. to plugin header. Trivial. |
| 1 | `WordPress.WP.EnqueuedResources.NonEnqueuedScript` | Inline `<script>` not enqueued. |
| 1 | `PluginCheck.Security.DirectDB.UnescapedDBParameter` | SQL escaping. |
| 1 | `rand_rand` | `rand()` to `wp_rand()`. |

## WARNING breakdown (940 total)

- **723 are "sm-prefix-too-short"** noise (`NonPrefixedVariableFound`, `NonPrefixedHooknameFound`, `NonPrefixedFunctionFound`). PHPCS already flags these despite `sm` being declared in `phpcs.xml.dist`'s prefix list. Accepted noise per CLAUDE.md gotchas. Renaming everything `sm_` to `sermonworks_` would be a project-wide breaking change with no security benefit.
- **95** combined `DirectDatabaseQuery` (Direct call + NoCaching). Legitimate but historically deferred for SM-style plugins.
- **37** combined `NonceVerification` (Missing + Recommended). Some are real, some are admin-flow false positives. Worth a focused review.
- **20** combined `ValidatedSanitizedInput` (MissingUnslash + InputNotSanitized). Real concerns.
- **9** `slow_db_query_meta_key`. Performance hint.
- Other smaller categories.

## Triage groupings

### Bucket A — quick mechanical errors (~75 sites, 1-2 sessions)

Easy class. Mostly find-and-replace plus a tiny audit:

- `date()` to `gmdate()` (14)
- `defined('ABSPATH')` guard (11)
- i18n missing domain (10) and mismatched domain (9) and translators comments (3)
- `parse_url`/`strip_tags`/`unlink`/`rand` swaps (22)
- License header (1)
- Plus 5-6 of the smaller error codes

### Bucket B — output escaping sweep (316 sites, multi-session)

The big one. Bounded but substantial. Hot spots:

- `views/partials/content-sermon-wrapper-start.php` (338 total findings; many likely OutputNotEscaped)
- `views/wpfc-podcast-feed.php` (118)
- `views/partials/content-sermon-wrapper-end.php` (89)
- `includes/admin/export/class-sm-export-sm.php` (109)
- `includes/admin/class-sm-admin-settings.php` (67)

Approach: file-by-file pass with `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` where appropriate. Each escape decision needs a moment of judgement (HTML vs text vs URL vs allow-some-HTML), so it's not a pure find-replace.

### Bucket C — real-but-deferred warnings (~150 sites)

`DirectDatabaseQuery`, `NonceVerification` (admin flows), `ValidatedSanitizedInput`. Substantive WP best-practice work. Probably a separate stream after the error buckets close.

### Bucket D — accepted noise (723 warnings)

The `sm_` short-prefix complaints. Don't action.

## Implications for WP.org submission

A submission with 395 errors will face friction. The plugin team's typical pattern is to send detailed feedback on first review and ask for fixes before approval. That can take 2-4 review cycles with weeks between each. So submitting now would either:

- Get bounced for OutputNotEscaped + ABSPATH-guard counts (most likely)
- Or get a long feedback email pointing at all the buckets above

Either way, doing Bucket A and at least a partial pass on Bucket B before submitting is the high-value move. Bucket A is cheap; Bucket B is the real cost.

## Recommendation

1. Schedule a "Bucket A sweep" session — probably 2-4 hours of focused work to clear the quick mechanical errors, ship as 3.0-rc3.
2. Decide whether to commit to the Bucket B sweep before submission, or submit-and-iterate based on reviewer feedback. Bucket B is real work but bounded.
3. Re-run Plugin Check after each commit to track progress (the workflow is in place).
4. Until then, slug-squat risk continues; rely on the public-evidence story for any disputes.
