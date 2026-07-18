# Deployment & Operations

## Stack

- **Runtime:** PHP (mysqli), no framework, no Composer dependencies.
- **Database:** MySQL / MariaDB (`utf8mb4` / `utf8mb4_unicode_ci`).
- **Hosting:** shared hosting (the committed DB user `u805024096_*` matches that pattern).
  Deployment is file-based (upload PHP files to the web root) — no build step.

## Local development setup

1. Install XAMPP (or any PHP + MySQL stack).
2. Create the database and tables from the canonical schema (after the migration phase:
   `backend/db/schema.sql`; today: `database.sql` / `install.php`). See
   [database/migrations.md](database/migrations.md).
3. Configure DB credentials via environment (target) — see below. Today they are hardcoded in
   `db.php`, which is exactly what the migration removes.
4. Serve the folder with Apache/PHP and open `index.php`.
5. Create a staff account via `register.php`, then log in.

## QA smoke test (local)

Don't consider a change done on `php -l` alone — **run it.** With XAMPP MySQL up and
`backend/db/schema.sql` imported:

1. Seed a login (hash via `php -r 'echo password_hash("test1234", PASSWORD_DEFAULT);'`):
   `INSERT INTO users (full_name,email,password_hash) VALUES ('QA','qa@test.com','<hash>');`
2. Start the server: `start-local-server.bat` (or `php -S localhost:8000`).
3. Log in, saving the cookie:
   `curl -c c.txt --data "login_submit=1&email=qa@test.com&password=test1234" http://localhost:8000/index.php`
4. Exercise endpoints with the cookie — expect **JSON** (not HTML):
   - read: `curl -b c.txt "http://localhost:8000/backend/api/doctors.php?api=get_stats"`
   - write: `curl -b c.txt --data "action=add_doctor&firstName=A&lastName=B&specialty=X&department=Y&licenseNumber=L1&employeeId=E1" http://localhost:8000/backend/api/doctors.php` → `{"success":true,...}`
   - shim: `curl -b c.txt "http://localhost:8000/Patient.php?api=get_stats"` → JSON via delegation
   - guard: same read **without** `-b c.txt` → HTTP `401`

## Environment variables (target)

After step 1 of the migration, `backend/config/config.php` reads these from the environment.
`backend/config/.env.example` documents them; the real `.env` is **gitignored**.

| Variable | Purpose | Example |
|----------|---------|---------|
| `DB_HOST` | Database host | `localhost` |
| `DB_NAME` | Database name | `asclepius_db` |
| `DB_USER` | Database user | `asclepius_app` |
| `DB_PASSWORD` | Database password | *(secret — never committed)* |

> On shared hosting without easy OS env vars, load a `.env` file that lives **outside** the
> web root (or is denied by the web server) — never a committed `.env`.

## Database hosting (cloud SQL)

`backend/db/connection.php` reads `DB_HOST` / `DB_PORT` / `DB_NAME` / `DB_USER` / `DB_PASSWORD`
(+ optional `DB_SSL_CA`) from `backend/config/.env`. So the database can run **anywhere** — local
XAMPP or a managed cloud MySQL — by changing `.env` only. No code change.

### Using a cloud database (keeps local light)

1. Create a managed MySQL database + user at the provider.
2. Import the schema: `mysql -h <host> -P <port> -u <user> -p <db> < backend/db/schema.sql`
   (or paste it into the provider's SQL console).
3. Point `backend/config/.env` at it:
   ```
   DB_HOST=<cloud-host>
   DB_PORT=<cloud-port>       # managed DBs often use a non-3306 port
   DB_NAME=<db>
   DB_USER=<user>
   DB_PASSWORD=<password>
   DB_SSL_CA=/path/to/ca.pem  # only if the provider requires TLS
   ```
4. Allow your dev machine's IP and the production server's IP in the provider's access controls
   (or enable public access — always with TLS).
5. With this, **local dev needs no MySQL of its own** — it talks to the cloud DB. Tradeoff: needs
   internet, slightly slower than localhost. (Keep a local XAMPP DB too and swap `.env` for
   offline/fast work.)

### Provider options

| Provider | Notes |
|----------|-------|
| **Hostinger Remote MySQL** | Simplest if already on Hostinger. Enable "Remote MySQL" in hPanel, allowlist your IP, reuse the existing DB. No new vendor, no TLS cert to manage. |
| **TiDB Cloud** / **Aiven for MySQL** | Managed, free/low tiers, MySQL-compatible, support foreign keys. Use the provider's custom port + download its CA cert for `DB_SSL_CA`. |
| **AWS RDS / Google Cloud SQL** | Robust, paid, more setup. For when you outgrow the above. |
| **PlanetScale** | Avoid for now — its Vitess engine restricts foreign keys, and our schema (appointments, billing, prescriptions, etc.) depends on them. |

### TLS
Managed providers usually require TLS. Download the provider's CA certificate, store it **outside
the web root** (e.g. next to `.env`), and set `DB_SSL_CA` to its path. `connection.php` enables
`MYSQLI_CLIENT_SSL` automatically when `DB_SSL_CA` is set.

## Environments

| Environment | Host | Database | Notes |
|-------------|------|----------|-------|
| Development | local (XAMPP) | local `asclepius_db` | `display_errors` on. |
| Staging | hosting (separate area) | separate staging DB | mirror of prod for verification. |
| Production | hosting web root | production DB | `display_errors` off; HTTPS; backups on. |

## Schema / migrations

- The canonical schema is [database/database.md](database/database.md), realized as
  `backend/db/schema.sql` after the migration phase.
- Changes are forward-only numbered files in `backend/db/migrations/` applied deliberately —
  **not** by `db.php` on each request. See [database/migrations.md](database/migrations.md).

## Production go-live checklist

Gate production on all of these (cross-referenced to [security.md](security.md)):

- [ ] **Credentials rotated** and moved to environment; none in source or git history usable (S-1).
- [ ] `.env` is gitignored; only `.env.example` is committed.
- [ ] **CSRF tokens** enforced on every write (S-2).
- [ ] All SQL parameterized; the `Patient.php get_contacts` interpolation fixed (S-3).
- [ ] DB error messages no longer returned to clients; errors logged server-side (S-4).
- [ ] `install.php` and `add_table.php` **removed** from the deployed files (S-5).
- [ ] `db.php` no longer runs `CREATE TABLE`; schema applied via migrations (S-6).
- [ ] Real password-reset flow in place, or `ForgotPassword.php` disabled until built (S-7).
- [ ] RBAC enforced; `users.role` populated (S-8).
- [ ] Login has a password policy + basic throttling (S-9).
- [ ] Session cookies set `HttpOnly` + `Secure` + `SameSite`; HTTPS enforced (S-10).
- [ ] `display_errors` off in production (S-11).
- [ ] Database **backups** scheduled and a restore tested.
- [ ] One canonical schema; `database.sql` / `install.php` no longer used as schema sources.

## Backups & monitoring

- Schedule regular automated DB backups; verify restores periodically.
- Monitor PHP/web-server error logs (where leaked errors now go instead of the client).
