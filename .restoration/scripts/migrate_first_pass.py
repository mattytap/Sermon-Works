#!/usr/bin/env python3
"""Bulk-migrate remaining first-pass upstream issues into Sermon Works.

Reads .restoration/upstream-issues.json, builds an issue body per the
ATTRIBUTION-PLAN-approved template (preamble + reviewer notes + verbatim
original body + comment summary + footer), and creates each issue via
`gh issue create`.

Idempotency note: this script is single-shot — re-running will create
duplicates. Used once on 2026-04-23 to migrate nine upstream issues
(#169, #172, #176, #223, #238, #248, #252, #258, #287). The three
others (#170, #263, #271) were created earlier as a template-render
check and are not in this script.
"""

import json
import subprocess
import sys

MIGRATION_DATE = '2026-04-23'
DEFAULT_CREDIT = (
    "Credit for the diagnosis belongs to the original reporter; this migration "
    "brings the report into the Sermon Works tracker so it can be triaged and "
    "fixed here."
)

MIGRATIONS = {
    287: {
        'title': 'https conversion not desired',
        'labels': ['bug', 'priority: standard', 'origin: upstream', 'scope: first-pass'],
        'credit': DEFAULT_CREDIT,
        'reviewer_note': (
            "**Reviewer note on classification:** the upstream inventory classed "
            "this as Security. Reading the report, the functional behaviour is an "
            "inconsistency between the audio player `<source src=...>` (rewritten "
            "to `https://`) and the download link (`http://`) — likely a "
            "side-effect of how the plugin interacts with the third-party Really "
            "Simple SSL plugin. I've labelled it `bug`; there's no exploit signal "
            "in the report, though URL-scheme handling in the audio output path "
            "is worth a glance during the audit."
        ),
    },
    258: {
        'title': 'Asset Pipeline',
        'labels': ['enhancement', 'priority: low', 'origin: upstream', 'scope: deferred'],
        'credit': DEFAULT_CREDIT,
        'reviewer_note': (
            "**Reviewer note on classification:** the upstream inventory classed "
            "this as Security (scope: first-pass). It is actually a feature "
            "proposal — adding webpack to bundle/minify frontend JS and CSS — "
            "and I have reclassified it as `enhancement` under `scope: deferred`. "
            "Not a first-pass concern; worth revisiting once security and bug "
            "fixes are complete."
        ),
    },
    252: {
        'title': 'Incompatibility with Admin Menu Editor Pro',
        'labels': ['bug', 'priority: high', 'origin: upstream', 'scope: first-pass', 'needs-repro'],
        'credit': DEFAULT_CREDIT,
        'reviewer_note': (
            "Upstream report is extremely terse (\"Publishing tools don't show "
            "up\") with no reproduction steps. `needs-repro` applied; we will "
            "need to install Admin Menu Editor Pro on LocalWP to observe the "
            "behaviour before attempting a fix."
        ),
    },
    248: {
        'title': 'Divi comments are not loading except the form',
        'labels': ['bug', 'priority: standard', 'origin: upstream', 'scope: first-pass', 'needs-repro'],
        'credit': DEFAULT_CREDIT,
        'reviewer_note': (
            "The upstream body is the empty GitHub issue template (all fields "
            "blank). `needs-repro` applied; the title is the only signal we "
            "have. We will need a Divi-themed LocalWP instance to observe the "
            "behaviour."
        ),
    },
    238: {
        'title': 'Importing has no success message once completed',
        'labels': ['bug', 'priority: standard', 'origin: upstream', 'scope: first-pass'],
        'credit': DEFAULT_CREDIT,
    },
    223: {
        'title': 'Fix next/previous sermon links not being ordered by date preached',
        'labels': ['bug', 'priority: standard', 'origin: upstream', 'scope: first-pass', 'help wanted'],
        'credit': DEFAULT_CREDIT,
    },
    176: {
        'title': 'Uppercase verses not detected by reftagger',
        'labels': ['bug', 'priority: low', 'origin: upstream', 'scope: first-pass'],
        'credit': DEFAULT_CREDIT,
    },
    172: {
        'title': 'Series separated by commas causes problems',
        'labels': ['bug', 'priority: standard', 'origin: upstream', 'scope: first-pass', 'good first issue'],
        'credit': (
            "Credit for the diagnosis belongs to the original reporter; thanks "
            "also to `@nikola3244` for the discussion of potential fixes on the "
            "upstream thread."
        ),
        'reviewer_note': (
            "**Context from the upstream thread:** the maintainer noted this is "
            "rooted in a [WordPress core ticket](https://core.trac.wordpress.org/ticket/14691) "
            "rather than Sermon Manager itself, and suggested a quote-wrapping "
            "workaround. No code fix landed upstream."
        ),
        'comment_summary': (
            "### Comment thread summary (2 comments, 2018-03-01)\n\n"
            "1. `@nikola3244` (2018-03-01) explained this is a WordPress core "
            "bug ([ticket #14691](https://core.trac.wordpress.org/ticket/14691)) "
            "rather than a Sermon Manager issue; suggested a quote-wrapping "
            "workaround for custom-taxonomy input "
            "(e.g. `\"Doubt, Disbelief, and Disobedience\", God` parsed as two "
            "terms).\n"
            "2. `@drmikegreen` (2018-03-01) agreed quote-wrapping could work; "
            "noted that making Series a category (rather than a tag-style "
            "taxonomy) would sidestep the problem but be a bigger change; also "
            "floated the pipe character `|` as a possible separator."
        ),
    },
    169: {
        'title': 'Sermon Manager not listed in My Top Level Menu',
        'labels': ['bug', 'priority: low', 'origin: upstream', 'scope: first-pass'],
        'credit': (
            "Credit for the diagnosis belongs to the original reporter; thanks "
            "also to `@nikola3244` for the debugging dialogue on the upstream "
            "thread."
        ),
        'reviewer_note': (
            "**Apparent resolution on upstream thread:** the reporter ultimately "
            "identified a conflict with the \"Delete Comments by Status\" plugin "
            "(by Micro Solutions Bangladesh), which also removed WooCommerce and "
            "Events Manager menu items. Removing the conflicting plugin restored "
            "Sermon Manager's menu. The upstream thread did not reach a code fix "
            "but identified the root cause. Worth closing with a pointer to the "
            "workaround, or investigating whether a defensive approach in our "
            "menu registration could survive plugins like these."
        ),
        'comment_summary': (
            "### Comment thread summary (10 comments, 2018-01-30 → 2018-01-31)\n\n"
            "1. `@nikola3244` asked what the reporter saw at the direct "
            "sermon-admin URL (`edit.php?post_type=wpfc_sermon`).\n"
            "2. `@cwgeimer` confirmed that URL loaded the sermon listing — just "
            "the menu item was missing.\n"
            "3. `@nikola3244` concluded Sermon Manager was initialised correctly "
            "and suspected another plugin was suppressing menu registration; "
            "asked for test-credentials access.\n"
            "4. `@cwgeimer` later returned: removing an unused plugin fixed the "
            "issue.\n"
            "5. `@nikola3244` asked which plugin.\n"
            "6. `@cwgeimer`: \"Delete Comments by Status, by Micro Solutions "
            "Bangladesh.\"\n"
            "7. `@nikola3244` noted that plugin also removed WooCommerce and "
            "Events Manager menu items, cross-referenced two other upstream "
            "support threads against it, and flagged for follow-up. No code fix "
            "landed in Sermon Manager."
        ),
    },
}


def blockquote(text):
    lines = text.rstrip().split('\n')
    return '\n'.join('> ' + line if line.strip() else '>' for line in lines)


def build_body(issue, mig):
    n = issue['number']
    author = issue['author']['login']
    date = issue['createdAt'][:10]
    raw_body = issue.get('body', '').rstrip()
    body_bq = blockquote(raw_body) if raw_body else '> _(empty upstream body)_'

    parts = [
        f"**Migrated from upstream.** Originally reported by `@{author}` on "
        f"{date} as [WP-for-Church/Sermon-Manager#{n}]"
        f"(https://github.com/WP-for-Church/Sermon-Manager/issues/{n}). "
        f"{mig['credit']}",
    ]

    if mig.get('reviewer_note'):
        parts.append(mig['reviewer_note'])

    parts.append('---')
    parts.append('### Original issue body')
    parts.append(body_bq)

    if mig.get('comment_summary'):
        parts.append(mig['comment_summary'])
    else:
        n_comments = len(issue.get('comments', []))
        if n_comments == 0:
            parts.append("### Comment thread\n\nNo comments on the upstream thread.")
        else:
            parts.append(
                f"### Comment thread\n\n{n_comments} comments on the upstream "
                f"thread — see the upstream URL above for the full record."
            )

    parts.append('---')
    parts.append(
        f"**Upstream:** [#{n}](https://github.com/WP-for-Church/Sermon-Manager/issues/{n}) · "
        f"**Originally reported:** {date} · "
        f"**Migrated to Sermon Works:** {MIGRATION_DATE} · "
        f"**Upstream reference:** `upstream-issue-{n}`"
    )

    return '\n\n'.join(parts)


def main():
    with open('.restoration/upstream-issues.json', 'r', encoding='utf-8') as f:
        issues = json.load(f)
    by_number = {i['number']: i for i in issues}

    ordered = sorted(MIGRATIONS.keys())

    created = []
    for num in ordered:
        mig = MIGRATIONS[num]
        issue = by_number[num]
        body = build_body(issue, mig)
        cmd = [
            'gh', 'issue', 'create',
            '--title', mig['title'],
            '--body', body,
        ]
        for label in mig['labels']:
            cmd.extend(['--label', label])
        result = subprocess.run(cmd, capture_output=True, text=True)
        if result.returncode == 0:
            url = result.stdout.strip()
            print(f'  upstream #{num} -> {url}')
            created.append((num, url))
        else:
            print(f'  upstream #{num} FAILED: {result.stderr.strip()}', file=sys.stderr)
            sys.exit(1)

    print()
    print(f'Created {len(created)} issues.')


if __name__ == '__main__':
    main()
