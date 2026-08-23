# backend/

Server-side PHP for Asclepius. This is the active home for backend logic in the
refactor-in-place migration (see [docs/backend-plan.md](../docs/backend-plan.md)). The shared
plumbing (config, DB, session, auth, RBAC, JSON/PDF helpers) lives here, and **all module data
access now runs through the per-module handlers in `api/`**. The flat `*.php` pages at the repo
root are the view layer; they include this folder's bootstrap and delegate `?api=` / POST calls
to the matching `api/` handler via a small compatibility shim.

## Layout

| Folder | Holds | Status |
|--------|-------|--------|
| `config/` | `config.php` (reads DB credentials from the environment / `.env`), `clinic.php` (clinic name, contact, logo — used by the sidebar and PDFs), `.env.example`. The real `.env` is **never** committed. | **done** |
| `db/`     | Canonical `schema.sql` (single source of truth, 17 tables), the mysqli connection (`connection.php`), numbered `migrations/` (`001`–`008`), and an empty `seeders/`. No DDL runs on request. | **done** |
| `auth/`   | `bootstrap.php` (session + DB + same-origin/CSRF check + login & role guard) and `rbac.php` (per-module permission matrix + `require_module_access()` / `can_access()`). | **done** |
| `lib/`    | `response.php` (`json_ok` / `json_fail` / `json_response`), `escape.php` (`h()`), `session.php` (hardened session cookie), `pdf.php` (dependency-free `SimplePdf`), `report.php` (shared clinical-report letterhead/signature/footer). | **done** |
| `api/`    | Per-module JSON handlers owning the `?api=` GET reads and POST `action` writes. All modules present (patients, doctors, appointments, medical_records, laboratory_results, prescriptions, billing, dental_records, psych_sessions, xray_studies, agency_referrals, staff directories), plus `xray_image.php` which serves stored x-ray image bytes. | **done** |

## Request flow

Every root page and every `api/` handler includes `auth/bootstrap.php` **first**. Bootstrap:

1. Starts the hardened session (`lib/session.php` — HttpOnly, SameSite=Lax, Secure on HTTPS).
2. Opens the single shared mysqli connection (`db/connection.php`, TLS-capable for cloud DBs).
3. Rejects cross-origin POSTs (Origin/Referer host must match — the current CSRF defense).
4. Redirects anonymous page loads to `login.php`; returns JSON `401`/`403` for API/POST.
5. Blocks accounts with no assigned staff role (legacy `pending` users get no access until an
   admin assigns a role in `Staff Accounts.php`).

After bootstrap, each handler includes `auth/rbac.php` and calls `require_module_access('<module>')`,
which enforces the module's `read` roles on GET/page loads and `write` roles on POST `action=…`
(`admin` always passes). Pages also call `can_access('<module>', 'write')` to show/hide write
controls. The permission matrix in `rbac.php` mirrors [docs/security.md](../docs/security.md).

A root page wires in like this:

```php
require_once __DIR__ . '/backend/auth/bootstrap.php';
// Compat shim: legacy Doctor.php?api=… / POST action=… delegate to the module handler.
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/doctors.php';
}
require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('doctors');
$canWriteDoctor = can_access('doctors', 'write');
```

Handlers respond with `json_ok()` / `json_fail()` / `json_response()`; writes use prepared
statements and log real DB errors server-side while returning a safe message to the client.

## Auth files

| File | Does |
|------|------|
| `auth/roles.php` | Names the three patient-portal role values in one place (`PORTAL_ROLES`). Included by both `bootstrap.php` and `login.php`. |
| `auth/bootstrap.php` | Session, `$conn`, the same-origin CSRF check, the login guard, then the role gate. The gate has two branches: portal roles are allowed only when `ASCLEPIUS_PORTAL` is defined, and staff need a recognized role. |
| `auth/rbac.php` | **Module-scoped** staff permissions: `can_access()`, `require_module_access()`. |
| `auth/portal.php` | **Row-scoped** patient access: `require_patient(): int` returns the one `patients.id` the session may read, or stops the request. Defines `ASCLEPIUS_PORTAL` before including `bootstrap.php`. Deliberately separate from `rbac.php` — the two models must not be mixed. |

## Conventions (refactor-in-place)

- One canonical schema in `db/schema.sql` — not redefined per page, never run as DDL on request.
  Schema changes go in a new numbered file under `db/migrations/`.
- DB credentials and `APP_ENV` come from the environment via `config/`, never hardcoded. In
  production, errors are logged, not shown; `development` displays them.
- Every page/handler includes `bootstrap.php` once instead of repeating the session/DB/guard block.
- All DB access uses prepared statements; the current CSRF defense is the same-origin check in
  bootstrap (a per-request token is a documented follow-up).

See [docs/architecture.md](../docs/architecture.md) for the full request flow,
[docs/api-reference.md](../docs/api-reference.md) for the endpoint list, and
[docs/backend-plan.md](../docs/backend-plan.md) for remaining migration work.
