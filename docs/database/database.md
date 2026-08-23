# Database — Schema Reference

**Engine:** MySQL / MariaDB · **Charset:** `utf8mb4` / `utf8mb4_unicode_ci`.
**Database name:** `asclepius_db` (local) — on shared hosting the DB name matches the hosting
account (e.g. `u805024096_asclepius`).

> **Source of truth:** the schema below is the **inline schema in `db.php`** — it is the most
> complete (indexes, `ON DELETE` rules, and the extra `medical_records` columns). The stale
> `database.sql` and `install.php` definitions are reconciled in
> [migrations.md](migrations.md). This reference will be realized as `backend/db/schema.sql`
> during the migration; nothing here changes the live database.

All tables share: `id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY`,
`created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`, and
`updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`.

## Tables

### `users` — login accounts (staff **and** patient portal)
| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| full_name | VARCHAR(150) | no | |
| email | VARCHAR(191) | no | **UNIQUE** (`unique_users_email`) — the login identity |
| password_hash | VARCHAR(255) | no | `password_hash()` output |
| role | VARCHAR(20) | no | default `pending`. Staff: `admin`, `doctor`, `reception`, `lab`, `cashier`. Portal: `patient_pending`, `patient`, `patient_rejected`. Also the staff/patient discriminator — there is deliberately no separate `type` column, so the two can never disagree. |
| patientId | INT UNSIGNED | yes | **UNIQUE** (`users_patient_uk`) → `patients(id)` `ON DELETE SET NULL`. NULL for every staff row and every unlinked signup. UNIQUE (which permits many NULLs) is what makes two accounts linking to one patient impossible. |
| claimedDob | DATE | yes | What the portal applicant typed. An **unverified claim**, for reception to match against — never treated as identity. |
| claimedPhone | VARCHAR(20) | yes | Same. |
| linkedAt | DATETIME | yes | When reception approved the link. |
| linkedBy | INT UNSIGNED | yes | → `users(id)` `ON DELETE SET NULL`. Who approved it. This plus `linkedAt` is the audit trail for the riskiest action in the system. |

Invariant: `role = 'patient'` if and only if `patientId IS NOT NULL`. `require_patient()`
(`backend/auth/portal.php`) re-checks it on every request and fails closed, so a patient record
deleted out from under an account revokes that account rather than leaving it dangling.

`patients.email` is deliberately **not** unique and is not a login identity — families share
addresses, and portal users can edit it themselves.

### `doctors` — clinician directory
| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| firstName, lastName | VARCHAR(100) | no | |
| middleName | VARCHAR(100) | yes | |
| specialty, department | VARCHAR(100) | no | |
| shift | VARCHAR(50) | yes | |
| licenseNumber | VARCHAR(100) | no | **UNIQUE** |
| employeeId | VARCHAR(100) | no | **UNIQUE** |
| phone | VARCHAR(20) | yes | |
| email | VARCHAR(191) | yes | **UNIQUE** |
| address, education, notes | TEXT | yes | |
| dob | DATE | yes | |
| gender | VARCHAR(20) | yes | |
| status | VARCHAR(50) | yes | default `'On Duty'` |

### `patients` — patient master
| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| firstName, lastName | VARCHAR(100) | no | |
| middleName | VARCHAR(100) | yes | |
| dateOfBirth | DATE | yes | |
| gender | VARCHAR(20) | yes | |
| bloodType | VARCHAR(10) | yes | |
| phone | VARCHAR(20) | yes | |
| email | VARCHAR(191) | yes | (not unique) |
| address, medicalHistory, allergies | TEXT | yes | |
| emergencyContact | VARCHAR(150) | yes | inline contact (see also `patient_contacts`) |
| emergencyPhone | VARCHAR(20) | yes | |
| insurance_provider | VARCHAR(150) | yes | |
| insurance_number | VARCHAR(100) | yes | |
| status | VARCHAR(50) | yes | default `'Active'` |

### `appointments`
| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| patientId | INT UNSIGNED | no | FK → `patients(id)` **ON DELETE CASCADE**, idx |
| doctorId | INT UNSIGNED | no | FK → `doctors(id)` **ON DELETE CASCADE**, idx |
| appointmentDate | DATE | no | |
| appointmentTime | TIME | no | |
| reason, notes | TEXT | yes | |
| status | VARCHAR(50) | yes | default `'Scheduled'` |

### `medical_records`
| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| patientId | INT UNSIGNED | no | FK → `patients(id)` **ON DELETE CASCADE**, idx |
| recordType | VARCHAR(100) | yes | |
| recordDate | DATE | no | |
| description, findings, recommendations | TEXT | yes | |
| physician | VARCHAR(150) | yes | free-text name (not an FK to `doctors`) |
| department | VARCHAR(100) | yes | |
| chiefComplaint, diagnosis, clinicalNotes, prescription | TEXT | yes | |
| bloodPressure, heartRate, temperature, weight | VARCHAR(50) | yes | vitals (stored as text) |
| allergies | VARCHAR(255) | yes | |
| attachments | VARCHAR(500) | yes | file path/reference |

### `laboratory_results` — a lab order (1 order → many tests)
Holds the **shared** fields for one order. The individual tests live in `laboratory_result_items`.
The per-test columns below (`testType`, `results`, `referenceRange`, `abnormalFlag`) are **legacy**:
kept for old rows / backward-compat, left NULL for orders created after migration 009.

| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| patientId | INT UNSIGNED | no | FK → `patients(id)` **ON DELETE CASCADE**, idx |
| testDate | DATE | no | |
| remarks | TEXT | yes | order-level notes |
| orderedBy | INT UNSIGNED | yes | FK → `doctors(id)` **ON DELETE SET NULL**, idx |
| testType | VARCHAR(100) | yes | **legacy** — moved to `laboratory_result_items` |
| results | TEXT | yes | **legacy** — moved to `laboratory_result_items` |
| referenceRange | VARCHAR(100) | yes | **legacy** — moved to `laboratory_result_items` |
| abnormalFlag | VARCHAR(10) | yes | **legacy** — moved to `laboratory_result_items` |

### `laboratory_result_items` — the tests within an order (migration 009)
| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| resultId | INT UNSIGNED | no | FK → `laboratory_results(id)` **ON DELETE CASCADE**, idx |
| testType | VARCHAR(100) | no | e.g. HbA1c, FBS |
| results | TEXT | yes | the encoded value / finding |
| referenceRange | VARCHAR(100) | yes | |
| abnormalFlag | VARCHAR(10) | yes | `'Y'` = abnormal, default `'N'` |

### `prescriptions`
| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| patientId | INT UNSIGNED | no | FK → `patients(id)` **ON DELETE CASCADE**, idx |
| doctorId | INT UNSIGNED | no | FK → `doctors(id)` **ON DELETE CASCADE**, idx |
| medicationName | VARCHAR(150) | no | |
| dosage, frequency, duration | VARCHAR(100) | yes | |
| prescriptionDate | DATE | no | |
| expiryDate | DATE | yes | |
| notes | TEXT | yes | |
| status | VARCHAR(50) | yes | default `'Active'` |

### `billing`
| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| patientId | INT UNSIGNED | no | FK → `patients(id)` **ON DELETE CASCADE**, idx |
| appointmentId | INT UNSIGNED | yes | FK → `appointments(id)` **ON DELETE SET NULL**, idx |
| description | VARCHAR(255) | yes | |
| amount | DECIMAL(10,2) | yes | |
| status | VARCHAR(50) | yes | default `'Pending'` |
| billingDate, paymentDate | DATE | yes | |
| paymentMethod | VARCHAR(50) | yes | |
| notes | TEXT | yes | |

### `patient_contacts` — emergency contacts (1 patient → many)
| Column | Type | Null | Notes |
|--------|------|:----:|-------|
| patientId | INT UNSIGNED | no | FK → `patients(id)` **ON DELETE CASCADE**, idx |
| contactName | VARCHAR(150) | no | |
| relationship | VARCHAR(100) | yes | |
| phoneNumber | VARCHAR(20) | no* | *required in `db.php`; nullable in `add_table.php` (see migrations) |
| email | VARCHAR(191) | yes | |
| address, notes | TEXT | yes | |
| isPrimary | TINYINT(1) | yes | default `0` |

## Staff role directories (new — to build)

Three new directories for non-clinical staff, each modeled on the existing `doctors` table
(product decision: separate page + table per role; see the unified-`staff` alternative in
[erd.md](erd.md) architect note 1).

**Shared columns (all three):** `id` PK, `firstName`, `lastName`, `middleName`, `employeeId`
UNIQUE, `phone`, `email`, `address`, `dob`, `gender`, `shift`, `status` (default `'Active'`),
`created_at`, `updated_at`.

| Table | Role-specific columns |
|-------|-----------------------|
| `lab_technicians` | `section` (Hematology / Biochemistry / Microbiology), `licenseNumber` (optional) |
| `cashiers` | `counterNo` |
| `receptionists` | `deskNo` |

**Planned accountability columns** on existing tables (nullable, `ON DELETE SET NULL`):

| Column added to | References | Meaning |
|-----------------|-----------|---------|
| `laboratory_results.performedBy` | `lab_technicians(id)` | tech who ran the test |
| `billing.processedBy` | `cashiers(id)` | cashier who took payment |
| `appointments.bookedBy` | `receptionists(id)` | receptionist who booked it |

**Planned auth column:** `users.role VARCHAR(20)` (`admin` / `doctor` / `lab` / `cashier` /
`reception`) for RBAC.

## Referential integrity summary

| Child table | Column → Parent | On delete |
|-------------|-----------------|-----------|
| appointments | patientId → patients | CASCADE |
| appointments | doctorId → doctors | CASCADE |
| medical_records | patientId → patients | CASCADE |
| laboratory_results | patientId → patients | CASCADE |
| laboratory_results | orderedBy → doctors | SET NULL |
| laboratory_result_items | resultId → laboratory_results | CASCADE |
| prescriptions | patientId → patients | CASCADE |
| prescriptions | doctorId → doctors | CASCADE |
| billing | patientId → patients | CASCADE |
| billing | appointmentId → appointments | SET NULL |
| patient_contacts | patientId → patients | CASCADE |

`users`, `doctors`, and `patients` are top-level (no outgoing FKs). See the diagram in
[erd.md](erd.md).

## Notes & data-modeling observations (for future cleanup, not changes now)

- **`physician` in `medical_records` is free text**, not an FK to `doctors`. Intentional for
  now; consider linking later for integrity/reporting.
- **Vitals stored as `VARCHAR(50)`** rather than numeric — flexible but not query-friendly.
- **`patients.emergencyContact/emergencyPhone`** duplicate what `patient_contacts` models;
  the contacts table is the richer source.
- **Naming is mixed:** most columns are `camelCase`, but `users` and a few patient columns use
  `snake_case` (`full_name`, `insurance_provider`). Left as-is to avoid churn.
