# QA Accounts — how to log in as each role

A guide for visually QA-ing Asclepius as a doctor, receptionist, lab tech, cashier, or admin.

## Key concept (read first)

- **Logins** live in the `users` table and each has a **role**: `admin`, `doctor`, `reception`,
  `lab`, `cashier`. The role decides what the account can see and do.
- **Directory records** (a doctor in the Doctor page, a patient in the Patient page, a lab
  technician, etc.) are **data, not logins**. A doctor you add on the Doctor page **cannot log
  in** — it is just a record.
- **Patients can now log in, but only via a linked portal account.** A `patients` row is still just
  a record. A *portal account* is a separate `users` row with role `patient`, pointed at exactly
  one `patients` row by `users.patientId`. Creating either one alone gives access to nothing: the
  patient must sign up **and** be linked by reception. See "Create a patient portal account" below.
- **There is no staff sign-up.** Staff logins are created by an admin in **`Staff Accounts.php`**,
  which also assigns the role. Public self-registration (`register.php`) was removed; accounts left
  over from it have role **`pending`** and see nothing until an admin assigns a role there.

## Fastest path: the seeded test accounts (local dev)

The local database already has one login per role. **Password for all: `test1234`**.

| Email | Role |
|-------|------|
| `qa@test.com` | admin |
| `doctor@test.com` | doctor |
| `reception@test.com` | reception |
| `lab@test.com` | lab |
| `cashier@test.com` | cashier |

Log in at `http://localhost:8000/login.php`. (If they're missing — e.g. a fresh DB — create
them with "Create a login for a role" below.)

## Create a patient portal account (for QA)

The portal is deliberately two-step, so QA has to do both steps.

1. **Sign up as the patient.** Open `Portal Register.php` (linked from the login page and the
   public site nav). Enter a name, email, **date of birth**, **mobile number**, and a password of
   at least 8 characters, and tick the confirmation box. This creates a `patient_pending` account
   and does **not** sign you in.
2. **Approve as reception.** Sign in as `reception@test.com`, open **Portal Accounts** in the
   sidebar, pick the signup, and link it to a `patients` row. Candidates are matched on the claimed
   date of birth, phone, or email; use the search box if nothing matches.
3. **Sign in as the patient** at `login.php`. You land on `Portal.php`.

To make step 2 find a candidate, give a patient record a matching date of birth and phone first
(Patient page, or SQL).

**Shortcut for a throwaway QA fixture** — sign up through the form, then link it directly:

```sql
UPDATE users
   SET role = 'patient', patientId = 1, linkedAt = NOW(), linkedBy = 1
 WHERE email = 'your.signup@example.com' AND role = 'patient_pending';
```

**What to expect at each state** (all three states can sign in):

| `users.role` | What the patient sees |
|---|---|
| `patient_pending` | "Verification pending" screen. No records, and no PHI query runs. |
| `patient` | The portal: their own appointments, results, prescriptions, bills. |
| `patient_rejected` | "We could not verify your account" screen. |

Revoking is `action=unlink` on the Portal Accounts page, and takes effect on the patient's **next
page load** — they do not have to be logged out, because the link is read from the database on
every request.

## Create a login for a specific role

1. Log in as an admin, open **Staff Accounts** in the sidebar, and create the account (name,
   email, role, password). The role is set at creation, so nothing else is needed.
2. To change a role afterwards, use the same page, or the database — open **phpMyAdmin**
   (`http://localhost/phpmyadmin`) or the MySQL CLI, select the `asclepius_db` database, and run:
   ```sql
   UPDATE users SET role = 'doctor' WHERE email = 'you@example.com';
   ```
   Valid roles: `admin`, `doctor`, `reception`, `lab`, `cashier`.
3. Log out and back in — the role is read at login.

## Add a PATIENT (a record, not a login)

Patients do not log in. To create one: log in as **reception** or **admin** -> open **Patients**
-> **Add Patient**. (Other roles can view patients but not add them.)

## What each role can access (what to expect when QA-ing)

Non-allowed pages redirect to the Dashboard; non-allowed actions return a 403. `admin` sees
everything.

| Role | Can do |
|------|--------|
| **admin** | Everything, incl. the staff directories (Lab Technicians / Cashiers / Receptionists) and all management. |
| **reception** | Register/manage **Patients**; book/manage **Appointments**; view **Doctors**, **Billing**; add **Agency Referrals**. |
| **lab** | **Laboratory Results** (add + download the **PDF report**); **X-ray** studies (add); view **Dental**, Medical Records, Patients, Doctors. |
| **cashier** | **Billing** — create bills, record/manage payments; view Patients, Appointments. |
| **doctor** | **Medical Records** + **Prescriptions** (add); **Dental** + **Psychiatry** (add); Lab Results + **PDF** (view); view X-ray, Patients, Doctors, Appointments; add Agency Referrals. |

Staff directories (Lab Technicians, Cashiers, Receptionists) are **admin-only**.

## Running the app locally

1. Start **XAMPP** and click **Start** on **MySQL**.
2. Double-click **`start-local-server.bat`** in the project folder (leave the window open).
3. Open `http://localhost:8000/index.php`.

## Appendix: seed a login directly via SQL (with a known password)

Generate a password hash:
```
C:\xampp\php\php.exe -r "echo password_hash('test1234', PASSWORD_DEFAULT);"
```
Then insert the user with the role you want:
```sql
INSERT INTO users (full_name, email, password_hash, role)
VALUES ('Dr Test', 'doc2@test.com', '<paste-hash-here>', 'doctor');
```
