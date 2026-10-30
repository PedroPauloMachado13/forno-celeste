---
name: test
description: Run and/or write the automated test suite inside Docker and report every failure to the developer. Manual only — the developer runs /test.
disable-model-invocation: true
argument-hint: "[run <path | --filter=Name>] | [write <feature | class>] | [coverage]"
---

# /test — run or write tests, and surface failures loudly

Arguments: `$ARGUMENTS`

| Argument | Meaning |
|---|---|
| *(empty)* or `run` | Run the whole suite |
| `run <path>` / `run --filter=<Name>` | Run part of the suite |
| `write <feature or class>` | Write tests for it, then run them |
| `coverage` | Run with coverage (needs PCOV or Xdebug in the container) |

Rules that always apply: `.claude/rules/docker.md` and `.claude/rules/anti-mocking.md`.

## 1. Pre-flight (stop if any fails)

1. Containers up: `docker compose ps`.
2. Framework: `composer.json` `require-dev` → Pest (`pestphp/pest`) or PHPUnit.
3. **Test database isolation** — `RefreshDatabase` wipes the database it points at. Read `phpunit.xml` (and `.env.testing` if it exists) and confirm `DB_DATABASE` points to a **dedicated testing database**, never the development one. Not isolated → stop and show the developer what to change (e.g. `<env name="DB_DATABASE" value="testing"/>` plus that database created in the `db` container).
4. Same engine as production: if tests run on SQLite while production uses MySQL/PostgreSQL, warn the developer (allowed only if they accept it).

## 2. Run

```bash
docker compose exec -T app php artisan test                       # whole suite
docker compose exec -T app php artisan test <path>                # one file/folder
docker compose exec -T app php artisan test --filter=<Name>       # one test/class
docker compose exec -T app php artisan test --coverage --min=80   # coverage mode
```

Capture the full output. Don't re-run hoping a failure disappears; if a test is flaky, report it as flaky with both outputs.

## 3. When anything fails — STOP and warn

Do **not** edit code or tests to make them pass. Show this to the developer, at the top of your answer:

```markdown
> ⚠️ **TESTS FAILING — <failed> failed / <total> total** (<duration>)

| # | Test | File:line | Error (short) | Likely cause: code or test? |
|---|---|---|---|---|

<details><summary>Raw output (failures only)</summary>
…
</details>

**How do you want to proceed?**
1. Fix the code (orchestrator sends it to `laravel-specialist`)
2. Update the test (only if the requirement really changed — explain which)
3. Leave it and note it in the PR
```

Then wait for the developer's choice.

## 4. Writing tests (`write …`)

1. Read the code under test and its route/FormRequest/Policy/Service/Repository chain.
2. Plan the cases before writing:
   - **HTTP feature tests:** happy path, validation errors (422 with the expected keys), unauthenticated (401), unauthorized (403), another user's record (IDOR → 403/404), not found (404), side effects persisted in the database.
   - **Services:** business rules and domain exceptions, against the real testing database.
   - **Jobs/listeners:** call `handle()` for real; assert the outcome and idempotency (running twice is safe). Separately assert dispatch with `Queue::fake()`/`Bus::fake()` only when that is the behaviour under test.
   - **Repositories:** queries return the right rows, scoped correctly, eager-loaded, paginated.
3. Use factories (with states) and `RefreshDatabase` (or `LazilyRefreshDatabase`). Arrange → Act → Assert. Test names describe behaviour (`it rejects invoices for blocked customers`).
4. Fake only external boundaries (`Http::fake()`, `Mail::fake()`, `Notification::fake()`, `Storage::fake()`, fake adapters for SDKs). Never mock classes from `app/`.
5. Write files under `tests/Feature/<Domain>/` or `tests/Unit/<Domain>/`, mirroring `app/`. Follow the existing style (Pest or PHPUnit).
6. Run the new tests, then the whole suite. New tests that fail are reported exactly like section 3 — a failing new test may have found a real bug.

## 5. When everything passes

Report: command, totals, duration and (in coverage mode) the coverage percentage — from the real output.
