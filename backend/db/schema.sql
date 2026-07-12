-- Asclepius — canonical database schema (single source of truth).
-- Captured from the previously-inline schema in db.php (the most complete version:
-- indexes, ON DELETE rules, and the full medical_records columns).
--
-- Local dev: uncomment the CREATE DATABASE / USE lines below.
-- Shared hosting: the database already exists; select it in your panel, then run the
-- CREATE TABLE statements. Do NOT run this on every request — apply deliberately.

-- CREATE DATABASE IF NOT EXISTS asclepius_db
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;
-- USE asclepius_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(191) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_users_email (email)
);

CREATE TABLE IF NOT EXISTS doctors (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  firstName VARCHAR(100) NOT NULL,
  lastName VARCHAR(100) NOT NULL,
  middleName VARCHAR(100),
  specialty VARCHAR(100) NOT NULL,
  department VARCHAR(100) NOT NULL,
  shift VARCHAR(50),
  licenseNumber VARCHAR(100) UNIQUE NOT NULL,
  employeeId VARCHAR(100) UNIQUE NOT NULL,
  phone VARCHAR(20),
  email VARCHAR(191) UNIQUE,
  address TEXT,
  dob DATE,
  gender VARCHAR(20),
  education TEXT,
  notes TEXT,
  signaturePath VARCHAR(255) NULL,
  status VARCHAR(50) DEFAULT 'On Duty',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS patients (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  firstName VARCHAR(100) NOT NULL,
  lastName VARCHAR(100) NOT NULL,
  middleName VARCHAR(100),
  dateOfBirth DATE,
  gender VARCHAR(20),
  bloodType VARCHAR(10),
  phone VARCHAR(20),
  email VARCHAR(191),
  address TEXT,
  emergencyContact VARCHAR(150),
  emergencyPhone VARCHAR(20),
  medicalHistory TEXT,
  allergies TEXT,
  insurance_provider VARCHAR(150),
  insurance_number VARCHAR(100),
  status VARCHAR(50) DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS appointments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  doctorId INT UNSIGNED NOT NULL,
  appointmentDate DATE NOT NULL,
  appointmentTime TIME NOT NULL,
  reason TEXT,
  status VARCHAR(50) DEFAULT 'Scheduled',
  notes TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX appointment_patient_idx (patientId),
  INDEX appointment_doctor_idx (doctorId),
  CONSTRAINT appointment_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT appointment_doctor_fk FOREIGN KEY (doctorId) REFERENCES doctors(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS medical_records (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  recordType VARCHAR(100),
  recordDate DATE NOT NULL,
  description TEXT,
  findings TEXT,
  recommendations TEXT,
  physician VARCHAR(150),
  department VARCHAR(100),
  chiefComplaint TEXT,
  diagnosis TEXT,
  clinicalNotes TEXT,
  prescription TEXT,
  bloodPressure VARCHAR(50),
  heartRate VARCHAR(50),
  temperature VARCHAR(50),
  weight VARCHAR(50),
  allergies VARCHAR(255),
  attachments VARCHAR(500),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX medical_record_patient_idx (patientId),
  CONSTRAINT medical_record_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS laboratory_results (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  testType VARCHAR(100),
  testDate DATE NOT NULL,
  results TEXT,
  referenceRange VARCHAR(100),
  remarks TEXT,
  abnormalFlag VARCHAR(10),
  orderedBy INT UNSIGNED,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX laboratory_patient_idx (patientId),
  INDEX laboratory_doctor_idx (orderedBy),
  CONSTRAINT laboratory_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT laboratory_doctor_fk FOREIGN KEY (orderedBy) REFERENCES doctors(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS prescriptions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  doctorId INT UNSIGNED NOT NULL,
  medicationName VARCHAR(150) NOT NULL,
  dosage VARCHAR(100),
  frequency VARCHAR(100),
  duration VARCHAR(100),
  prescriptionDate DATE NOT NULL,
  expiryDate DATE,
  notes TEXT,
  status VARCHAR(50) DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX prescription_patient_idx (patientId),
  INDEX prescription_doctor_idx (doctorId),
  CONSTRAINT prescription_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT prescription_doctor_fk FOREIGN KEY (doctorId) REFERENCES doctors(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS billing (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  appointmentId INT UNSIGNED,
  description VARCHAR(255),
  amount DECIMAL(10, 2),
  status VARCHAR(50) DEFAULT 'Pending',
  billingDate DATE,
  paymentDate DATE,
  paymentMethod VARCHAR(50),
  notes TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX billing_patient_idx (patientId),
  INDEX billing_appointment_idx (appointmentId),
  CONSTRAINT billing_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT billing_appointment_fk FOREIGN KEY (appointmentId) REFERENCES appointments(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS patient_contacts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patientId INT UNSIGNED NOT NULL,
  contactName VARCHAR(150) NOT NULL,
  relationship VARCHAR(100),
  phoneNumber VARCHAR(20) NOT NULL,
  email VARCHAR(191),
  address TEXT,
  isPrimary TINYINT(1) DEFAULT 0,
  notes TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX patient_contacts_patient_idx (patientId),
  CONSTRAINT patient_contacts_patient_fk FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE
);
-- 002: Staff role directories — lab technicians, cashiers, receptionists.
-- Product decision: one page + table per role (modeled on doctors), see docs/database/erd.md.
-- Accountability FKs (laboratory_results.performedBy etc.) are deferred until the modules
-- actually record them (docs/database/migrations.md N-4).

CREATE TABLE IF NOT EXISTS lab_technicians (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  firstName VARCHAR(100) NOT NULL,
  lastName VARCHAR(100) NOT NULL,
  middleName VARCHAR(100),
  employeeId VARCHAR(100) UNIQUE NOT NULL,
  section VARCHAR(50),
  licenseNumber VARCHAR(100),
  shift VARCHAR(50),
  phone VARCHAR(20),
  email VARCHAR(191),
  address TEXT,
  dob DATE,
  gender VARCHAR(20),
  status VARCHAR(50) DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS cashiers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  firstName VARCHAR(100) NOT NULL,
  lastName VARCHAR(100) NOT NULL,
  middleName VARCHAR(100),
  employeeId VARCHAR(100) UNIQUE NOT NULL,
  counterNo VARCHAR(50),
  shift VARCHAR(50),
  phone VARCHAR(20),
  email VARCHAR(191),
  address TEXT,
  dob DATE,
  gender VARCHAR(20),
  status VARCHAR(50) DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS receptionists (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  firstName VARCHAR(100) NOT NULL,
  lastName VARCHAR(100) NOT NULL,
  middleName VARCHAR(100),
  employeeId VARCHAR(100) UNIQUE NOT NULL,
  deskNo VARCHAR(50),
  shift VARCHAR(50),
  phone VARCHAR(20),
  email VARCHAR(191),
  address TEXT,
  dob DATE,
  gender VARCHAR(20),
  status VARCHAR(50) DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);
-- 003: Token-based password reset (replaces the client-only ForgotPassword stub).
-- `token` stores the SHA-256 hex of the emailed token (never the raw token).

CREATE TABLE IF NOT EXISTS password_resets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  userId INT UNSIGNED NOT NULL,
  token VARCHAR(64) NOT NULL,
  expiresAt DATETIME NOT NULL,
  usedAt DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX password_resets_token_idx (token),
  CONSTRAINT password_resets_user_fk FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
);
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
  remarks TEXT,
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
  remarks TEXT,
  radiologist VARCHAR(150),
  status VARCHAR(50) DEFAULT 'Released',
  imagePath VARCHAR(255) NULL,
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
