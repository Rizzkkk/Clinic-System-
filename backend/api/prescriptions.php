<?php
// Prescriptions module API.
//   GET  ?api=get_prescriptions -> JSON array (joined to patient + doctor)
//   GET  ?api=get_stats         -> { total, active, expired }
//   POST action=add_prescription    -> { success, message }
//   POST action=delete_prescription (id) -> { success }
//
// Auth (session + login guard) and $conn come from bootstrap.

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('prescriptions');

// Writes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_prescription') {
        $patientId        = (int) ($_POST['patientId'] ?? 0);
        $doctorId         = (int) ($_POST['doctorId'] ?? 0);
        $medicationName   = $_POST['medicationName']   ?? '';
        $dosage           = $_POST['dosage']           ?? '';
        $frequency        = $_POST['frequency']        ?? '';
        $duration         = $_POST['duration']         ?? '';
        $prescriptionDate = $_POST['prescriptionDate'] ?? '';
        $expiryDate       = ($_POST['expiryDate'] ?? '') !== '' ? $_POST['expiryDate'] : null;
        $notes            = $_POST['notes']            ?? '';

        // patient, doctor, and prescriptionDate are required (NOT NULL columns / FKs).
        if (!$patientId || !$doctorId || $prescriptionDate === '') {
            json_fail('A patient, a doctor, and the prescription date are required.');
        }

        $stmt = $conn->prepare('
            INSERT INTO prescriptions (patientId, doctorId, medicationName, dosage, frequency, duration, prescriptionDate, expiryDate, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('iisssssss', $patientId, $doctorId, $medicationName, $dosage, $frequency, $duration, $prescriptionDate, $expiryDate, $notes);

        if ($stmt->execute()) {
            json_ok(['message' => 'Prescription added successfully']);
        }
        error_log('add_prescription failed: ' . $stmt->error);
        json_fail('Could not add the prescription.');
    }

    if ($action === 'update_prescription') {
        $id               = (int) ($_POST['id'] ?? 0);
        $doctorId         = (int) ($_POST['doctorId'] ?? 0);
        $medicationName   = $_POST['medicationName']   ?? '';
        $dosage           = $_POST['dosage']           ?? '';
        $frequency        = $_POST['frequency']        ?? '';
        $duration         = $_POST['duration']         ?? '';
        $prescriptionDate = $_POST['prescriptionDate'] ?? '';
        $expiryDate       = ($_POST['expiryDate'] ?? '') !== '' ? $_POST['expiryDate'] : null;
        $notes            = $_POST['notes']            ?? '';
        if (!$id || !$doctorId || $prescriptionDate === '') {
            json_fail('A doctor and the prescription date are required.');
        }
        $stmt = $conn->prepare('UPDATE prescriptions SET doctorId=?, medicationName=?, dosage=?, frequency=?, duration=?, prescriptionDate=?, expiryDate=?, notes=? WHERE id=?');
        $stmt->bind_param('isssssssi', $doctorId, $medicationName, $dosage, $frequency, $duration, $prescriptionDate, $expiryDate, $notes, $id);
        if ($stmt->execute()) {
            json_ok(['message' => 'Prescription updated successfully']);
        }
        error_log('update_prescription failed: ' . $stmt->error);
        json_fail('Could not update the prescription.');
    }

    if ($action === 'delete_prescription') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM prescriptions WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('delete_prescription failed: ' . $stmt->error);
        json_fail('Could not delete the prescription.');
    }

    json_fail('Unknown action.');
}

// Reads
$api = $_GET['api'] ?? '';

if ($api === 'get_prescriptions') {
    $result = $conn->query('
        SELECT p.id, p.medicationName, p.dosage, p.frequency, p.duration, p.prescriptionDate, p.expiryDate, p.status, p.notes,
               pt.firstName AS patientFirstName, pt.lastName AS patientLastName,
               d.firstName AS doctorFirstName, d.lastName AS doctorLastName
        FROM prescriptions p
        JOIN patients pt ON p.patientId = pt.id
        JOIN doctors d ON p.doctorId = d.id
        ORDER BY p.prescriptionDate DESC
    ');
    $prescriptions = [];
    while ($row = $result->fetch_assoc()) {
        $prescriptions[] = $row;
    }
    json_response($prescriptions);
}

if ($api === 'get_stats') {
    $total   = (int) $conn->query('SELECT COUNT(*) AS c FROM prescriptions')->fetch_assoc()['c'];
    $active  = (int) $conn->query("SELECT COUNT(*) AS c FROM prescriptions WHERE status = 'Active' AND expiryDate >= CURDATE()")->fetch_assoc()['c'];
    $expired = (int) $conn->query("SELECT COUNT(*) AS c FROM prescriptions WHERE expiryDate < CURDATE()")->fetch_assoc()['c'];
    json_response(['total' => $total, 'active' => $active, 'expired' => $expired]);
}

// Printable prescription PDF for one record. Read op, so RBAC (above) allows lab/doctor/admin.
if ($api === 'prescription_pdf') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $conn->prepare('
        SELECT p.id, p.medicationName, p.dosage, p.frequency, p.duration, p.prescriptionDate, p.expiryDate, p.notes,
               pt.firstName AS pFirst, pt.lastName AS pLast, pt.dateOfBirth AS pDob, pt.gender AS pGender,
               d.firstName AS dFirst, d.lastName AS dLast, d.signaturePath AS dSig
        FROM prescriptions p
        JOIN patients pt ON p.patientId = pt.id
        JOIN doctors d ON p.doctorId = d.id
        WHERE p.id = ? LIMIT 1
    ');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        json_fail('Prescription not found.', 404);
    }

    require_once __DIR__ . '/../lib/pdf.php';
    require_once __DIR__ . '/../lib/report.php';
    $pdf = new SimplePdf();
    $L = 56; $R = 539;
    $y = report_header($pdf, 'Prescription', $L, $R);
    report_field($pdf, $L, $y, 'Rx No.', 'RX-' . str_pad((string) $row['id'], 5, '0', STR_PAD_LEFT));
    report_field($pdf, $L, $y, 'Patient', trim($row['pFirst'] . ' ' . $row['pLast']));
    report_field($pdf, $L, $y, 'Date of Birth', $row['pDob']);
    report_field($pdf, $L, $y, 'Gender', $row['pGender']);
    report_field($pdf, $L, $y, 'Date', $row['prescriptionDate']);
    report_section($pdf, $L, $R, $y, 'Prescription');
    report_field($pdf, $L, $y, 'Medication', $row['medicationName']);
    report_field($pdf, $L, $y, 'Dosage', $row['dosage']);
    report_field($pdf, $L, $y, 'Frequency', $row['frequency']);
    report_field($pdf, $L, $y, 'Duration', $row['duration']);
    report_field($pdf, $L, $y, 'Valid Until', $row['expiryDate']);
    $y = $pdf->textBlock($L, $y + 6, 'Notes: ' . ($row['notes'] ?: '-'), 10, 95, 14);
    $sig = $row['dSig'] ? __DIR__ . '/../../storage/signatures/' . basename($row['dSig']) : null;
    report_signature($pdf, $L, 720, trim($row['dFirst'] . ' ' . $row['dLast']), $sig);
    report_footer($pdf, $L, $R);

    $out = $pdf->output();
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="prescription-' . $id . '.pdf"');
    header('Content-Length: ' . strlen($out));
    echo $out;
    exit;
}

json_fail('Unknown endpoint.', 404);
