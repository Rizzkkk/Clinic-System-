# Project Overview

## What Asclepius is

Asclepius is a **staff-facing clinic/hospital information system** for Asclepius Medical &
Diagnostic Group Inc. Authenticated staff log in and manage the core operations of a clinic:
patient records, the doctor directory, appointment scheduling, clinical/medical records,
laboratory results, prescriptions, and billing. It is a server-rendered PHP application
backed by MySQL.

It is **not** a patient-facing portal. All users are clinic staff; access is (today) a single
shared login, moving to role-based access — see [requirements.md](requirements.md).

## Modules

### Working modules (real database CRUD)

| Module | File (current) | Does |
|--------|----------------|------|
| Dashboard | `Dashboard.php` | System-wide counts and activity overview (read-only). |
| Patients | `Patient.php` | Register/list/delete patients; manage emergency contacts. |
| Doctors | `Doctor.php` | Register/list/delete doctors; filter by department/specialty/status. |
| Appointments | `Appointment.php` | Book (walk-in + online), list, update status, delete appointments. |
| Medical Records | `Medical Records.php` | Create/list/delete clinical records with vitals and notes. |
| Laboratory Results | `Laboratory Result.php` | Record/list/delete lab tests; flag abnormal results. |
| Prescriptions | `Prescription.php` | Create/list/delete prescriptions. |
| Billing | `Biling.php` *(filename misspelled)* | Create bills, update payment status, delete bills. |

### Planned modules (stubs — static UI today, no backend or tables)

| Module | File (current) | Notes |
|--------|----------------|-------|
| Dental imaging | `Dental.php` | Static UI; needs a `dental_records` table + backend. |
| Psychiatry | `Psych.php` | Static UI; needs a `psych_sessions` table + backend. |
| X-ray / radiology | `X-ray.php` | Static UI; needs an `xray_studies` table + backend. |
| Agency referral | `Agency Referral.php` | Static UI; **name/content mismatch** (page content is recruitment-themed). Needs requirements clarification + an `agency_referrals` table. |
| Forgot password | `ForgotPassword.php` | Client-side only; needs a real token-based reset (`password_resets` table). |

Stub modules are **designed into the data model now but built later** — see
[database/migrations.md](database/migrations.md).

## Glossary

- **Patient** — a person receiving care; the central entity most records hang off of.
- **Doctor** — a clinician on staff (the doctor directory; distinct from a login `user`).
- **User** — a staff login account (the `users` table). Not the same as a doctor record.
- **Appointment** — a scheduled visit linking a patient to a doctor at a date/time.
- **Medical record** — a clinical encounter record (diagnosis, vitals, notes) for a patient.
- **Laboratory result** — a lab test result for a patient, optionally ordered by a doctor.
- **Prescription** — a medication order for a patient by a doctor.
- **Billing / bill** — a charge for a patient, optionally tied to an appointment.
- **Emergency contact** — a `patient_contacts` row; a patient may have several.

## SDLC process

This project follows a lightweight, documentation-first SDLC:

1. **Requirements** — captured in [requirements.md](requirements.md).
2. **Design** — [architecture.md](architecture.md), [database/](database/), [api-reference.md](api-reference.md).
3. **Build** — implemented per [backend-plan.md](backend-plan.md), one module at a time, smallest safe change first.
4. **Test** — manual verification per module (login → CRUD round-trip) plus the checklist in [deployment.md](deployment.md). Automated tests are a future addition.
5. **Deploy** — per [deployment.md](deployment.md); secrets via environment, dev utilities removed.

**Documentation rule:** any change that affects architecture, data model, API contract,
security, or deployment updates the relevant doc in the same change.

### Environments

| Environment | Purpose | DB |
|-------------|---------|----|
| Development | Local work (XAMPP/MySQL on `localhost`). | local `asclepius_db` |
| Staging | Pre-production verification on hosting. | separate staging DB |
| Production | Live clinic use (shared hosting). | production DB, credentials via environment only |

### Branch / release flow

- `main` is the deployable branch.
- Work on short-lived feature branches; open a PR; review against [security.md](security.md)
  and the relevant module doc before merge.
- Tag releases; record notable changes in the changelog.
