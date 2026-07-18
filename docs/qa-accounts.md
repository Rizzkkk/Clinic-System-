# QA Accounts — how to log in as each role

A guide for visually QA-ing Asclepius as a doctor, receptionist, lab tech, cashier, or admin.

## Key concept (read first)

- **Logins** live in the `users` table and each has a **role**: `admin`, `doctor`, `reception`,
  `lab`, `cashier`. The role decides what the account can see and do.
- **Directory records** (a doctor in the Doctor page, a patient in the Patient page, a lab
  technician, etc.) are **data, not logins**. A doctor you add on the Doctor page **cannot log
  in** — it is just a record. **Patients never log in** at all.
- **Sign-up creates a no-access account.** `register.php` now creates the account with role
  **`pending`** (it cannot see anything until an admin assigns a real role). This closed a security
  hole where self-registration previously became an **admin**. To grant access, set the account's
  `role` in the database (steps below). A proper role-assignment screen is future work.

## Fastest path: the seeded test accounts (local dev)

The local database already has one login per role. **Password for all: `test1234`**.

| Email | Role |
|-------|------|
| `qa@test.com` | admin |
| `doctor@test.com` | doctor |
| `reception@test.com` | reception |
| `lab@test.com` | lab |
| `cashier@test.com` | cashier |

Log in at `http://localhost:8000/index.php`. (If they're missing — e.g. a fresh DB — create
them with "Create a login for a role" below.)

## Create a login for a specific role

1. Open `register.php`, create an account (name + email + password). It is created as **admin**.
2. Change its role in the database — open **phpMyAdmin** (`http://localhost/phpmyadmin`) or the
   MySQL CLI, select the `asclepius_db` database, and run:
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
