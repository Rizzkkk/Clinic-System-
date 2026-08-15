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
| 1.5 | Register a new account (`register.php`) | Created as **pending** (admin assigns role); name/email validated; any error shows as a SweetAlert overlay with the CREATE ACCOUNT button still reachable | |
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
| 7.6 | `register.php`: submit with mismatched passwords / bad input | Error shows as a **SweetAlert overlay**; **CREATE ACCOUNT button stays visible/clickable**; entered values retained; **no refresh needed** | |
| 7.7 | `login.php` bad login / `ForgotPassword.php` request | Message shows as a SweetAlert overlay (no layout shift) | |

---

## Running slices in parallel (subagents)

For a full pass, the matrix can be split across subagents — e.g. one agent per role driving
sections 2–4 for that role, or one agent per module for section 4 — each reporting Pass/Fail
with the failing request. Synthesize the results and fix before sign-off.
