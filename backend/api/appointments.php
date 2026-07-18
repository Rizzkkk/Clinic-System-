<?php
// Appointments module API.
//   GET  ?api=get_appointments -> JSON array (joined to patient + doctor)
//   GET  ?api=get_patients     -> JSON array (for the booking dropdown)
//   GET  ?api=get_doctors      -> JSON array (for the booking dropdown)
//   GET  ?api=get_stats        -> { total, scheduled, completed, cancelled, todayAppointments, pendingCheckins, todayPercentage }
//   POST action=add_appointment    -> { success, message }
//   POST action=update_status (id, status)   -> { success }
//   POST action=delete_appointment (id)      -> { success }
//
// Auth (session + login guard) and $conn come from bootstrap.

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('appointments');

// ----- Writes -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_appointment') {
        $patientId       = (int) ($_POST['patientId'] ?? 0);
        $doctorId        = (int) ($_POST['doctorId'] ?? 0);
        $appointmentDate = $_POST['appointmentDate'] ?? '';
        $appointmentTime = $_POST['appointmentTime'] ?? '';
        $reason          = $_POST['reason'] ?? '';
        $notes           = $_POST['notes'] ?? '';

        if (!$patientId || !$doctorId || $appointmentDate === '' || $appointmentTime === '') {
            json_fail('Please select a patient, doctor, date, and time.');
        }

        $stmt = $conn->prepare('
            INSERT INTO appointments (patientId, doctorId, appointmentDate, appointmentTime, reason, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('iissss', $patientId, $doctorId, $appointmentDate, $appointmentTime, $reason, $notes);

        if ($stmt->execute()) {
            json_ok(['message' => 'Appointment scheduled successfully']);
        }
        error_log('add_appointment failed: ' . $stmt->error);
        json_fail('Could not schedule the appointment.');
    }

    if ($action === 'update_status') {
        $id     = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $stmt = $conn->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('update_status failed: ' . $stmt->error);
        json_fail('Could not update the appointment status.');
    }

    if ($action === 'update_appointment') {
        $id              = (int) ($_POST['id'] ?? 0);
        $doctorId        = (int) ($_POST['doctorId'] ?? 0);
        $appointmentDate = $_POST['appointmentDate'] ?? '';
        $appointmentTime = $_POST['appointmentTime'] ?? '';
        $reason          = $_POST['reason'] ?? '';
        $notes           = $_POST['notes'] ?? '';
        $status          = $_POST['status'] ?? '';
        if (!$id || !$doctorId || $appointmentDate === '' || $appointmentTime === '') {
            json_fail('Please provide the doctor, date, and time.');
        }
        $stmt = $conn->prepare('UPDATE appointments SET doctorId=?, appointmentDate=?, appointmentTime=?, reason=?, notes=?, status=? WHERE id=?');
        $stmt->bind_param('isssssi', $doctorId, $appointmentDate, $appointmentTime, $reason, $notes, $status, $id);
        if ($stmt->execute()) {
            json_ok(['message' => 'Appointment updated successfully']);
        }
        error_log('update_appointment failed: ' . $stmt->error);
        json_fail('Could not update the appointment.');
    }

    if ($action === 'delete_appointment') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('delete_appointment failed: ' . $stmt->error);
        json_fail('Could not delete the appointment.');
    }

    json_fail('Unknown action.');
}

// ----- Reads --------------------------------------------------------------
$api = $_GET['api'] ?? '';

if ($api === 'get_appointments') {
    $result = $conn->query('
        SELECT a.id, a.patientId, a.doctorId, a.appointmentDate, a.appointmentTime, a.reason, a.status, a.notes,
               p.firstName AS patientFirstName, p.lastName AS patientLastName,
               d.firstName AS doctorFirstName, d.lastName AS doctorLastName
        FROM appointments a
        JOIN patients p ON a.patientId = p.id
        JOIN doctors d ON a.doctorId = d.id
        ORDER BY a.appointmentDate DESC
    ');
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    json_response($appointments);
}

if ($api === 'get_patients') {
    $result = $conn->query('SELECT id, firstName, lastName, dateOfBirth, gender, bloodType, phone, email, address, emergencyContact, emergencyPhone, status FROM patients ORDER BY lastName, firstName');
    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
    json_response($patients);
}

if ($api === 'get_doctors') {
    $result = $conn->query('SELECT id, firstName, lastName FROM doctors ORDER BY lastName, firstName');
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    json_response($doctors);
}

if ($api === 'get_stats') {
    $total             = (int) $conn->query('SELECT COUNT(*) AS c FROM appointments')->fetch_assoc()['c'];
    $scheduled         = (int) $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status = 'Scheduled'")->fetch_assoc()['c'];
    $completed         = (int) $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status = 'Completed'")->fetch_assoc()['c'];
    $cancelled         = (int) $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status = 'Cancelled'")->fetch_assoc()['c'];
    $todayAppointments = (int) $conn->query('SELECT COUNT(*) AS c FROM appointments WHERE DATE(appointmentDate) = CURDATE()')->fetch_assoc()['c'];
    $pendingCheckins   = (int) $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status = 'Scheduled' AND DATE(appointmentDate) = CURDATE()")->fetch_assoc()['c'];
    $todayPercentage   = $total > 0 ? (int) round(($todayAppointments / $total) * 100) : 0;

    json_response([
        'total'             => $total,
        'scheduled'         => $scheduled,
        'completed'         => $completed,
        'cancelled'         => $cancelled,
        'todayAppointments' => $todayAppointments,
        'pendingCheckins'   => $pendingCheckins,
        'todayPercentage'   => $todayPercentage,
    ]);
}

json_fail('Unknown endpoint.', 404);
