# Claude Code setup — Laravel + Docker

Team guide for the `.claude/` folder: what each file does, how the agents collaborate, how to install it in a project, and which decisions are open for review. This file is **not** loaded into Claude's context.

## Folder map

```text
CLAUDE.md                     # minimal, always loaded: project facts + pitfalls & surprises
CLAUDE.local.md               # personal, git-ignored: your ports, tokens, preferences
.mcp.json                     # MCP servers: ClickUp (OAuth) + Laravel Boost (through Docker)
.claude/
├── settings.json             # shared permissions: Docker-only runtime, git safety, secret files
├── settings.local.example.json  # copy to settings.local.json for personal permissions
├── agents/
│   ├── architect.md          # designs + reviews code structure (read-only)
│   ├── laravel-specialist.md # implements everything (only agent that writes app code)
│   ├── database-manager.md   # designs + reviews schema/SQL, runs DB analysis & approved ops
│   └── security-auditor.md   # audits every change (read-only)
├── skills/
│   ├── laravel-specialist/   # upstream skill (vendored, unmodified) preloaded by the agent
│   ├── qa/                   # /qa — runs app checks in Docker (auto, step 8)
│   ├── doc/                  # /doc — beginner-friendly docs (auto, step 9)
│   ├── test/                 # /test — MANUAL only; runs/writes tests, warns on failures
│   └── pull-request/         # /pull-request — MANUAL only; GitHub PR + ClickUp sync
└── rules/
    ├── agent-workflow.md     # the pipeline every feature/fix follows (always loaded)
    ├── architecture.md       # Controller → Service → Repository standard (loads for app/, routes/)
    ├── docker.md             # every command runs in a container (always loaded)
    ├── git-workflow.md       # branches, commits, PRs, merge policy (always loaded)
    └── anti-mocking.md       # no fake code, no mocks of our own classes, no fake evidence (always loaded)
```

## How a feature flows

```mermaid
flowchart TD
    A[Developer request] --> B[Orchestrator: intake + branch]
    B --> C1[architect: design]
    B --> C2[database-manager: design]
    C1 --> D{Plans conflict?}
    C2 --> D
    D -- yes --> E[Agents reconcile / developer decides]
    D -- no --> F[Approval gate]
    E --> F
    F --> G[laravel-specialist: implement]
    G -. consults when plan has a gap .-> C1
    G -. consults when plan has a gap .-> C2
    G --> H1[architect: review]
    G --> H2[database-manager: review]
    G --> H3[security-auditor: audit]
    H1 & H2 & H3 --> I{Critical/High findings?}
    I -- yes, max 3 rounds --> G
    I -- no --> J[/qa/]
    J -- fails --> G
    J -- passes --> K[/doc/]
    K --> L[Report to developer]
    L -. manual .-> M[/test/]
    L -. manual .-> N[/pull-request/ → GitHub + ClickUp]
```

The main Claude session is the orchestrator. Subagents can't ask you questions, so the orchestrator gathers questions and asks you.

## Install in a project

1. Copy `CLAUDE.md`, `.mcp.json` and the `.claude/` folder to the project root. Fill in the **Project** and **Docker services** sections of `CLAUDE.md`.
2. Add to `.gitignore`:
   ```gitignore
   CLAUDE.local.md
   .claude/settings.local.json
   ```
3. Each developer copies `CLAUDE.local.md` (template) and, optionally, `.claude/settings.local.example.json` → `.claude/settings.local.json`.
4. **GitHub CLI** on the host: `gh auth login`.
5. **ClickUp MCP:** start Claude Code in the project, approve the `clickup` server, run `/mcp` and complete the OAuth login.
6. **Laravel Boost (recommended):**
   ```bash
   docker compose exec app composer require laravel/boost --dev
   docker compose exec app php artisan boost:install
   ```
   In the installer, select Claude Code, **skills** and **MCP**. Skip the AI guidelines if you want `CLAUDE.md` to stay minimal (Boost writes them into `CLAUDE.md`). Afterwards, make sure `.mcp.json` still runs Boost through Docker (`docker compose exec -T app php artisan boost:mcp`) — Boost may regenerate it with a host `php` command.
7. Protect `main` and `develop` on GitHub (PR required, 1 approval, status checks, no force-push).
8. Run `/doctor prompt-audit` once to let Claude Code check the instruction files for conflicts.

### Projects using Laravel Sail

Sail's PHP service is `laravel.test`. Put that in the `CLAUDE.md` services table and in `.mcp.json`. `settings.json` denies `./vendor/bin/*` on the host, which also blocks `./vendor/bin/sail`; keep using `docker compose exec -T laravel.test …`, or remove those two deny rules.

## Design decisions to review

| # | Decision | Where | Alternative |
|---|---|---|---|
| 1 | The main session orchestrates; agents exchange information through it. Only `laravel-specialist` may spawn other agents (architect / database-manager) to consult | `agent-workflow.md`, agents | Let every agent spawn others (more autonomy, more cost and less predictability) |
| 2 | Reviewers (`architect`, `database-manager`, `security-auditor`) run on `opus`; `laravel-specialist` inherits the session model | agents' `model:` | All `inherit`, or `sonnet` for the implementer to cut cost |
| 3 | Approval gate only for schema, queues, public API, auth/payments/personal data | `agent-workflow.md` step 4 | Approve every plan |
| 4 | The implementer writes **no tests**; tests only via manual `/test`; `/qa` never runs the suite | agent override table, `qa` | Let `/qa` run the suite, or let the implementer write tests |
| 5 | `/qa` and `/doc` run automatically in the pipeline; `/test` and `/pull-request` are manual | skills' `disable-model-invocation` | Make `/doc` manual too |
| 6 | `/qa` runs as a forked subagent to keep the main context clean | `qa` `context: fork` | Run inline to see every command live |
| 7 | Branching: `main` + `develop` + `feature/fix/hotfix`, squash merge, merge (not rebase) to update branches | `git-workflow.md` | Trunk-based (only `main`) |
| 8 | Anti-mocking: real testing DB on the production engine; only Laravel fakes at external boundaries | `anti-mocking.md` | Allow SQLite in-memory for speed |
| 9 | `laravel-specialist` skill vendored unmodified and pinned; overrides live in the agent | `skills/laravel-specialist/UPSTREAM.md` | Install with a package manager and accept drift |
| 10 | Architecture open decisions (grouping, base repository, actions, DTO library, Larastan level, queue driver) | `architecture.md` §11 | — |

## Keeping it healthy

- **Surprises go to `CLAUDE.md`** → "Known pitfalls and surprises", one line each, dated.
- **Architecture changes** come from the architect's "Proposed changes to the standard" sections → team review → edit `architecture.md`, bump its version, add a changelog line.
- **Permissions are enforced by Claude Code, instructions are not.** Anything that must never happen belongs in `settings.json` (`deny`/`ask`) or GitHub branch protection, not only in prose. Permission rules match command text, so they guide Claude but are not a security boundary.
- Keep always-loaded files short (`CLAUDE.md`, `agent-workflow.md`, `docker.md`, `git-workflow.md`, `anti-mocking.md`): they cost context on every prompt.
