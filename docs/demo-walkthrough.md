# Demo Walkthrough — Asclepius (for a stakeholder / boss demo)

A complete, step-by-step script to demonstrate the system end to end. Everything below is
implemented and was runtime-tested. Follow it top to bottom for a ~10-15 minute demo, or jump to
the sections that matter most to your audience.

---

## 0. Before the demo (2 minutes of setup)

1. Start **XAMPP** and click **Start** on **MySQL**.
2. Double-click **`start-local-server.bat`** (leave the window open). It serves the app at
   `http://localhost:8000`.
3. Open `http://localhost:8000/index.php` in your browser, then click **Staff login**.
4. Have the seeded accounts ready (all password **`test1234`**), from `docs/qa-accounts.md`:

   | Login | Role | Use it to show |
   |-------|------|----------------|
   | `qa@test.com` | admin | Everything (full access) |
   | `doctor@test.com` | doctor | Clinical: records, prescriptions, PDF reports |
   | `reception@test.com` | reception | Front desk: patients, appointments |
   | `lab@test.com` | lab | Lab results, X-ray image upload |
   | `cashier@test.com` | cashier | Billing / receipts |

Tip: have a small **JPG signature image** file handy (any small JPG) to upload for a doctor, so
the PDF reports show a real signature.

---

## 1. The one-line pitch

"Asclepius is a role-based clinic management system: staff log in and only see what their job
allows, every record is created, edited, and stored securely, and clinical documents
(prescriptions, lab results, medical reports) print as professional PDFs with the doctor's
signature."

---

## 2. Security & roles (the strongest opener)

This shows the system is not a toy - access is enforced.

1. **Log in as the doctor** (`doctor@test.com`). Point out the **left sidebar**: it shows only
   what a doctor may use. It does **not** show Lab Technicians, Cashiers, Receptionists, or
   Billing.
2. In the address bar, type `http://localhost:8000/Biling.php` and press Enter. **The system
   redirects you back to the Dashboard** - a doctor cannot open Billing even by URL.
3. **Log out** (sidebar, bottom). Point out that logout fully ends the session.
4. **Log in as the cashier** (`cashier@test.com`). Now the sidebar shows **Billing** but not the
   clinical modules. "Each role sees a different, safe view of the same system."
5. (Optional) On the login page, click **Create account** and register one. Explain: "New
   sign-ups are created with **no access** until an administrator assigns a role - so nobody can
   self-register into the system." (Log in as `qa@test.com` admin to assign roles via the
   database if asked.)

---

## 3. Register and edit a patient (reception)

1. **Log in as reception** (`reception@test.com`) -> **Patients**.
2. Click **Register patient**, fill in a name/details, **Save**. The new patient appears in the
   list immediately.
3. Click the **Edit** (pencil) button on that patient, change something (e.g. phone or blood
   type), **Update Patient**. The change is saved to the database.
4. Talking point: "Create, edit, and delete are all real database operations with server-side
   validation."

---

## 4. Add a doctor with a signature (admin)

1. **Log in as admin** (`qa@test.com`) -> **Doctors**.
2. Click **Add new doctor**. Fill in the name, specialty, etc. In **Signature image**, choose your
   small JPG. **Register Doctor**.
3. This doctor's signature will now appear on the PDF reports they sign (next sections).

---

## 5. Prescriptions with a printable PDF (doctor)

1. **Log in as the doctor** (`doctor@test.com`) -> **Prescription**.
2. In **New Prescription**, choose a **patient** and a **doctor** from the dropdowns (real records,
   not free text), enter a medication, dosage, frequency, and date. **Create Prescription**.
3. In the table, click the **red PDF icon** on that row. A clean **Prescription PDF** opens -
   clinic header, patient, doctor, medication details, and a **signature line/image**.
4. Click the **Edit** (pencil) icon on a row to change it, then submit - "prescriptions are
   editable, and only a doctor or admin can write them."

---

## 6. Medical report PDF (doctor)

1. Still as the doctor -> **Medical Records**.
2. Click **Create New Record**, pick the patient, fill in **diagnosis**, findings, notes, and
   choose the **physician**. **Create Record**.
3. On that row, click the **PDF icon** -> a **Medical Report** opens with the clinic header,
   patient, **diagnosis**, findings, and the **doctor signature**. "This is the template a clinic
   hands to a patient."

---

## 7. Laboratory results + X-ray images (lab technician)

This is a highlight - real file upload, access-controlled.

1. **Log in as lab** (`lab@test.com`) -> **Laboratory Results**.
2. Click **Add Result**, choose a patient, enter a test and date, **Save Result**.
3. On the row, click the **PDF icon** to download the **lab report** (a doctor can also view it;
   a cashier cannot).
4. Go to **X-ray** -> **Add Study**, choose a patient, and this time **attach an image file**
   (JPG/PNG/PDF). **Save**.
5. On the X-ray row, click **View image** - the uploaded X-ray opens. Talking point: "Images are
   stored securely and only served to authorized roles - a cashier or receptionist gets an access
   error if they try."
6. (Optional, to prove the restriction) as the lab tech everything works; mention that **only the
   lab uploads** lab results and X-rays - doctors review them but cannot upload, matching real
   clinic roles.

---

## 8. Billing and receipts (cashier)

1. **Log in as cashier** (`cashier@test.com`) -> **Billing**.
2. Use **Quick Bill**: choose a patient, description, amount, status. **Create Bill**.
3. On the row, click the **PDF icon** for a printable **receipt/invoice**. Edit a bill with the
   pencil icon to show corrections.

---

## 9. Appointments (reception)

1. **Log in as reception** -> **Appointment**.
2. Use **Register Walk-in** or **Book Online**: pick a patient and doctor, set date/time, save.
   The appointment appears in the schedule; its status can be updated.

---

## 10. Polished touches to point out

- **Friendly alerts**: errors and confirmations show as clean pop-ups (SweetAlert), not raw
  browser boxes.
- **Consistent, role-aware navigation**: the same sidebar everywhere, filtered by role.
- **Password reset**: on the login page, **Forgot password** runs a real secure token flow.
- **Professional PDFs**: generated on the server with no external service.

---

## 11. What to say about "is it production-ready?"

Honest and strong:
- **Done and secure:** role-based access enforced on every page and API; passwords hashed;
  SQL-injection-safe (prepared statements); errors hidden in production; file uploads validated;
  real login/logout/password-reset.
- **Cloud-ready:** the app can point at a managed cloud MySQL database (no code change - just
  configuration).
- **Remaining before go-live (operational, not features):** point it at the cloud database,
  enable HTTPS, schedule backups, and rotate the database password. These are standard deployment
  steps, documented in `docs/production-release.md` and `docs/system-status.md`.

---

## 12. Known limitations (be upfront if asked)

- **Inline edit** is live on 12 of 14 modules; **Medical Records and Appointments** currently
  support create/list/delete in the UI (their edit buttons are the last piece being wired - the
  save-changes capability already exists in the backend).
- **SMS/OTP verification** is scoped and pending a decision on where it applies (e.g. login
  2-factor vs. appointment reminders).
- **Patient self-service portal** (patients logging in to see their own records) is intentionally
  a separate future project - see `docs/proposals/patient-portal.md`.

---

## 13. If something goes wrong during the demo

- **A page bounces you to the Dashboard/login:** that role isn't allowed there - it's the security
  working. Log in with the right role from the table in section 0.
- **A PDF doesn't open:** check the browser didn't block the pop-up/new tab.
- **"Access pending" screen:** you logged in with a self-registered account that has no role;
  use one of the seeded accounts instead.
- **Login fails:** confirm XAMPP MySQL is running and you used password `test1234`.

Reset any demo data you created by deleting those rows (or just leave them - they are clearly
test entries).
