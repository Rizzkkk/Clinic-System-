<?php
// Add patient_contacts table to existing ASCLEPIUS database

require_once __DIR__ . '/../db.php';

$table_sql = "CREATE TABLE IF NOT EXISTS patient_contacts (
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
  FOREIGN KEY (patientId) REFERENCES patients(id) ON DELETE CASCADE
)";

$index_sql_1 = "CREATE INDEX idx_patientId ON patient_contacts(patientId)";
$index_sql_2 = "CREATE INDEX idx_isPrimary ON patient_contacts(isPrimary)";

$success = false;
$message = "";

if ($conn->query($table_sql) === TRUE) {
    $success = true;
    $message = "patient_contacts table created successfully!";
    
    $conn->query($index_sql_1);
    $conn->query($index_sql_2);
} else {
    if (strpos($conn->error, 'already exists') !== false) {
        $success = true;
        $message = "patient_contacts table already exists!";
    } else {
        $message = "Error creating table: " . $conn->error;
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Patient Contacts Table</title>
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
            <p class="text-gray-500 mt-2">Patient Contacts Table</p>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-green-900 mb-2">✅ Success</h2>
                <p class="text-green-800"><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php else: ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-red-900 mb-2">❌ Error</h2>
                <p class="text-red-800"><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-blue-50 rounded-lg p-4 mb-6 text-sm">
            <h3 class="font-semibold text-blue-900 mb-2">📋 Table Details:</h3>
            <ul class="text-blue-800 space-y-1">
                <li>✓ Stores emergency contacts for patients</li>
                <li>✓ Tracks relationship to patient</li>
                <li>✓ Supports multiple contacts per patient</li>
                <li>✓ Mark primary contact</li>
            </ul>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm text-gray-700">
            <p><strong>Columns:</strong></p>
            <ul class="list-disc list-inside mt-2 space-y-1 text-xs">
                <li>contactName - Emergency contact name</li>
                <li>relationship - Relation to patient (Father, Mother, Spouse, etc.)</li>
                <li>phoneNumber - Contact phone number</li>
                <li>email - Contact email address</li>
                <li>address - Contact address</li>
                <li>isPrimary - Mark as primary contact</li>
                <li>notes - Additional notes</li>
            </ul>
        </div>

        <a href="Patient.php" class="w-full bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white font-bold py-3 px-4 rounded-lg transition-all duration-200 text-center block">
            Go to Patient Page
        </a>

        <p class="text-center text-gray-500 text-xs mt-4">
            You can delete <code>add_table.php</code> after use
        </p>
    </div>
</body>
</html>
