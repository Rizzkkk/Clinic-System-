# Testing & QA Guide

How to verify the whole Asclepius clinic system works before shipping a change or
handing it over. There is no automated test suite yet — this is a **manual /
scripted QA process**. Work through the sections in order; each one builds on the
previous.

For install and run instructions, see `docs/getting-started.md`.

---

## 0. Before you start

1. The app is running (`http://localhost:8000/index.php` → **Staff Login**) and the database schema
   is loaded.
2. Set `APP_ENV=development` in `backend/config/.env` while testing so PHP
   warnings and errors are visible in the browser instead of hidden.
3. Create one login per role to test with (next section).

### Seed a test login for each role

Logins live in the `users` table. Roles: `admin`, `doctor`, `reception`, `lab`,
`cashier` (plus `pending`, which has no access). Patients and doctor-directory
records are data, not logins.

Fastest way — register through the UI, then set the role in SQL:

1. Open `register.php` and create an account (it starts as `pending`).
2. Promote it:
   ```sql
   UPDATE users SET role = 'doctor' WHERE email = 'doctor@test.com';
   ```
3. Repeat for each role you want to test.

Or insert directly with a known password. Generate a hash, then insert:

```
php -r "echo password_hash('test1234', PASSWORD_DEFAULT);"
```
```sql
INSERT INTO users (full_name, email, password_hash, role)
VALUES ('QA Admin', 'qa@test.com', '<paste-hash>', 'admin');
```

A convenient set of accounts for full-system QA (all with the same password):

| Email               | Role      |
|---------------------|-----------|
| `qa@test.com`       | admin     |
| `doctor@test.com`   | doctor    |
| `reception@test.com`| reception |
| `lab@test.com`      | lab       |
| `cashier@test.com`  | cashier   |

---

## 1. Roles and the permission matrix

Access is enforced by `backend/auth/rbac.php`. **`admin` can do everything.** For
the other roles, "read" means view a page or GET its data; "write" means
create/update/delete. Anything not allowed is blocked (see section 3 for the
expected 302/403 behaviour).

| Module (page)                         | Read roles                         | Write roles          |
|---------------------------------------|------------------------------------|----------------------|
| Patients (`Patient.php`)              | reception, lab, cashier, doctor    | reception            |
| Doctors (`Doctor.php`)                | reception, lab, doctor             | admin only           |
| Appointments (`Appointment.php`)      | reception, lab, cashier, doctor    | reception            |
| Medical Records (`Medical Records.php`)| lab, doctor                       | doctor               |
| Laboratory Results (`Laboratory Result.php`)| lab, doctor                  | lab                  |
| Prescriptions (`Prescription.php`)    | lab, doctor                        | doctor               |
| Billing (`Biling.php`)                | reception, cashier                 | cashier              |
| Dental (`Dental.php`)                 | lab, doctor                        | doctor               |
| Psychiatry (`Psych.php`)              | doctor                             | doctor               |
| X-ray (`X-ray.php`)                   | lab, doctor                        | lab                  |
| Agency Referrals (`Agency Referral.php`)| reception, doctor                | reception, doctor    |
| Lab Technicians (`Lab Technicians.php`)| admin only                        | admin only           |
| Cashiers (`Cashiers.php`)             | admin only                         | admin only           |
| Receptionists (`Receptionists.php`)   | admin only                         | admin only           |

---

## 2. Authentication and session

Log in at `login.php`; the app stores the user in the session and every page
redirects unauthenticated visitors back to login.

- [ ] Valid email + password logs in and lands on `Dashboard.php`.
- [ ] Wrong password is rejected (no login, generic error — it must not reveal
      whether the email exists).
- [ ] Visiting any module page (e.g. `Patient.php`) while logged out redirects to
      `login.php`.
- [ ] `logout.php` ends the session; afterwards protected pages redirect to login
      again.
- [ ] A freshly registered account (role `pending`) can log in but sees no module
      pages (everything redirects to the dashboard).

---

## 3. RBAC enforcement (per role)

Enforcement happens in two places and both must hold:

- **Page load / GET data** checks the module's **read** roles.
- **POST with an `action`** (create/update/delete) checks the module's **write**
  roles.

Expected "denied" behaviour:
- **Page load** for a disallowed module → **302 redirect** to `Dashboard.php`.
- **API request** (`?api=...` or any POST) for a disallowed module → **HTTP 403**
  with a JSON body `{"success": false, "message": "..."}`.

For each role, log in and check both the UI and the API:

- [ ] **Sidebar visibility** — the sidebar only shows the modules that role may
      read (per the matrix in section 1). No links to disallowed pages appear.
- [ ] **Page guard** — manually typing the URL of a disallowed page redirects to
      the dashboard (e.g. a `cashier` opening `Medical Records.php`).
- [ ] **API read guard** — a disallowed `?api=` GET returns 403.
- [ ] **API write guard** — a disallowed create/update/delete returns 403 (e.g. a
      `doctor` trying to write Billing).
- [ ] **Admin** can reach every page and every action.

### Scripted RBAC / smoke check (curl)

Use a cookie jar to keep the session. Replace the email/password as needed.

```
# Log in and save the session cookie
curl -s -c cookies.txt -d "email=doctor@test.com&password=test1234" \
  http://localhost:8000/login.php -o /dev/null

# Allowed read for a doctor -> expect HTTP 200 and JSON
curl -s -b cookies.txt "http://localhost:8000/Prescription.php?api=list"

# Disallowed for a doctor (Billing write) -> expect HTTP 403
curl -s -b cookies.txt -w "\nHTTP %{http_code}\n" \
  -d "action=create&amount=100" "http://localhost:8000/Biling.php"
```

- [ ] Every page returns **HTTP 200** for an allowed role with **no PHP warnings**
      in the output or server log.
- [ ] Disallowed requests return **403** (API) or redirect (page), never a 200
      with data.

---

## 4. Per-module CRUD (data persists)

For each module, logged in as a role with **write** access, verify the full
lifecycle. The goal is that changes actually reach the database and survive a
reload — not just that the UI looks right.

For each module in the matrix:

- [ ] **Create** a record via the page form; it appears in the list without a
      manual refresh being required to see stale/incorrect data.
- [ ] **Read** — reload the page; the record is still there (confirm it persisted,
      e.g. `SELECT COUNT(*)` on the table).
- [ ] **Update** — edit the record; the change is saved and shown.
- [ ] **Delete** — remove the record; it disappears and is gone from the table.
- [ ] Required-field validation rejects empty/invalid input with a clear message.

Modules to cover: Patients, Doctors, Appointments, Medical Records, Laboratory
Results, Prescriptions, Billing, Dental, Psychiatry, X-ray, Agency Referrals, and
the admin-only staff directories (Lab Technicians, Cashiers, Receptionists).

Cross-module data setup: create at least one **patient** and one **doctor** first,
since most clinical modules reference them in dropdowns.

---

## 5. Dashboard, reports and PDFs

- [ ] **Dashboard** (`Dashboard.php`) shows counts/activity that match the data you
      created (e.g. patient count increments after adding a patient).
- [ ] **PDF reports** download and open correctly for: Laboratory Results,
      Prescription, Billing (receipt), and Medical Records. A valid PDF starts with
      the bytes `%PDF`.
- [ ] A **doctor signature** (uploaded on the Doctor page) is embedded in the
      relevant PDF report.

---

## 6. File uploads

- [ ] **X-ray image** upload (`X-ray.php`) saves the file and it is served back
      through the app (not by guessing the disk path). The file lands under
      `storage/xray/` and the database row stores only the path.
- [ ] **Doctor signature** upload saves under `storage/signatures/` and appears in
      PDFs.
- [ ] Direct web access to files under `storage/` is blocked (they must only be
      served through the app).

---

## 7. Regression and hygiene pass

Before calling QA done:

- [ ] Every page loads with **no PHP notices/warnings** (check with
      `APP_ENV=development` and the server log).
- [ ] The **sidebar renders on every page** for every role (a broken sidebar has
      regressed before — watch for it).
- [ ] No hardcoded/demo data leaks into the UI; every list reflects the database.
- [ ] Switching roles (log out / log in as another role) shows the correct sidebar
      and access with no stale session state.
- [ ] Set `APP_ENV` back to `production` when finished so errors are no longer
      shown to end users.

---

## 8. Quick per-role sanity script (manual)

A fast end-to-end pass touching each role's core job:

1. **admin** — log in; confirm every sidebar link and one staff directory
   (e.g. Cashiers) opens; add a staff record.
2. **reception** — register a patient; book an appointment; open Billing (read).
3. **doctor** — add a medical record and a prescription for that patient;
   download the prescription PDF.
4. **lab** — add a laboratory result and download its PDF; add an X-ray study with
   an image.
5. **cashier** — create a bill for the patient and record a payment; download the
   receipt.

If all five complete without a redirect loop, a 403 on an allowed action, or a
PHP warning, the core system is healthy.
