-- 005: Add an image reference to X-ray studies so the lab can upload the actual X-ray file.
-- The DB stores only the relative path; the bytes live under storage/xray/ and are served
-- through the authenticated endpoint backend/api/xray_image.php (login + RBAC).

ALTER TABLE xray_studies ADD COLUMN imagePath VARCHAR(255) NULL AFTER status;
