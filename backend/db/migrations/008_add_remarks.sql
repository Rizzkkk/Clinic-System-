-- 008: Add a free-text Remarks field to the diagnostic modules so staff can note test-specific
-- detail (e.g. fasting required, specimen notes). The specific test itself stays in the existing
-- column (testType / sessionType / bodyPart), now offered as a pick-or-type list on the form.

ALTER TABLE laboratory_results ADD COLUMN remarks TEXT NULL AFTER referenceRange;
ALTER TABLE psych_sessions     ADD COLUMN remarks TEXT NULL AFTER notes;
ALTER TABLE xray_studies       ADD COLUMN remarks TEXT NULL AFTER impression;
