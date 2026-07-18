<?php
// Agency Referrals module API (RBAC-gated; see backend/auth/rbac.php).
//   GET  ?api=get_records -> JSON array (joined to patient)
//   GET  ?api=get_stats   -> { total, recentMonth }
//   POST action=add_record    -> { success, message }
//   POST action=delete_record (id) -> { success }

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('agency_referrals');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_record') {
        $patientId = (int) ($_POST['patientId'] ?? 0);
        $referralDate = ($_POST['referralDate'] ?? '') !== '' ? $_POST['referralDate'] : null;
        $agencyName = $_POST['agencyName'] ?? '';
        $referredBy = $_POST['referredBy'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $status = $_POST['status'] ?? '';
        if (!$patientId || $referralDate === null) {
            json_fail('A patient and the Referral Date are required.');
        }

        $stmt = $conn->prepare('INSERT INTO agency_referrals (patientId, referralDate, agencyName, referredBy, reason, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssss', $patientId, $referralDate, $agencyName, $referredBy, $reason, $notes, $status);

        if ($stmt->execute()) {
            json_ok(['message' => 'Referral saved successfully']);
        }
        error_log('agency_referrals add_record failed: ' . $stmt->error);
        json_fail('Could not save the record.');
    }

    if ($action === 'update_record') {
        $id = (int) ($_POST['id'] ?? 0);
        $referralDate = ($_POST['referralDate'] ?? '') !== '' ? $_POST['referralDate'] : null;
        $agencyName = $_POST['agencyName'] ?? '';
        $referredBy = $_POST['referredBy'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $status = $_POST['status'] ?? '';
        if (!$id || $referralDate === null) {
            json_fail('The referral and its Referral Date are required.');
        }
        $stmt = $conn->prepare('UPDATE agency_referrals SET referralDate=?, agencyName=?, referredBy=?, reason=?, notes=?, status=? WHERE id=?');
        $stmt->bind_param('ssssssi', $referralDate, $agencyName, $referredBy, $reason, $notes, $status, $id);
        if ($stmt->execute()) {
            json_ok(['message' => 'Referral updated successfully']);
        }
        error_log('agency_referrals update_record failed: ' . $stmt->error);
        json_fail('Could not update the record.');
    }

    if ($action === 'delete_record') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM agency_referrals WHERE id = ?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            json_ok();
        }
        error_log('agency_referrals delete_record failed: ' . $stmt->error);
        json_fail('Could not delete the record.');
    }

    json_fail('Unknown action.');
}

$api = $_GET['api'] ?? '';

if ($api === 'get_records') {
    $result = $conn->query('
        SELECT r.*, p.firstName AS patientFirstName, p.lastName AS patientLastName
        FROM agency_referrals r
        JOIN patients p ON r.patientId = p.id
        ORDER BY r.referralDate DESC, r.id DESC
    ');
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    json_response($rows);
}

if ($api === 'get_stats') {
    $total  = (int) $conn->query('SELECT COUNT(*) AS c FROM agency_referrals')->fetch_assoc()['c'];
    $recent = (int) $conn->query('SELECT COUNT(*) AS c FROM agency_referrals WHERE referralDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetch_assoc()['c'];
    json_response(['total' => $total, 'recentMonth' => $recent]);
}

json_fail('Unknown endpoint.', 404);