# Security

Asclepius stores **patient health information**, so security is a production gate, not a
nice-to-have. This doc records the auth model, the RBAC matrix, and the prioritized issues
with remediation.

## Authentication (current)

- Session-based. `index.php` verifies email + password with `password_verify()` against the
  `password_hash` in `users`, then `session_regenerate_id(true)` and stores `user_id` /
  `user_name` in `$_SESSION`.
- Registration (`register.php`) hashes with `password_hash($pw, PASSWORD_DEFAULT)`, rejects
  duplicate emails, and validates the full name and email format (see S-13).
- Every module page redirects to `index.php` when `$_SESSION['user_id']` is unset.

This part is sound. The gaps below are around the *rest* of the stack.

## Authorization — RBAC (target)

Today all authenticated users have full access. Production enforces least-privilege via a
`users.role` column (see [database/migrations.md](database/migrations.md)) checked in
`backend/auth/rbac.php`.

**Permission matrix** (C=create, R=read, U=update, D=delete). Confirm the `doctor` row.

| Module | admin | reception | lab | cashier | doctor *(proposed)* |
|--------|:-----:|:---------:|:---:|:-------:|:-------------------:|
| Patients | CRUD | CRUD | R | R | R |
| Doctors (directory) | CRUD | R | R | — | R |
| Appointments | CRUD | CRUD | R | R | R |
| Medical Records | CRUD | — | R | — | CRUD |
| Laboratory Results | CRUD | — | CRUD | — | R |
| Prescriptions | CRUD | — | R | — | CRUD |
| Billing | CRUD | R | — | CRUD | — |
| Dashboard | R | R | R | R | R |
| Diagnostics (X-ray/Dental/Psych, planned) | CRUD | — | CRUD | — | R |
| User management | CRUD | — | — | — | — |

> **Open decision (from [requirements.md](requirements.md)):** the four confirmed roles are
> `admin`, `reception`, `lab`, `cashier`. Medical Records and Prescriptions are clinical;
> they need an owner. The `doctor` role above is **recommended** — confirm before building
> RBAC. Until then, restrict those two modules to `admin`.

## Issues & remediation

Severity: HIGH · MED · LOW. Several were resolved during the migration (rows marked `done`);
the rest are tracked here.

| # | Sev | Issue | Where | Remediation |
|---|:---:|-------|-------|-------------|
| S-1 | HIGH | **DB credentials hardcoded and committed.** | `db.php` (live host `u805024096_*`), `install.php` (`root`) | Move to environment via `backend/config` + `.env` (gitignored). **Rotate the exposed credentials** — they are in git history. |
| S-2 | done | **CSRF** — done for module writes via an **Origin/Referer check** + `SameSite=Lax` cookies in `bootstrap.php` / `backend/lib/session.php` (no per-write token needed; the app is same-origin `fetch()`). Verified: cross-origin POST → `403`. | bootstrap | Done. *Follow-up:* extend the check to `index.php`/`register.php` login/register POSTs. |
| S-3 | MED | **SQL injection pattern** — id interpolated into query. | `Patient.php` `?api=get_contacts` (`WHERE patientId = $patientId`) | Use a prepared statement with a bound `i` param. (Currently int-cast, so not exploitable, but must not be the pattern.) |
| S-4 | MED | **DB error messages leaked to client.** | all handlers return `'Error: ' . $stmt->error` | Return a generic message; log the real error server-side. |
| S-5 | MED | **Dev/install utilities shippable.** | `install.php`, `add_table.php` | Remove from production; they create tables and (install.php) use `root`/no password. |
| S-6 | MED | **DDL runs on every request.** | `db.php` | Connection-only; schema via migrations. Also avoids masking schema drift. |
| S-7 | done | **Real password reset** implemented — `ForgotPassword.php` is a server-driven token flow (`password_resets` table; SHA-256-hashed single-use token, 1h expiry on the DB clock; generic no-enumeration response; dev logs the link, prod emails it). | ForgotPassword.php | Done. |
| S-8 | done | **RBAC** — `users.role`, login stores role, `require_module_access()` (read/write per the matrix above) enforced in all 7 API handlers + the 4 server-rendered pages. admin = superuser; `doctor` role confirmed. | whole app | Done. Runtime grid QA passed: 35/35 reads + 11/11 write checks across the 5 roles match the matrix. |
| S-9 | LOW | **No password policy / rate limiting** on login/register. | `index.php`, `register.php` | Add minimum length + basic throttling/lockout on repeated failures. |
| S-10 | done | Session cookies now set `HttpOnly` + `SameSite=Lax` + `Secure`-on-HTTPS via `backend/lib/session.php`. | session | Done (idle timeout still optional). |
| S-11 | done | Error display is now env-controlled: `config.php` forces `display_errors=0` + `log_errors=1` when `APP_ENV=production` (the default). Dev sets `APP_ENV=development`. | config.php | Done. |
| S-12 | done | **RBAC enforced in the UI + hardening.** New `can_access($module,$mode)` boolean drives a shared role-filtered sidebar (`frontend/partials/sidebar.php`) and hides in-page write controls, so users only see what they can use. Added page-load `require_module_access` to `Patient`/`Doctor`/`Appointment` (were API-only, so the shell rendered for any role). `current_role()` now defaults to `''` (no access) instead of `'admin'`. | rbac.php, all pages | Done. |
| S-13 | done | **Input validation** — shared `backend/lib/validation.php` (`is_valid_name`/`is_valid_email`) rejects malformed names (digits/symbols) and emails **server-side before any DB write** in `register.php` and all five registration APIs (add + update); matching HTML5 `pattern` gives client-side feedback. Improves data integrity for PHI records. | validation.php, register.php, api/* | Done. Names/email only; free-text fields (address, notes) unchanged. Password policy (S-9) still open. |

## Secrets handling

- No credentials, API keys, or tokens in the repository — ever. Use environment variables
  (`backend/config`).
- `.env` is gitignored; `.env.example` documents the variable names only.
- Because S-1 credentials are already in git history, they must be **rotated**, not just moved.

## Data privacy

- Limit access to patient data by role (RBAC).
- Serve over **HTTPS** in production (S-10).
- Keep regular, access-controlled database backups ([deployment.md](deployment.md)).
- Per-user audit logging of access to patient records is a recommended future enhancement.
