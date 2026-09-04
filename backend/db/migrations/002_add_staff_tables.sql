-- 002: Staff role directories - lab technicians, cashiers, receptionists.
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
