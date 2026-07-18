-- 004: Build out the previously-stub modules — dental, psychiatry, x-ray, agency referrals.
-- Each is patient-linked (ON DELETE CASCADE), modeled on the existing clinical modules.

CREATE TABLE IF NOT EXISTS dental_records (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  recordDate DATE NOT NULL,
  procedureName VARCHAR(150),
  toothNumber VARCHAR(20),
  findings TEXT,
  recommendations TEXT,
  dentist VARCHAR(150),
  status VARCHAR(50) DEFAULT 'Completed',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX dental_patient_idx (patientId),
  CONSTRAINT dental_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS psych_sessions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  sessionDate DATE NOT NULL,
  sessionType VARCHAR(100),
  chiefComplaint TEXT,
  notes TEXT,
  followUpDate DATE,
  clinician VARCHAR(150),
  status VARCHAR(50) DEFAULT 'Completed',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX psych_patient_idx (patientId),
  CONSTRAINT psych_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS xray_studies (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  studyDate DATE NOT NULL,
  bodyPart VARCHAR(100),
  findings TEXT,
  impression TEXT,
  radiologist VARCHAR(150),
  status VARCHAR(50) DEFAULT 'Released',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX xray_patient_idx (patientId),
  CONSTRAINT xray_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS agency_referrals (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  referralDate DATE NOT NULL,
  agencyName VARCHAR(150),
  reason TEXT,
  referredBy VARCHAR(150),
  status VARCHAR(50) DEFAULT 'Pending',
  notes TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX referral_patient_idx (patientId),
  CONSTRAINT referral_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE
);
