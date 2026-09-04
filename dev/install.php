<?php
// Installation script for ASCLEPIUS system
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'asclepius_db';

$conn = new mysqli($dbHost, $dbUser, $dbPassword);

if ($conn->connect_error) {
    die('Failed to connect to MySQL: ' . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === FALSE) {
    die('Error creating database: ' . $conn->error);
}

$conn->select_db($dbName);

$tables = [
    "CREATE TABLE IF NOT EXISTS users (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      full_name VARCHAR(150) NOT NULL,
      email VARCHAR(191) NOT NULL,
      password_hash VARCHAR(255) NOT NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY unique_users_email (email)
    )",
    
    "CREATE TABLE IF NOT EXISTS doctors (
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
      status VARCHAR(50) DEFAULT 'On Duty',
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    )",
    
    "CREATE TABLE IF NOT EXISTS patients (
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
    )",
    
    "CREATE TABLE IF NOT EXISTS appointments (
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
      FOREIGN KEY (patientId) REFERENCES patients(id),
      FOREIGN KEY (doctorId) REFERENCES doctors(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS medical_records (
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
      FOREIGN KEY (patientId) REFERENCES patients(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS laboratory_results (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      patientId INT UNSIGNED NOT NULL,
      testType VARCHAR(100),
      testDate DATE NOT NULL,
      results TEXT,
      referenceRange VARCHAR(100),
      abnormalFlag VARCHAR(10),
      orderedBy INT UNSIGNED,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      FOREIGN KEY (patientId) REFERENCES patients(id),
      FOREIGN KEY (orderedBy) REFERENCES doctors(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS prescriptions (
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
      FOREIGN KEY (patientId) REFERENCES patients(id),
      FOREIGN KEY (doctorId) REFERENCES doctors(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS billing (
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
      FOREIGN KEY (patientId) REFERENCES patients(id),
      FOREIGN KEY (appointmentId) REFERENCES appointments(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS patient_contacts (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      patientId INT UNSIGNED NOT NULL,
      contactName VARCHAR(150) NOT NULL,
      relationship VARCHAR(100),
      phoneNumber VARCHAR(20),
      email VARCHAR(191),
      address TEXT,
      isPrimary BOOLEAN DEFAULT FALSE,
      notes TEXT,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      FOREIGN KEY (patientId) REFERENCES patients(id)
    )"
];

$created = 0;
$errors = [];

foreach ($tables as $table) {
    if ($conn->query($table) === TRUE) {
        $created++;
    } else {
        $errors[] = $conn->error;
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASCLEPIUS Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #00685d 0%, #008376 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body>
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">ASCLEPIUS</h1>
            <p class="text-gray-500 mt-2">Medical & Diagnostic System</p>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-blue-900 mb-2">✅ Installation Complete</h2>
            <p class="text-blue-800">Database and <strong><?php echo $created; ?></strong> tables created successfully!</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-yellow-900 mb-2">⚠️ Notes:</h3>
                <ul class="text-sm text-yellow-800 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm">
            <h3 class="font-semibold text-gray-900 mb-3">Next Steps:</h3>
            <ol class="text-gray-700 space-y-2 list-decimal list-inside">
                <li>Create your user account</li>
                <li>Login to the system</li>
                <li>Start adding doctors and patients</li>
            </ol>
        </div>

        <a href="../login.php" class="w-full bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white font-bold py-3 px-4 rounded-lg transition-all duration-200 text-center block">
            Go to Login Page
        </a>

        <p class="text-center text-gray-500 text-xs mt-4">
            You can delete <code>install.php</code> after installation
        </p>
    </div>
</body>
</html>
