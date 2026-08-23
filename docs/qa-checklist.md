# QA Checklist — Asclepius

A systematic, per-feature and per-role test plan so nothing is missed. Work through it after
any change that touches a page, the RBAC matrix, or a form. Mark each row Pass / Fail / N/A.

**How to run:** start the app locally (`start-local-server.bat`, or
`C:\xampp\php\php.exe -S localhost:8000`) with XAMPP MySQL running. Log in with the seeded
accounts from [qa-accounts.md](qa-accounts.md) (all password `test1234`):
`qa@test.com` (admin), `doctor@test.com`, `reception@test.com`, `lab@test.com`,
`cashier@test.com`. "Persists" means: after submitting, the new row is still there after a
manual page refresh (i.e. it is in the database), not just shown on screen.

---

## 1. Auth & session

| # | Test | Expected | Result |
|---|------|----------|:------:|
| 1.1 | Log in with a valid account | Lands on Dashboard; session set | |
| 1.2 | Log in with a wrong password | Rejected, no session | |
| 1.3 | Open a module URL directly while logged out | Redirected to `login.php` | |
| 1.4 | Log out (sidebar) | Session cleared; back to login | |
| 1.5 | Login page | Only the **patient** sign-up link is offered; `register.php` is gone (404) | |
| 1.5b | As admin, create a staff account in `Staff Accounts.php` | Account created with the chosen role; the new login works and sees exactly that role's sidebar | |
| 1.5c | As doctor/reception/lab/cashier, open `Staff Accounts.php` | Redirected to the Dashboard; the sidebar link is not shown | |
| 1.6 | Password reset (`ForgotPassword.php`) end-to-end | Request → link (dev: error log) → set new password → log in with it; messages show as a SweetAlert overlay | |

## 2. RBAC — sidebar visibility (the "only see what I can use" test)

For each role, log in and check the **left sidebar** shows only the allowed links. Forbidden
links must be **absent** (not just disabled).

| Role | Sidebar MUST show | Sidebar MUST NOT show |
|------|-------------------|-----------------------|
| **admin** | Everything | — |
| **doctor** | Dashboard, Patients, Doctors, Appointment, Medical Records, Laboratory Results, Agency Referral, Prescription, Dental, X-ray, Psych | **Lab Technicians, Cashiers, Receptionists, Billing** |
| **reception** | Dashboard, Patients, Doctors, Appointment, Billing, Agency Referral | Lab Technicians, Cashiers, Receptionists, Medical Records, Laboratory Results, Prescription, Dental, X-ray, Psych |
| **lab** | Dashboard, Patients, Doctors, Medical Records, Laboratory Results, Dental, X-ray | Lab Technicians, Cashiers, Receptionists, Billing, Appointment, Prescription, Psych, Agency Referral |
| **cashier** | Dashboard, Patients, Appointment, Billing | Lab Technicians, Cashiers, Receptionists, Doctors, Medical Records, Laboratory Results, Prescription, Dental, X-ray, Psych |

## 3. RBAC — page & API enforcement (defense in depth)

| # | Test | Expected | Result |
|---|------|----------|:------:|
| 3.1 | As **doctor**, open `Lab Technicians.php` / `Cashiers.php` / `Receptionists.php` / `Biling.php` by URL | Redirect to Dashboard | |
| 3.2 | As **cashier**, open `Prescription.php` / `Laboratory Result.php` by URL | Redirect to Dashboard | |
| 3.3 | As **lab**, open `Biling.php` by URL | Redirect to Dashboard | |
| 3.4 | As a forbidden role, POST an `action` to that module's `backend/api/<module>.php` | `403` JSON `{success:false}` | |
| 3.5 | As **reception** (read-only on Doctors), the Doctors page loads but the **Add** button is hidden | Page shows, no write control | |
| 3.6 | Missing/blank session role (edge) | Treated as no access, not admin | |

## 4. Per-module create / read / delete (does it PERSIST?)

For each module: open it as a role with write access, add a record choosing a **real patient
from the dropdown** (where applicable), submit, and confirm it persists after refresh.

| # | Module (page) | Write role(s) | Patient picker | Doctor picker | Create persists | Delete works | Result |
|---|---------------|---------------|:--------------:|:-------------:|:---------------:|:------------:|:------:|
| 4.1 | Patients (`Patient.php`) | reception, admin | n/a | n/a | | | |
| 4.2 | Doctors (`Doctor.php`) | admin | n/a | n/a | | | |
| 4.3 | Appointments (`Appointment.php`) | reception, admin | yes | yes | | | |
| 4.4 | Medical Records (`Medical Records.php`) | doctor, admin | yes | yes (physician) | | | |
| 4.5 | Laboratory Results (`Laboratory Result.php`) — add an order with **2-3 tests**, each with its own value/range/flag | lab, admin | yes | yes (ordered by) | | | |
| 4.6 | **Prescriptions (`Prescription.php`)** | doctor, admin | **yes** | **yes** | | | |
| 4.7 | Billing (`Biling.php`) | cashier, admin | yes | n/a | | | |
| 4.8 | Dental (`Dental.php`) | doctor, admin | yes | n/a | | | |
| 4.9 | Psychiatry (`Psych.php`) | doctor, admin | yes | n/a | | | |
| 4.10 | X-ray (`X-ray.php`) | lab, admin | yes | n/a | | | |
| 4.11 | Agency Referral (`Agency Referral.php`) | reception, doctor, admin | yes | n/a | | | |
| 4.12 | Lab Technicians / Cashiers / Receptionists | admin | n/a | n/a | | | |

## 5. Stats & reports (no fake numbers)

| # | Test | Expected | Result |
|---|------|----------|:------:|
| 5.1 | Every stat tile (Dashboard, Prescription, Billing, Lab Results, Medical Records) | Reflects real DB counts; changes after you add a record | |
| 5.2 | No hardcoded demo numbers (`+12%`, `₱0`, `1248`, `CVS #421`, "Elena Rodriguez") anywhere | None visible | |
| 5.3 | Laboratory Result → per-order **PDF** download (as lab or doctor) | Valid `application/pdf`, **lists every test** in the order | |
| 5.4 | PDF as **cashier/reception** (no lab/doctor read) | Blocked (403 / redirect) | |

## 6. Regression / hygiene

| # | Test | Expected | Result |
|---|------|----------|:------:|
| 6.1 | `php -l` on every changed page | No syntax errors | |
| 6.2 | Load every page as admin | HTTP 200, **no PHP warnings** in the server log | |
| 6.3 | Sidebar is identical across all pages (shared partial) | Same links/order everywhere for a given role | |
| 6.4 | Prescription "Quick Prescribe"/fake modal is gone | Only the real form remains | |

## 7. Input validation & error display (registration forms)

Name fields accept letters + spaces, hyphen, apostrophe, and period (incl. accented letters
like Peña); they must reject digits and other symbols. Email must be well-formed. Validation is
enforced **both** client-side (browser bubble) and server-side (rejected before any DB write).

| # | Test | Expected | Result |
|---|------|----------|:------:|
| 7.1 | Type `John123` or `@dmin` into any name field and submit | Blocked client-side with a validation bubble | |
| 7.2 | Bypass the client (curl/devtools) and POST a digit-containing name to the module API | `400` JSON `{success:false}`, no row inserted | |
| 7.3 | Valid names with punctuation/accents (`O'Brien`, `Mary-Jane`, `Peña`, `Cruz Jr.`) | Accepted and persist | |
| 7.4 | Quick patient registration `Last, First Middle` (with comma) | Accepted (comma allowed on that field only) | |
| 7.5 | Malformed email in any registration form | Rejected client + server | |
| 7.6 | `Staff Accounts.php`: mismatched passwords / password under 8 chars / duplicate email / bad name | Rejected server-side with a SweetAlert error; no account created | |
| 7.7 | `login.php` bad login / `ForgotPassword.php` request | Message shows as a SweetAlert overlay (no layout shift) | |

---

## Running slices in parallel (subagents)

For a full pass, the matrix can be split across subagents — e.g. one agent per role driving
sections 2–4 for that role, or one agent per module for section 4 — each reporting Pass/Fail
with the failing request. Synthesize the results and fix before sign-off.

## 8. Patient portal

The dominant risk is PHI leaking between patients, so section 8.2 is the one that must never be
skipped. Seed **two** patients, each with data in all four modules, before starting.

### 8.1 Account lifecycle
- [ ] `Portal Register.php` rejects: blank fields, a name with digits, a bad email, a future date
      of birth, a bad phone, a password under 8 characters, mismatched passwords, an unticked
      confirmation box, and a duplicate email.
- [ ] A successful signup creates `role = 'patient_pending'` with `patientId` NULL, and does **not**
      sign the user in.
- [ ] Signing in as that account lands on `Portal.php` and shows "Verification pending" — no records.
- [ ] Reception's Portal Accounts page lists the signup with its claimed date of birth and phone,
      and suggests matching patient records.
- [ ] Approving links the account. The patient sees their records on the **next page load**,
      without signing out and back in.
- [ ] Rejecting shows the patient the "could not verify" screen.
- [ ] Unlinking removes access on the next page load.
- [ ] Linking a patient who already has a portal account is refused with a clear message.

### 8.2 Cross-patient leak matrix (must pass before release)
- [ ] Signed in as patient A, every one of `Portal.php`, `Portal Appointments.php`,
      `Portal Results.php`, `Portal Prescriptions.php`, `Portal Billing.php`, `Portal Profile.php`
      shows **only** A's data and **none** of patient B's. Use distinctive marker text in B's rows
      so a leak is obvious.
- [ ] As a `patient`, every staff page (Dashboard, Patient, Appointment, Prescription, Laboratory
      Result, Billing, Portal Accounts, ...) redirects away.
- [ ] As a `patient`, **every** file in `backend/api/` returns `403` JSON.
- [ ] As staff, every `Portal*.php` page redirects to `Dashboard.php` (and does not loop).
- [ ] Signed out, every `Portal*.php` page redirects to `login.php`.
- [ ] Portal responses carry `Cache-Control: no-store, private`.

### 8.3 Write paths
- [ ] An appointment request is stored as `status = 'Requested'` and appears in the staff
      Appointment page; the "Portal Requests" stat card counts it.
- [ ] Reception confirming it (status → `Scheduled`) is reflected in the patient's view.
- [ ] The staff `scheduled` / `completed` / `cancelled` counts are **not** inflated by requests.
- [ ] A 4th open request is refused while 3 are outstanding.
- [ ] Posting `patientId=<other patient>` or `status=Scheduled` in the request body is ignored: the
      row lands on the signed-in patient with status `Requested`.
- [ ] Updating contact details changes only the signed-in patient's phone/email/address.
- [ ] Posting `firstName` / `dateOfBirth` / `allergies` to the profile form changes nothing.
- [ ] A cross-origin POST to any portal form or to `portal_accounts.php` returns `403`.

### 8.4 Output
- [ ] Staff-entered free text containing `<script>` renders escaped in every portal view.
- [ ] Empty states read sensibly for a patient with no records in a module.
- [ ] Ctrl-P on a record page prints the table without the nav or the forms.
