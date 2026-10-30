---
name: doc
description: Write or update beginner-friendly project documentation — setup, feature guides, API endpoints, queues/runbooks, configuration, integrations, ADRs and changelog. Use after a feature/fix passes QA (step 9 of the agent workflow) or when the developer asks to document something.
argument-hint: "[what to document — default: the current branch's changes]"
---

# /doc — documentation a newcomer can follow on day one

**Reader:** a junior developer in their first week who knows basic PHP but not this project, Laravel internals or Docker. Every page answers *what is this, why does it exist, how do I use it, what can go wrong*.

Target: `$ARGUMENTS` (default: everything changed on the current branch — `git diff --stat origin/develop...HEAD`).

## 1. What MUST be documented

| When the change… | Document it in | Template |
|---|---|---|
| Changes setup (new service, tool, command, port) | `README.md` → "Getting started" | — |
| Adds or changes a feature | `docs/features/<feature>.md` | Feature |
| Adds or changes an HTTP endpoint | `docs/api/<resource>.md` (or the project's OpenAPI file) | Endpoint |
| Adds a job, queue, listener or scheduled task | `docs/operations/queues.md` | Runbook |
| Adds an env var or config key | `.env.example` (comment above the key) + `docs/configuration.md` | Config row |
| Integrates a third-party service | `docs/integrations/<service>.md` | Integration |
| Makes an architectural decision (new pattern or library, deviation from the standard, queue driver, auth strategy) | `docs/adr/NNNN-<kebab-title>.md` | ADR |
| Changes the schema | `docs/database/schema.md` is owned by `database-manager` — only check it is updated and linked from the feature page | — |
| Breaks behaviour for users or API clients | `CHANGELOG.md` → "Unreleased" | — |

## 2. What NOT to document

- What the code already says clearly (no line-by-line narration).
- Secrets, real credentials, real customer data, internal URLs with tokens.
- Temporary debugging notes or work-in-progress thoughts.
- Copies of `.claude/rules/architecture.md` — link to it instead.

## 3. Writing style (noob-friendly)

- Start with **2–3 sentences: what it is and why it exists**, in plain words.
- One idea per sentence. Short sentences. Active voice. "You" for the reader.
- Define every acronym and every project term on first use (e.g. "DTO (Data Transfer Object — a small class that only carries data)").
- Use an analogy when a concept is abstract (e.g. "A queue works like a restaurant ticket rail: the waiter (web request) pins the order and goes back to the customers; the cook (worker) picks it up when free.").
- Numbered steps for procedures. Every command is copy-paste ready **in Docker form**, followed by the expected output.
- A **Mermaid diagram** for any flow with more than three steps.
- Examples use realistic but fake data.
- Link to code paths (`app/Services/Billing/InvoiceService.php`) instead of pasting large code blocks.
- End with **"Common problems"** (symptom → cause → fix) and a **"Last updated: YYYY-MM-DD"** line.

## 4. Process

1. Find what changed and map each change to the table in section 1.
2. Read the existing docs first. Update in place; never create a duplicate page.
3. Write from the templates in [templates.md](templates.md).
4. Verify: run every non-destructive command you documented (in Docker) and confirm the output matches; check every link and code path exists. Anything you couldn't verify is marked `> ⚠️ Not verified:` in the page.
5. Report the pages created/updated, one line each.
