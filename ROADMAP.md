# ROADMAP

A working backlog of features under consideration for **Sermon Works**. None of these are commitments. There is no paid tier and no plan to introduce one — the plugin is, and will remain, free and GPLv2.

For known bugs and security findings, see the [issue tracker](https://github.com/mattytap/Sermon-Works/issues). For how to propose a new feature, see [CONTRIBUTING.md](.github/CONTRIBUTING.md).

## Restoration milestones

Not features, but they gate releases.

1. **Security fixes.** Audit findings filed as issues `#13`–`#37` on the tracker. Stream A (most issues) has shipping patches; Stream B (four major-vendor items) is the remaining work. Each fix is verified on a local WordPress install before its tracker issue is closed.
2. **Modernisation.** Bring the codebase up to current PHP and WordPress APIs whilst preserving drop-in compatibility. PHP 8.1 is the version floor.
3. **Public release readiness.** Repository visibility flip from private to public. Currently gated on the security and modernisation work above.
4. **WordPress.org submission.** Submit as `sermon-works`. Requires the WP.org-format `readme.txt` rewritten and [Plugin Check](https://wordpress.org/plugins/plugin-check/) passing.

## Salvaged from the original "Pro" pitch

Captured from the upstream `readme.txt`. Starting points, not specifications — anything that earns a place on a real roadmap will be rewritten as something concrete and prioritised first.

- Templates for changing the look
- Multiple podcast support
- Divi integration with custom builder modules
- Elementor: custom elements
- Beaver Builder: custom modules
- WPBakery Page Builder: custom modules
- Stronger out-of-the-box theme compatibility
- Page assignment for archive & taxonomy views
- Easier migration from other plugins
- SEO & marketing tooling
- Live chat support inside the plugin
- PowerPress compatibility

## How to nominate a feature

Open an [issue](https://github.com/mattytap/Sermon-Works/issues/new/choose) and make the case. The bar for adding a new feature is higher than the bar for fixing a bug — Sermon Works is a caretaker restoration, and "fix not change" is the working stance. See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for what makes a strong feature request.
