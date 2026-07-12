# backend/

Server-side PHP for Asclepius. This folder is the **target home** for backend logic during
the refactor-in-place migration (see [docs/backend-plan.md](../docs/backend-plan.md)). It is
currently a **skeleton** — code still lives in the flat `*.php` pages at the repo root and is
migrated here one module at a time.

## Layout

| Folder | Holds | Status |
|--------|-------|--------|
| `config/` | `config.php` (reads DB credentials from environment), `.env.example`. Secrets are **never** committed. | **done** |
| `db/`     | Canonical `schema.sql`, the mysqli connection (`connection.php`), and numbered `migrations/`. Single source of truth for the schema. | schema + connection **done**; migrations/ pending |
| `auth/`   | `bootstrap.php` (session + DB + login guard). Login/register/logout and RBAC role checks still to come. | bootstrap **done**; rest planned |
| `lib/`    | Shared helpers: `response.php` (JSON), `escape.php` (`h()`). Validation + CSRF still to come. | response + escape **done**; rest planned |
| `api/`    | Per-module JSON handlers that own the `?api=` GET and POST `action` endpoints. | `doctors.php` **done**; others planned |

## Conventions (refactor-in-place)

- One canonical schema in `db/schema.sql` — not redefined per page, and not run as DDL on
  every request.
- DB credentials come from the environment via `config/`, never hardcoded.
- Every page includes a single `bootstrap.php` (session + DB + auth guard) instead of
  repeating that block.
- All POST actions validate a CSRF token; all DB access uses prepared statements.

See [docs/architecture.md](../docs/architecture.md) for the request flow and
[docs/backend-plan.md](../docs/backend-plan.md) for the migration sequence.
