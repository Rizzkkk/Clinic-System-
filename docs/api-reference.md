# API Reference

Documents the **existing** in-page JSON API so the refactor (see
[backend-plan.md](backend-plan.md)) preserves the contract. Each working module's `*.php`
page is also its own endpoint: it answers `GET ?api=...` for reads and `POST` with an
`action` field for writes, both returning `application/json`.

> **Verification status:** the conventions and the **Doctors** and **Patients** contracts
> below were read directly from the source. The other modules' endpoint names come from a
> code survey; their exact request fields map to the columns in
> [database/database.md](database/database.md) and should be reconfirmed against each handler
> during migration.
>
> **Migrated (all modules):** Doctors, Patients, Laboratory Results, Billing, Prescriptions,
> Medical Records, and Appointments now live under `backend/api/*.php` (same contract; Patient's
> `get_contacts` parameterized; Appointment's `get_stats` no longer interpolates the date). Each
> old page keeps a shim that delegates its `?api=`/POST to the new handler. Dashboard has no API
> of its own (server-renders + calls the others via the shims). All runtime-QA'd.

## Conventions (verified)

**Auth:** every module page calls `session_start()` and redirects to `login.php` if
`$_SESSION['user_id']` is unset. API calls therefore require a valid session cookie.

**Reads — `GET <Module>.php?api=<name>`:**
- `?api=get_<entities>` → a **JSON array** of rows (`SELECT * ... ORDER BY created_at DESC`).
- `?api=get_stats` → a **JSON object** of counts (shape varies per module).

**Writes — `POST <Module>.php` with `action=<name>`:**
- Create → `{ "success": true, "message": "..." }` or
  `{ "success": false, "message": "Error: ..." }`.
- Delete → `{ "success": true }` or `{ "success": false, "message": "..." }`.
- Request fields are form-encoded `$_POST` keys matching the table columns.

> **Known issues in the current contract** (to fix during migration, see [security.md](security.md)):
> no CSRF token is required on writes; failure responses leak raw `$stmt->error`; one read
> (`Patients ?api=get_contacts`) interpolates the id into SQL instead of binding it.

---

## Doctors — `Doctor.php` *(verified)*

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_doctors` | — | array of `doctors` rows |
| GET | `?api=get_stats` | — | `{ total, onDuty, load }` (load = % on duty) |
| POST | `action=add_doctor` | `firstName, lastName, middleName, specialty, department, shift, licenseNumber, employeeId, phone, email, address, dob, gender, education, notes` | `{ success, message }` |
| POST | `action=delete_doctor` | `id` | `{ success }` |

## Patients — `Patient.php` *(verified)*

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_patients` | — | array of `patients` rows |
| GET | `?api=get_stats` | — | `{ total, active, inactive }` |
| GET | `?api=get_contacts` | `patientId` | array of `patient_contacts` rows *interpolated SQL — fix* |
| GET | `?api=get_all_contacts` | — | array of contacts joined with patient name |
| POST | `action=add_patient` | `firstName, lastName, middleName, dateOfBirth, gender, bloodType, phone, email, address, emergencyContact, emergencyPhone, medicalHistory, allergies, insurance_provider, insurance_number` | `{ success, message }` |
| POST | `action=delete_patient` | `id` | `{ success }` |
| POST | `action=add_contact` | `patientId, contactName, relationship, phoneNumber, email, address, isPrimary, notes` | `{ success, message }` |
| POST | `action=update_contact` | `id, contactName, relationship, phoneNumber, email, address, isPrimary, notes` | `{ success, message }` |
| POST | `action=delete_contact` | `id` | `{ success }` |

## Appointments — `Appointment.php` *(survey)*

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_appointments` | — | array of appointments (joined to patient/doctor) |
| GET | `?api=get_patients` | — | array (for the booking dropdown) |
| GET | `?api=get_doctors` | — | array (for the booking dropdown) |
| GET | `?api=get_stats` | — | counts object |
| POST | `action=add_appointment` | `patientId, doctorId, appointmentDate, appointmentTime, reason, status, notes` | `{ success, message }` |
| POST | `action=update_status` | `id, status` | `{ success }` |
| POST | `action=delete_appointment` | `id` | `{ success }` |

## Medical Records — `Medical Records.php` *(survey)*

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_records` | — | array of records (joined to patient) |
| GET | `?api=get_stats` | — | counts object |
| POST | `action=add_record` | patient + clinical fields (`patientId, recordType, recordDate, physician, department, chiefComplaint, diagnosis, clinicalNotes, prescription, bloodPressure, heartRate, temperature, weight, allergies, description, findings, recommendations, attachments`) | `{ success, message }` |
| POST | `action=delete_record` | `id` | `{ success }` |

## Laboratory Results — `Laboratory Result.php` *(survey)*

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_results` | — | array of results (joined to patient/doctor) |
| GET | `?api=get_stats` | — | counts object |
| GET | `?api=lab_report_pdf` | `id` | **PDF** (`application/pdf`) report for one result. Read op, so RBAC allows lab + doctor + admin. `404` JSON if not found. |
| POST | `action=add_result` | `patientId, testType, testDate, results, referenceRange, remarks, abnormalFlag, orderedBy` | `{ success, message }` |
| POST | `action=update_result` | same as add + `id` | `{ success, message }` |
| POST | `action=delete_result` | `id` | `{ success }` |

> PDF is generated by `backend/lib/pdf.php` (`SimplePdf`) — dependency-free, no Composer.

## Prescriptions — `Prescription.php` *(survey)*

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_prescriptions` | — | array (joined to patient/doctor) |
| GET | `?api=get_stats` | — | counts object |
| POST | `action=add_prescription` | `patientId, doctorId, medicationName, dosage, frequency, duration, prescriptionDate, expiryDate, notes` | `{ success, message }` |
| POST | `action=delete_prescription` | `id` | `{ success }` |

## Billing — `Biling.php` *(survey; filename misspelled)*

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_bills` | — | array (joined to patient) |
| GET | `?api=get_stats` | — | counts object |
| POST | `action=add_bill` | `patientId, appointmentId?, description, amount, status, billingDate, paymentDate?, paymentMethod?, notes` | `{ success, message }` |
| POST | `action=update_payment_status` | `id, status, paymentDate, paymentMethod` | `{ success }` |
| POST | `action=delete_bill` | `id` | `{ success }` |

## Dashboard — `Dashboard.php` *(survey)*

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_stats` | — | counts across patients, doctors, appointments, lab results |

## Staff directories — `backend/api/lab_technicians.php`, `cashiers.php`, `receptionists.php` *(verified)*

All three share one contract (admin-only via RBAC; other roles get `403`):

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_staff` | — | array of rows |
| GET | `?api=get_stats` | — | `{ total, active }` |
| POST | `action=add_staff` | `firstName*, lastName*, middleName, employeeId*, shift, phone, email, dob` + role-specific (`section`/`licenseNumber`, `counterNo`, `deskNo`) | `{ success, message }` |
| POST | `action=delete_staff` | `id` | `{ success }` |

Pages: `Lab Technicians.php`, `Cashiers.php`, `Receptionists.php` (same shim pattern).

## Diagnostic modules — `dental_records`, `psych_sessions`, `xray_studies`, `agency_referrals` *(verified)*

Patient-linked; one shared contract (RBAC-gated per module — see [security.md](security.md)):

| Method | Endpoint | Request | Response |
|--------|----------|---------|----------|
| GET | `?api=get_records` | — | array (joined to patient) |
| GET | `?api=get_stats` | — | `{ total, recentMonth }` |
| POST | `action=add_record` | `patientId*` + module fields (a required date; e.g. `recordDate`, `procedureName`, …) | `{ success, message }` |
| POST | `action=delete_record` | `id` | `{ success }` |

Pages (replaced the old stubs): `Dental.php`, `Psych.php`, `X-ray.php`, `Agency Referral.php`.

## All modules built

Dental, Psychiatry, X-ray, Agency Referral (above) and password reset (`ForgotPassword.php`,
server-driven token flow) are now implemented. No module remains stub-only.
