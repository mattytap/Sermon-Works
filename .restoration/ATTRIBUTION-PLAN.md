# Upstream PR Ingestion — Attribution Plan

> How we bring an upstream Pull Request from `WP-for-Church/Sermon-Manager` into `mattytap/Sermon-Works` while giving the original author 100% credit.

## Principle

Every change authored by an upstream contributor must retain them as the git **author** on the resulting commit(s) in Sermon-Works. The maintainer (or Claude Code acting on their behalf) becomes the **committer** — the person who applied the patch. This is the same mechanism the Linux kernel uses for patches from non-maintainers: proper attribution baked into the ledger, not just a line in a credits file.

Supplementary attribution also goes into `CONTRIBUTORS.md` at the repo root and, at release time, into the Credits section of `readme.txt` for the WordPress.org plugin page.

## Preconditions

- You are in the `Sermon-Works` working directory
- Either the `upstream` remote is configured (pre-disconnect), **or** the PR has already been fetched into a local `upstream-pr-<N>` branch (post-disconnect). Before the upstream remote was removed, every open PR's head was fetched into a durable local branch and pushed to `origin` — so ingestion post-disconnect starts from those branches without needing `upstream` at all.
- You've reviewed the PR and decided to ingest it (the inventory in `.restoration/inventory.md` is the starting triage)

## Per-PR workflow

### 1. Fetch the upstream PR branch into a local tracking branch

```
git fetch upstream pull/<N>/head:upstream-pr-<N>
```

Replace `<N>` with the upstream PR number. The `pull/<N>/head` ref is GitHub's exposed head of every PR — it gives us the exact commits the author submitted.

**Skip this step if the branch already exists locally** (i.e. it was pre-fetched before the `upstream` remote was severed). Verify with `git branch | grep upstream-pr-<N>`.

### 2. Create a clean branch for our ingestion

```
git checkout -b pr/<N>-<short-kebab-description> upstream-pr-<N>
```

Branch naming convention: `pr/<upstream-number>-<short-description>`, e.g. `pr/274-fix-shortcode-pagination`. Prefix `pr/` groups ingestion branches together in `git branch` listings.

### 3. Rebase onto our `main` (if the PR targeted an old base)

```
git rebase main
```

If the rebase has conflicts, resolve them carefully — the rule is: **preserve the author's intent, don't rewrite their code unless the upstream base has moved under it**. When in doubt, commit the rebase merge and open the PR for discussion; don't silently alter the author's diff.

If the rebase is messy enough that we'd be rewriting the change substantially, prefer to close the ingestion PR with a note and re-implement the feature as our own work with `Co-authored-by:` trailers crediting the original author.

### 4. Push to our `origin`

```
git push origin pr/<N>-<short-description>
```

### 5. Open a PR in Sermon-Works with the attribution template

```
gh pr create \
  --repo mattytap/Sermon-Works \
  --base main \
  --head pr/<N>-<short-description> \
  --title "<original PR title> (from upstream #<N> by @<author>)" \
  --body-file .restoration/templates/ingestion-pr-body.md
```

Or inline the body. Template:

```
### Ingesting upstream contribution

Originally submitted by **@<upstream-author>** as [WP-for-Church/Sermon-Manager#<N>](<upstream-url>) on <YYYY-MM-DD>.

This PR brings that change into Sermon Works with the original author preserved in the git commit metadata. Review as you would any PR — the author's intent is the baseline, and any further changes should be proposed as follow-up commits with their own attribution.

**Original description:**

> <first paragraph or two of original PR body, quoted verbatim>

**Changes in this ingestion:**
- [ ] Rebased onto current `main` — conflicts: <none | describe>
- [ ] CONTRIBUTORS.md updated
- [ ] Tested locally — <how>
- [ ] No behavioural drift from original intent

Closes: <any matching upstream issue in our tracker>
```

### 6. Update `CONTRIBUTORS.md`

Add a row/entry for this contributor (or append to their existing row if they have one). See `CONTRIBUTORS.md` for format.

### 7. Merge or iterate

Review the PR yourself (or with the maintainer). Land it on `main` via standard merge/squash. **Do not squash commits that have multiple authors** — squashing would collapse the upstream author's commits into one under your name. Use plain merge (or rebase merge if linear history is wanted) to preserve author metadata.

## Rejected PRs — still credit the author

If an upstream PR is reviewed and declined (e.g. introduces a feature that doesn't fit our scope), close the ingestion PR in Sermon-Works with a respectful comment explaining why, tag the original author (with `@<upstream-author>` — but in a *local* comment only, not one that pings them on GitHub; see below), and add them to `CONTRIBUTORS.md` under an "Acknowledgements" section for the effort even though the code wasn't merged.

**Don't ping upstream authors with @mentions in Sermon-Works** unless they've indicated interest in the restoration. Most will have moved on; @-mentioning them years later on an abandoned project's fork is noise. Credit is given in the record, not in notifications.

## Co-authored commits

When *we* modify someone's ingested code before merging (for example, to resolve a rebase conflict meaningfully or to fix a bug in their patch), use a `Co-authored-by:` trailer so the original author still appears in GitHub's commit attribution:

```
Fix shortcode pagination on static front page

Rebased onto current main; no behavioural changes from the original
patch. Adjusted comment formatting to match WPCS.

Co-authored-by: Brian Freytag <brianfreytag@users.noreply.github.com>
```

The `Co-authored-by:` trailer must be on its own line at the bottom of the commit message, preceded by a blank line. GitHub parses it automatically.

## Sanity checks before breaking the upstream connection

Before we remove the `upstream` remote and sever the fork relationship on GitHub, confirm:

- [ ] Every open upstream PR has been triaged (ingested, deferred, or rejected with attribution)
- [ ] Every open upstream issue has been triaged (migrated to Sermon-Works issues, or deferred)
- [ ] `CONTRIBUTORS.md` reflects every upstream contributor who's had code or issue-reports land
- [ ] `.restoration/` is committed and pushed so the evidence trail is durable
