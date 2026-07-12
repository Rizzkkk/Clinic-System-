<?php
// Laboratory Results module API.
//   GET  ?api=get_results      -> JSON array (joined to patient + ordering doctor)
//   GET  ?api=get_stats        -> { total, abnormal, recentMonth }
//   POST action=add_result     -> { success, message }
//   POST action=delete_result (id) -> { success }
//
// Auth (session + login guard) and $conn come from bootstrap.

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('laboratory_results');

// ----- Writes -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_result') {
        $patientId      = (int) ($_POST['patientId'] ?? 0);
        $testType       = $_POST['testType']       ?? '';
        $testDate       = $_POST['testDate']       ?? '';
        $results        = $_POST['results']        ?? '';
        $referenceRange = $_POST['referenceRange'] ?? '';
        $remarks        = $_POST['remarks']        ?? '';
        $abnormalFlag   = $_POST['abnormalFlag']   ?? '';
        // orderedBy is a nullable FK to doctors(id): bind NULL (not 0) when no doctor is chosen.
        $orderedBy      = ($_POST['orderedBy'] ?? '') !== '' ? (int) $_POST['orderedBy'] : null;

        // testDate is required (DATE NOT NULL); reject early with a clear message.
        if (!$patientId || $testDate === '') {
            json_fail('A patient and the Test Date are required.');
        }

        $stmt = $conn->prepare('
            INSERT INTO laboratory_results (patientId, testType, testDate, results, referenceRange, remarks, abnormalFlag, orderedBy)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('issssssi', $patientId, $testType, $testDate, $results, $referenceRange, $remarks, $abnormalFlag, $orderedBy);

        if ($stmt->execute()) {
            json_ok(['message' => 'Laboratory result added successfully']);
        }
        error_log('add_result failed: ' . $stmt->error);
        json_fail('Could not add the laboratory result.');
    }

    if ($action === 'update_result') {
        $id             = (int) ($_POST['id'] ?? 0);
        $testType       = $_POST['testType']       ?? '';
        $testDate       = $_POST['testDate']       ?? '';
        $results        = $_POST['results']        ?? '';
        $referenceRange = $_POST['referenceRange'] ?? '';
        $remarks        = $_POST['remarks']        ?? '';
        $abnormalFlag   = $_POST['abnormalFlag']   ?? '';
        $orderedBy      = ($_POST['orderedBy'] ?? '') !== '' ? (int) $_POST['orderedBy'] : null;
        if (!$id || $testDate === '') {
            json_fail('The result record and its Test Date are required.');
        }
        $stmt = $conn->prepare('UPDATE laboratory_results SET testType=?, testDate=?, results=?, referenceRange=?, remarks=?, abnormalFlag=?, orderedBy=? WHERE id=?');
        $stmt->bind_param('ssssssii', $testType, $testDate, $results, $referenceRange, $remarks, $abnormalFlag, $orderedBy, $id);
        if ($stmt->execute()) {
            json_ok(['message' => 'Laboratory result updated successfully']);
        }
        error_log('update_result failed: ' . $stmt->error);
        json_fail('Could not update the laboratory result.');
    }

    if ($action === 'delete_result') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM laboratory_results WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('delete_result failed: ' . $stmt->error);
        json_fail('Could not delete the laboratory result.');
    }

    json_fail('Unknown action.');
}

// ----- Reads --------------------------------------------------------------
$api = $_GET['api'] ?? '';

if ($api === 'get_results') {
    $result = $conn->query('
        SELECT lr.id, lr.patientId, lr.testType, lr.testDate, lr.results, lr.referenceRange, lr.remarks, lr.abnormalFlag,
               p.firstName AS patientFirstName, p.lastName AS patientLastName,
               d.firstName AS doctorFirstName, d.lastName AS doctorLastName
        FROM laboratory_results lr
        JOIN patients p ON lr.patientId = p.id
        LEFT JOIN doctors d ON lr.orderedBy = d.id
        ORDER BY lr.testDate DESC
    ');
    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    json_response($results);
}

if ($api === 'get_stats') {
    $total       = (int) $conn->query('SELECT COUNT(*) AS c FROM laboratory_results')->fetch_assoc()['c'];
    $abnormal    = (int) $conn->query("SELECT COUNT(*) AS c FROM laboratory_results WHERE abnormalFlag = 'Y'")->fetch_assoc()['c'];
    $recentMonth = (int) $conn->query('SELECT COUNT(*) AS c FROM laboratory_results WHERE testDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetch_assoc()['c'];
    json_response(['total' => $total, 'abnormal' => $abnormal, 'recentMonth' => $recentMonth]);
}

// PDF report for a single result. A read operation, so RBAC (above) already limits it to
// lab, doctor, and admin — i.e. both the lab-technician and doctor sides.
if ($api === 'lab_report_pdf') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $conn->prepare('
        SELECT lr.id, lr.testType, lr.testDate, lr.results, lr.referenceRange, lr.remarks, lr.abnormalFlag,
               p.firstName AS pFirst, p.lastName AS pLast, p.dateOfBirth AS pDob, p.gender AS pGender,
               d.firstName AS dFirst, d.lastName AS dLast
        FROM laboratory_results lr
        JOIN patients p ON lr.patientId = p.id
        LEFT JOIN doctors d ON lr.orderedBy = d.id
        WHERE lr.id = ? LIMIT 1
    ');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        json_fail('Laboratory result not found.', 404);
    }

    require_once __DIR__ . '/../lib/pdf.php';
    require_once __DIR__ . '/../lib/report.php';
    $pdf = new SimplePdf();
    $L = 56;
    $R = 539;
    $y = report_header($pdf, 'Laboratory Result Report', $L, $R);

    $field = function (string $label, ?string $value) use ($pdf, $L, &$y): void {
        $pdf->text($L, $y, $label, 10, true);
        $pdf->text($L + 130, $y, ($value === null || $value === '') ? '-' : $value, 10);
        $y += 18;
    };

    $field('Report ID', 'LAB-' . str_pad((string) $row['id'], 5, '0', STR_PAD_LEFT));
    $field('Patient', trim($row['pFirst'] . ' ' . $row['pLast']));
    $field('Date of Birth', $row['pDob']);
    $field('Gender', $row['pGender']);
    $y += 6;
    $field('Test Type', $row['testType']);
    $field('Test Date', $row['testDate']);
    $field('Reference Range', $row['referenceRange']);
    $field('Remarks', $row['remarks']);
    $field('Result Flag', strtoupper((string) $row['abnormalFlag']) === 'Y' ? 'ABNORMAL' : 'Normal');
    $orderedBy = trim(($row['dFirst'] ?? '') . ' ' . ($row['dLast'] ?? ''));
    $field('Ordered By', $orderedBy !== '' ? 'Dr. ' . $orderedBy : 'Not assigned');
    $y += 12;
    $pdf->text($L, $y, 'Results', 11, true); $y += 6;
    $pdf->line($L, $y, $R, $y); $y += 18;
    $y = $pdf->textBlock($L, $y, $row['results'] ?? '', 10, 95, 14);

    report_footer($pdf, $L, $R);

    $out = $pdf->output();
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="lab-result-' . $id . '.pdf"');
    header('Content-Length: ' . strlen($out));
    echo $out;
    exit;
}

json_fail('Unknown endpoint.', 404);
