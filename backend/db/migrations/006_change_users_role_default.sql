-- 006: Security fix. The users.role column previously defaulted to 'admin', so any row inserted
-- without a role (e.g. public self-registration) silently became a superuser. Default to
-- 'pending' (a no-access state); an administrator assigns a real role afterwards.

ALTER TABLE users ALTER role SET DEFAULT 'pending';
