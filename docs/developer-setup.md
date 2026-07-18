# Developer Setup

Quick start for a new developer. This is a **living doc** — we expand it as the project grows.
For the bigger picture see [roadmap.md](roadmap.md) (status + codebase map).

## Prerequisites
- **PHP 8.x** (built/tested on 8.2). No Composer dependencies.
- A **MySQL / MariaDB** database — local (XAMPP) or a managed cloud DB (see "Database" below).
- Git.

## 1. Get the code
```
git clone <repo-url>
cd asclepius-demos
```

## 2. Configure the DB connection
- Copy `backend/config/.env.example` to `backend/config/.env`.
- Fill in `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` (plus `DB_PORT` / `DB_SSL_CA` for a cloud DB).
- `.env` is gitignored — never commit it.

## 3. Create the schema
- Apply the canonical schema (fresh install):
  `mysql -h <host> -P <port> -u <user> -p <db> < backend/db/schema.sql`
- Then apply incremental migrations from `backend/db/migrations/` in order (e.g. `001_*.sql`).
  Details: [database/migrations.md](database/migrations.md).

## 4. Run it
- Local (Windows + XAMPP): start MySQL, then double-click `start-local-server.bat`
  (or run `C:\xampp\php\php.exe -S localhost:8000` from the repo root).
- Open `http://localhost:8000/register.php` -> create an account -> log in.

## 5. QA your change
Don't ship on `php -l` alone — run it. Smoke-test recipe is in
[deployment.md](deployment.md) ("QA smoke test (local)").

## Database — pick a provider (DECISION NEEDED)
We want a **cloud SQL (MySQL) database** so there's no heavy local DB. The app is already
cloud-ready (`DB_PORT` + optional TLS via `DB_SSL_CA`), so choosing a provider is a `.env`
change, not code. Options and tradeoffs: [deployment.md](deployment.md) ("Database hosting").

> **Chosen provider:** _______________ (to decide). Quick guidance: Hostinger Remote MySQL if
> already on Hostinger; otherwise TiDB Cloud or Aiven. Avoid PlanetScale (it restricts foreign
> keys, which our schema relies on).

## Where things live
- Entry pages (their URLs) at the repo root; server logic in `backend/`; shared assets in
  `frontend/`; uploads/PDFs in `storage/`; docs in `docs/`. Full map: [roadmap.md](roadmap.md) section 2.

## Conventions
- All DB access uses prepared statements; writes go through `backend/api/<module>.php`.
- Migrated pages keep a backward-compat shim (see [backend-plan.md](backend-plan.md)).
- No emojis in code or docs. Update the relevant docs + `CHANGELOG.md` in the same change.
