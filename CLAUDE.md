# CLAUDE.md

The role of this file is to describe common mistakes and confusion points that agents might encounter as they work in this project. If you ever encounter something in the project that surprises you, please alert the developer working with you and indicate that this is the case in the CLAUDE.md file to help prevent future agents from having the same issue.

> This file is injected into every prompt. Keep it short. Detailed rules live in `.claude/rules/`, agents in `.claude/agents/`, workflows in `.claude/skills/`.

## Project

- **What it is:** Bakery management system for Forno Celeste — inventory control, sales tracking, waste/discharge logging, price management and financial overview.
- **Stack:** Laravel 12, PHP 8.4, MySQL 8.4, Redis 7, Inertia.js + Vue 3, Tailwind CSS 4, Vite 7

## Docker services (fill in per project)

| Role | Compose service |
|---|---|
| PHP (artisan, composer, tinker) | `app` |
| Database | `db` |
| Queue worker | `queue` |
| Scheduler | `scheduler` |
| Node / assets | `node` |
| Cache / queue backend | `redis` |

## Non-negotiables

1. **Docker only.** Every runtime command runs in a container: `docker compose exec -T <service> ...`. The host only runs `git`, `gh` and `docker`. See `.claude/rules/docker.md`.
2. **Agent pipeline.** Every feature/fix follows `.claude/rules/agent-workflow.md`. Application code is written only by the `laravel-specialist` agent.
3. **Manual-only skills.** `/test` and `/pull-request` run only when the developer types them.

## Known pitfalls and surprises

- **No PHP/Node on the host**: `php`, `composer`, `npm`, `npx`, `mysql`, `psql` on the host are denied in `.claude/settings.json`. → Use the Docker form from `.claude/rules/docker.md`.
- **Skills and docs show host commands**: the vendored `laravel-specialist` skill (and most docs online) write `php artisan ...`. → Always translate to `docker compose exec -T app php artisan ...`.
- **Laravel Boost rewrites files**: `boost:install` / `boost:update` can add a large `<laravel-boost-guidelines>` block to this file and regenerate `.mcp.json` with a host `php` command. → Keep the block out of this file and restore the Docker command in `.mcp.json` after updates.
- **Claude settings location**: permissions live in `.claude/settings.json` (shared) and `.claude/settings.local.json` (personal), not in `.claude/rules/`.
- **Tests wipe the database**: `RefreshDatabase` drops data in whatever DB the tests point at. → `/test` checks that tests use the dedicated testing DB before running.
