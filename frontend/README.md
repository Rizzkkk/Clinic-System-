# frontend/

Presentation layer for Asclepius — the page markup and shared assets. This folder is the
**target home** during the refactor-in-place migration (see
[docs/frontend.md](../docs/frontend.md)). It is currently a **skeleton** — markup still lives
inline in the flat `*.php` pages at the repo root and is extracted here as each module is
migrated.

## Layout

| Folder | Holds | Status |
|--------|-------|--------|
| `views/` | Per-module page markup (the entry pages a user navigates to). | planned |
| `assets/css/` | Shared styles extracted from the per-page inline `<style>` blocks. | planned |
| `assets/js/`  | Shared and per-page `fetch()` logic that calls the backend `?api=` / POST endpoints. | planned |
| `assets/img/` | `ASCLEPIUS.jpg`, `Doctors.webp` (moved here from the repo root). | done |

## Design system (to standardize)

- Font: **Poppins** (auth pages) / **Inter + Manrope** (module pages) — to be unified.
- Palette: teal — `#2bb18f`, `#0aa6a6`, `#259676`, `#00685d`.
- Module pages currently load **Tailwind via CDN** with a per-page theme config; auth pages
  use hand-written CSS. The branding is also inconsistent ("ASCLEPIUS" vs "MedLab Pro").
  These are normalized during migration — see [docs/frontend.md](../docs/frontend.md).
