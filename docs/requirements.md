# Requirements

Scope: the requirements for taking Asclepius to **production** (not MVP/pilot). Covers the 8
working modules, the planned (stub) modules, and the move to role-based access.

## User roles

Today the system has a **single shared login** (one `users` table, no roles). Production
introduces **role-based access control (RBAC)**. Confirmed roles:

| Role | Primary responsibility | Modules (intended) |
|------|------------------------|--------------------|
| `admin` | Full system access, user management, configuration. | All. |
| `reception` | Front desk: register patients, book/manage appointments. | Patients, Appointments, Doctor directory (read), Dashboard. |
| `lab` | Laboratory & diagnostics. | Laboratory Results, and the planned X-ray / Dental / diagnostics modules. |
| `cashier` | Payments and invoicing. | Billing, Dashboard (financial view). |
| `doctor` *(recommended, confirm)* | Clinical work. | Medical Records, Prescriptions; read access to Patients, Lab Results, Appointments. |

> **Open decision:** Prescriptions and Medical Records are clinical modules. The four
> confirmed roles don't obviously own them. A `doctor`/clinician role is **recommended**;
> until confirmed, treat Medical Records + Prescriptions as `admin` (+ `doctor` when added).
> The full role × module × CRUD matrix lives in [security.md](security.md).

## Functional requirements

Each working module exposes the same shape: an authenticated page that renders a list/UI and
serves its own JSON endpoints (`?api=...` for reads, POST `action=...` for writes). Exact
contracts are in [api-reference.md](api-reference.md).

| # | Module | Requirements |
|---|--------|--------------|
| FR-1 | Auth | Staff register an account, log in (email + password), and log out. Passwords are hashed (`password_hash`). Unauthenticated access to any module redirects to login. Production adds password reset and role assignment. |
| FR-2 | Dashboard | Show counts/metrics across patients, doctors, appointments, and lab results. Read-only. |
| FR-3 | Patients | Create, list, and delete patients. Manage one-to-many emergency contacts (add/update/delete, mark primary). |
| FR-4 | Doctors | Create, list, and delete doctors. Filter by department/specialty/status. Enforce unique `licenseNumber` and `employeeId`. |
| FR-5 | Appointments | Create appointments (walk-in and online) linking a patient and doctor at a date/time; list; update status; delete. |
| FR-6 | Medical Records | Create, list, and delete clinical records (diagnosis, vitals, notes) per patient. |
| FR-7 | Laboratory Results | Create, list, and delete lab results per patient; record ordering doctor; flag abnormal results. |
| FR-8 | Prescriptions | Create, list, and delete prescriptions linking a patient and doctor. |
| FR-9 | Billing | Create bills per patient (optionally tied to an appointment); update payment status; delete. |
| FR-10 | RBAC | Restrict module/action access by role per the matrix in [security.md](security.md). |

### Planned-module requirements (designed now, built later)

| # | Module | Requirements (target) |
|---|--------|------------------------|
| FR-11 | Dental | Store and view dental imaging/diagnostic records per patient (`dental_records`). |
| FR-12 | Psychiatry | Store psychiatry session notes per patient (`psych_sessions`). |
| FR-13 | X-ray / radiology | Store radiology studies/results per patient, with ordering doctor (`xray_studies`). |
| FR-14 | Agency referral | **Clarify the feature first** (current page content mismatches its name). Likely: track referrals of a patient to external partners (`agency_referrals`). |
| FR-15 | Password reset | Token-based password reset with expiry (`password_resets`), replacing the current client-only flow. |
| FR-16 | PDF lab results | Export a laboratory result as a downloadable PDF, generatable from **both** the doctor view and the lab-technician view. Needs the Lab module migrated, a PHP PDF library (e.g. Dompdf/FPDF), and RBAC so `doctor` + `lab` can access it. |
| FR-17 | Staff directories | Manage staff per role on **three separate pages** — Lab Technicians, Cashiers, Receptionists — each a registry (add/list/edit/delete) modeled on the Doctor directory. Each role doubles as the login access level (RBAC). See [database/erd.md](database/erd.md) for the data model. |

## Non-functional requirements

| # | Category | Requirement |
|---|----------|-------------|
| NFR-1 | Security & privacy | Patient data is sensitive. All DB access uses prepared statements; all POST actions require a CSRF token; output is HTML-escaped; DB error details are never returned to the client; credentials live in environment variables, never in the repo. See [security.md](security.md). |
| NFR-2 | Access control | Least-privilege via RBAC (NFR ties to FR-10). |
| NFR-3 | Data integrity | Foreign keys with explicit `ON DELETE` behavior; one canonical schema; reversible, numbered migrations. See [database/](database/). |
| NFR-4 | Availability | Production runs on managed/shared hosting with HTTPS and routine database backups. |
| NFR-5 | Performance | Indexed foreign keys; list endpoints remain responsive for realistic clinic data volumes. (Pagination is a future enhancement for large lists.) |
| NFR-6 | Maintainability | Clear `frontend/` vs `backend/` separation; no duplicated schema; consistent module structure; no dev/install utilities in production. |
| NFR-7 | Compatibility | Modern evergreen browsers; responsive down to tablet width. |
| NFR-8 | Auditability | `created_at` / `updated_at` on all tables (already present). Per-user audit logging is a future enhancement. |

## Out of scope (production v1)

- Patient-facing portal.
- Automated test suite (planned follow-up).
- Per-user audit logging and reporting/analytics beyond the dashboard counts.
- Building the planned stub modules' backends (designed only — see [database/migrations.md](database/migrations.md)).
