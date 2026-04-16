# 0007 — GitHub Mirror via CI Filtered Push

**Status:** Accepted
**Date:** 2026-04-16
**Author:** Sebastian Hofer

## Context

The extension needs to be published on Packagist.org and the TYPO3 Extension Repository (TER),
both of which require a publicly accessible repository URL. Development happens on a private,
self-hosted GitLab instance that Packagist and the TYPO3 docs rendering service cannot reach.

At the same time, the repository contains `.claude/` — internal agent configurations, guidelines,
and automated workflows — that must remain private and must never appear in the public mirror.

## Decision

Use a **GitLab CI pipeline** to maintain a public GitHub mirror. On every push to `main` and on
every tag push, a CI job:

1. Clones the full repository
2. Removes `.claude/` from the working history using `git filter-repo --path .claude --invert-paths`
3. Force-pushes the filtered result to the public GitHub repository via a deploy key

The public GitHub repository is **read-only by convention** — no direct development happens there.
All development, code review, and issue tracking for internal contributors stays on GitLab.
GitHub serves as the public distribution surface for Packagist, TER, and community contributors.

## Reasoning

The CI filtered push approach gives full control over what is public without requiring any change
to the development workflow on GitLab. Developers commit and branch normally; the pipeline
handles the rest automatically.

### Alternatives considered

| Alternative | Why rejected |
|---|---|
| GitLab built-in push mirror | Mirrors everything including `.claude/` — no path exclusion support |
| Separate public branch in GitLab | Fragile: `.claude/` must never accidentally land on the public branch; requires discipline across all contributors |
| Manual publishing | Error-prone, creates lag between internal and public state, does not scale |
| Moving `.claude/` to a separate private repo | Breaks the self-contained project structure; fragments the tooling from the code it supports |

## Consequences

### Positive
- `.claude/` (agents, guidelines, internal workflows) is guaranteed never to appear on GitHub
- Development workflow on GitLab is unchanged — no special branches, no extra steps
- GitHub mirror is always in sync with `main` automatically after each merge
- Community contributors can open issues and pull requests on GitHub normally
- Packagist and TYPO3 docs can access the public URL without credentials

### Negative / Trade-offs
- CI pipeline must be maintained; if it breaks, the public mirror falls out of sync
- `git filter-repo` rewrites commit SHAs in the filtered clone — GitHub history has different
  SHAs than GitLab history (acceptable since GitHub is read-only)
- A GitHub deploy key must be rotated when it expires or is compromised
- Force-push to GitHub `main` is required on every sync (normal for mirror setups)

### Implications for consumers
- Consumers install via Packagist (`composer require queo/simple-rest-api`) — transparent
- Community contributors submit PRs on GitHub; maintainers cherry-pick or re-apply on GitLab
- The GitHub repo description should state clearly: "Mirror of internal GitLab repo — PRs welcome,
  development tracked internally"
