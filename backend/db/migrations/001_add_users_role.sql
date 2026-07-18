-- 001: Add the RBAC role column to users.
-- Existing users default to 'admin' (full access), so applying this is non-breaking.
-- Roles: admin, reception, lab, cashier, doctor.

ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin';
