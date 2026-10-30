## Summary

<1–3 sentences: what changed and why, in plain words.>

**ClickUp:** [CU-<task-id> — <task name>](<task URL>)

## Changes

**Architecture (Controller → Service → Repository)**
- <e.g. New `InvoiceService::create()` + `InvoiceRepositoryInterface::createWithItems()`>

**Database**
- Migrations: <list or "none"> · Reversible: <yes/no> · Risky on large tables: <yes/no — why>

**Queues / jobs**
- <e.g. `SendInvoiceEmail` on `notifications` (3 tries, backoff 10/60/300s) — or "none">

**Configuration**
- New env vars: <`NAME` — purpose (added to `.env.example`)> — or "none"

## Agent reviews

| Reviewer | Verdict | Open notes |
|---|---|---|
| Architect | <APPROVED / …> | |
| Database manager | | |
| Security auditor | | |
| QA | <PASSED / FAILED> | |
| Tests (`/test`) | <passed / failed / not run> | |

## How to test

1. `docker compose up -d`
2. `docker compose exec -T app php artisan migrate`
3. <steps to exercise the change, with expected results>

## Risks and rollback

- <What could go wrong in production and how to roll back (e.g. "revert PR; migration is reversible with `migrate:rollback --step=1`")>

## Checklist

- [ ] Docs updated (`/doc`)
- [ ] `.env.example` updated (if new variables)
- [ ] No secrets, dumps or personal files committed
- [ ] Worker command covers any new queue name
