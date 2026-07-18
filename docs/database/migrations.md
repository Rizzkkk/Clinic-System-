# Database — Migrations & Schema Consolidation

## The problem: three divergent schema definitions

The schema is currently defined in **three places that disagree**, plus a one-off patch
script:

| Source | Role today | Divergences |
|--------|-----------|-------------|
| `db.php` (inline `$schemaStatements`) | Runs on **every request** | **Most complete** — has indexes, `ON DELETE` rules, and the extra `medical_records` columns (`physician`, `diagnosis`, vitals, …). |
| `database.sql` | Manual import file | Stale — FKs without `ON DELETE`, **no indexes**, `medical_records` **missing** the extra columns. |
| `install.php` | One-click installer (web) | Has the extra `medical_records` columns, but FKs without `ON DELETE`, uses `root`/no password, DB name `asclepius_db`. Also a security risk (S-5). |
| `add_table.php` | One-off: adds `patient_contacts` | `phoneNumber` **nullable** here vs **NOT NULL** in `db.php`; `isPrimary BOOLEAN` vs `TINYINT(1)`. |

This drift means the live schema depends on which script last ran. Production needs **one**
source of truth.

## Resolution

1. **Adopt `db.php`'s inline schema as the canonical schema.** It is the superset. Capture it
   verbatim into **`backend/db/schema.sql`** (full column list in [database.md](database.md)).
2. **Retire the others as schema sources:** delete `install.php` and `add_table.php` from the
   deployment (S-5); keep `database.sql` only if regenerated *from* the canonical schema
   (otherwise remove to avoid confusion).
3. **Stop `db.php` from running DDL on every request** (S-6) — the connection file only
   connects; schema changes go through migrations.
4. **Resolve the `patient_contacts` conflict** in favor of `db.php`: `phoneNumber NOT NULL`,
   `isPrimary TINYINT(1) DEFAULT 0`.

> **Phase 0 note:** this is a documentation/plan. **No migration is run and the live database
> is not touched here.** Execution happens in the migration phase
> ([backend-plan.md](backend-plan.md), steps 1–2).

## Migration approach (going forward)

- Forward-only, **numbered** SQL files in `backend/db/migrations/` (`001_*.sql`,
  `002_*.sql`, …), each a small, reviewable change.
- Each migration notes how to reverse it where practical (avoid destructive changes).
- `schema.sql` always reflects the cumulative result (the "current" full schema), so a fresh
  environment can be created from it directly.
- Migrations are applied **deliberately** (CLI/import), never automatically on a web request.

### Initial migrations

| # | Migration | Purpose |
|---|-----------|---------|
| baseline | `backend/db/schema.sql` | The full current schema (fresh installs apply this directly). Now includes `users.role`. |
| 001 | `migrations/001_add_users_role.sql` | Adds `users.role VARCHAR(20) NOT NULL DEFAULT 'admin'` for RBAC (existing users default to `admin`). **Created + applied locally.** Apply to staging/prod before deploying the RBAC code. |

## Planned tables (designed now, created when the modules are built)

Specified here so the data model is production-complete. **Not created in this phase.** All
follow the existing conventions (`id` PK, `created_at`/`updated_at`, FKs with explicit
`ON DELETE`).

| # | Migration | Table | Key columns / FKs |
|---|-----------|-------|-------------------|
| P-1 | `add_password_resets.sql` | `password_resets` | `userId` FK → `users(id)` CASCADE; `token` (hashed, unique); `expiresAt DATETIME`; `usedAt DATETIME` null. Replaces the client-only `ForgotPassword.php` (S-7). |
| P-2 | `add_dental_records.sql` | `dental_records` | `patientId` FK → `patients(id)` CASCADE; `recordDate`, `toothNumber?`, `procedure`, `findings`, `imagePath?`, `dentist?`. For `Dental.php`. |
| P-3 | `add_psych_sessions.sql` | `psych_sessions` | `patientId` FK CASCADE; `doctorId` FK → `doctors(id)` SET NULL; `sessionDate`, `sessionType`, `notes`, `followUpDate?`. For `Psych.php`. |
| P-4 | `add_xray_studies.sql` | `xray_studies` | `patientId` FK CASCADE; `orderedBy` FK → `doctors(id)` SET NULL; `studyDate`, `bodyPart`, `findings`, `imagePath?`, `status`. For `X-ray.php`. |
| P-5 | `add_agency_referrals.sql` | `agency_referrals` | **Provisional** — `patientId` FK CASCADE; `agencyName`, `referralDate`, `reason`, `status`. **Confirm the feature first** (page content/name mismatch — see [requirements.md](requirements.md) FR-14) before building. |

## Staff role directories (build next, before the planned modules)

| # | Migration | Adds |
|---|-----------|------|
| N-1..N-3 | `migrations/002_add_staff_tables.sql` | **Done.** `lab_technicians` (+`section`, `licenseNumber`), `cashiers` (+`counterNo`), `receptionists` (+`deskNo`). Also appended to the `schema.sql` baseline. |
| N-4 | `add_accountability_fks.sql` | **Deferred** until the modules actually record who did what: `laboratory_results.performedBy` → lab_technicians, `billing.processedBy` → cashiers, `appointments.bookedBy` → receptionists (all nullable, `ON DELETE SET NULL`). |

See [database.md](database.md) for the columns and [erd.md](erd.md) architect note 1 for the
unified-`staff` alternative if you change your mind on three separate tables.

## Applied numbered migrations (recent)

All are in `backend/db/migrations/` and folded into the `schema.sql` baseline:

| # | Migration | Adds |
|---|-----------|------|
| 003 | `add_password_resets` | `password_resets` (token reset flow, S-7). |
| 004 | `add_diagnostic_modules` | `dental_records`, `psych_sessions`, `xray_studies`, `agency_referrals`. |
| 005 | `add_xray_image` | `xray_studies.imagePath` (uploaded X-ray file; served via `backend/api/xray_image.php`). |
| 006 | `change_users_role_default` | `users.role` default `admin` -> `pending` (security: self-registration no longer becomes admin). |
| 007 | `add_doctor_signature` | `doctors.signaturePath` (JPG signature embedded in PDF reports). |
| 008 | `add_remarks` | `remarks TEXT` on `laboratory_results`, `psych_sessions`, `xray_studies`. |
| 009 | `add_laboratory_result_items` | New `laboratory_result_items` child table (1 lab order → many tests, each with its own value/range/flag). Backfills one item per existing order; parent per-test columns kept but unused for new orders (non-destructive). |

## Suggested follow-up cleanups (not required for v1)

- Link `medical_records.physician` to `doctors(id)` instead of free text (integrity/reporting).
- Store vitals as numeric types instead of `VARCHAR(50)`.
- Reconcile `patients.emergencyContact/emergencyPhone` with the richer `patient_contacts` table.

These are noted in [database.md](database.md) and deliberately deferred to avoid unnecessary
churn.
