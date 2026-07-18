# Proposal: Patient Portal (self-service patient access)

Status: **NOT approved — decision pending.** This is a proposal only. Nothing here is built.
The owner is undecided on whether Asclepius should have a patient-facing portal; this document
scopes it so the decision can be made deliberately.

## What it is

Today Asclepius is a **staff-only** system. Patients are **records**, not users — they cannot log
in. Staff (admin, doctor, lab, reception, cashier) create and view patient data on the patient's
behalf. A "patient portal" would let a **patient log in to their own account** and see a limited,
read-only view of their own data — for example their lab results, X-ray images, prescriptions,
appointments, and bills — and possibly request appointments.

## Why it is a large, separate effort (not a small add)

A portal is not another module; it introduces a **second, untrusted class of user** and changes
several cross-cutting assumptions:

1. **Separate authentication.** Patients need their own login, distinct from staff. That means a
   patient account table (or a `users.type` of staff vs patient), an invite/registration and
   identity-verification flow, and password reset for patients.
2. **Account-to-record linkage.** Each patient login must be securely tied to exactly one
   `patients` row, with a vetting step so people cannot claim someone else's record.
3. **A separate access model.** The current RBAC (`MODULE_PERMISSIONS`) is staff-role based and
   grants access to *all* records of a module. A patient must see **only their own** records —
   every query needs an ownership filter (`WHERE patientId = <their id>`), which is a different
   and stricter pattern than the current one. A mistake here leaks another patient's PHI.
4. **A separate UI.** Staff pages assume full access and show management controls; a portal needs
   its own read-only, patient-friendly pages and navigation.
5. **Heightened privacy/security.** Self-service PHI raises the stakes: stricter session handling,
   audit logging of who viewed what, careful error messages, and (in many jurisdictions)
   regulatory considerations for giving patients electronic access to their health data.

## Rough phased scope (if approved)

- **Phase P-1 — Foundation:** patient account model + linkage to `patients`, patient login /
  logout / password reset, and an ownership-scoped access helper (the portal equivalent of
  `require_module_access`, always filtered to the logged-in patient's id).
- **Phase P-2 — Read-only views:** the patient sees their own lab results, X-ray images
  (reusing the authenticated `xray_image.php` pattern with an ownership check), prescriptions,
  appointments, and bills.
- **Phase P-3 (optional) — Light interaction:** request an appointment, download a report,
  update limited contact details — each added carefully with validation.
- **Cross-cutting:** audit logging of patient views, portal-specific session hardening, and a
  security review before launch.

## Risks and cost

- **PHI leakage risk** is the dominant concern: any missing ownership filter exposes another
  patient's data. This needs a dedicated security review and tests.
- Meaningful effort (multiple phases), plus ongoing support (patient onboarding, password resets,
  support requests) that a staff-only tool does not incur.
- Regulatory/consent questions depend on the clinic's jurisdiction and policies.

## Recommendation

Treat the patient portal as a **separate project after the staff system is production-hardened**,
not part of the current go-live. First finish the go-live items (see `docs/system-status.md` and
`docs/production-release.md`) so the core system is stable and secure; then decide on the portal
with its own plan, budget, and security review.

If the owner wants to proceed, start with **Phase P-1 only** (foundation + one read-only view,
e.g. lab results) behind a feature flag, get it security-reviewed, and expand from there.

## Decision needed from the owner

- Do we want patients to log in at all? If yes, which data should they see first (lab results,
  X-ray images, prescriptions, appointments, bills)?
- Is appointment *requesting* in scope, or view-only for v1?
- Any regulatory/consent constraints for the clinic's location?

Until these are answered, this stays parked. Referenced from `docs/system-status.md` under
"decisions pending".
