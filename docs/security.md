# Security

Asclepius stores **patient health information**, so security is a production gate, not a
nice-to-have. This doc records the auth model, the RBAC matrix, and the prioritized issues
with remediation.

## Authentication (current)

- Session-based. `login.php` verifies email + password with `password_verify()` against the
  `password_hash` in `users`, then `session_regenerate_id(true)` and stores `user_id` /
  `user_name` in `$_SESSION`.
- Registration (`register.php`) hashes with `password_hash($pw, PASSWORD_DEFAULT)`, rejects
  duplicate emails, and validates the full name and email format (see S-13).
- Every module page redirects to `login.php` when `$_SESSION['user_id']` is unset.

This part is sound. The gaps below are around the *rest* of the stack.

### Two classes of account (patient portal)

Since the patient portal, `users` holds **staff logins and patient logins**, told apart by `role`.
`backend/auth/roles.php` names the portal values in one place:

| Role | Meaning | May sign in? | Sees records? |
|------|---------|:------------:|---------------|
| `patient_pending` | Signed up, awaiting reception review. `patientId IS NULL`. | yes | no - verification screen only |
| `patient` | Verified and linked to a `patients` row. `patientId IS NOT NULL`. | yes | **own rows only** |
| `patient_rejected` | Denied, or unlinked after a bad match. `patientId IS NULL`. | yes | no - status screen only |

Patients sign up at `Portal Register.php`, which creates a `patient_pending` row and **does not
create a session**. Reception verifies identity and links the account to a `patients` row in
`Portal Accounts.php`. Signing up grants nothing.

**The chokepoint.** `backend/auth/bootstrap.php` allows a portal role through only when
`ASCLEPIUS_PORTAL` is defined, which only `backend/auth/portal.php` does. One check therefore keeps
patient accounts out of **all 16 `backend/api/*.php` handlers and every staff page**, with no
per-file changes. Verified at runtime: a patient account gets `403` JSON from all 16 handlers and a
redirect from every staff page.

## Row-level access (patient portal)

Staff RBAC (`rbac.php`) is **module-scoped**: a role that may read a module may read *every row of
it*. That is correct for staff and completely wrong for a patient, so the portal uses a second,
stricter model in `backend/auth/portal.php`.

`require_patient(): int` returns the single `patients.id` the session may read, or stops the
request. It reads **both the role and the link from the database on every request**, never from the
session, so decisions made by reception take effect on the next page load - an approval grants
access without the patient re-logging in, and an **unlink revokes it immediately**. There is also
no cached authorization in the session to tamper with.

Four rules make a leak hard to introduce. They are mechanically checkable:

1. **The guard returns the id**; it does not set a global. Skip it and there is nothing to bind.
   Every portal page opens with `$patientId = require_patient();`.
2. **No `$conn->query(` in portal code** - an unprepared statement is the tell that no patient id
   is bound. Every statement is prepared, including the non-patient-scoped doctors dropdown, so
   this stays an exception-free grep.
3. **Every statement against a patient-scoped table binds `$patientId`.** Single records use a
   compound `WHERE id = ? AND patientId = ?` (no row means 404) rather than fetch-then-compare, so
   there is no branch to forget.
4. **Child tables re-filter through their parent** - `laboratory_result_items` is joined back to
   `laboratory_results` on the same bound id, never by a list of order ids gathered a moment
   earlier. (The staff handler fetches all items with no `WHERE` at all, which is safe only because
   staff may read everything; copying that shape into the portal would leak every patient's tests.)

Portal pages never read a *patient* id from `$_GET`/`$_POST` - only record ids, always paired with
the ownership filter. Verified at runtime: posting `patientId=2` to the appointment-request and
contact-update forms is ignored, and the write lands on the patient the session owns.

Portal responses also send `Cache-Control: no-store, private` (clinic and household devices are
shared) and `X-Content-Type-Options: nosniff`.

## Authorization — RBAC (target)

Today all authenticated users have full access. Production enforces least-privilege via a
`users.role` column (see [database/migrations.md](database/migrations.md)) checked in
`backend/auth/rbac.php`.

**Permission matrix** (C=create, R=read, U=update, D=delete). Confirm the `doctor` row.

| Module | admin | reception | lab | cashier | doctor | patient |
|--------|:-----:|:---------:|:---:|:-------:|:------:|:-------:|
| Patients | CRUD | CRUD | R | R | R | own contact details only (U) |
| Doctors (directory) | CRUD | R | R | — | R | name + specialty only, to pick one |
| Appointments | CRUD | CRUD | R | R | R | **own rows** R + request (C as `Requested`) |
| Medical Records | CRUD | — | R | — | CRUD | — |
| Laboratory Results | CRUD | — | CRUD | — | R | **own rows** R |
| Prescriptions | CRUD | — | R | — | CRUD | **own rows** R |
| Billing | CRUD | R | — | CRUD | — | **own rows** R |
| Dashboard | R | R | R | R | R | — (own portal home instead) |
| Diagnostics (X-ray/Dental/Psych) | CRUD | — | CRUD | — | R | — |
| Portal Accounts (approve/reject/unlink) | CRUD | CRUD | — | — | — | — |
| User management | CRUD | — | — | — | — | — |

The `patient` column is **not** part of `MODULE_PERMISSIONS`; it is enforced by
`require_patient()` per the row-level rules above. A patient role never reaches `rbac.php` at all.

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
| S-2 | done | **CSRF** — done for module writes via an **Origin/Referer check** + `SameSite=Lax` cookies in `bootstrap.php` / `backend/lib/session.php` (no per-write token needed; the app is same-origin `fetch()`). Verified: cross-origin POST → `403`. | bootstrap | Done. *Follow-up:* extend the check to `login.php`/`register.php` login/register POSTs. |
| S-3 | done | **SQL injection pattern** — fixed. `get_contacts` now uses a prepared statement with a bound `i` param (`backend/api/patients.php:209-213`). A repo-wide grep for interpolated/concatenated SQL returns zero hits outside `dev/`. | `backend/api/patients.php` | Done. |
| S-4 | MED | **DB error messages leaked to client.** | all handlers return `'Error: ' . $stmt->error` | Return a generic message; log the real error server-side. |
| S-5 | MED | **Dev/install utilities shippable.** | `install.php`, `add_table.php` | Remove from production; they create tables and (install.php) use `root`/no password. |
| S-6 | MED | **DDL runs on every request.** | `db.php` | Connection-only; schema via migrations. Also avoids masking schema drift. |
| S-7 | done | **Real password reset** implemented — `ForgotPassword.php` is a server-driven token flow (`password_resets` table; SHA-256-hashed single-use token, 1h expiry on the DB clock; generic no-enumeration response; dev logs the link, prod emails it). | ForgotPassword.php | Done. |
| S-8 | done | **RBAC** — `users.role`, login stores role, `require_module_access()` (read/write per the matrix above) enforced in all 7 API handlers + the 4 server-rendered pages. admin = superuser; `doctor` role confirmed. | whole app | Done. Runtime grid QA passed: 35/35 reads + 11/11 write checks across the 5 roles match the matrix. |
| S-9 | MED | **No password policy / rate limiting** on login/register. *Partly addressed:* `Portal Register.php` enforces a minimum of 8 characters. `register.php` (staff) still has none, and there is **no throttling or lockout anywhere**. Severity raised from LOW because portal accounts are internet-facing and sit in front of PHI. | `login.php`, `register.php` | Add a minimum length to the staff form, and throttling/lockout on repeated login failures. |
| S-10 | done | Session cookies now set `HttpOnly` + `SameSite=Lax` + `Secure`-on-HTTPS via `backend/lib/session.php`. | session | Done (idle timeout still optional). |
| S-11 | done | Error display is now env-controlled: `config.php` forces `display_errors=0` + `log_errors=1` when `APP_ENV=production` (the default). Dev sets `APP_ENV=development`. | config.php | Done. |
| S-12 | done | **RBAC enforced in the UI + hardening.** New `can_access($module,$mode)` boolean drives a shared role-filtered sidebar (`frontend/partials/sidebar.php`) and hides in-page write controls, so users only see what they can use. Added page-load `require_module_access` to `Patient`/`Doctor`/`Appointment` (were API-only, so the shell rendered for any role). `current_role()` now defaults to `''` (no access) instead of `'admin'`. | rbac.php, all pages | Done. |
| S-14 | done | **Patient portal row-level access.** Patients are a second, untrusted class of user on the same `users` table. `backend/auth/portal.php` scopes every read and write to the one `patients` row the account is linked to; the `bootstrap.php` chokepoint keeps portal roles out of all staff pages and API handlers. Approval writes are guarded by the current role in the `WHERE` clause, and `UNIQUE(users.patientId)` makes a double-link impossible. | portal.php, bootstrap.php, api/portal_accounts.php, Portal*.php | Done. Runtime QA passed: two seeded patients, zero cross-patient bleed across all four views; 16/16 API handlers and 6/6 staff pages denied to a patient; forged `patientId`/`status` in write bodies ignored; unlink revoked access on the next request. |
| S-16 | **HIGH** | **Web-server deny rules are `.htaccess`-only, and nothing protects `.git/` or `dev/`.** The repo ships exactly three `.htaccess` files (`backend/`, `backend/api/`, `storage/`). **nginx ignores `.htaccess` entirely**, and even on Apache the vhost needs `AllowOverride All` or they are silently skipped. With no rules: `GET /backend/config/.env` returns the DB password in plaintext, `/backend/db/*.sql` and `/storage/xray/*` (PHI images) are served raw, `GET /.git/config` allows cloning the full source **including the DB credentials still in git history**, and `/dev/install.php` runs unauthenticated DDL as `root` with `display_errors=1`. | webserver config, `dev/` | Add explicit deny rules for `/backend/`, `/storage/`, `/dev/`, dotfiles and `*.sql` (see deployment.md); delete `dev/` from the deployed tree; **rotate the credentials in git history (S-1)**. Verify with `curl` after deploying. |
| S-17 | **HIGH** | **`Secure` cookie flag is lost behind a TLS terminator.** `backend/lib/session.php:15` sets `Secure` from `$_SERVER['HTTPS']`, which php-fpm only receives if the web server passes it. Behind nginx without `fastcgi_param HTTPS on;`, every session cookie on an HTTPS site ships without `Secure` and can be captured on any downgraded request. | `backend/lib/session.php:15` | Set `fastcgi_param HTTPS on;` in the fastcgi block, or honour `X-Forwarded-Proto` in `session.php`. |
| S-18 | **HIGH** | **Host-header injection in the password-reset link.** `ForgotPassword.php:48` builds the reset URL from `$_SERVER['HTTP_HOST']`. An attacker requesting a reset for a victim's email with `Host: evil.tld` causes a genuine clinic email to arrive carrying an attacker-controlled link; one click leaks the single-use token and the account. Now materially worse than before, because portal accounts front full medical histories. | `ForgotPassword.php:48` | Build the URL from a configured `APP_URL` in `.env`, never from the request. Add an nginx `default_server` returning 444 as defence in depth. |
| S-19 | MED | **No CSP / `X-Frame-Options` / HSTS site-wide.** Fixed for the portal only: `backend/auth/portal.php` now sends `X-Frame-Options: DENY`, `frame-ancestors 'none'`, and `Referrer-Policy: same-origin`, because portal pages carry state-changing forms and were clickjackable. Staff pages are still framable, and `Portal Accounts.php` loads unpinned CDN scripts (Tailwind, SweetAlert2) on a page rendering patient names and dates of birth. | all pages | Set the headers at the web-server level for the whole site; pin or self-host the CDN assets. |
| S-20 | MED | **Password reset does not invalidate existing sessions.** `ForgotPassword.php:78-84` updates the hash but leaves other sessions alive, so an attacker's stolen session survives the victim's recovery. | `ForgotPassword.php` | Destroy that user's other sessions on reset. |
| S-15 | LOW | **Account enumeration on portal signup** - "an account with this email already exists" is an existence oracle on a *health* service. Consistent with the existing `register.php` behaviour. | `Portal Register.php` | Accepted for now. Fix by always showing the same neutral message and emailing the address instead. |
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
- Per-user audit logging of access to patient records is a recommended future enhancement. The
  riskiest action is already recorded: `users.linkedAt` / `users.linkedBy` capture who granted a
  portal account access to which record. A full view-audit table is separate future work.
- **Identity verification before linking is a human control, not a technical one.** The date of
  birth and mobile number a portal applicant submits are stored as an unverified *claim*
  (`users.claimedDob`, `users.claimedPhone`) purely so reception has something to check against the
  real record. Reception must confirm identity by phone or in person before linking; the UI says so
  and the confirmation dialog names the patient and their date of birth. Train reception on this.
