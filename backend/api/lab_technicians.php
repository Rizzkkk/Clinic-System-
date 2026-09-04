<?php
// Lab Technicians staff directory API (admin-only via RBAC).
//   GET  ?api=get_staff -> JSON array
//   GET  ?api=get_stats -> { total, active }
//   POST action=add_staff   -> { success, message }
//   POST action=delete_staff (id) -> { success }
//
// Auth (session + login guard) and $conn come from bootstrap.

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/validation.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('lab_technicians');

// Writes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_staff') {
        $firstName  = $_POST['firstName']  ?? '';
        $lastName   = $_POST['lastName']   ?? '';
        $middleName = $_POST['middleName'] ?? '';
        $employeeId = $_POST['employeeId'] ?? '';
        $shift      = $_POST['shift']      ?? '';
        $phone      = $_POST['phone']      ?? '';
        $email      = $_POST['email']      ?? '';
        $dob        = ($_POST['dob'] ?? '') !== '' ? $_POST['dob'] : null;
        $section = $_POST['section'] ?? '';
        $licenseNumber = $_POST['licenseNumber'] ?? '';

        if ($firstName === '' || $lastName === '' || $employeeId === '') {
            json_fail('First name, last name, and employee ID are required.');
        }
        if ($nameError = first_invalid_name(['First name' => $firstName, 'Last name' => $lastName, 'Middle name' => $middleName])) {
            json_fail($nameError);
        }
        if ($email !== '' && !is_valid_email($email)) {
            json_fail('Please enter a valid email address.');
        }

        $stmt = $conn->prepare('
            INSERT INTO lab_technicians (firstName, lastName, middleName, employeeId, shift, phone, email, dob, section, licenseNumber)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('ssssssssss', $firstName, $lastName, $middleName, $employeeId, $shift, $phone, $email, $dob, $section, $licenseNumber);

        if ($stmt->execute()) {
            json_ok(['message' => 'Lab Technician registered successfully']);
        }
        error_log('lab_technicians add_staff failed: ' . $stmt->error);
        json_fail('Could not register the staff member. Check the details (employee ID must be unique).');
    }

    if ($action === 'update_staff') {
        $id         = (int) ($_POST['id'] ?? 0);
        $firstName  = $_POST['firstName']  ?? '';
        $lastName   = $_POST['lastName']   ?? '';
        $middleName = $_POST['middleName'] ?? '';
        $employeeId = $_POST['employeeId'] ?? '';
        $shift      = $_POST['shift']      ?? '';
        $phone      = $_POST['phone']      ?? '';
        $email      = $_POST['email']      ?? '';
        $dob        = ($_POST['dob'] ?? '') !== '' ? $_POST['dob'] : null;
        $section    = $_POST['section'] ?? '';
        $licenseNumber = $_POST['licenseNumber'] ?? '';
        if (!$id || $firstName === '' || $lastName === '' || $employeeId === '') {
            json_fail('Record, first name, last name, and employee ID are required.');
        }
        if ($nameError = first_invalid_name(['First name' => $firstName, 'Last name' => $lastName, 'Middle name' => $middleName])) {
            json_fail($nameError);
        }
        if ($email !== '' && !is_valid_email($email)) {
            json_fail('Please enter a valid email address.');
        }
        $stmt = $conn->prepare('UPDATE lab_technicians SET firstName=?, lastName=?, middleName=?, employeeId=?, shift=?, phone=?, email=?, dob=?, section=?, licenseNumber=? WHERE id=?');
        $stmt->bind_param('ssssssssssi', $firstName, $lastName, $middleName, $employeeId, $shift, $phone, $email, $dob, $section, $licenseNumber, $id);
        if ($stmt->execute()) {
            json_ok(['message' => 'Lab Technician updated successfully']);
        }
        error_log('lab_technicians update_staff failed: ' . $stmt->error);
        json_fail('Could not update the staff member.');
    }

    if ($action === 'delete_staff') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM lab_technicians WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('lab_technicians delete_staff failed: ' . $stmt->error);
        json_fail('Could not delete the staff member.');
    }

    json_fail('Unknown action.');
}

// Reads
$api = $_GET['api'] ?? '';

if ($api === 'get_staff') {
    $result = $conn->query('SELECT * FROM lab_technicians ORDER BY created_at DESC');
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    json_response($rows);
}

if ($api === 'get_stats') {
    $total  = (int) $conn->query('SELECT COUNT(*) AS c FROM lab_technicians')->fetch_assoc()['c'];
    $active = (int) $conn->query("SELECT COUNT(*) AS c FROM lab_technicians WHERE status = 'Active'")->fetch_assoc()['c'];
    json_response(['total' => $total, 'active' => $active]);
}

json_fail('Unknown endpoint.', 404);