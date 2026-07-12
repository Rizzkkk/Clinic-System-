<?php
// Medical Records module API.
//   GET  ?api=get_records -> JSON array (joined to patient)
//   GET  ?api=get_stats   -> { total, recentMonth }
//   POST action=add_record    -> { success, message }
//   POST action=delete_record (id) -> { success }
//
// Auth (session + login guard) and $conn come from bootstrap.

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('medical_records');

// ----- Writes -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_record') {
        $patientId       = (int) ($_POST['patientId'] ?? 0);
        $recordType      = $_POST['recordType']      ?? '';
        $recordDate      = $_POST['recordDate']      ?? '';
        $description     = $_POST['description']     ?? '';
        $findings        = $_POST['findings']        ?? '';
        $recommendations = $_POST['recommendations'] ?? '';
        $physician       = $_POST['physician']       ?? '';
        $department      = $_POST['department']      ?? '';
        $chiefComplaint  = $_POST['chiefComplaint']  ?? '';
        $diagnosis       = $_POST['diagnosis']       ?? '';
        $clinicalNotes   = $_POST['clinicalNotes']   ?? '';
        $prescription    = $_POST['prescription']    ?? '';
        $bloodPressure   = $_POST['bloodPressure']   ?? '';
        $heartRate       = $_POST['heartRate']       ?? '';
        $temperature     = $_POST['temperature']     ?? '';
        $weight          = $_POST['weight']          ?? '';
        $allergies       = $_POST['allergies']       ?? '';
        $attachments     = $_POST['attachments']     ?? '';

        $stmt = $conn->prepare('
            INSERT INTO medical_records (patientId, recordType, recordDate, description, findings, recommendations, physician, department, chiefComplaint, diagnosis, clinicalNotes, prescription, bloodPressure, heartRate, temperature, weight, allergies, attachments)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param(
            'isssssssssssssssss',
            $patientId, $recordType, $recordDate, $description, $findings, $recommendations,
            $physician, $department, $chiefComplaint, $diagnosis, $clinicalNotes, $prescription,
            $bloodPressure, $heartRate, $temperature, $weight, $allergies, $attachments
        );

        if ($stmt->execute()) {
            json_ok(['message' => 'Medical record added successfully']);
        }
        error_log('add_record failed: ' . $stmt->error);
        json_fail('Could not add the medical record.');
    }

    if ($action === 'update_record') {
        $id              = (int) ($_POST['id'] ?? 0);
        $recordType      = $_POST['recordType']      ?? '';
        $recordDate      = $_POST['recordDate']      ?? '';
        $description     = $_POST['description']     ?? '';
        $findings        = $_POST['findings']        ?? '';
        $recommendations = $_POST['recommendations'] ?? '';
        $physician       = $_POST['physician']       ?? '';
        $department      = $_POST['department']      ?? '';
        $chiefComplaint  = $_POST['chiefComplaint']  ?? '';
        $diagnosis       = $_POST['diagnosis']       ?? '';
        $clinicalNotes   = $_POST['clinicalNotes']   ?? '';
        $prescription    = $_POST['prescription']    ?? '';
        $bloodPressure   = $_POST['bloodPressure']   ?? '';
        $heartRate       = $_POST['heartRate']       ?? '';
        $temperature     = $_POST['temperature']     ?? '';
        $weight          = $_POST['weight']          ?? '';
        $allergies       = $_POST['allergies']       ?? '';
        $attachments     = $_POST['attachments']     ?? '';
        if (!$id || $recordDate === '') {
            json_fail('The record and its date are required.');
        }
        $stmt = $conn->prepare('UPDATE medical_records SET recordType=?, recordDate=?, description=?, findings=?, recommendations=?, physician=?, department=?, chiefComplaint=?, diagnosis=?, clinicalNotes=?, prescription=?, bloodPressure=?, heartRate=?, temperature=?, weight=?, allergies=?, attachments=? WHERE id=?');
        $stmt->bind_param(
            'sssssssssssssssssi',
            $recordType, $recordDate, $description, $findings, $recommendations,
            $physician, $department, $chiefComplaint, $diagnosis, $clinicalNotes, $prescription,
            $bloodPressure, $heartRate, $temperature, $weight, $allergies, $attachments, $id
        );
        if ($stmt->execute()) {
            json_ok(['message' => 'Medical record updated successfully']);
        }
        error_log('update_record failed: ' . $stmt->error);
        json_fail('Could not update the medical record.');
    }

    if ($action === 'delete_record') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM medical_records WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('delete_record failed: ' . $stmt->error);
        json_fail('Could not delete the medical record.');
    }

    json_fail('Unknown action.');
}

// ----- Reads --------------------------------------------------------------
$api = $_GET['api'] ?? '';

if ($api === 'get_records') {
    $result = $conn->query('
        SELECT mr.id, mr.patientId, mr.recordType, mr.recordDate, mr.description, mr.findings, mr.recommendations,
               p.firstName AS patientFirstName, p.lastName AS patientLastName
        FROM medical_records mr
        JOIN patients p ON mr.patientId = p.id
        ORDER BY mr.recordDate DESC
    ');
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    json_response($records);
}

if ($api === 'get_stats') {
    $total       = (int) $conn->query('SELECT COUNT(*) AS c FROM medical_records')->fetch_assoc()['c'];
    $recentMonth = (int) $conn->query('SELECT COUNT(*) AS c FROM medical_records WHERE recordDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetch_assoc()['c'];
    json_response(['total' => $total, 'recentMonth' => $recentMonth]);
}

// Printable medical report for one record. Read op, so RBAC (above) allows lab/doctor/admin.
if ($api === 'record_pdf') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $conn->prepare('
        SELECT mr.id, mr.recordType, mr.recordDate, mr.physician, mr.department, mr.chiefComplaint,
               mr.diagnosis, mr.clinicalNotes, mr.findings, mr.recommendations,
               p.firstName AS pFirst, p.lastName AS pLast, p.dateOfBirth AS pDob, p.gender AS pGender
        FROM medical_records mr
        JOIN patients p ON mr.patientId = p.id
        WHERE mr.id = ? LIMIT 1
    ');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        json_fail('Medical record not found.', 404);
    }

    // Medical records store the physician as free text; try to resolve it to a doctor to embed
    // their signature image.
    $sig = null;
    $physician = (string) ($row['physician'] ?? '');
    if ($physician !== '') {
        $ds = $conn->prepare("SELECT signaturePath FROM doctors WHERE CONCAT('Dr. ', firstName, ' ', lastName) = ? OR CONCAT(firstName, ' ', lastName) = ? LIMIT 1");
        $ds->bind_param('ss', $physician, $physician);
        $ds->execute();
        $d = $ds->get_result()->fetch_assoc();
        if ($d && !empty($d['signaturePath'])) {
            $sig = __DIR__ . '/../../storage/signatures/' . basename($d['signaturePath']);
        }
    }

    require_once __DIR__ . '/../lib/pdf.php';
    require_once __DIR__ . '/../lib/report.php';
    $pdf = new SimplePdf();
    $L = 56; $R = 539;
    $y = report_header($pdf, 'Medical Report', $L, $R);
    report_field($pdf, $L, $y, 'Report No.', 'MR-' . str_pad((string) $row['id'], 5, '0', STR_PAD_LEFT));
    report_field($pdf, $L, $y, 'Patient', trim($row['pFirst'] . ' ' . $row['pLast']));
    report_field($pdf, $L, $y, 'Date of Birth', $row['pDob']);
    report_field($pdf, $L, $y, 'Gender', $row['pGender']);
    report_field($pdf, $L, $y, 'Date', $row['recordDate']);
    report_field($pdf, $L, $y, 'Physician', $physician);
    report_field($pdf, $L, $y, 'Department', $row['department']);
    report_section($pdf, $L, $R, $y, 'Assessment');
    report_field($pdf, $L, $y, 'Chief Complaint', $row['chiefComplaint']);
    report_field($pdf, $L, $y, 'Diagnosis', $row['diagnosis']);
    $y += 4;
    $pdf->text($L, $y, 'Findings', 10, true); $y += 14;
    $y = $pdf->textBlock($L, $y, $row['findings'] ?? '', 10, 95, 14); $y += 6;
    $pdf->text($L, $y, 'Clinical Notes', 10, true); $y += 14;
    $y = $pdf->textBlock($L, $y, $row['clinicalNotes'] ?? '', 10, 95, 14); $y += 6;
    $pdf->text($L, $y, 'Recommendations', 10, true); $y += 14;
    $y = $pdf->textBlock($L, $y, $row['recommendations'] ?? '', 10, 95, 14);
    report_signature($pdf, $L, 720, preg_replace('/^Dr\.\s*/i', '', $physician), $sig);
    report_footer($pdf, $L, $R);

    $out = $pdf->output();
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="medical-record-' . $id . '.pdf"');
    header('Content-Length: ' . strlen($out));
    echo $out;
    exit;
}

json_fail('Unknown endpoint.', 404);
