# Production Release — Final Touches

The concrete "before we go live" list: what to **delete**, what you **must do**, and what's
**planned**. Pairs with the environment/setup detail in [deployment.md](deployment.md) and the
issue list in [security.md](security.md).

## Delete / do not deploy

| Item | Why |
|------|-----|
| the `dev/` folder | Archived setup utilities: `install.php` (connects as MySQL `root`), `add_table.php`, and the stale `database.sql` — all superseded by `backend/db/schema.sql`. Delete the whole folder. |
| `start-local-server.bat` | Local Windows/XAMPP dev helper — never serve it from production. |
| `CLAUDE.md` | An AI prompt, not project docs. Harmless but irrelevant to the app. |
| The **local** `backend/config/.env` | The production server must have its **own** `.env` with real credentials — do not ship the local one. |

> Direct web access to `backend/` internals (incl. `.env`) is already blocked by
> `backend/.htaccess`. Confirm your host honors `.htaccess` (Apache/LiteSpeed do; nginx needs
> equivalent rules).

## Must do before production

1. **Create `backend/config/.env` on the server** with the real Hostinger DB credentials.
   (It's gitignored, so it is never deployed for you.)
2. **Rotate the DB password** — `Asclepius-123` is in git history; change it in the hosting
   panel and update the server `.env`.
3. **Apply the canonical schema** (`backend/db/schema.sql`) and confirm existing tables match —
   especially the extra `medical_records` columns. See [database/migrations.md](database/migrations.md).
4. **Finish the module migrations** — Appointment, Medical Records, Laboratory Result,
   Prescription, Billing, Dashboard (Doctor done and Patient done are done). See [backend-plan.md](backend-plan.md).
5. **Add CSRF protection** to every POST action (currently none — [security.md](security.md) S-2).
6. **Implement RBAC** — restrict pages/actions by role (`admin` / `reception` / `lab` /
   `cashier` / `doctor`). Today any login sees everything ([security.md](security.md) S-8).
7. **Password reset** — implement a real token-based flow for `ForgotPassword.php`, or disable
   the link until it's built ([security.md](security.md) S-7).
8. **PHP hardening** — `display_errors = Off` in production, errors to the log; session cookies
   `HttpOnly` + `Secure` + `SameSite`; enforce **HTTPS** ([security.md](security.md) S-10/S-11).
9. **Database backups** scheduled, with a tested restore.

## Planned / should do (not launch-blocking)

- **PDF lab results** — export a lab result as a PDF, available from **both the doctor view and
  the lab-technician view**. Requires the Laboratory Result module migrated first, a small PHP
  PDF library (e.g. Dompdf or FPDF — the one justified new dependency), and the RBAC roles so
  both `doctor` and `lab` can generate it. See [requirements.md](requirements.md) FR-16.
- **Build the planned modules** (Dental, Psychiatry, X-ray, Agency Referral) against the tables
  in [database/migrations.md](database/migrations.md), or hide their nav links until built.
- **Clarify "Agency Referral"** — page name/content mismatch ([frontend.md](frontend.md)).
- **Unify branding & design system** — "MedLab Pro" vs "ASCLEPIUS"; reduce the Tailwind-CDN
  dependency; extract inline CSS/JS to `frontend/assets/` ([frontend.md](frontend.md)).
- **Automated tests** once the module structure is stable.

## Production-readiness test (passed 2026-07-08)

Verified locally end-to-end:
- Fresh DB from `backend/db/schema.sql` creates all 12 tables cleanly (deployable).
- Auth guard returns `401` without a session; login works.
- All 12 pages load `200` as admin with **zero PHP warnings/errors**.
- RBAC grid holds: 35/35 read + 11/11 write checks across the 5 roles match the matrix.
- PDF lab report returns valid `application/pdf` (lab/doctor/admin); reception/cashier `403`.
- `display_errors` is off unless `APP_ENV=development` (config-enforced).

## Still open before go-live (decisions)

- **ForgotPassword** — client-only stub; the "Forgot Password?" link on the login page does
  nothing. Build a real token-based reset (`password_resets`) **or** hide the link.
- **Stub pages** (Dental, Psych, X-ray, Agency Referral) — static placeholders; hide their nav
  links or build them.

## Quick pre-launch checklist

- [x] Code done: modules migrated, CSRF, RBAC, PDF, env config, error hardening — all QA'd
- [ ] Set `APP_ENV=production` in the server `backend/config/.env` (created there; DB password rotated)
- [ ] `dev/` folder + `start-local-server.bat` removed from the server
- [ ] Canonical schema applied (or migrations run) on the production/cloud DB
- [ ] ForgotPassword + stub pages resolved (built or hidden)
- [ ] HTTPS on; DB backups scheduled
