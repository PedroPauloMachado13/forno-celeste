# Upstream source — do not edit the skill files in this folder

- **Skill:** `laravel-specialist` by Jeff Allen (MIT, see `LICENSE`)
- **Listed at:** https://skills.laravel.cloud/skills/laravel-specialist
- **Repository:** https://github.com/jeffallan/claude-skills/tree/main/skills/laravel-specialist
- **Pinned commit:** `143243cb2da0e52b02a766fa69c1ff77a71cc73c` (2026-10-28)

## Why it is vendored unmodified

Project-specific behaviour (Docker commands, no automatic tests, Controller → Service → Repository, anti-mocking) is applied by the override table in `.claude/agents/laravel-specialist.md`. Keeping these files identical to upstream makes updates a plain copy.

## How to update

1. Copy `skills/laravel-specialist/` from the upstream repository over this folder (keep `UPSTREAM.md`).
2. Update the pinned commit and date above.
3. Read the upstream diff and check whether the override table in `.claude/agents/laravel-specialist.md` still covers every conflict.
