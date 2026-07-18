<?php
// Psychiatry Sessions module API (RBAC-gated; see backend/auth/rbac.php).
//   GET  ?api=get_records -> JSON array (joined to patient)
//   GET  ?api=get_stats   -> { total, recentMonth }
//   POST action=add_record    -> { success, message }
//   POST action=delete_record (id) -> { success }

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('psych_sessions');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_record') {
        $patientId = (int) ($_POST['patientId'] ?? 0);
        $sessionDate = ($_POST['sessionDate'] ?? '') !== '' ? $_POST['sessionDate'] : null;
        $sessionType = $_POST['sessionType'] ?? '';
        $clinician = $_POST['clinician'] ?? '';
        $chiefComplaint = $_POST['chiefComplaint'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $followUpDate = ($_POST['followUpDate'] ?? '') !== '' ? $_POST['followUpDate'] : null;
        $status = $_POST['status'] ?? '';
        if (!$patientId || $sessionDate === null) {
            json_fail('A patient and the Session Date are required.');
        }

        $remarks = $_POST['remarks'] ?? '';
        $stmt = $conn->prepare('INSERT INTO psych_sessions (patientId, sessionDate, sessionType, clinician, chiefComplaint, notes, remarks, followUpDate, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssssss', $patientId, $sessionDate, $sessionType, $clinician, $chiefComplaint, $notes, $remarks, $followUpDate, $status);

        if ($stmt->execute()) {
            json_ok(['message' => 'Session saved successfully']);
        }
        error_log('psych_sessions add_record failed: ' . $stmt->error);
        json_fail('Could not save the record.');
    }

    if ($action === 'update_record') {
        $id = (int) ($_POST['id'] ?? 0);
        $sessionDate = ($_POST['sessionDate'] ?? '') !== '' ? $_POST['sessionDate'] : null;
        $sessionType = $_POST['sessionType'] ?? '';
        $clinician = $_POST['clinician'] ?? '';
        $chiefComplaint = $_POST['chiefComplaint'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $followUpDate = ($_POST['followUpDate'] ?? '') !== '' ? $_POST['followUpDate'] : null;
        $status = $_POST['status'] ?? '';
        if (!$id || $sessionDate === null) {
            json_fail('The session and its Session Date are required.');
        }
        $remarks = $_POST['remarks'] ?? '';
        $stmt = $conn->prepare('UPDATE psych_sessions SET sessionDate=?, sessionType=?, clinician=?, chiefComplaint=?, notes=?, remarks=?, followUpDate=?, status=? WHERE id=?');
        $stmt->bind_param('ssssssssi', $sessionDate, $sessionType, $clinician, $chiefComplaint, $notes, $remarks, $followUpDate, $status, $id);
        if ($stmt->execute()) {
            json_ok(['message' => 'Session updated successfully']);
        }
        error_log('psych_sessions update_record failed: ' . $stmt->error);
        json_fail('Could not update the record.');
    }

    if ($action === 'delete_record') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM psych_sessions WHERE id = ?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            json_ok();
        }
        error_log('psych_sessions delete_record failed: ' . $stmt->error);
        json_fail('Could not delete the record.');
    }

    json_fail('Unknown action.');
}

$api = $_GET['api'] ?? '';

if ($api === 'get_records') {
    $result = $conn->query('
        SELECT r.*, p.firstName AS patientFirstName, p.lastName AS patientLastName
        FROM psych_sessions r
        JOIN patients p ON r.patientId = p.id
        ORDER BY r.sessionDate DESC, r.id DESC
    ');
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    json_response($rows);
}

if ($api === 'get_stats') {
    $total  = (int) $conn->query('SELECT COUNT(*) AS c FROM psych_sessions')->fetch_assoc()['c'];
    $recent = (int) $conn->query('SELECT COUNT(*) AS c FROM psych_sessions WHERE sessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetch_assoc()['c'];
    json_response(['total' => $total, 'recentMonth' => $recent]);
}

json_fail('Unknown endpoint.', 404);