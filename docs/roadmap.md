# Asclepius — Roadmap & Codebase Map

The **single source of truth for "where are we and what's next."** Start here each session.
Detailed reasoning lives in the linked docs; this file is the map + the checklist.

Status values used below: done · in progress · todo (checklists use `- [x]` / `- [ ]`)

---

## 1. Status snapshot (2026-06-28)

| Phase | What | Status |
|-------|------|:------:|
| 0 | Organize + document (folders, full docs, ERD) | done |
| 1 | Backend refactor-in-place — foundation + migrate all 8 modules | done |
| 1b | CSRF on writes done · extract inline CSS/JS | in progress |
| 2 | Staff role pages (Lab Tech / Cashier / Reception) + RBAC | done |
| 3 | Features: PDF lab results · password reset · 4 stub modules (all built + QA'd) | done |
| 4 | Production go-live hardening | todo |

**Next action:** Phase 4 go-live (manual/ops): create the server `backend/config/.env` with
`APP_ENV=production`, rotate the DB password, delete `dev/`, point at the cloud DB, HTTPS,
backups. All code phases (0-3) are built and QA'd.

---

## 2. Codebase map — where everything goes

```
asclepius-demos/
├── (root = web root)            Entry pages a browser hits directly. URLs unchanged.
│   ├── index.php                Landing (public) ┐
│   ├── login.php                Staff login      │
│   ├── Staff Accounts.php       Admin-only staff account creation
│   ├── ForgotPassword.php       Reset            ┘
│   ├── Dashboard.php            ┐
│   ├── Patient.php              │
│   ├── Doctor.php               │ working module pages — now thin VIEWS:
│   ├── Appointment.php          │ each = bootstrap + (shim →) backend/api handler
│   ├── Prescription.php         │
│   ├── Medical Records.php      │
│   ├── Laboratory Result.php    │
│   ├── Biling.php               ┘ (filename misspelled; "Billing")
│   ├── Dental.php  Psych.php  X-ray.php  "Agency Referral.php"   stub pages (no backend yet)
│   ├── db.php                   Backward-compat DB include (→ backend/db/connection.php)
│   ├── start-local-server.bat   local launcher (XAMPP) — do not deploy
│   └── .editorconfig  .gitignore  CHANGELOG.md
│
├── backend/                     ALL server logic. Web-blocked except api/ (.htaccess).
│   ├── config/                  config.php (reads .env) · .env (gitignored) · .env.example
│   ├── db/                      connection.php · schema.sql (canonical) · migrations/ · seeders/
│   ├── auth/                    bootstrap.php (session + DB + login guard)
│   ├── lib/                     response.php (JSON) · escape.php (h())
│   └── api/                     per-module JSON handlers: doctors, patients, appointments,
│                                laboratory_results, prescriptions, billing, medical_records
│                                (Dashboard has none — it server-renders + uses the shims)
│
├── frontend/                    Presentation. (views/ + assets/ to be filled in step 8.)
│   ├── views/                   (empty — pages still at root for now; see §5)
│   └── assets/{css,js,img}      img/ holds the logos; css/js to be filled later
│
├── storage/                     Uploads + generated PDFs. Web-denied; served via PHP later.
│
├── dev/                         Archived setup utilities (install.php, add_table.php,
│                                database.sql) — NOT for production; delete the folder at go-live.
│
└── docs/                        This documentation set. Index: docs/README.md
```

**Two patterns that keep this safe to do incrementally:**
1. **`db.php` shim** — unmigrated/legacy pages keep working unchanged.
2. **Per-page api-delegation shim** — a migrated page still answers its old `X.php?api=` / POST
   URL by delegating to `backend/api/<module>.php`, so cross-page callers don't break.

---

## 3. The plan — step checklist

### Phase 0 — Organize & document
- [x] `frontend/ backend/ docs/ storage/` structure + folder conventions ([architecture.md](architecture.md))
- [x] Full docs: overview, requirements, architecture, API reference, frontend audit, security, deployment
- [x] Complete production ERD ([database/erd.md](database/erd.md)) + schema reference + migration plan

### Phase 1 — Backend refactor-in-place
Foundation:
- [x] Credentials → `.env`; `db.php` shim (no hardcoded creds, no DDL-per-request)
- [x] Canonical `backend/db/schema.sql`; `backend/auth/bootstrap.php` + `lib/` helpers
- [x] `.htaccess` protection for `backend/`

Module migrations (all runtime-QA'd):
- [x] Doctor · Patient (fixed raw-SQL `get_contacts`) · Laboratory Result · Billing
- [x] Prescription · Medical Records · Appointment · Dashboard
- [x] QA smoke test + fixed empty-optional-date strict-mode bug ([deployment.md](deployment.md))

Remaining in Phase 1:
- [x] **CSRF** — Origin/Referer check + `SameSite=Lax`/`HttpOnly` cookies in `bootstrap.php` + `backend/lib/session.php` (same-origin design; no per-write token). Cross-origin POST → `403`, verified.
- [~] **Assets** — images moved to `frontend/assets/img/` (refs updated). Bulk inline CSS/JS
  extraction **deferred** to a browser-QA pass: most JS is page-specific and most CSS is Tailwind
  utility classes in the HTML; the extractable shared bits (auth-page CSS, Tailwind config) need
  visual verification in a browser. Low value, not production-blocking.

### Phase 2 — Staff & access control
- [x] **3 staff role pages**: `Lab Technicians.php`, `Cashiers.php`, `Receptionists.php` —
  registry CRUD (add/list/delete + stats) on tables from migration 002, admin-only via RBAC,
  linked from the Dashboard sidebar. QA'd: admin CRUD works on all three; non-admin gets 403
  (API) / redirect (page). Accountability FKs (N-4) deferred until the modules record them.
- [x] **RBAC**: `users.role` (migration 001) + login stores the role + `backend/auth/rbac.php`
  matrix-driven `require_module_access()` (read vs write per [security.md](security.md)) enforced
  across all 7 API handlers + the 4 server-rendered pages. `admin` is a superuser; `doctor` role
  confirmed. Grid QA passed: 35/35 reads + 11/11 writes across the 5 roles match the matrix.

### Phase 3 — Features
- [x] **PDF lab results** (doctor + lab) — `backend/lib/pdf.php` (`SimplePdf`, dependency-free) +
  `?api=lab_report_pdf&id=` on the Lab Results handler + a per-row PDF link. RBAC allows lab +
  doctor + admin (read op); reception/cashier get 403. QA'd: valid `application/pdf`.
- [x] **Real password reset** — `ForgotPassword.php` rebuilt as a server-driven token flow
  (`password_resets`, migration 003; single-use SHA-256 token, 1h DB-clock expiry, no user
  enumeration; dev logs the link, prod emails it). QA'd end-to-end.
- [x] **Built the 4 stub modules**: Dental (`dental_records`), Psychiatry (`psych_sessions`),
  X-ray (`xray_studies`), Agency Referral (`agency_referrals`) — migration 004; patient-linked
  table + `backend/api/<module>.php` CRUD + a functional page (patient dropdown, add form, list)
  replacing each static stub, RBAC-gated. QA'd (admin CRUD; RBAC blocks; pages load clean).

### Phase 5 — Patient portal (done)
Patients can sign in to their own records. See [proposals/patient-portal.md](proposals/patient-portal.md)
for the scope decisions and [security.md](security.md) for the access model.
- [x] Migration 010 — `users.patientId` + the three portal roles + the audit columns
- [x] `backend/auth/portal.php` — `require_patient()`, the row-scoped access model
- [x] `bootstrap.php` chokepoint — one check keeps patients out of every staff page and API handler
- [x] `Portal Register.php` (public signup) + reception's `Portal Accounts.php` review queue
- [x] Read views — appointments, bills, prescriptions, lab results, each scoped to the patient
- [x] Writes — request an appointment (`status = 'Requested'`), update own contact details
- [x] Leak matrix QA — two patients, zero cross-patient bleed; 16/16 API handlers denied
- [ ] *Deferred:* PDF downloads, X-ray images, approval emails, login rate limiting (S-9),
      a full patient-view audit log

### Phase 4 — Production go-live
Full checklist in [production-release.md](production-release.md). Headlines:
- [ ] Delete `install.php` / `add_table.php` / `start-local-server.bat` from the server
- [ ] Create server `backend/config/.env`; **rotate the DB password** (it's in git history)
- [ ] HTTPS · secure session cookies · `display_errors` off · DB backups
- [ ] **Apply migration 010 before deploying the portal code** — it is applied by hand, and the
      portal reads columns that do not exist until it runs
- [ ] Re-run the portal leak matrix (qa-checklist section 8.2) against the deployed environment
- [ ] Brief reception: verify identity by phone or in person **before** linking a portal account

---

## 4. How to continue (next session)
1. Read this file + [backend-plan.md](backend-plan.md) (migration pattern) + the auto-memory.
2. Pick the next todo item (currently: Phase 4 go-live).
3. Make the change → **QA it** with the smoke test in [deployment.md](deployment.md) (don't stop at `php -l`).
4. Update this checklist + `CHANGELOG.md` in the same change.

## 5. Open organization decisions (deferred, not blocking)
- **`public/` document root** — most secure (backend physically unreachable over HTTP), but
  changes every page URL + needs hosting docroot control. Today we keep entry pages at root and
  protect `backend/` with `.htaccess`. Revisit at go-live. ([architecture.md](architecture.md))
- **Move images** into `frontend/assets/img/` — DONE (refs in `index.php`, the auth pages,
  `ForgotPassword.php` updated).
- **Rename `Biling.php` → `Billing.php`** — defer; renaming changes its URL and any links to it.

---

## 6. Database hosting — cloud SQL (not local)

Goal: run the database on a **managed cloud MySQL** so there's no heavy local MySQL; the app
connects remotely. **This is configuration, not a rewrite** — `backend/db/connection.php` reads
host / port / user / password / optional TLS from `backend/config/.env`. The app is now
cloud-ready (`DB_PORT` + optional `DB_SSL_CA` supported; verified locally).

Steps:
1. Create a managed MySQL database + user at a provider.
2. Import `backend/db/schema.sql` into it (provider console, or
   `mysql -h <host> -P <port> -u <user> -p <db> < backend/db/schema.sql`).
3. Point `backend/config/.env` (dev and prod) at it: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`,
   `DB_PASSWORD`, and `DB_SSL_CA` if the provider requires TLS.
4. Allow your dev IP + the prod server IP in the provider's access controls.
5. Local dev then needs **no local MySQL** — point `.env` at the cloud DB. Tradeoff: needs
   internet and is a little slower than localhost (keep XAMPP around for offline/fast work).

Provider options (full detail + the foreign-key caveat in [deployment.md](deployment.md)):
- **Hostinger Remote MySQL** — simplest if already on Hostinger: enable "Remote MySQL", allowlist
  your IP, reuse the existing DB. No new vendor, no TLS cert to manage.
- **TiDB Cloud** / **Aiven for MySQL** — managed, free/low tiers, support foreign keys (our schema
  needs them). Custom port + TLS via `DB_SSL_CA`.
- **Avoid PlanetScale for now** — its Vitess engine restricts foreign keys, which our schema relies on.
