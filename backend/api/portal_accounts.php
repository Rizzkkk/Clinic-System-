<?php
// Patient portal account approval API (RBAC-gated; see backend/auth/rbac.php).
//   GET  ?api=get_pending                 -> JSON array of unverified signups
//   GET  ?api=get_candidates&userId=      -> JSON array of matching patients rows
//   GET  ?api=search_patients&q=          -> JSON array, free-text patient search fallback
//   GET  ?api=get_linked                  -> JSON array of approved accounts
//   GET  ?api=get_stats                   -> { pending, linked }
//   POST action=approve (userId, patientId) -> { success, message }
//   POST action=reject  (userId)            -> { success, message }
//   POST action=unlink  (userId)            -> { success, message }
//
// Approving links a login to a patients row, which grants permanent access to that patient's
// full medical history. Every write here is therefore guarded by the account's *current* role in
// the WHERE clause, so a forged or replayed request cannot re-point an already-approved account,
// and cannot touch a staff row at all.

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('portal_accounts');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'approve') {
        $userId = (int) ($_POST['userId'] ?? 0);
        $patientId = (int) ($_POST['patientId'] ?? 0);
        if (!$userId || !$patientId) {
            json_fail('Select both a signup and the patient record to link it to.');
        }

        $approvedBy = (int) ($_SESSION['user_id'] ?? 0);

        // The role predicate is the important half of this statement: the only rows it can ever
        // touch are unlinked portal signups. An already-linked account or ANY staff row matches
        // zero rows, so a forged or replayed request cannot re-point an account or mint a
        // patient link on a staff login. 'patient_rejected' is included so a mis-click on reject
        // or unlink is recoverable - without it the account is bricked, since users.email is
        // UNIQUE and the person cannot re-register.
        $stmt = $conn->prepare(
            "UPDATE users
                SET role = 'patient', patientId = ?, linkedAt = NOW(), linkedBy = ?
              WHERE id = ? AND role IN ('patient_pending', 'patient_rejected')"
        );
        $stmt->bind_param('iii', $patientId, $approvedBy, $userId);

        if ($stmt->execute()) {
            if ($stmt->affected_rows === 0) {
                json_fail('That account is already linked, or is not a portal signup. Refresh and try again.');
            }
            json_ok(['message' => 'Portal account linked to the patient record.']);
        }

        // UNIQUE(users.patientId) stops a second account being linked to the same patient.
        if ($conn->errno === 1062) {
            json_fail('That patient is already linked to another portal account.');
        }
        error_log('portal_accounts approve failed: ' . $stmt->error);
        json_fail('Could not link the account.');
    }

    if ($action === 'reject') {
        $userId = (int) ($_POST['userId'] ?? 0);
        if (!$userId) {
            json_fail('Select a signup to reject.');
        }
        $stmt = $conn->prepare("UPDATE users SET role = 'patient_rejected' WHERE id = ? AND role = 'patient_pending'");
        $stmt->bind_param('i', $userId);
        if ($stmt->execute()) {
            if ($stmt->affected_rows === 0) {
                json_fail('That signup is no longer awaiting review. Refresh and try again.');
            }
            json_ok(['message' => 'Signup rejected.']);
        }
        error_log('portal_accounts reject failed: ' . $stmt->error);
        json_fail('Could not reject the signup.');
    }

    if ($action === 'unlink') {
        // The "we linked the wrong person" lever. Clearing patientId revokes access on that
        // account's very next request, because require_patient() reads the link from the database
        // rather than from their session.
        $userId = (int) ($_POST['userId'] ?? 0);
        if (!$userId) {
            json_fail('Select an account to unlink.');
        }
        $stmt = $conn->prepare("UPDATE users SET role = 'patient_rejected', patientId = NULL WHERE id = ? AND role = 'patient'");
        $stmt->bind_param('i', $userId);
        if ($stmt->execute()) {
            if ($stmt->affected_rows === 0) {
                json_fail('That account is not currently linked. Refresh and try again.');
            }
            json_ok(['message' => 'Account unlinked. The patient can no longer see any records.']);
        }
        error_log('portal_accounts unlink failed: ' . $stmt->error);
        json_fail('Could not unlink the account.');
    }

    json_fail('Unknown action.');
}

$api = $_GET['api'] ?? '';

if ($api === 'get_pending') {
    $result = $conn->query(
        "SELECT id, full_name, email, claimedDob, claimedPhone, created_at, role
           FROM users
          WHERE role IN ('patient_pending', 'patient_rejected')
          ORDER BY FIELD(role, 'patient_pending', 'patient_rejected'), created_at ASC, id ASC"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    json_response($rows);
}

if ($api === 'get_candidates') {
    $userId = (int) ($_GET['userId'] ?? 0);
    if (!$userId) {
        json_fail('Select a signup first.');
    }

    $claim = $conn->prepare("SELECT full_name, email, claimedDob, claimedPhone FROM users WHERE id = ? AND role IN ('patient_pending', 'patient_rejected') LIMIT 1");
    $claim->bind_param('i', $userId);
    $claim->execute();
    $applicant = $claim->get_result()->fetch_assoc();
    $claim->close();

    if (!$applicant) {
        json_fail('That signup is no longer a portal signup awaiting review.', 404);
    }

    $dob = $applicant['claimedDob'];
    $phone = (string) ($applicant['claimedPhone'] ?? '');
    $email = (string) ($applicant['email'] ?? '');

    // Candidates are suggestions for a human to check, never an automatic match. alreadyLinked
    // lets the UI disable a record that another account already owns, before the UNIQUE fires.
    $stmt = $conn->prepare(
        "SELECT p.id, p.firstName, p.lastName, p.middleName, p.dateOfBirth, p.gender,
                p.phone, p.email, p.status,
                EXISTS(SELECT 1 FROM users u2 WHERE u2.patientId = p.id) AS alreadyLinked
           FROM patients p
          WHERE (? IS NOT NULL AND p.dateOfBirth = ?)
             OR (? <> '' AND p.phone = ?)
             OR (? <> '' AND p.email = ?)
          ORDER BY p.lastName, p.firstName
          LIMIT 25"
    );
    $stmt->bind_param('ssssss', $dob, $dob, $phone, $phone, $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    json_response($rows);
}

if ($api === 'search_patients') {
    // Fallback for when nothing auto-matches (a typo in the claim, or a record filed under a
    // maiden name). Reception still has to verify identity before linking.
    $term = trim($_GET['q'] ?? '');
    if ($term === '') {
        json_response([]);
    }
    $like = '%' . $term . '%';
    $stmt = $conn->prepare(
        "SELECT p.id, p.firstName, p.lastName, p.middleName, p.dateOfBirth, p.gender,
                p.phone, p.email, p.status,
                EXISTS(SELECT 1 FROM users u2 WHERE u2.patientId = p.id) AS alreadyLinked
           FROM patients p
          WHERE p.firstName LIKE ? OR p.lastName LIKE ? OR p.phone LIKE ? OR p.email LIKE ?
          ORDER BY p.lastName, p.firstName
          LIMIT 25"
    );
    $stmt->bind_param('ssss', $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    json_response($rows);
}

if ($api === 'get_linked') {
    $result = $conn->query(
        "SELECT u.id, u.full_name, u.email, u.linkedAt,
                p.id AS patientId, p.firstName, p.lastName, p.dateOfBirth,
                a.full_name AS linkedByName
           FROM users u
      LEFT JOIN patients p ON u.patientId = p.id
      LEFT JOIN users a ON u.linkedBy = a.id
          WHERE u.role = 'patient'
          ORDER BY u.linkedAt DESC, u.id DESC"
    );
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    json_response($rows);
}

if ($api === 'get_stats') {
    $pending = (int) $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'patient_pending'")->fetch_assoc()['c'];
    $linked  = (int) $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'patient'")->fetch_assoc()['c'];
    json_response(['pending' => $pending, 'linked' => $linked]);
}

json_fail('Unknown endpoint.', 404);
