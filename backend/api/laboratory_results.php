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

// A lab order holds many tests. Build the item list from the parallel POST arrays
// (testType[], results[], referenceRange[], abnormalFlag[]); keep only rows with a test type.
function lab_collect_items(): array {
    $types  = (array) ($_POST['testType'] ?? []);
    $vals   = (array) ($_POST['results'] ?? []);
    $ranges = (array) ($_POST['referenceRange'] ?? []);
    $flags  = (array) ($_POST['abnormalFlag'] ?? []);
    $items = [];
    foreach ($types as $i => $type) {
        $type = trim((string) $type);
        if ($type === '') { continue; }
        $items[] = [
            'testType'       => $type,
            'results'        => (string) ($vals[$i] ?? ''),
            'referenceRange' => (string) ($ranges[$i] ?? ''),
            'abnormalFlag'   => (($flags[$i] ?? '') === 'Y') ? 'Y' : 'N',
        ];
    }
    return $items;
}

// ----- Writes -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_result') {
        $patientId = (int) ($_POST['patientId'] ?? 0);
        $testDate  = $_POST['testDate'] ?? '';
        $remarks   = $_POST['remarks']  ?? '';
        // orderedBy is a nullable FK to doctors(id): bind NULL (not 0) when no doctor is chosen.
        $orderedBy = ($_POST['orderedBy'] ?? '') !== '' ? (int) $_POST['orderedBy'] : null;
        $items     = lab_collect_items();

        // testDate is required (DATE NOT NULL); an order needs at least one test.
        if (!$patientId || $testDate === '') {
            json_fail('A patient and the Test Date are required.');
        }
        if (!$items) {
            json_fail('Add at least one test.');
        }

        $conn->begin_transaction();
        $order = $conn->prepare('INSERT INTO laboratory_results (patientId, testDate, remarks, orderedBy) VALUES (?, ?, ?, ?)');
        $order->bind_param('issi', $patientId, $testDate, $remarks, $orderedBy);
        $ok = $order->execute();
        if ($ok) {
            $orderId = $conn->insert_id;
            $item = $conn->prepare('INSERT INTO laboratory_result_items (resultId, testType, results, referenceRange, abnormalFlag) VALUES (?, ?, ?, ?, ?)');
            foreach ($items as $it) {
                $item->bind_param('issss', $orderId, $it['testType'], $it['results'], $it['referenceRange'], $it['abnormalFlag']);
                if (!$item->execute()) { $ok = false; break; }
            }
        }
        if ($ok) {
            $conn->commit();
            json_ok(['message' => 'Laboratory result added successfully']);
        }
        $conn->rollback();
        error_log('add_result failed: ' . $conn->error);
        json_fail('Could not add the laboratory result.');
    }

    if ($action === 'update_result') {
        $id        = (int) ($_POST['id'] ?? 0);
        $testDate  = $_POST['testDate'] ?? '';
        $remarks   = $_POST['remarks']  ?? '';
        $orderedBy = ($_POST['orderedBy'] ?? '') !== '' ? (int) $_POST['orderedBy'] : null;
        $items     = lab_collect_items();
        if (!$id || $testDate === '') {
            json_fail('The result record and its Test Date are required.');
        }
        if (!$items) {
            json_fail('Add at least one test.');
        }

        // Update the order's shared fields, then replace its tests (delete + re-insert).
        $conn->begin_transaction();
        $order = $conn->prepare('UPDATE laboratory_results SET testDate=?, remarks=?, orderedBy=? WHERE id=?');
        $order->bind_param('ssii', $testDate, $remarks, $orderedBy, $id);
        $ok = $order->execute();
        if ($ok) {
            $del = $conn->prepare('DELETE FROM laboratory_result_items WHERE resultId=?');
            $del->bind_param('i', $id);
            $ok = $del->execute();
        }
        if ($ok) {
            $item = $conn->prepare('INSERT INTO laboratory_result_items (resultId, testType, results, referenceRange, abnormalFlag) VALUES (?, ?, ?, ?, ?)');
            foreach ($items as $it) {
                $item->bind_param('issss', $id, $it['testType'], $it['results'], $it['referenceRange'], $it['abnormalFlag']);
                if (!$item->execute()) { $ok = false; break; }
            }
        }
        if ($ok) {
            $conn->commit();
            json_ok(['message' => 'Laboratory result updated successfully']);
        }
        $conn->rollback();
        error_log('update_result failed: ' . $conn->error);
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
        SELECT lr.id, lr.patientId, lr.testDate, lr.remarks, lr.orderedBy,
               p.firstName AS patientFirstName, p.lastName AS patientLastName,
               d.firstName AS doctorFirstName, d.lastName AS doctorLastName
        FROM laboratory_results lr
        JOIN patients p ON lr.patientId = p.id
        LEFT JOIN doctors d ON lr.orderedBy = d.id
        ORDER BY lr.testDate DESC, lr.id DESC
    ');
    $orders = [];
    $index  = [];
    while ($row = $result->fetch_assoc()) {
        $row['items'] = [];
        $orders[] = $row;
        $index[(int) $row['id']] = count($orders) - 1;
    }
    if ($orders) {
        $itemsRes = $conn->query('SELECT resultId, testType, results, referenceRange, abnormalFlag FROM laboratory_result_items ORDER BY id');
        while ($it = $itemsRes->fetch_assoc()) {
            $rid = (int) $it['resultId'];
            if (isset($index[$rid])) { $orders[$index[$rid]]['items'][] = $it; }
        }
    }
    json_response($orders);
}

if ($api === 'get_stats') {
    // total / abnormal count individual tests; recentMonth counts orders in the last 30 days.
    $total       = (int) $conn->query('SELECT COUNT(*) AS c FROM laboratory_result_items')->fetch_assoc()['c'];
    $abnormal    = (int) $conn->query("SELECT COUNT(*) AS c FROM laboratory_result_items WHERE abnormalFlag = 'Y'")->fetch_assoc()['c'];
    $recentMonth = (int) $conn->query('SELECT COUNT(*) AS c FROM laboratory_results WHERE testDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetch_assoc()['c'];
    json_response(['total' => $total, 'abnormal' => $abnormal, 'recentMonth' => $recentMonth]);
}

// PDF report for a single result. A read operation, so RBAC (above) already limits it to
// lab, doctor, and admin — i.e. both the lab-technician and doctor sides.
if ($api === 'lab_report_pdf') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = $conn->prepare('
        SELECT lr.id, lr.testDate, lr.remarks,
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

    $itemStmt = $conn->prepare('SELECT testType, results, referenceRange, abnormalFlag FROM laboratory_result_items WHERE resultId = ? ORDER BY id');
    $itemStmt->bind_param('i', $id);
    $itemStmt->execute();
    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
    // Keep a value inside its column width (Helvetica is proportional; this is a rough char cap).
    $cell = function (?string $v, int $max): string {
        $v = (string) ($v ?? '');
        return strlen($v) > $max ? substr($v, 0, $max - 2) . '..' : $v;
    };

    $field('Report ID', 'LAB-' . str_pad((string) $row['id'], 5, '0', STR_PAD_LEFT));
    $field('Patient', trim($row['pFirst'] . ' ' . $row['pLast']));
    $field('Date of Birth', $row['pDob']);
    $field('Gender', $row['pGender']);
    $field('Test Date', $row['testDate']);
    $orderedBy = trim(($row['dFirst'] ?? '') . ' ' . ($row['dLast'] ?? ''));
    $field('Ordered By', $orderedBy !== '' ? 'Dr. ' . $orderedBy : 'Not assigned');
    $y += 12;

    // Tests table: one row per test in the order.
    $cType = $L; $cResult = $L + 170; $cRange = $L + 310; $cFlag = $L + 430;
    $pdf->text($L, $y, 'Tests', 11, true); $y += 8;
    $pdf->line($L, $y, $R, $y); $y += 14;
    $pdf->text($cType, $y, 'Test', 9, true);
    $pdf->text($cResult, $y, 'Result', 9, true);
    $pdf->text($cRange, $y, 'Reference', 9, true);
    $pdf->text($cFlag, $y, 'Flag', 9, true);
    $y += 4;
    $pdf->line($L, $y, $R, $y); $y += 14;
    if (!$items) {
        $pdf->text($cType, $y, '(no tests recorded)', 9); $y += 16;
    } else {
        foreach ($items as $it) {
            $flag = strtoupper((string) $it['abnormalFlag']) === 'Y' ? 'ABNORMAL' : 'Normal';
            $pdf->text($cType, $y, $cell($it['testType'], 26) ?: '-', 9);
            $pdf->text($cResult, $y, $cell(($it['results'] === null || $it['results'] === '') ? 'Pending' : $it['results'], 22), 9);
            $pdf->text($cRange, $y, $cell(($it['referenceRange'] === null || $it['referenceRange'] === '') ? '-' : $it['referenceRange'], 18), 9);
            $pdf->text($cFlag, $y, $flag, 9);
            $y += 16;
        }
    }
    $y += 12;
    $pdf->text($L, $y, 'Remarks', 11, true); $y += 6;
    $pdf->line($L, $y, $R, $y); $y += 18;
    $y = $pdf->textBlock($L, $y, $row['remarks'] ?? '', 10, 95, 14);

    report_footer($pdf, $L, $R);

    $out = $pdf->output();
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="lab-result-' . $id . '.pdf"');
    header('Content-Length: ' . strlen($out));
    echo $out;
    exit;
}

json_fail('Unknown endpoint.', 404);
