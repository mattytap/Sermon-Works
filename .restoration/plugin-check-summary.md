# Plugin Check findings summary

**Snapshot baseline:** v3.0-rc4 (Plugin Check workflow run on 2026-05-01).
**Current state on `main`:** v3.0-rc5. **Bucket B closed**, see § rc5 update.

**Latest captured run:** GitHub Actions workflow `plugin-check.yml`, run ID 25227901173, 2026-05-01.
**Plugin Check Action:** `WordPress/plugin-check-action@v1`.
**Source artefact:** `.restoration/plugin-check/plugin-check-results.txt` (gitignored).

## rc5 update (2026-05-05)

The Bucket B output-escape sweep landed in 3.0-rc5 across 11 commits (`1290533` to `f3b89c3` on `main`). The 316 `WordPress.Security.EscapeOutput.OutputNotEscaped` errors plus one drift site retired in full (317 -> 0) without any new errors introduced. The four W-1/W-2/LB-5 small fixes from the 2026-05-04 pre-submission audit also landed in the same RC (`1290533`). Detailed sweep notes live at `.restoration/pre-submission-3.0-2026-05-04/REPORT.md` (gitignored).

The body sections below preserve the rc4 baseline as the snapshot against which the rc5 work was planned and measured. The "WP.org submission readiness" assessment at the bottom is superseded; rc5 closes the OutputNotEscaped gate that the rc4 paragraph called out as the reason a same-day submission would bounce.

## Headline

- **1,265 total findings** across 53 files (down from 1,335 on rc2)
  - **325 ERRORs** (down from 395; -70 closed by Bucket A)
  - **940 WARNINGs** (unchanged; same accepted-noise mix as before)

## ERROR breakdown (325 remaining)

| Count | Code | Notes |
|---:|---|---|
| 316 | `WordPress.Security.EscapeOutput.OutputNotEscaped` | **Bucket B**, parked. Mostly in view templates where `<?php echo $var ?>` should be `<?php echo esc_html( $var ) ?>` (or the appropriate escaper). |
| 3 | `WordPress.WP.AlternativeFunctions.file_system_operations_fread` | **Tier 2**. Needs WP_Filesystem refactor. |
| 2 | `WordPress.WP.AlternativeFunctions.file_system_operations_fopen` | **Tier 2**. Same. |
| 2 | `WordPress.DB.PreparedSQL.NotPrepared` | **Tier 2**. SQL injection guard, needs audit. |
| 1 | `WordPress.WP.EnqueuedResources.NonEnqueuedScript` | **Tier 2**. Inline `<script>` needs proper enqueue. |
| 1 | `PluginCheck.Security.DirectDB.UnescapedDBParameter` | **Tier 2**. SQL escaping. |

## Bucket A — closed in 3.0-rc4

All 70 sites cleared in 10 small commits between commits `47bd230` and `a199f81`, version-bumped at `93ea6ef`. Categories closed:

| Code | Count | Commit |
|---|---:|---|
| `plugin_header_no_license` | 1 | `47bd230` |
| `missing_direct_file_access_protection` | 11 | `4f7695b` |
| `WordPress.DateTime.RestrictedFunctions.date_date` | 14 | `9641710` (12 to `gmdate()`, 2 to `wp_date()`) |
| `WordPress.WP.AlternativeFunctions.parse_url_parse_url` | 8 | `7760875` |
| `WordPress.WP.AlternativeFunctions.strip_tags_strip_tags` | 7 | `bd0e2d8` |
| `WordPress.WP.AlternativeFunctions.unlink_unlink` | 6 | `fa23b58` |
| `WordPress.WP.AlternativeFunctions.rand_rand` | 1 | `3c35c5e` |
| `WordPress.WP.I18n.MissingArgDomain` | 10 | `5949fd4` |
| `WordPress.WP.I18n.TextDomainMismatch` | 9 | `b7eca56` |
| `WordPress.WP.I18n.MissingTranslatorsComment` | 3 | `a199f81` |

## Bucket B — output escaping sweep (316 sites, parked)

The big remaining ask. Bounded but substantial. Hot spots (per file, descending):

- `views/partials/content-sermon-wrapper-start.php` (~338 of the file's total findings include this code)
- `views/wpfc-podcast-feed.php` (~118)
- `views/partials/content-sermon-wrapper-end.php` (~89)
- `includes/admin/export/class-sm-export-sm.php` (~109)
- `includes/admin/class-sm-admin-settings.php` (~67)

Five files account for the bulk of the work; the remaining sites are scattered.

**Approach:** file-by-file pass. Add `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` at output sites depending on context (HTML body text, attribute value, URL, allow-some-HTML). Each escape decision needs a moment of judgement, not pure find/replace. The sm-prefix warnings will mask things visually in the report; filter by ERROR severity when reading.

**Suggested cadence:** one hot-spot file per session. After each file, re-run `gh workflow run plugin-check.yml` and confirm the ERROR count drop matches the file's Bucket B contribution. Ship a fresh rcN per file or per pair.

## Tier 2 substantive errors (9 sites, parked)

Smaller scattered set, less mechanical than Bucket A. Specifics:

- **`includes/sm-core-functions.php`**: 1× `fopen` at line 302, 1× `fread` at line 310, 1× `fopen` at line 338. Switch to `WP_Filesystem` API or use `file_get_contents` / `file_put_contents` if the Plugin Check is happy with those.
- **2× `WordPress.DB.PreparedSQL.NotPrepared`**: SQL queries built without `$wpdb->prepare()`. Need to read the queries, decide whether the inputs are trusted (e.g. constants) or need preparation. May intersect with existing `DirectDatabaseQuery` warnings.
- **1× `NonEnqueuedScript`**: an inline `<script>` somewhere needs to be moved to `wp_enqueue_script()` with an external file or use `wp_add_inline_script()`.
- **1× `UnescapedDBParameter`**: SQL parameter not run through `esc_sql()` / `$wpdb->prepare()`.

These can ship together as 3.0-rcN once Bucket B is at least partially down — they're not the high-value next step.

## Bucket C — real-but-deferred warnings (~150 sites, no work yet)

`DirectDatabaseQuery` (Direct call + NoCaching, ~95), `NonceVerification` (Missing + Recommended, ~37), `ValidatedSanitizedInput` (~20). Substantive WP best-practice work. After the error buckets close.

## Bucket D — accepted noise (723 warnings, do not action)

The `sm_` short-prefix complaints (`NonPrefixedVariableFound` ~476, `NonPrefixedHooknameFound` ~176, `NonPrefixedFunctionFound` ~71). PHPCS already flags these despite `sm` being declared in `phpcs.xml.dist`'s prefix list. Project-wide breaking change with no security benefit.

## WP.org submission readiness

WP.org plugin reviewers focus on the ERROR count. With 325 remaining and 316 of those being `OutputNotEscaped`, a submission today would still get bounced or face a long feedback cycle. The Bucket A pass cleared the hygiene + scaffolding errors that don't reflect real risk; the remaining errors *do* mostly reflect real escaping risk that reviewers care about.

**Path to ready:** complete Bucket B (or at least the 5 hot-spot files), then either ship and iterate or finish Tier 2 for clean submission.
