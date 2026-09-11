# Asclepius

Clinic management system for Asclepius Medical & Diagnostic Group Inc. - staff modules,
a row-scoped patient portal, and the public website, plus a React landing page kept as a
design reference.

## What ships

Server-rendered PHP 8 + MySQL (mysqli). No framework, no build step. Thin view pages sit at
the repo root (`Patient.php`, `Appointment.php`, ...), each delegating `?api=` and POST
`action` requests to a handler in `backend/api/`. The public homepage is `index.php`.

| Area | Holds | Docs |
|------|-------|------|
| `backend/` | Bootstrap, auth/RBAC, API handlers, schema and migrations. | `backend/README.md` |
| `frontend/` | Shared partials and static assets; no build step. | `frontend/README.md` |
| `storage/` | Uploaded X-rays and signatures (not web-reachable). | `storage/README.md` |
| root `*.php` | Per-module entry pages, served at the web root. | - |

## Landing page source (not built, not served)

`index.html`, `src/`, `package.json` and `vite.config.js` are a React 19 + Tailwind v4 + Vite
landing page merged from the `landing-page` branch. **It is not built and not deployed** - the
project is deliberately no-build-step, and `.htaccess` pins `DirectoryIndex` to `index.php` so
the PHP site keeps serving. **Its design was ported into the PHP public pages on 2026-09-11**
(`index.php`, `frontend/assets/css/landing.css`); the tree stays as the reference for that work.

Its **content was not ported and must not be.** Every component is marked
`@status Development / Staging` over `STUB DATA`: invented physicians with credentials, room
numbers and consultation schedules, and twelve named real HMO companies listed as accredited
partners. The Doctors and HMO sections stay unbuilt until the owner supplies the real roster and
accreditation list.

All nine components are written and wired into `src/App.jsx`:

| Component | Covers |
|-----------|--------|
| `Navbar.jsx` | Emergency bar, logo, nav anchors, staff portal link. |
| `Hero.jsx` | Headline, CTAs, trust badges, floating cards, quick stats. |
| `About.jsx` | Corporate profile, mission and vision, facilities. |
| `Services.jsx` | Outpatient clinics (cardiology, pedia, OB-GYN, dental). |
| `Diagnostics.jsx` | Laboratory packages, X-ray, ultrasound, ECG, blood chemistry. |
| `Doctors.jsx` | Specialist roster, schedules, credentials. |
| `HMOSection.jsx` | Accredited health cards and PhilHealth. |
| `AppointmentModal.jsx` | Booking and inquiry modal. |
| `Footer.jsx` | Operating hours, location, hotline, copyright. |

### Design system

Brand tones are medical cyan (`#0891b2`), sky blue (`#0284c7`) and deep navy (`#0f172a`), with
emerald for accreditation cues and soft coral for the emergency hotline. Clean sans-serif on a
`max-w-[1400px]` container, glassmorphic badges, accessible contrast ratios. These are the
values to carry across when porting the design into the PHP pages.

## Running it

Point a PHP 8 server with mysqli at the repo root; `start-local-server.bat` does this on Windows.
Import `backend/db/schema.sql`, then apply `backend/db/migrations/` in numeric order - there is
no migration runner, so migrations are applied by hand and must land before code that reads the
new columns.
