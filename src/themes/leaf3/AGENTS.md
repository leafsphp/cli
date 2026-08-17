# Agent Instructions

Before performing any task:

1. Read `.leaf/CONTEXT.md` for project goals and architectural decisions.
2. Use Leaf documentation. Fetch these RAW (curl or a non-summarizing fetch) — summarized docs have invented APIs that don't exist:

- https://leafphp.dev/ai/SKILL.md
- https://leafphp.dev/ai/references/lite.md — this is a lite app: modules that MVC auto-configures need manual wiring here, and this file is the complete contract (db, views, Vite, schema). Read it before writing any setup code.
- https://leafphp.dev/llms.txt

3. Inspect the codebase and use `leaf context` when you need a concise map of the application's structure. `context` lives in the global `leaf` CLI (no `php` prefix) — `php leaf context` will fail.

4. When your work is complete, update `.leaf/CONTEXT.md`.
