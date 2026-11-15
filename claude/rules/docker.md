# Docker — every command runs in a container

The host has **no project runtime**. PHP, Composer, Node, database clients and the Redis CLI exist only inside containers.
On the host you may run only: `git`, `gh`, `docker` / `docker compose` (and `make` targets that wrap Docker, if the project has them).

## Rules

- Use `docker compose exec -T <service> <command>`. Always pass `-T`: agent shells are non-interactive.
- Service names come from the table in `CLAUDE.md` (defaults: `app`, `db`, `queue`, `scheduler`, `node`, `redis`).
- Containers down? Run `docker compose up -d`, then `docker compose ps` and confirm every service is `running`/`healthy` before continuing.
- One-off command for a service that isn't running: `docker compose run --rm <service> <command>`.
- After changing job, listener or scheduled-task code, restart the workers: `docker compose exec -T app php artisan queue:restart` (or `docker compose restart queue`).
- When a skill, doc or answer shows a host command (`php artisan …`, `composer …`, `npm …`, `./vendor/bin/…`), translate it to the Docker form before running it.
- Never print secrets: no `env` / `printenv` inside containers, no `docker compose config`, no reading `.env`. Reference variables **inside** the container instead (see the database rows below).
- Never run `docker compose down -v`, `docker volume rm` or `docker system prune` without explicit approval — they delete the database volume.

## Cheat sheet

| Task | Command |
|---|---|
| Artisan | `docker compose exec -T app php artisan <cmd>` |
| Composer | `docker compose exec -T app composer <cmd>` |
| Tinker one-liner | `docker compose exec -T app php artisan tinker --execute="dump(App\Models\User::count());"` |
| Code style (Pint) | `docker compose exec -T app ./vendor/bin/pint --dirty` |
| Static analysis | `docker compose exec -T app ./vendor/bin/phpstan analyse --memory-limit=1G <paths>` |
| Tests (only through `/test`) | `docker compose exec -T app php artisan test` |
| Assets | `docker compose run --rm node npm run build` |
| DB overview | `docker compose exec -T app php artisan db:show` · `php artisan db:table <table>` |
| MySQL / MariaDB query | `docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "<SQL>"'` |
| PostgreSQL query | `docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c "<SQL>"'` |
| App logs | `docker compose exec -T app tail -n 200 storage/logs/laravel.log` |
| Container logs | `docker compose logs --tail=200 <service>` |
| Queue health | `docker compose ps queue` · `docker compose exec -T app php artisan queue:failed` |
