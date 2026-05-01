# ROADMAP

A working backlog of features under consideration for **Sermon Works**. None of these are commitments.

Sermon Works is a caretaker restoration. The aim is not to be the most-featured sermon plugin in the space, but to keep an abandoned and security-vulnerable codebase running for the churches still relying on it. Best-practice contributions (security hardening, accessibility, performance, Gutenberg block support) have a clear path. Radical scope changes (rebrands, pivots into adjacent niches) do not.

There is no paid tier and no plan to introduce one. The plugin is, and will remain, free and GPLv2.

For known bugs and security findings, see the [issue tracker](https://github.com/mattytap/Sermon-Works/issues). For how to propose a new feature, see [CONTRIBUTING.md](.github/CONTRIBUTING.md).

## 3.0: restoration release

The first stable Sermon Works release. Closes out the restoration scope:

- All security audit findings (`#13`–`#37`) shipping with patches.
- Codebase modernised against current PHP (8.1+ floor) and WordPress (6.0+ floor) APIs.
- Drop-in compatibility preserved for existing Sermon Manager 2.15.16 installs (custom post type, taxonomies, option keys, shortcode output).
- Salvaged upstream bug fixes ingested with attribution from the open-PR backlog: upstream PRs `#300` (RSS ternary), `#274` (shortcode pagination), `#292` (sermons_array defensive check), `#273` (sermon_video_embed REST and import/export), `#264` (French bible verse names).
- Release candidates (`3.0-rc1`, `3.0-rc2`, ...) until the formal UAT signs off.

## 3.1: first feature release

Targets after 3.0 stable. Order is rough priority.

- **Gutenberg block support.** Blocks for the sermon CPT, preacher and series taxonomies, and the major shortcodes. Closes the largest "modern WordPress plugin" gap.
- **WP search integration.** Per upstream PR `#159` (sermons searchable from standard WP archive search).
- **WP Authors integration.** Optional link between preacher taxonomy terms and WordPress user accounts (upstream issue `#194`).
- **Archive UX.** Per-page count setting (upstream `#143`), frontend sort direction (upstream `#52`), duplicate-sermon button (upstream `#244`).
- **Good-first-issue wins from upstream.** `#82`, `#113`, `#117`, `#138`, `#155`.

## Beyond 3.1

Areas of interest with no committed timeline.

- Accessibility audit (WCAG 2.1 AA).
- Performance: lazy-loaded sermon images, podcast feed caching.
- Wider importer support, e.g. from Church Content plugin (upstream `#239`).
- Block-theme compatibility for `wpfc_sermon` archives.
- Unit test pattern building on the existing PHPUnit infrastructure (upstream `#195`).
- Archived-sermons widget (upstream `#150`).
- **Namespace the bundled CMB2 library.** Rename `CMB2_*` classes throughout `includes/vendor/CMB2/` to a project-specific prefix (e.g. `SW_CMB2_*`) to prevent runtime collisions with other active plugins that also bundle their own CMB2 copy (GiveWP, MetaBox, WPForms Pro, and many others). The case-sensitivity fix in 3.0-rc3 closed one Linux-host variant of this issue, but a different active plugin shipping a different CMB2 version can still cause class-already-defined or class-not-found fatals depending on load order. Substantial mechanical refactor across the bundled vendor tree; ships when there is appetite.

## Out of scope

- A paid tier. The original "Pro" pitch is closed; nothing from it carries over.
- Per-theme compatibility shims, e.g. zerif-lite (upstream PR `#245`).
- Removing CMB2 (upstream `#190`). CMB2 was upgraded to 2.11.0 during restoration.

## How to nominate a feature

Open an [issue](https://github.com/mattytap/Sermon-Works/issues/new/choose) and make the case. The bar for adding a new feature is higher than the bar for fixing a bug. Sermon Works is a caretaker restoration and "fix not change" is the working stance. Requests aligned with current best practice (accessibility, performance, modern WordPress APIs) have the clearest path; radical scope changes will be declined. See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for what makes a strong feature request.
