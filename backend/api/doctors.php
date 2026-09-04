<?php
// Doctors module API.
//   GET  ?api=get_doctors  -> JSON array of doctors
//   GET  ?api=get_stats    -> { total, onDuty, load }
//   POST action=add_doctor -> { success, message }
//   POST action=delete_doctor (id) -> { success }
//
// Auth (session + login guard) and $conn come from bootstrap.

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/validation.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('doctors');

// Validate + store an uploaded doctor signature (JPEG only, since the PDF engine embeds JPEG).
// Responds with json_fail() and exits on any problem. Write access (admin) is already enforced.
function doctor_store_signature(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        json_fail('The signature failed to upload. Please try again.');
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        json_fail('The signature image is too large (max 3 MB).');
    }
    if ((new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) !== 'image/jpeg') {
        json_fail('The signature must be a JPG image.');
    }
    $dir = __DIR__ . '/../../storage/signatures';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $name = bin2hex(random_bytes(16)) . '.jpg';
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        json_fail('Could not save the signature.');
    }
    return $name;
}

// Writes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_doctor') {
        $firstName     = $_POST['firstName']     ?? '';
        $lastName      = $_POST['lastName']      ?? '';
        $middleName    = $_POST['middleName']    ?? '';
        $specialty     = $_POST['specialty']     ?? '';
        $department    = $_POST['department']    ?? '';
        $shift         = $_POST['shift']         ?? '';
        $licenseNumber = $_POST['licenseNumber'] ?? '';
        $employeeId    = $_POST['employeeId']    ?? '';
        $phone         = $_POST['phone']         ?? '';
        $email         = ($_POST['email'] ?? '') !== '' ? $_POST['email'] : null; // UNIQUE col: '' would collide
        $address       = $_POST['address']       ?? '';
        $dob           = ($_POST['dob'] ?? '') !== '' ? $_POST['dob'] : null;
        $gender        = $_POST['gender']        ?? '';
        $education     = $_POST['education']      ?? '';
        $notes         = $_POST['notes']         ?? '';

        if ($firstName === '' || $lastName === '') {
            json_fail('First name and last name are required.');
        }
        if ($nameError = first_invalid_name(['First name' => $firstName, 'Last name' => $lastName, 'Middle name' => $middleName])) {
            json_fail($nameError);
        }
        if ($email !== null && !is_valid_email($email)) {
            json_fail('Please enter a valid email address.');
        }

        $signaturePath = null;
        if (isset($_FILES['signature']) && $_FILES['signature']['error'] !== UPLOAD_ERR_NO_FILE) {
            $signaturePath = doctor_store_signature($_FILES['signature']);
        }

        $stmt = $conn->prepare('
            INSERT INTO doctors (firstName, lastName, middleName, specialty, department, shift, licenseNumber, employeeId, phone, email, address, dob, gender, education, notes, signaturePath)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param(
            'ssssssssssssssss',
            $firstName, $lastName, $middleName, $specialty, $department, $shift,
            $licenseNumber, $employeeId, $phone, $email, $address, $dob, $gender,
            $education, $notes, $signaturePath
        );

        if ($stmt->execute()) {
            json_ok(['message' => 'Doctor registered successfully']);
        }
        error_log('add_doctor failed: ' . $stmt->error);
        json_fail('Could not register the doctor. Please check the details and try again.');
    }

    if ($action === 'update_doctor') {
        $id            = (int) ($_POST['id'] ?? 0);
        $firstName     = $_POST['firstName']     ?? '';
        $lastName      = $_POST['lastName']      ?? '';
        $middleName    = $_POST['middleName']    ?? '';
        $specialty     = $_POST['specialty']     ?? '';
        $department    = $_POST['department']    ?? '';
        $shift         = $_POST['shift']         ?? '';
        $licenseNumber = $_POST['licenseNumber'] ?? '';
        $employeeId    = $_POST['employeeId']    ?? '';
        $phone         = $_POST['phone']         ?? '';
        $email         = ($_POST['email'] ?? '') !== '' ? $_POST['email'] : null;
        $address       = $_POST['address']       ?? '';
        $dob           = ($_POST['dob'] ?? '') !== '' ? $_POST['dob'] : null;
        $gender        = $_POST['gender']        ?? '';
        $education     = $_POST['education']      ?? '';
        $notes         = $_POST['notes']         ?? '';
        if (!$id || $firstName === '' || $lastName === '') {
            json_fail('The doctor record, first name, and last name are required.');
        }
        if ($nameError = first_invalid_name(['First name' => $firstName, 'Last name' => $lastName, 'Middle name' => $middleName])) {
            json_fail($nameError);
        }
        if ($email !== null && !is_valid_email($email)) {
            json_fail('Please enter a valid email address.');
        }

        // Optional signature replacement.
        if (isset($_FILES['signature']) && $_FILES['signature']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newSig = doctor_store_signature($_FILES['signature']);
            $sel = $conn->prepare('SELECT signaturePath FROM doctors WHERE id = ?');
            $sel->bind_param('i', $id);
            $sel->execute();
            $old = $sel->get_result()->fetch_assoc();
            $stmt = $conn->prepare('UPDATE doctors SET firstName=?, lastName=?, middleName=?, specialty=?, department=?, shift=?, licenseNumber=?, employeeId=?, phone=?, email=?, address=?, dob=?, gender=?, education=?, notes=?, signaturePath=? WHERE id=?');
            $stmt->bind_param('ssssssssssssssssi', $firstName, $lastName, $middleName, $specialty, $department, $shift, $licenseNumber, $employeeId, $phone, $email, $address, $dob, $gender, $education, $notes, $newSig, $id);
            $ok = $stmt->execute();
            if ($ok && $old && !empty($old['signaturePath'])) {
                $p = __DIR__ . '/../../storage/signatures/' . basename($old['signaturePath']);
                if (is_file($p)) { @unlink($p); }
            }
        } else {
            $stmt = $conn->prepare('UPDATE doctors SET firstName=?, lastName=?, middleName=?, specialty=?, department=?, shift=?, licenseNumber=?, employeeId=?, phone=?, email=?, address=?, dob=?, gender=?, education=?, notes=? WHERE id=?');
            $stmt->bind_param('sssssssssssssssi', $firstName, $lastName, $middleName, $specialty, $department, $shift, $licenseNumber, $employeeId, $phone, $email, $address, $dob, $gender, $education, $notes, $id);
            $ok = $stmt->execute();
        }
        if ($ok) {
            json_ok(['message' => 'Doctor updated successfully']);
        }
        error_log('update_doctor failed: ' . $stmt->error);
        json_fail('Could not update the doctor.');
    }

    if ($action === 'delete_doctor') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM doctors WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('delete_doctor failed: ' . $stmt->error);
        json_fail('Could not delete the doctor.');
    }

    json_fail('Unknown action.');
}

// Reads
$api = $_GET['api'] ?? '';

if ($api === 'get_doctors') {
    $result = $conn->query('SELECT * FROM doctors ORDER BY created_at DESC');
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    json_response($doctors);
}

if ($api === 'get_stats') {
    $total  = (int) $conn->query('SELECT COUNT(*) AS c FROM doctors')->fetch_assoc()['c'];
    $onDuty = (int) $conn->query("SELECT COUNT(*) AS c FROM doctors WHERE status = 'On Duty'")->fetch_assoc()['c'];
    $load   = $total > 0 ? (int) round(($onDuty / $total) * 100) : 0;
    json_response(['total' => $total, 'onDuty' => $onDuty, 'load' => $load]);
}

json_fail('Unknown endpoint.', 404);
