# Frontend

## Patient portal (separate surface)

The portal deliberately reuses the **public website's** design system, not the staff one: patients
should never see clinic-staff chrome, and `landing.css` already provides the layout primitives a
record page needs.

| | Staff module pages | Patient portal |
|---|---|---|
| CSS | Tailwind CDN + per-page config | `landing.css` + `portal.css` |
| Font | Inter / Manrope + Material Symbols | Poppins |
| Chrome | `frontend/partials/sidebar.php` (260px, role-filtered) | `frontend/partials/portal-nav.php` + `portal-footer.php` (horizontal strip) |
| Data flow | server-rendered shell + `fetch()` against `?api=` | server-rendered, plain form POST |

Pages: `Portal.php` (home + summary tiles), `Portal Appointments.php` (list + request form),
`Portal Results.php`, `Portal Prescriptions.php`, `Portal Billing.php`, `Portal Profile.php`
(read-only identity + editable contact details), and the public `Portal Register.php`.
`Portal Accounts.php` is reception's approval screen and is a **staff** page, so it uses the staff
system and the sidebar.

`portal.css` also carries a **print stylesheet**: v1 has no PDF downloads, so Ctrl-P on a record
page is the supported way for a patient to keep a copy. Feedback uses the same SweetAlert2 overlay
convention as `login.php` / `register.php`, never an inline message box.

## Design system (current)

The root `*.php` pages are the **view layer** (served at the web root); shared markup lives in
`frontend/partials/` — notably **`sidebar.php`**, the single role-filtered left nav included by
every module page (`<?php $active='<module>'; require __DIR__.'/frontend/partials/sidebar.php'; ?>`).
It renders each link only if `can_access($module)` is true, so the nav matches the user's role
and is identical across pages (replacing the previously duplicated, inconsistent hardcoded navs).

The frontend is plain HTML/CSS/JS rendered by PHP. There is **no build step**. Two distinct
visual styles exist today and should be unified during migration:

| Surface | Styling | Font |
|---------|---------|------|
| Landing (`index.php`) | Shared `frontend/assets/css/landing.css` | Poppins |
| Auth login (`login.php`) | Shared `frontend/assets/css/login.css` | Poppins |
| Module pages (Patient, Doctor, Appointment, …) | **Tailwind via CDN** with a per-page `tailwind.config` theme | Inter + Manrope + Material Symbols |
| Install/utility pages (`install.php`, `add_table.php`) | Tailwind via CDN | Inter |

**Palette (teal):** `#2bb18f`, `#0aa6a6`, `#259676` (auth) and `#00685d` / `#008376`
(modules). **Layout:** module pages use a fixed 260px left sidebar (`w-sidebar`) + content.

### Known inconsistencies to standardize

- **Branding mismatch:** module pages are titled **"MedLab Pro"** / "Laboratory Information
  System", while auth pages and the sidebar say **"ASCLEPIUS"**. Pick one.
- **Two Tailwind theme configs** are duplicated across module pages — extract to one shared
  config/stylesheet.
- **Tailwind CDN** is convenient but not ideal for production (FOUC, no purge, external
  dependency). Consider a built/pinned stylesheet during the asset-extraction step.
- **Inline CSS/JS everywhere** — extract to `frontend/assets/` (see [backend-plan.md](backend-plan.md) step 8).

## Page inventory & feature audit

Legend: done = working (real DB) · partial · stub = static UI, no backend.

| Page (file) | State | Auth guard | What it does | Gaps / notes |
|-------------|:----:|:---------:|--------------|--------------|
| `index.php` (Landing) | done | n/a | Public clinic website (Hi-Precision style layout). | Shared header/footer partials. |
| `privacy.php` | done | n/a | Privacy policy. | |
| `cookies.php` | done | n/a | Cookie policy + preference toggles (localStorage). | Banner on all public pages. |
| `terms.php` | done | n/a | Terms of use. | |
| `faq.php` | done | n/a | Frequently asked questions. | |
| `login.php` (Login) | done | n/a | Centered staff login card, no photo. | "Remember me" decorative. |
| `register.php` | done | n/a | Create staff account; duplicate-email check; `password_hash`. | No password strength/format rules; no role selection (RBAC pending). |
| `ForgotPassword.php` | done | no | Server-driven token reset (email link -> new password). | Token-based (`password_resets`), single-use, 1h expiry, no user enumeration. |
| `Dashboard.php` | done | yes | Counts/metrics across modules; `?api=get_stats`. | Some UI state kept in `localStorage`. |
| `Patient.php` | done | yes | CRUD patients + emergency contacts (sub-resource). | `?api=get_contacts` uses interpolated SQL (fix). No CSRF. |
| `Doctor.php` | done | yes | CRUD doctors; filter by dept/specialty/status. | Cleanest module — prepared statements only. Migration reference. |
| `Appointment.php` | done | yes | Book (walk-in/online), list, update status, delete. | Largest page (~1,312 lines); some `localStorage` sync. No CSRF. |
| `Medical Records.php` | done | yes | CRUD clinical records with vitals/notes. | Escapes output via `h()`. No CSRF. |
| `Laboratory Result.php` | done | yes | List lab results; abnormal flag; per-row **PDF report**; write-gated create form (patient + ordering-doctor dropdowns). | Create form added; fake tabs/"Batch verify" removed. |
| `Prescription.php` | done | yes | Create prescriptions with real **patient + doctor dropdowns** → saves to DB; real stat tiles; delete. | Rebuilt from a fake client-side demo. Write-gated (doctor/admin). |
| `Biling.php` (Billing) | done | yes | Real "Quick Bill" (patient dropdown → saves); real stat tiles; update payment status; delete. | Filename misspelled. Fake "Recent Activity"/"Revenue Breakdown" removed. Write-gated (cashier/admin). |
| `Agency Referral.php` | done | yes | Registry: refer a patient to an external agency (add/list/delete). | `agency_referrals`; reception/doctor + admin. |
| `Dental.php` | done | yes | Registry: dental records per patient. | `dental_records`; lab/doctor read, doctor write. |
| `Psych.php` | done | yes | Registry: psychiatry sessions per patient. | `psych_sessions`; doctor + admin. |
| `X-ray.php` | done | yes | Registry: x-ray studies per patient. | `xray_studies`; lab write, lab/doctor read. |
| `Lab Technicians.php` | done | yes | Staff registry: add/list/delete lab technicians + stats. | Admin-only (RBAC). Built on the new backend from day one. |
| `Cashiers.php` | done | yes | Staff registry: add/list/delete cashiers + stats. | Admin-only (RBAC). |
| `Receptionists.php` | done | yes | Staff registry: add/list/delete receptionists + stats. | Admin-only (RBAC). |

> **Migration status:** all 8 modules now run on the new backend (`backend/api/*.php`) via the
> shared bootstrap + a compat shim. Server-rendered ones keep their table query in the page;
> Dashboard has no API of its own. Next: CSRF + extracting inline CSS/JS to `frontend/assets/`
> (see [backend-plan.md](backend-plan.md)).

## Frontend ↔ backend interaction

Pages talk to their own backend via `fetch()` against the `?api=` / POST `action` endpoints
documented in [api-reference.md](api-reference.md). After migration, those `fetch()` URLs
point at `backend/api/<module>.php`, and every write sends a CSRF token. The interaction
pattern (JSON in/out) does not change — only where the code lives.

## Accessibility & UX follow-ups (production)

- Ensure all inputs have associated `<label>`s (mostly present); fix the login "Username"
  label (it's an email field).
- Provide clear loading and error states for `fetch()` calls (some buttons are UI-only today).
- Verify color contrast for the teal-on-white palette meets WCAG AA.
- Keep flows simple; avoid decorative controls that imply unimplemented behavior
  (e.g. "Remember me", "Batch verify").
