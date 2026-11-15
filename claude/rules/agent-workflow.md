# Agent workflow — the feature / fix pipeline

Applies to **every feature and every fix** that changes code, config, migrations or infrastructure. Docs-only changes skip it.

The **main session is the orchestrator**. Subagents never start this pipeline: they do their step and return a report. Subagents cannot ask the developer questions, so the orchestrator collects questions and asks them.

## The team

| Member | Role | Writes |
|---|---|---|
| `architect` (agent) | Designs the code structure, then reviews the diff against `.claude/rules/architecture.md` | Nothing (read-only) |
| `ux-ui-specialist` (agent) | Plans screens, flows, states, microcopy, responsiveness and accessibility; reviews UI after implementation | `docs/ux/**` only |
| `database-manager` (agent) | Designs schema, indexes and query strategy; reviews migrations and SQL; runs DB analysis and approved DB operations | `docs/database/**` only |
| `laravel-specialist` (agent) | Implements every change | **All application code** — the only agent that edits `app/`, `routes/`, `config/`, `database/`, `resources/`, `bootstrap/` |
| `security-auditor` (agent) | Audits every change | Nothing (read-only) |
| `/qa` (skill) | Runs the application checks in Docker | Nothing |
| `/doc` (skill) | Updates beginner-friendly docs | `docs/**`, `README.md`, `.env.example` comments |
| `/test`, `/pull-request` (skills) | **Manual only** — never invoke them yourself; remind the developer | `tests/**` / git + GitHub + ClickUp |

## Pipeline

1. **Intake** — Restate the goal and acceptance criteria in a few lines. Ask the developer about anything ambiguous *before* designing. Make sure work happens on a correctly named branch (`.claude/rules/git-workflow.md`).
2. **Design (parallel)** — Launch `architect` (design mode), `database-manager` (design mode) and `ux-ui-specialist` (design mode, when the change has screens) at the same time, each with the full requirement and the acceptance criteria.
3. **Reconcile** — Compare the two plans. If they conflict (a repository query with no supporting index, a job that needs a new table, a constraint the service doesn't handle), send each agent the other's plan **once** and ask for a joint resolution. Still unresolved → ask the developer, showing both options and their trade-offs.
4. **Approval gate** — Show the developer the consolidated plan (files per layer, schema changes, queues, risks). **Wait for approval** when the change adds or alters tables, adds queues/workers, changes a public API contract, or touches auth, payments or personal data. Small fixes may proceed unless the developer asked to review every plan.
5. **Implement** — Launch `laravel-specialist` with: the requirement, both plans, and every decision taken in steps 3–4.
6. **Review (parallel)** — Launch `architect` (review mode), `database-manager` (review mode), `security-auditor` and `ux-ui-specialist` (review mode, when the diff touches views/components/frontend) at the same time on the resulting diff, each with the implementation report.
7. **Fix loop** — Merge all findings into one deduplicated list. Send every **Critical/High** finding (and any Medium the developer wants fixed) back to `laravel-specialist`. Re-run only the reviewers that raised findings. **Max 3 rounds**; after that, stop and escalate to the developer with the open findings.
8. **QA** — Run the `/qa` skill. Failures go back to `laravel-specialist`, then run `/qa` again.
9. **Docs** — Run the `/doc` skill for anything a newcomer would need to know about the change.
10. **Report** — Tell the developer: what changed, each reviewer's verdict, the QA result, open findings and risks, and the reminder that tests run only with `/test` and the PR opens only with `/pull-request`.

## Conventions shared by all agents

- **Severity:** `Critical` (exploitable, data loss, outage) · `High` (bug or rule violation that must be fixed before merge) · `Medium` (should fix) · `Low` (nice to have) · `Info`.
- **Findings** cite `path:line`, explain *why* it matters, and propose a concrete fix. IDs are prefixed per agent: `A-` architect, `D-` database-manager, `S-` security-auditor, `Q-` QA.
- **Reviewer verdicts** end the report with one line: `VERDICT: APPROVED` · `VERDICT: APPROVED WITH NOTES` · `VERDICT: CHANGES REQUIRED`.
- **Disagreements** are stated as trade-offs. The orchestrator decides or asks the developer; no agent silently overrides another.
- **Evidence:** report the commands actually run (Docker form) and their real output. Never claim a check passed without running it (`.claude/rules/anti-mocking.md`).
- **Proportionality:** a one-line fix gets a short plan and a short review. Don't produce essays for trivial changes.
