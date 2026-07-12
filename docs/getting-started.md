# Getting Started — Install, Configure & Run

How to get the Asclepius clinic system running on your machine, including the
database setup and migrations. No prior knowledge of the codebase is assumed.

Asclepius is a server-rendered PHP application (flat pages at the repo root,
shared logic under `backend/`). There is **no framework, no Composer, and no npm
build step** — you need PHP and a MySQL/MariaDB database, and that is it.

---

## 1. Prerequisites

- **PHP 8.x** (built and tested on 8.2) with the **mysqli** extension enabled
  (bundled with XAMPP and most PHP distributions).
- **MySQL 5.7+ / MariaDB 10.2+** — local (e.g. XAMPP) or a managed cloud database.
- **Git** (to clone the repository).

The database is named **`asclepius_db`** throughout this guide.

---

## 2. Get the code

```
git clone https://github.com/Rizzkkk/Clinic-System-.git
cd Clinic-System-
```

---

## 3. Quick start — Windows + XAMPP (recommended for local testing)

This is the fastest path. The included launcher creates the database, loads the
schema, and starts the web server for you.

1. Install **XAMPP** and open the **XAMPP Control Panel**.
2. Click **Start** next to **MySQL** (the row turns green). Apache is not needed —
   the app runs on PHP's built-in server.
3. Copy the environment template so the app can connect:
   - Copy `backend/config/.env.example` to `backend/config/.env`.
   - The defaults already match a stock XAMPP install (host `localhost`, user
     `root`, empty password, database `asclepius_db`), so no edits are needed for
     local testing. See section 5 for what each setting means.
4. Double-click **`start-local-server.bat`** in the project root. It will:
   - create the `asclepius_db` database if it does not exist,
   - load the full schema from `backend/db/schema.sql`,
   - start the server at `http://localhost:8000`.
5. Leave that window **open** (closing it stops the server).
6. Open your browser to **`http://localhost:8000/index.php`**.

If the launcher reports it cannot reach MySQL, MySQL is not started — go back to
step 2 and try again.

Continue at **section 6 (First login)**.

---

## 4. Manual setup — any OS (macOS / Linux / Windows without the launcher)

Use this if you are not on XAMPP or prefer to run the steps yourself.

### 4a. Configure the database connection

Credentials are read from environment variables (or a `.env` file); nothing is
hardcoded. Copy the template and fill it in:

```
cp backend/config/.env.example backend/config/.env
```

Then edit `backend/config/.env` (see section 5 for the full list). At minimum set
`DB_NAME`, `DB_USER`, and `DB_PASSWORD`. `.env` is gitignored — never commit it.

### 4b. Create the database

```
mysql -u <user> -p -e "CREATE DATABASE IF NOT EXISTS asclepius_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4c. Create the tables (schema)

`backend/db/schema.sql` is the **single source of truth** for the schema. It
already includes every change to date, so a fresh install only needs this one
file — you do **not** need to replay the numbered migrations.

```
mysql -h <host> -P <port> -u <user> -p asclepius_db < backend/db/schema.sql
```

(The `CREATE DATABASE` / `USE` lines inside `schema.sql` are commented out on
purpose, so you select the database on the command line as shown above.)

### 4d. Run the server

From the repository root:

```
php -S localhost:8000
```

Open **`http://localhost:8000/index.php`**.

---

## 5. Environment variables (`backend/config/.env`)

| Variable      | Required | Default       | Notes |
|---------------|----------|---------------|-------|
| `APP_ENV`     | no       | `production`  | Use `development` locally to show PHP errors in the browser. Production logs them instead. |
| `DB_HOST`     | yes      | `localhost`   | Database host. |
| `DB_NAME`     | yes      | (none)        | Database name — `asclepius_db`. |
| `DB_USER`     | yes      | (none)        | Database user. |
| `DB_PASSWORD` | no       | (empty)       | Database password. Empty is valid for a default XAMPP `root`. |
| `DB_PORT`     | no       | `3306`        | Set for cloud databases that use a non-standard port. |
| `DB_SSL_CA`   | no       | (none)        | Path to a CA certificate. Set only when a managed cloud DB requires TLS (Aiven, TiDB Cloud, etc.). |

**Cloud MySQL:** moving off local is a `.env`-only change — set `DB_HOST`,
`DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, and `DB_SSL_CA` if the provider
requires TLS. No code changes are needed.

---

## 6. First login and creating accounts

The app is session-based. Logins live in the `users` table and each has a
**role** that decides what it can see and do.

- **`register.php`** creates a new account with the role **`pending`**, which has
  **no access** until an administrator promotes it. (This is deliberate — it
  prevents self-registration from gaining privileges.)
- To grant a role, update it directly in the database:

  ```sql
  UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
  ```

  Valid roles: `admin`, `doctor`, `reception`, `lab`, `cashier`.

- Log out and back in for a role change to take effect (the role is read at login).

Create your first **admin** this way, then use it to manage the rest of the
system. To seed one login per role for testing, see `docs/testing-guide.md`.

> Patients and doctor-directory entries are **data, not logins** — they are added
> from inside the app (Patients / Doctors pages) and never sign in.

---

## 7. Upgrading an existing database (migrations)

For a **fresh** install you only need `schema.sql` (section 4c). Migrations matter
when you already have a populated database and are pulling in newer changes.

- Migrations are **forward-only, numbered SQL files** in
  `backend/db/migrations/` (`001_*.sql`, `002_*.sql`, …).
- Apply them **in numeric order**, and only the ones newer than your current
  database. They are applied deliberately by hand — the app never runs schema
  changes automatically on a web request.

```
mysql -h <host> -P <port> -u <user> -p asclepius_db < backend/db/migrations/001_add_users_role.sql
mysql -h <host> -P <port> -u <user> -p asclepius_db < backend/db/migrations/002_add_staff_tables.sql
# ...continue in order through the highest-numbered file you have not applied.
```

`schema.sql` always reflects the cumulative result of every migration, so the two
never disagree.

---

## 8. Project layout (orientation)

- **Repo root** — the pages you navigate to by URL (`index.php` login,
  `Dashboard.php`, `Patient.php`, `Doctor.php`, `Appointment.php`,
  `Prescription.php`, `Biling.php`, `Medical Records.php`,
  `Laboratory Result.php`, `X-ray.php`, `Dental.php`, `Psych.php`,
  `Agency Referral.php`, staff pages, `register.php`, `logout.php`). `db.php` is a
  compatibility shim that opens the shared connection.
- **`backend/`** — server logic: `config/` (env + connection settings),
  `db/` (canonical `schema.sql` + `migrations/`), `auth/` (session bootstrap +
  RBAC), `lib/` (helpers, PDF), `api/` (one JSON handler per module).
- **`frontend/`** — shared assets (CSS/JS/images) and the role-filtered sidebar.
- **`storage/`** — uploaded/generated files (X-ray images, doctor signatures).
  The database stores only the file path; the bytes live on disk.

---

## 9. Troubleshooting

- **"Could not reach MySQL" from the launcher** — MySQL is not started. Open the
  XAMPP Control Panel and Start MySQL, then run the launcher again.
- **"Service temporarily unavailable" in the browser** — the app could not connect
  to the database. Check `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` in
  `backend/config/.env` and that the database exists and the schema is loaded.
- **Blank page / white screen** — set `APP_ENV=development` in
  `backend/config/.env` to surface the underlying PHP error, then fix and set it
  back to `production`.
- **Cannot see any pages after registering** — new accounts are `pending` by
  design. Promote the account with the `UPDATE users SET role = ...` step in
  section 6.
