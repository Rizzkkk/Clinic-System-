<?php
// X-Ray Studies module API (RBAC-gated; see backend/auth/rbac.php).
//   GET  ?api=get_records -> JSON array (joined to patient)
//   GET  ?api=get_stats   -> { total, recentMonth }
//   POST action=add_record    -> { success, message }
//   POST action=delete_record (id) -> { success }

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('xray_studies');

// Validate + store an uploaded X-ray file under storage/xray/ and return its stored filename.
// On any problem it responds with json_fail() and exits. Upload is reachable only for the
// 'write' roles (lab + admin) because the POST already passed require_module_access above.
function xray_store_upload(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        json_fail('The image failed to upload. Please try again.');
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        json_fail('The image is too large (max 10 MB).');
    }
    $allowed = [
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/webp'      => 'webp',
        'application/pdf' => 'pdf',
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        json_fail('Unsupported file type. Upload a JPG, PNG, WEBP, or PDF.');
    }
    $dir = __DIR__ . '/../../storage/xray';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        json_fail('Could not save the uploaded image.');
    }
    return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_record') {
        $patientId = (int) ($_POST['patientId'] ?? 0);
        $studyDate = ($_POST['studyDate'] ?? '') !== '' ? $_POST['studyDate'] : null;
        $bodyPart = $_POST['bodyPart'] ?? '';
        $radiologist = $_POST['radiologist'] ?? '';
        $findings = $_POST['findings'] ?? '';
        $impression = $_POST['impression'] ?? '';
        $remarks = $_POST['remarks'] ?? '';
        $status = $_POST['status'] ?? '';
        if (!$patientId || $studyDate === null) {
            json_fail('A patient and the Study Date are required.');
        }

        // Optional X-ray image/file. move_uploaded_file + RBAC (above) keep this lab/admin only.
        $imagePath = null;
        if (isset($_FILES['xrayImage']) && $_FILES['xrayImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagePath = xray_store_upload($_FILES['xrayImage']);
        }

        $stmt = $conn->prepare('INSERT INTO xray_studies (patientId, studyDate, bodyPart, radiologist, findings, impression, remarks, status, imagePath) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssssss', $patientId, $studyDate, $bodyPart, $radiologist, $findings, $impression, $remarks, $status, $imagePath);

        if ($stmt->execute()) {
            json_ok(['message' => 'Study saved successfully']);
        }
        error_log('xray_studies add_record failed: ' . $stmt->error);
        json_fail('Could not save the record.');
    }

    if ($action === 'update_record') {
        $id = (int) ($_POST['id'] ?? 0);
        $studyDate = ($_POST['studyDate'] ?? '') !== '' ? $_POST['studyDate'] : null;
        $bodyPart = $_POST['bodyPart'] ?? '';
        $radiologist = $_POST['radiologist'] ?? '';
        $findings = $_POST['findings'] ?? '';
        $impression = $_POST['impression'] ?? '';
        $remarks = $_POST['remarks'] ?? '';
        $status = $_POST['status'] ?? '';
        if (!$id || $studyDate === null) {
            json_fail('The study and its Study Date are required.');
        }

        // Optional image replacement: if a new file is uploaded, store it and remove the old one.
        if (isset($_FILES['xrayImage']) && $_FILES['xrayImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newImage = xray_store_upload($_FILES['xrayImage']);
            $sel = $conn->prepare('SELECT imagePath FROM xray_studies WHERE id = ?');
            $sel->bind_param('i', $id);
            $sel->execute();
            $old = $sel->get_result()->fetch_assoc();
            $stmt = $conn->prepare('UPDATE xray_studies SET studyDate=?, bodyPart=?, radiologist=?, findings=?, impression=?, remarks=?, status=?, imagePath=? WHERE id=?');
            $stmt->bind_param('ssssssssi', $studyDate, $bodyPart, $radiologist, $findings, $impression, $remarks, $status, $newImage, $id);
            $ok = $stmt->execute();
            if ($ok && $old && !empty($old['imagePath'])) {
                $p = __DIR__ . '/../../storage/xray/' . basename($old['imagePath']);
                if (is_file($p)) { @unlink($p); }
            }
        } else {
            $stmt = $conn->prepare('UPDATE xray_studies SET studyDate=?, bodyPart=?, radiologist=?, findings=?, impression=?, remarks=?, status=? WHERE id=?');
            $stmt->bind_param('sssssssi', $studyDate, $bodyPart, $radiologist, $findings, $impression, $remarks, $status, $id);
            $ok = $stmt->execute();
        }
        if ($ok) {
            json_ok(['message' => 'Study updated successfully']);
        }
        error_log('xray_studies update_record failed: ' . $stmt->error);
        json_fail('Could not update the record.');
    }

    if ($action === 'delete_record') {
        $id = (int) ($_POST['id'] ?? 0);

        // Grab the stored file first so we can remove it after the row is gone.
        $sel = $conn->prepare('SELECT imagePath FROM xray_studies WHERE id = ?');
        $sel->bind_param('i', $id);
        $sel->execute();
        $found = $sel->get_result()->fetch_assoc();
        $img = $found['imagePath'] ?? null;

        $stmt = $conn->prepare('DELETE FROM xray_studies WHERE id = ?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            if ($img) {
                $path = __DIR__ . '/../../storage/xray/' . basename($img);
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            json_ok();
        }
        error_log('xray_studies delete_record failed: ' . $stmt->error);
        json_fail('Could not delete the record.');
    }

    json_fail('Unknown action.');
}

$api = $_GET['api'] ?? '';

if ($api === 'get_records') {
    $result = $conn->query('
        SELECT r.*, p.firstName AS patientFirstName, p.lastName AS patientLastName
        FROM xray_studies r
        JOIN patients p ON r.patientId = p.id
        ORDER BY r.studyDate DESC, r.id DESC
    ');
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    json_response($rows);
}

if ($api === 'get_stats') {
    $total  = (int) $conn->query('SELECT COUNT(*) AS c FROM xray_studies')->fetch_assoc()['c'];
    $recent = (int) $conn->query('SELECT COUNT(*) AS c FROM xray_studies WHERE studyDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetch_assoc()['c'];
    json_response(['total' => $total, 'recentMonth' => $recent]);
}

json_fail('Unknown endpoint.', 404);