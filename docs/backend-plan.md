# Backend Plan — Refactor In Place

This is the plan to turn the flat, spaghetti PHP pages into a clean `frontend/` + `backend/`
structure **without** rewriting working code or adopting a framework. See
[architecture.md](architecture.md) for the target shape and the framework decision record.

> **Progress (2026-06-28):** Steps **1–6 done** — **all 8 modules migrated** (Doctor, Patient,
> Laboratory Result, Billing, Prescription, Medical Records, Appointment, Dashboard) and
> **runtime-QA'd** via a curl smoke test (login, 401 guard, every read/write/delete, shim
> delegation). QA caught + fixed an empty-optional-date strict-mode bug. Each migrated page keeps
> a backward-compat shim (Dashboard has no own API). **Remaining: step 7 (CSRF), step 8 (extract
> CSS/JS).** Then: 3 staff role pages + RBAC, PDF lab results. Then step 7 (CSRF), step 8 (extract assets).
> Queued: 3 staff role directories (Lab Technicians / Cashiers / Receptionists) + RBAC, PDF lab
> results. See the table below and `CHANGELOG.md`.

## Target backend layout

```
backend/
  config/
    config.php        # returns DB settings read from environment ($_ENV / getenv)
    .env.example      # documents required vars; real .env is gitignored
  db/
    connection.php    # opens mysqli using config.php; sets utf8mb4; selects DB
    schema.sql        # the ONE canonical schema (source of truth)
    migrations/       # numbered, forward-only SQL (001_*.sql, 002_*.sql, ...)
  auth/
    bootstrap.php     # session_start + connection + login guard + current-user/role
    login.php         # (from index.php) authenticate + session_regenerate_id
    staff_accounts.php # (from Staff Accounts.php) admin-only staff account creation
    logout.php        # destroy session
    rbac.php          # require_role('lab'|'reception'|'cashier'|'admin'|'doctor')
  lib/
    response.php      # json_response($data, $status) — sets header + exits
    escape.php        # h($value) — htmlspecialchars wrapper
    validate.php      # small input validators (required, int, date, email)
    csrf.php          # csrf_token(), csrf_check() for POST actions
  api/
    patients.php      # ?api=get_patients|get_stats|get_contacts|...  + POST actions
    doctors.php
    appointments.php
    medical_records.php
    laboratory_results.php
    prescriptions.php
    billing.php
    dashboard.php
```

## Conventions

1. **One canonical schema.** `backend/db/schema.sql` is the only schema definition. The inline
   schema in `db.php` and the stale `database.sql` / `install.php` are retired (see
   [database/migrations.md](database/migrations.md)).
2. **No DDL on request.** `connection.php` only connects — it never runs `CREATE TABLE`.
   Schema changes happen through migrations, run deliberately.
3. **Config from environment.** `config/config.php` reads `DB_HOST`, `DB_NAME`, `DB_USER`,
   `DB_PASSWORD` from the environment. No credentials in source. See [deployment.md](deployment.md).
4. **Single bootstrap.** Every page/handler does `require backend/auth/bootstrap.php` instead
   of repeating `session_start()` + `require db.php` + the login-guard block.
5. **Prepared statements only.** No string-interpolated SQL (fix `Patient.php`'s
   `?api=get_contacts`). See [security.md](security.md).
6. **CSRF on every write.** Every POST `action` calls `csrf_check()` before touching the DB.
7. **No leaked errors.** Handlers return a generic message; real `$stmt->error` goes to the
   server log, never the JSON response.
8. **Preserve the API contract.** The `?api=` / POST `action` names and JSON shapes stay the
   same so the frontend keeps working — see [api-reference.md](api-reference.md).

## How one module splits

Take `Patient.php` (current: ~996 lines, everything in one file). It becomes:

- `backend/api/patients.php` — the top PHP block (lines ~1–192): the POST `action` handlers
  and `?api=` reads, now using shared bootstrap + CSRF + prepared statements.
- `frontend/views/patients.php` — the HTML page; `require`s bootstrap, links shared
  CSS/JS, and points its `fetch()` calls at `backend/api/patients.php`.
- Inline `<style>` → `frontend/assets/css/`; inline `<script>` → `frontend/assets/js/`.

**Backward-compatibility shim (required).** Other pages call a module by its old URL — e.g.
Dashboard, Appointment, and Medical Records all call `Patient.php?api=...`. So a migrated page
keeps a short shim at the top that delegates `?api=` / POST `action` requests to the new
handler. This prevents cross-module regressions during incremental migration:

```php
require_once __DIR__ . '/backend/auth/bootstrap.php';
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/<module>.php';
}
```

> Note: server-rendered modules (e.g. Laboratory Result, Dashboard) also build page data with
> `$conn` in the body. For those, keep the server-side query block in the page (it uses `$conn`
> from bootstrap); only the `?api=`/POST handlers move to `backend/api/`.

## Migration sequence (lowest-risk first)

Do these as separate, individually verifiable changes. Verify each by logging in and
exercising the affected module before moving on.

| Step | Change | Why first / risk |
|------|--------|------------------|
| 1 | Extract DB credentials to `backend/config` + `.env`; rotate the committed credentials. | Highest-value security fix; small, isolated. |
| 2 | Create `backend/db/schema.sql` (canonical) + migrations; stop `db.php` running DDL; remove `install.php` & `add_table.php` from the deploy. | Removes the 3-way divergence and per-request DDL. |
| 3 | Add `backend/auth/bootstrap.php` + `lib/` helpers (CSRF, h(), json, validate). | Shared foundation the modules will use. |
| 4 | Migrate **Doctor** first (smallest; prepared statements only; simplest API). | Reference implementation; lowest risk. |
| 5 | Migrate **Patient** (fix the raw-SQL `get_contacts`; it has contacts sub-resource). | Proves the pattern on a harder module + fixes a known bug. |
| 6 | Migrate the rest: Appointment, Medical Records, Laboratory Result, Prescription, Billing, Dashboard. | Mechanical once the pattern is set. |
| 7 | Add CSRF tokens to all POST actions; switch all writes to `csrf_check()`. | Cross-cutting; do after handlers are centralized. |
| 8 | Extract inline CSS/JS to `frontend/assets`; unify branding/design system. | Cosmetic/maintainability; lowest urgency. |

## After the migration (separate efforts)

- **RBAC** — add `users.role`, enforce `require_role(...)` per the matrix in
  [security.md](security.md).
- **Stub modules** — build Dental, Psychiatry, X-ray, Agency Referral, and the real
  password-reset flow against the planned tables in [database/migrations.md](database/migrations.md).
- **Automated tests** — add once the module structure is stable.

## Definition of done (per migrated module)

- Page loads behind the auth guard; unauthenticated access redirects to login.
- All `?api=` reads and POST `action`s behave identically to before (same JSON contract).
- All SQL is parameterized; all writes check CSRF; no DB error text reaches the client.
- No schema defined outside `backend/db/`; no credentials in source.
- The module's row in [frontend.md](frontend.md) and [api-reference.md](api-reference.md) is
  updated to point at the new paths.
