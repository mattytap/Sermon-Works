# Sermon Works

> Sola fide. Fully featured.

A caretaker restoration of [Sermon Manager](https://github.com/WP-for-Church/Sermon-Manager) — a WordPress plugin that helps churches publish sermons online — left dormant by its original maintainers, [WP-for-Church](https://wpforchurch.com/), since 2019.

## Install

The current release is **3.0**.

1. Open the [Releases page](https://github.com/mattytap/Sermon-Works/releases) and download `sermon-works-3.0.zip` from the latest release.
2. In WordPress admin, go to **Plugins, Add New, Upload Plugin**.
3. Choose the ZIP, click **Install Now**, then **Activate**.

Existing Sermon Manager users can deactivate Sermon Manager and activate Sermon Works in its place. Database, shortcodes, post types and taxonomies are unchanged, so existing sermons, preachers and series carry across.

Requires PHP 8.1+ and WordPress 6.0+.

## Status

Latest tag: `3.0`. WordPress.org submission pending.

- **Security audit complete.** 25 issues filed on the [tracker](https://github.com/mattytap/Sermon-Works/issues), including three publicly-disclosed CVEs (CVE-2025-12368, CVE-2025-63000, CVE-2025-63002). All filed findings have shipping patches; operational verification at formal UAT closes each issue.
- **Modernisation complete.** Codebase brought up to current PHP and WordPress APIs. PHP 8.1+ floor, WordPress 6.0+ floor.
- **Drop-in compatibility preserved.** `wpfc_sermon` post type, `wpfc_*` taxonomies and option keys, and shortcode output structure unchanged from Sermon Manager 2.15.16.
- **WordPress.org submission queued.** UAT cleared at 3.0-rc5; Plugin Check CI cleared at 3.0-rc6; 3.0 stable cut.

## Who is Sermon Works for?

Three audiences are particularly in scope.

**Existing Sermon Manager site administrators** running an installation of the original plugin and increasingly concerned about its unpatched CVEs. Sermon Works is being designed as a drop-in replacement; you should be able to deactivate Sermon Manager and activate Sermon Works without losing sermons, preachers, series, or settings.

**Churches building a new WordPress site** who need a maintained sermon plugin and want one that's GPL-licensed, free, and unlikely to disappear.

**Church tech volunteers and developers** who'd rather work with a plugin that's actively maintained, lints clean against [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards), and has a tractable issue queue.

## Why "Sermon Works"?

The strapline gives it away if you know the territory. Beyond the joke, the name signals **caretaker restoration** rather than new brand: existing Sermon Manager users encountering Sermon Works should recognise it as the same line of work, modernised — not a competitor or a replacement they have to evaluate from scratch.

## Principles

These shape what gets accepted and what gets shipped.

- **Fix not change.** For code that already works, the bar to alter behaviour is high; the bar to fix bugs and security holes is low. Sermon Works exists to take care of an existing codebase, not to redesign it.
- **Drop-in compatibility.** Existing Sermon Manager installations should be able to switch to Sermon Works and have everything keep working. The `wpfc_sermon` custom post type, the `wpfc_preacher` and `wpfc_sermon_series` taxonomies, and all `wpfc_*` option keys are preserved deliberately.
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
