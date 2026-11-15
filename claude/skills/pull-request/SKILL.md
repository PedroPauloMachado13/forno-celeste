---
name: pull-request
description: Prepare and open a GitHub pull request for the current branch and sync it with the linked ClickUp task through the ClickUp MCP server. Manual only — the developer runs /pull-request.
disable-model-invocation: true
argument-hint: "[base branch — default develop; hotfix/* defaults to main] [--draft]"
allowed-tools: Bash(git status *) Bash(git diff *) Bash(git log *) Bash(git branch *) Bash(gh pr view *) Bash(gh pr list *) Bash(gh auth status *)
---

# /pull-request — GitHub PR + ClickUp sync

Arguments: `$ARGUMENTS`. Rules: `.claude/rules/git-workflow.md`.

## Current state

- Branch: !`git branch --show-current`
- Uncommitted changes: !`git status --short`
- Commits not in develop: !`git log --oneline origin/develop..HEAD`

## 1. Preconditions — stop and tell the developer if any fails

1. Not on `main` or `develop`, and the branch name follows `<type>/<task-id>-<kebab>`.
2. Working tree clean. If not, show the changes and ask whether to commit them (Conventional Commits, `[CU-<task-id>]` suffix).
3. `/qa` passed for the current `HEAD` in this session. If not, run the `qa` skill first.
4. No open Critical/High findings from the agent reviews in this session.
5. `gh auth status` is OK (the developer runs `gh auth login` on the host if not).
6. Base branch: argument, else `main` for `hotfix/*`, else `develop`. Run `git fetch origin` and confirm the branch merges cleanly (`git merge-base` / `git diff origin/<base>...HEAD`).

## 2. Load the ClickUp task

1. Extract the task ID from the branch name (ClickUp ID like `86b1x2y3z` or custom ID like `BILL-123`).
2. Fetch the task with the ClickUp MCP tools (server `clickup` in `.mcp.json`; e.g. `clickup_get_task`, or `clickup_search` for custom IDs): name, description, acceptance criteria, URL, list, current status.
3. If the ClickUp tools are missing or unauthenticated, tell the developer to run `/mcp` and authenticate `clickup`. Continue without ClickUp only if they say so.
4. Compare the task's acceptance criteria with what the branch implements. List any criterion not covered — the developer decides whether to proceed.

## 3. Gather the change

- `git diff --stat origin/<base>...HEAD`, `git log --oneline origin/<base>..HEAD`.
- Note: migrations (and whether they are reversible), new env vars, new queues/jobs, new routes, new dependencies, docs touched.
- Collect the latest verdicts from this session: architect, database-manager, security-auditor, QA, and `/test` if it was run.

## 4. Draft the PR

- **Title:** `<type>(<scope>): <summary> [CU-<task-id>]` (≤ 72 characters before the tag).
- **Body:** fill [template.md](template.md). Plain English, short sentences; a reviewer who never saw the task must understand it.
- Show the title and body to the developer and **wait for approval** — the PR is visible to the whole team.

## 5. Publish

```bash
git push -u origin <branch>            # prompts for approval (settings: ask)
gh pr create --base <base> --head <branch> --title "<title>" --body-file - <<'EOF'
<approved body>
EOF
```

Add `--draft` when requested. Then `gh pr view --json url,number` to get the real URL.

## 6. Report

PR URL and number · base branch · ClickUp task URL and its new status · what remains (human review, CI checks: `gh pr checks <number>`).

## Never

Merge, approve or close PRs · push to `main`/`develop` · force-push · open a PR without the developer approving the text · include secrets or `.env` content in the PR body.
