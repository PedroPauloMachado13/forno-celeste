---
name: qa
description: Quality-assurance run inside Docker — checks containers, dependencies, app boot and caches, migrations, routes, code style, static analysis, queues and scheduler, logs, assets, dependency audit and a smoke test of the changed endpoints. Use after every implementation (step 8 of the agent workflow) or when the developer asks whether everything works. Does NOT run the test suite (that is the manual /test skill).
argument-hint: "[changed | full] [base branch, default develop]"
context: fork
background: false
---

# QA — prove the application works, with real evidence

You verify; you don't fix. Every check runs **inside Docker** (`.claude/rules/docker.md`) and is reported with the command and its real output (`.claude/rules/anti-mocking.md`). A check you couldn't run is `SKIPPED` with the reason — never `PASS`.

Arguments: `$ARGUMENTS` — scope `changed` (default) or `full`, and the base branch (default `develop`).

## 1. Scope

- `git status --short` and `git diff --stat origin/<base>...HEAD` → list changed files and group them: migrations, models, routes/controllers, jobs/listeners/schedule, config, frontend, composer/npm manifests, Docker files.
- With `full`, run every check regardless of what changed.

## 2. Checks

| # | Check | Run when | Command(s) — all through `docker compose exec -T app …` unless noted |
|---|---|---|---|
| 1 | Containers healthy | always | host: `docker compose ps` — every service `running`/`healthy` |
| 2 | Composer consistent | always | `composer validate --no-check-publish` |
| 3 | App boots | always | `php artisan about` |
| 4 | Caches compile | always | `php artisan optimize` then `php artisan optimize:clear` (catches route closures and config errors) |
| 5 | Migrations | always | `php artisan migrate:status`; if pending: `php artisan migrate` |
| 6 | Migrations reversible | new migrations | `php artisan migrate:rollback --step=<n>` then `php artisan migrate` — this prompts the developer; if declined, mark `SKIPPED (not approved)` |
| 7 | Routes | routes/controllers changed | `php artisan route:list -v --path=<prefix>` — new routes exist with the expected verbs, names and auth middleware |
| 8 | Code style | PHP changed | `./vendor/bin/pint --test <changed php files>` |
| 9 | Static analysis | PHP changed and `vendor/bin/phpstan` exists | `./vendor/bin/phpstan analyse <changed paths> --memory-limit=1G` |
| 10 | Queues | jobs/listeners/events changed | `php artisan queue:restart`; trigger the flow (smoke test); host: `docker compose logs --since=5m queue`; `php artisan queue:failed` shows no new failures; Horizon projects: `php artisan horizon:status` |
| 11 | Worker coverage | new queue name | every queue name used in code appears in the worker command of `docker-compose*.yml` |
| 12 | Scheduler | console/schedule changed | `php artisan schedule:list` |
| 13 | Smoke test | always | `GET /up` (Laravel health route) returns 200; each changed route answers without a 5xx (401/403/422 are fine unauthenticated). Use a local token from `CLAUDE.local.md` if one is listed. Prefer `curl` inside the app/web container; report URL + status code |
| 14 | Logs clean | always | `tail -n 200 storage/logs/laravel.log` (or Boost `last-error`) — no new `ERROR`/`CRITICAL` since QA started |
| 15 | Assets build | frontend changed | host: `docker compose run --rm node npm run build` |
| 16 | Dependency audit | manifests changed, or `full` | `composer audit`; frontend: `docker compose run --rm node npm audit --omit=dev` |
| 17 | Test suite | — | **Not run.** Always report `SKIPPED (manual — run /test)` |

## 3. Report

```markdown
## QA report — <branch> (<scope>)
| # | Check | Result | Evidence (command → key output) |
|---|---|---|---|
| 1 | Containers healthy | ✅ PASS | `docker compose ps` → 6/6 running |
| … | … | ❌ FAIL / ⚠️ WARN / ⏭️ SKIPPED (<reason>) | … |

### Failures
| ID | Check | What failed | Likely cause | Suggested owner |
(IDs Q-1, Q-2, …; owner is usually laravel-specialist, or database-manager for DB issues)

QA VERDICT: PASSED | FAILED (<n> failures)
```

`PASSED` requires every applicable check to be PASS or WARN. Test-suite `SKIPPED` doesn't block.

## Never

- Edit code, tests or config to make a check pass.
- Run destructive commands (`migrate:fresh`, `db:wipe`, `down -v`) to get a clean state.
- Print secrets (`env`, `printenv`, `docker compose config`, `.env`).
