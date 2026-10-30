# Anti-mocking — real code, real data paths, real evidence

"Mocking" here means anything that makes work *look* done without being done. It applies to production code, tests and reports.

## Production code

- No fake implementations: no hardcoded return values, placeholder data, `// TODO: implement`, stubbed services or `return true;` stand-ins.
- No silent fallbacks that hide failures (catch-and-ignore, default values that mask missing data). Fail loudly or handle the case explicitly.
- If something can't be built yet (missing credentials, unclear business rule), stop and tell the developer. Don't simulate it.

## Tests (written and run only through `/test`)

- Use the real stack: the real database engine (a dedicated testing database in Docker, same engine as production), real Eloquent models, factories and real container bindings.
- Never mock your own code: no `Mockery`, `$this->mock()`, `partialMock()` or `spy()` for services, repositories, models, jobs or anything else in `app/`.
- Fake only external boundaries you don't own, with Laravel's built-in fakes: `Http::fake()` for third-party APIs, `Mail::fake()`, `Notification::fake()`, `Storage::fake()`. Use `Queue::fake()` / `Bus::fake()` / `Event::fake()` only to assert that something was dispatched — and test the job or listener itself for real in a separate test.
- Third-party SDK without a Laravel fake: it must sit behind an interface in `app/Integrations/` (an adapter). Bind a fake adapter in tests; never mock SDK internals.
- Never change a test's expectation just to make it pass. A failing test is information: report it.
- No `markTestSkipped()` / `->skip()` / `->todo()` to hide failures.

## Evidence

- Never claim "tests pass", "QA passed", "migration ran" or "it works" without running the command in Docker in this session and showing the real output.
- If a command couldn't run (container down, missing tool), say so explicitly. "Not verified" is an acceptable answer; a guess is not.
