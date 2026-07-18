-- 007: Store an optional signature image per doctor so clinical PDF reports can embed it.
-- Only the filename/path is stored; the JPEG lives under storage/signatures/ (web-denied).

ALTER TABLE doctors ADD COLUMN signaturePath VARCHAR(255) NULL AFTER notes;
