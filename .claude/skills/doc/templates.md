# Documentation templates

Copy the template, delete the hints in `<angle brackets>`, keep the headings.

---

## Feature — `docs/features/<feature>.md`

````markdown
# <Feature name>

<2–3 sentences: what it does, who uses it, why it exists.>

## How it works

```mermaid
sequenceDiagram
    <actor and main steps>
```

1. <Step in plain words — e.g. "The customer submits the form.">
2. <…>

## Where the code lives

| Part | Path |
|---|---|
| Route | `routes/api.php` → `invoices.store` |
| Controller | `app/Http/Controllers/Api/Billing/InvoiceController.php` |
| Business rules | `app/Services/Billing/InvoiceService.php` |
| Data access | `app/Repositories/Eloquent/Billing/EloquentInvoiceRepository.php` |
| Background work | `app/Jobs/Billing/SendInvoiceEmail.php` (queue: `notifications`) |

## Business rules

- <Rule in plain words — e.g. "Blocked customers can't receive new invoices.">

## Try it locally

```bash
docker compose up -d
docker compose exec -T app php artisan migrate
<command or request that exercises the feature>
```

Expected result: <what you should see>.

## Common problems

| Symptom | Cause | Fix |
|---|---|---|

Last updated: YYYY-MM-DD
````

---

## Endpoint — `docs/api/<resource>.md`

````markdown
## `POST /api/invoices` — Create an invoice

<One sentence: what it does.>

- **Auth:** <Sanctum token / session> · **Permission:** <policy ability>
- **Rate limit:** <e.g. 60/min>

### Request

| Field | Type | Required | Rules | Example |
|---|---|---|---|---|

```json
{ "customer_id": 12, "due_date": "2026-12-01", "items": [{ "description": "Consulting", "amount_cents": 150000 }] }
```

### Responses

| Status | When | Body |
|---|---|---|
| 201 | Created | `InvoiceResource` |
| 401 | No/invalid token | — |
| 403 | Not allowed | — |
| 422 | Validation failed | `{ "message": "…", "errors": { "field": ["…"] } }` |

### Example

```bash
curl -s -X POST http://localhost:<port>/api/invoices \
  -H "Authorization: Bearer <local-token>" -H "Accept: application/json" \
  -H "Content-Type: application/json" -d '<json>'
```
````

---

## Runbook (jobs, queues, scheduled tasks) — section in `docs/operations/queues.md`

```markdown
### <JobName>

- **What it does:** <plain words>
- **Triggered by:** <service method / event / schedule>
- **Queue:** `<name>` · **Tries:** <n> · **Backoff:** <seconds> · **Timeout:** <seconds> · **Unique:** <yes/no>
- **Safe to run twice?** <yes — why / no — why>

**Check it is working**
- `docker compose ps queue`
- `docker compose logs --tail=100 queue`
- `docker compose exec -T app php artisan queue:failed`

**When it fails**
1. Read the error: `docker compose exec -T app php artisan queue:failed`
2. Fix the cause (<common causes>)
3. Retry: `docker compose exec -T app php artisan queue:retry <id>` (or `all`)
```

---

## Configuration row — table in `docs/configuration.md`

```markdown
| Variable | Required | Default | What it controls | Where to get it |
|---|---|---|---|---|
| `INVOICE_PDF_DISK` | no | `local` | Storage disk for generated PDFs | — |
```

---

## Integration — `docs/integrations/<service>.md`

```markdown
# <Service> integration

<What we use it for, in 2 sentences.>

- **Code:** `app/Integrations/<Vendor>/` (interface + client)
- **Credentials:** env vars `<NAMES>` — values live in <password manager / secret store>, never in git
- **Sandbox / local:** <how to test without hitting production>
- **Limits:** <rate limits, timeouts>
- **When it is down:** <what our app does — retries, queue, user message>

Last updated: YYYY-MM-DD
```

---

## ADR — `docs/adr/NNNN-<kebab-title>.md`

```markdown
# NNNN. <Decision title>

- **Status:** Proposed | Accepted | Superseded by NNNN
- **Date:** YYYY-MM-DD
- **Deciders:** <names / agents involved>

## Context
<The problem and the forces at play, in plain words.>

## Decision
<What we decided.>

## Alternatives considered
| Option | Pros | Cons |
|---|---|---|

## Consequences
<What gets easier, what gets harder, what we must watch.>
```
