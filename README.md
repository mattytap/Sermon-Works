# Mattytap Sermons

> Faithful to the source.

A caretaker restoration of [Sermon Manager](https://github.com/WP-for-Church/Sermon-Manager) — a WordPress plugin that helps churches publish sermons online. Originally by WP-for-Church alongside a paid Pro tier; public development on GitHub stopped in early 2019, but free-version releases continued privately to WordPress.org for another five years, ending at 2.30.0 in September 2024. The upstream maintainer's web presence is no longer reachable, and three publicly-disclosed CVEs against the WordPress.org-shipped version remain unpatched. Mattytap Sermons is the maintained successor line.

## Install

The current release is **3.0.2**.

1. Open the [Releases page](https://github.com/mattytap/Mattytap-Sermons/releases) and download `sermon-works-3.0.2.zip` from the latest release.
2. In WordPress admin, go to **Plugins, Add New, Upload Plugin**.
3. Choose the ZIP, click **Install Now**, then **Activate**.

Existing Sermon Manager users can deactivate Sermon Manager and activate Mattytap Sermons in its place. Database, shortcodes, post types and taxonomies are unchanged, so existing sermons, preachers and series carry across.

Requires PHP 8.1+ and WordPress 6.0+.

## Status

Latest tag: `3.0.2`. WordPress.org resubmission as `mattytap-sermons` is in flight following a 2026-05-11 trademark rejection of the `sermon-works` slug.

- **Security audit complete.** 25 issues filed on the [tracker](https://github.com/mattytap/Mattytap-Sermons/issues), including three publicly-disclosed CVEs (CVE-2025-12368, CVE-2025-63000, CVE-2025-63002). All filed findings have shipping patches; operational verification at formal UAT closes each issue.
- **Modernisation complete.** Codebase brought up to current PHP and WordPress APIs. PHP 8.1+ floor, WordPress 6.0+ floor.
- **Drop-in compatibility against 2.30.0.** The `wpfc_sermon` post type, `wpfc_*` taxonomies, core option keys, six shortcodes, and view-template surface match the WP.org-shipped 2.30.0 line. Multi-attachments for notes and bulletin, post-body editor support, and the Twenty Twenty-Four theme wrapper are cherry-picked from upstream 2.30.0; a small set of upstream changes are documented as gaps with rationale. See [`.restoration/DROP-IN-AUDIT.md`](.restoration/DROP-IN-AUDIT.md) for the full classification.
- **Renamed for WordPress.org submission.** The `sermon-works` slug was bounced in pre-review on 2026-05-11 on trademark grounds. The plugin has been renamed to Mattytap Sermons (slug `mattytap-sermons`). The same pre-review surfaced a list of technical findings being worked through for the renamed RC, planned as `3.1-rc1`.

## Who is Mattytap Sermons for?

Three audiences are particularly in scope.

**Existing Sermon Manager site administrators** running an installation of the original plugin and increasingly concerned about its unpatched CVEs. Mattytap Sermons is being designed as a drop-in replacement; you should be able to deactivate Sermon Manager and activate Mattytap Sermons without losing sermons, preachers, series, or settings.

**Churches building a new WordPress site** who need a maintained sermon plugin and want one that's GPL-licensed, free, and unlikely to disappear.

**Church tech volunteers and developers** who'd rather work with a plugin that's actively maintained, lints clean against [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards), and has a tractable issue queue.

## Why "Mattytap Sermons"?

The name pairs the maintainer's GitHub handle, `mattytap`, with the plain category noun. It's a deliberately unflashy choice, settled on after the original name was bounced in WordPress.org pre-review on trademark grounds. The signalling intent is **caretaker restoration** rather than new brand: existing Sermon Manager users encountering Mattytap Sermons should recognise it as the same line of work, modernised — not a competitor or a replacement they have to evaluate from scratch.

What we're restoring isn't quite the "abandoned 2019 plugin" story it might initially look like. WP-for-Church kept the free version on WordPress.org through eleven releases between 2019 and 2024, alongside a paid Pro tier. Bundled vendor libraries weren't kept current and the security backlog wasn't worked; three CVEs disclosed in late 2025 against the WordPress.org-shipped version remain unpatched. So Mattytap Sermons is more accurately a caretaker for the 2024 plugin than the 2019 one — for the churches and volunteers who relied on the free version.

## Principles

These shape what gets accepted and what gets shipped.

- **Fix not change.** For code that already works, the bar to alter behaviour is high; the bar to fix bugs and security holes is low. Mattytap Sermons exists to take care of an existing codebase, not to redesign it.
- **Drop-in compatibility against 2.30.0.** Existing Sermon Manager installations should be able to switch to Mattytap Sermons and have data and front-end render keep working. The `wpfc_sermon` custom post type, the `wpfc_preacher` and `wpfc_sermon_series` taxonomies, and all `wpfc_*` option keys are preserved deliberately. Where 2.30.0 added features beyond the 2.15.16 GitHub baseline Mattytap Sermons forked from, upstream surface is either cherry-picked or documented as a gap with rationale. The audit reference is [`.restoration/DROP-IN-AUDIT.md`](.restoration/DROP-IN-AUDIT.md).
- **Attribution preserved.** Upstream authors retain their place in the git commit history; ingested upstream Pull Requests preserve the original author as commit author. See [CONTRIBUTORS.md](CONTRIBUTORS.md) for the human-readable record and [`.restoration/ATTRIBUTION-PLAN.md`](.restoration/ATTRIBUTION-PLAN.md) for the methodology.
- **Security-first, in public.** Audit findings are filed on the tracker as issues, fixed via tracked commits, and credited in release notes. New vulnerability reports go through [SECURITY.md](SECURITY.md)'s private disclosure channel first; coordinated disclosure protects existing Sermon Manager installs that may share the same code path.

## Documentation

- [ROADMAP.md](ROADMAP.md) — features under consideration; deliberately not commitments.
- [CONTRIBUTORS.md](CONTRIBUTORS.md) — credits for the original Sermon Manager team and contributors to this restoration.
- [SECURITY.md](SECURITY.md) — how to report a vulnerability privately.
- [.github/CONTRIBUTING.md](.github/CONTRIBUTING.md) — how to contribute code.
- [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) — community standards (Contributor Covenant).
- `readme.txt` — the WordPress.org-format readme.

## Credit

This work would not exist without the original [Sermon Manager](https://github.com/WP-for-Church/Sermon-Manager) by [WP-for-Church](https://wpforchurch.com/) and [Jason Westbrook](https://github.com/jasonmwestbrook). Their codebase is the foundation; this restoration is the renovation.

## License

GPLv2, inherited from the upstream plugin.
