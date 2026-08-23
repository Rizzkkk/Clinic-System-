-- 010: Patient portal. Patients can now log in and see their own records.
--
-- Portal logins live on the existing users table rather than a separate table, so login,
-- password_hash, and the whole password_resets flow are reused unchanged. `role` stays the
-- single staff/patient discriminator (a separate `type` column would be a second source of
-- truth that can drift out of sync with role). New role values:
--
--   'patient_pending'   signed up, awaiting reception review. patientId IS NULL.
--   'patient'           verified and linked to a patients row. patientId IS NOT NULL.
--   'patient_rejected'  reviewed and denied, or unlinked after a bad match. patientId IS NULL.
--
-- claimedDob / claimedPhone are what the applicant typed at signup. They are NOT identity --
-- they exist so reception can match the claim against a real patients row before linking.
-- Matching on name alone is how the wrong record gets handed to the wrong person.

ALTER TABLE users
  ADD COLUMN patientId    INT UNSIGNED NULL AFTER role,
  ADD COLUMN claimedDob   DATE         NULL AFTER patientId,
  ADD COLUMN claimedPhone VARCHAR(20)  NULL AFTER claimedDob,
  ADD COLUMN linkedAt     DATETIME     NULL AFTER claimedPhone,
  ADD COLUMN linkedBy     INT UNSIGNED NULL AFTER linkedAt;

-- One portal account per patient. MySQL permits many NULLs in a UNIQUE index, so existing
-- staff rows and unlinked pending rows are unaffected. This is what makes a double-link
-- impossible even if the approval screen is misused or a request is forged.
--
-- ON DELETE SET NULL on patientId: deleting a patient record must not delete the login row
-- silently. It breaks the link instead, and require_patient() then fails closed.
ALTER TABLE users
  ADD UNIQUE KEY users_patient_uk  (patientId),
  ADD CONSTRAINT users_patient_fk   FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE SET NULL,
  ADD CONSTRAINT users_linked_by_fk FOREIGN KEY (linkedBy)  REFERENCES users(id)    ON DELETE SET NULL;

-- No backfill: existing staff rows already carry a valid role, and the new columns are NULL
-- for them by definition.
--
-- Appointment requests need no schema change. A portal request is an appointments row with
-- status 'Requested'; reception confirms it by setting status to 'Scheduled' through the
-- existing update_appointment / update_status actions.
