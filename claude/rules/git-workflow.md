# Git and GitHub workflow

## Branches

| Branch | Purpose | Created from | Merges into |
|---|---|---|---|
| `main` | Production — every commit is deployable, tagged `vX.Y.Z` | — | — |
| `develop` | Integration / staging | `main` | `main` (release PR) |
| `feature/<task-id>-<short-kebab>` | New functionality | `develop` | `develop` |
| `fix/<task-id>-<short-kebab>` | Bug fix found before release | `develop` | `develop` |
| `hotfix/<task-id>-<short-kebab>` | Urgent production fix | `main` | `main`, then back-merge `main` → `develop` |
| `chore/…`, `refactor/…`, `docs/…` | Maintenance | `develop` | `develop` |

- `<task-id>` is the ClickUp task ID (e.g. `86b1x2y3z`) or the custom ID (e.g. `BILL-123`). Example: `feature/86b1x2y3z-invoice-export`.
- `/pull-request` finds the ClickUp task from the branch name. No task? Ask the developer for one before creating the branch.
- Never commit to or push `main`/`develop` directly. Never force-push a shared branch. Never rewrite published history.

## Commits

- Conventional Commits: `<type>(<scope>): <imperative summary> [CU-<task-id>]` — types: `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `chore`, `build`, `ci`.
- Small, focused commits; each leaves the app bootable.
- Never commit: `.env*` (except `.env.example`), credentials, DB dumps, `CLAUDE.local.md`, `.claude/settings.local.json`.
- Agents commit only when the developer asks or as part of `/pull-request`.

## Keeping a branch up to date

- `git fetch origin && git merge origin/develop` — merge, not rebase, so a force-push is never needed. Resolve conflicts, then run `/qa` again.

## Pull requests

- Opened only through `/pull-request`. Target `develop` (`hotfix/*` → `main`).
- One PR = one ClickUp task. Aim for < ~400 changed lines; split larger work into stacked tasks.
- Merge requires: 1 human approval, green CI, no open Critical/High findings from the agent reviews.
- **Agents never merge, approve or close PRs.**
- Merge strategy: squash merge into `develop`; merge commit `develop` → `main` for releases. Delete the branch after merge.

## Enforced on GitHub, not by agents

Protect `main` and `develop`: require a PR, 1 approval and passing status checks; block force-push and deletion.
