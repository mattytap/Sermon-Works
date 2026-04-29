# Contributing to Sermon Works

Thanks for considering a contribution. Sermon Works is a caretaker restoration of WP-for-Church's [Sermon Manager](https://github.com/WP-for-Church/Sermon-Manager) plugin, currently in pre-release. Contributions are welcome — and shaped by the project's stance: **fix not change** for code that already works, with security and correctness prioritised over feature additions.

By participating, you agree to abide by the [Code of Conduct](../CODE_OF_CONDUCT.md).

## Before you start

1. **Check the [issue tracker](https://github.com/mattytap/Sermon-Works/issues) first.** Many bugs are already filed and many feature ideas are already on the [ROADMAP](../ROADMAP.md) as no-commitment backlog items.
2. **Open an issue before opening a PR for non-trivial changes.** A short description of what you are proposing — and why — is faster than writing code that may not fit. Trivial fixes (typos, comment corrections, obvious one-line bug fixes) are fine to PR directly.
3. **Security findings go through [SECURITY.md](../SECURITY.md), not the public issue tracker.** Sermon Works is tracking security work openly, but new findings need private disclosure first so existing Sermon Manager installations aren't exposed.

## Branching and commits

There is one long-lived branch: `main`. Feature work happens on local feature branches that are PR'd into `main`.

Commit style:

- **Conventional Commits** (`type(scope): subject`), kept succinct. Recent commits in `git log` are the best style guide.
- **One conceptual change per commit.** Granular commits are preferred over batched ones — easier to review, easier to revert.
- **`Refs #N` rather than `Closes #N`** until an issue's stated validation step has been run. Many security issues have a smoke-test in their scope checklist; commits that ship the code refer to the issue without auto-closing it.

## Attribution for ingested upstream work

Sermon Works preserves attribution to the original Sermon Manager authors at both the file level (existing PHPDoc tags retained) and the commit level (when ingesting upstream Pull Requests, the original author is preserved as git commit author). See [`.restoration/ATTRIBUTION-PLAN.md`](../.restoration/ATTRIBUTION-PLAN.md) for the methodology.

If your contribution picks up someone else's prior work, please add `Co-authored-by:` trailers so the credit is preserved and reflected in [CONTRIBUTORS.md](../CONTRIBUTORS.md).

## Code style

The project lints with [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards) via PHP_CodeSniffer. To run the linter locally:

```sh
composer install
vendor/bin/phpcs
```

The ruleset (`phpcs.xml.dist`) is `WordPress-Extra`, so the `Security.*` sniffs fire. New code should be clean against this ruleset; legacy code is being brought up to it incrementally as part of the restoration.

**PHP version floor: 8.1.** The plugin will not activate on lower versions.

## Testing

PHPUnit is configured (`phpunit.xml.dist`) but the WordPress test harness is not yet wired up. For now, manual smoke-testing is done in a local WordPress install. Test-harness contributions will become a norm once the harness is in place.

## Translations

Translations will be coordinated via WordPress.org's GlotPress once Sermon Works is published there. The translation pipeline is not live yet.

## Questions?

Open a [GitHub Discussion](https://github.com/mattytap/Sermon-Works/discussions) or an issue tagged `question`. The maintainer is [@mattytap](https://github.com/mattytap).
