# frontend/

Presentation layer for Asclepius - shared page markup and static assets. The per-module
**entry pages still live as flat
`*.php` files at the repo root** (served at the web root); this folder holds the shared pieces
extracted out of them. There is **no build step** — plain HTML/CSS/JS rendered by PHP, with
Tailwind and a few libraries loaded via CDN.

## Layout

| Folder | Holds | Status |
|--------|-------|--------|
| `partials/` | `sidebar.php` (module nav); `public-header.php` / `public-footer.php` (public website pages + cookie banner). | partial |
| `assets/img/` | `ASCLEPIUS.jpg` (logo, wired into the sidebar and PDFs via `backend/config/clinic.php`), `Doctors.webp`. | **done** |
| `assets/css/` | `landing.css`, `login.css`; shared module styles still planned for extraction. | partial |
| `assets/js/`  | `cookies.js` (cookie consent banner + preferences); module `fetch()` logic still planned. | partial |
| `views/` | Per-module page markup, once the root `*.php` pages are moved here. | planned |

## Shared sidebar

Every module page includes `partials/sidebar.php` as the first element inside `<body>`:

```php
<?php $active = 'doctors'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>
```

Set `$active` to the page's module key (or `'dashboard'`) to highlight the current link. The
sidebar renders each link only when `can_access($module)` is true, so the nav automatically
matches the signed-in user's role and is identical across pages. It pulls the clinic name, logo,
and contact lines from `backend/config/clinic.php`, and its styling uses only theme-independent
Tailwind utilities so it looks the same under each page's custom Tailwind config.

## Design system (current)

- **Palette (teal):** `#00685d` / `#008376` (module pages, sidebar), `#2bb18f` / `#0aa6a6` /
  `#259676` (auth pages).
- **Fonts:** Inter + Manrope + Material Symbols (module pages); Poppins (auth pages).
- **Layout:** module pages use a fixed 260px left sidebar + content area.

### Known inconsistencies to standardize

- **Branding mismatch:** module page `<title>`s say "MedLab Pro" while the sidebar and auth
  pages say "ASCLEPIUS". Pick one.
- **Two duplicated Tailwind theme configs** across module pages — extract to one shared config.
- **Inline CSS/JS** still lives in each page — move into `assets/css/` and `assets/js/`.
- **Tailwind + SweetAlert2 via CDN** — fine for the demo; pin/build for production.

## Frontend ↔ backend interaction

Pages talk to their own backend with `fetch()` against the `?api=` GET and POST `action`
endpoints listed in the header comment of each `backend/api/*.php` handler; JSON in, JSON out.
Those calls currently hit the root page (e.g. `Doctor.php?api=get_doctors`), which delegates to
`backend/api/doctors.php` via a compat shim. Use the shared `showError` / `showSuccess` /
`confirmAction` helpers for user feedback rather than raw `alert()`/`confirm()`.
