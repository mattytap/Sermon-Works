# Security Policy

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues, discussions, or pull requests.**

Use GitHub's private vulnerability reporting:

> **[Report a vulnerability](https://github.com/mattytap/Sermon-Works/security/advisories/new)**

When reporting, please include:

- A description of the vulnerability and its impact
- Steps to reproduce, ideally with a minimal proof of concept
- Affected versions or commits
- Any suggested mitigation

You should receive an acknowledgement within five working days.

## Why private disclosure matters here

Sermon Works exists because the upstream [Sermon Manager](https://github.com/WP-for-Church/Sermon-Manager) plugin was left dormant with known unpatched vulnerabilities still installed on production sites worldwide. Quiet, coordinated disclosure of any new findings against Sermon Works helps protect those existing Sermon Manager installations — many of which Sermon Works will eventually offer a migration path for.

## Disclosure window

Sermon Works follows a 90-day coordinated-disclosure window from the date of acknowledgement. We aim to ship a fix faster — most security work on this codebase is done publicly via the [issue tracker](https://github.com/mattytap/Sermon-Works/issues) — but the 90-day window gives room when a fix needs broader discussion.

When a fix ships:

- We credit the reporter in the release notes (with their permission and preferred attribution)
- We request a CVE assignment where the issue warrants one
- We publish the relevant GitHub Security Advisory

## Supported versions

Sermon Works is in 3.0 release-candidate cycle. The latest commit on `main` is the only supported version; a versioned-release matrix will replace this section at the first stable release.

## Scope

**In scope:**

- Sermon Works plugin source (`sermons.php`, `includes/`, `views/`)
- Bundled vendor libraries (`includes/vendor/*`)

**Out of scope:**

- The original Sermon Manager plugin. Already-disclosed CVEs in the upstream — including CVE-2025-12368, CVE-2025-63000, and CVE-2025-63002 — are tracked in the [Sermon Works issue tracker](https://github.com/mattytap/Sermon-Works/issues); check there first.
- WordPress core, themes, and other plugins running alongside Sermon Works
- Infrastructure of sites running Sermon Works

If you are unsure whether something is in scope, report it anyway. We would rather triage an out-of-scope report than miss something.

## Acknowledgements

Public CVE reporters whose findings are reflected in this codebase are credited in the relevant tracker issues. Future reporters will be credited in release notes and GitHub Security Advisories where applicable.
