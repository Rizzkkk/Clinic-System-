<?php
// Patients module API.
//   GET  ?api=get_patients      -> JSON array of patients
//   GET  ?api=get_stats         -> { total, active, inactive }
//   GET  ?api=get_contacts&patientId=ID -> JSON array of that patient's contacts
//   GET  ?api=get_all_contacts  -> JSON array of contacts joined with patient name
//   POST action=add_patient     -> { success, message }
//   POST action=delete_patient (id)   -> { success }
//   POST action=add_contact     -> { success, message }
//   POST action=update_contact (id)   -> { success, message }
//   POST action=delete_contact (id)   -> { success }
//
// Auth (session + login guard) and $conn come from bootstrap.

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../lib/validation.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('patients');

// Writes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_patient') {
        $firstName          = $_POST['firstName']          ?? '';
        $lastName           = $_POST['lastName']           ?? '';
        $middleName         = $_POST['middleName']         ?? '';
        $dateOfBirth        = ($_POST['dateOfBirth'] ?? '') !== '' ? $_POST['dateOfBirth'] : null;
        $gender             = $_POST['gender']             ?? '';
        $bloodType          = $_POST['bloodType']          ?? '';
        $phone              = $_POST['phone']              ?? '';
        $email              = $_POST['email']              ?? '';
        $address            = $_POST['address']            ?? '';
        $emergencyContact   = $_POST['emergencyContact']   ?? '';
        $emergencyPhone     = $_POST['emergencyPhone']     ?? '';
        $medicalHistory     = $_POST['medicalHistory']     ?? '';
        $allergies          = $_POST['allergies']          ?? '';
        $insurance_provider = $_POST['insurance_provider'] ?? '';
        $insurance_number   = $_POST['insurance_number']   ?? '';

        if ($firstName === '' || $lastName === '') {
            json_fail('First name and last name are required.');
        }
        if ($nameError = first_invalid_name(['First name' => $firstName, 'Last name' => $lastName, 'Middle name' => $middleName, 'Emergency contact name' => $emergencyContact])) {
            json_fail($nameError);
        }
        if ($email !== '' && !is_valid_email($email)) {
            json_fail('Please enter a valid email address.');
        }

        $stmt = $conn->prepare('
            INSERT INTO patients (firstName, lastName, middleName, dateOfBirth, gender, bloodType, phone, email, address, emergencyContact, emergencyPhone, medicalHistory, allergies, insurance_provider, insurance_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param(
            'sssssssssssssss',
            $firstName, $lastName, $middleName, $dateOfBirth, $gender, $bloodType,
            $phone, $email, $address, $emergencyContact, $emergencyPhone,
            $medicalHistory, $allergies, $insurance_provider, $insurance_number
        );

        if ($stmt->execute()) {
            json_ok(['message' => 'Patient registered successfully']);
        }
        error_log('add_patient failed: ' . $stmt->error);
        json_fail('Could not register the patient. Please check the details and try again.');
    }

    if ($action === 'update_patient') {
        $id                 = (int) ($_POST['id'] ?? 0);
        $firstName          = $_POST['firstName']          ?? '';
        $lastName           = $_POST['lastName']           ?? '';
        $middleName         = $_POST['middleName']         ?? '';
        $dateOfBirth        = ($_POST['dateOfBirth'] ?? '') !== '' ? $_POST['dateOfBirth'] : null;
        $gender             = $_POST['gender']             ?? '';
        $bloodType          = $_POST['bloodType']          ?? '';
        $phone              = $_POST['phone']              ?? '';
        $email              = $_POST['email']              ?? '';
        $address            = $_POST['address']            ?? '';
        $emergencyContact   = $_POST['emergencyContact']   ?? '';
        $emergencyPhone     = $_POST['emergencyPhone']     ?? '';
        $medicalHistory     = $_POST['medicalHistory']     ?? '';
        $allergies          = $_POST['allergies']          ?? '';
        $insurance_provider = $_POST['insurance_provider'] ?? '';
        $insurance_number   = $_POST['insurance_number']   ?? '';
        if (!$id || $firstName === '' || $lastName === '') {
            json_fail('The patient record, first name, and last name are required.');
        }
        if ($nameError = first_invalid_name(['First name' => $firstName, 'Last name' => $lastName, 'Middle name' => $middleName, 'Emergency contact name' => $emergencyContact])) {
            json_fail($nameError);
        }
        if ($email !== '' && !is_valid_email($email)) {
            json_fail('Please enter a valid email address.');
        }
        $stmt = $conn->prepare('UPDATE patients SET firstName=?, lastName=?, middleName=?, dateOfBirth=?, gender=?, bloodType=?, phone=?, email=?, address=?, emergencyContact=?, emergencyPhone=?, medicalHistory=?, allergies=?, insurance_provider=?, insurance_number=? WHERE id=?');
        $stmt->bind_param(
            'sssssssssssssssi',
            $firstName, $lastName, $middleName, $dateOfBirth, $gender, $bloodType,
            $phone, $email, $address, $emergencyContact, $emergencyPhone,
            $medicalHistory, $allergies, $insurance_provider, $insurance_number, $id
        );
        if ($stmt->execute()) {
            json_ok(['message' => 'Patient updated successfully']);
        }
        error_log('update_patient failed: ' . $stmt->error);
        json_fail('Could not update the patient.');
    }

    if ($action === 'delete_patient') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM patients WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('delete_patient failed: ' . $stmt->error);
        json_fail('Could not delete the patient.');
    }

    if ($action === 'add_contact') {
        $patientId    = (int) ($_POST['patientId'] ?? 0);
        $contactName  = $_POST['contactName']  ?? '';
        $relationship = $_POST['relationship'] ?? '';
        $phoneNumber  = $_POST['phoneNumber']  ?? '';
        $email        = $_POST['email']        ?? '';
        $address      = $_POST['address']      ?? '';
        $isPrimary    = isset($_POST['isPrimary']) ? 1 : 0;
        $notes        = $_POST['notes']        ?? '';

        $stmt = $conn->prepare('
            INSERT INTO patient_contacts (patientId, contactName, relationship, phoneNumber, email, address, isPrimary, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param(
            'isssssis',
            $patientId, $contactName, $relationship, $phoneNumber, $email, $address, $isPrimary, $notes
        );

        if ($stmt->execute()) {
            json_ok(['message' => 'Contact added successfully']);
        }
        error_log('add_contact failed: ' . $stmt->error);
        json_fail('Could not add the contact.');
    }

    if ($action === 'update_contact') {
        $contactName  = $_POST['contactName']  ?? '';
        $relationship = $_POST['relationship'] ?? '';
        $phoneNumber  = $_POST['phoneNumber']  ?? '';
        $email        = $_POST['email']        ?? '';
        $address      = $_POST['address']      ?? '';
        $isPrimary    = isset($_POST['isPrimary']) ? 1 : 0;
        $notes        = $_POST['notes']        ?? '';
        $id           = (int) ($_POST['id'] ?? 0);

        $stmt = $conn->prepare('
            UPDATE patient_contacts
            SET contactName = ?, relationship = ?, phoneNumber = ?, email = ?, address = ?, isPrimary = ?, notes = ?
            WHERE id = ?
        ');
        $stmt->bind_param(
            'sssssisi',
            $contactName, $relationship, $phoneNumber, $email, $address, $isPrimary, $notes, $id
        );

        if ($stmt->execute()) {
            json_ok(['message' => 'Contact updated successfully']);
        }
        error_log('update_contact failed: ' . $stmt->error);
        json_fail('Could not update the contact.');
    }

    if ($action === 'delete_contact') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM patient_contacts WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            json_ok();
        }
        error_log('delete_contact failed: ' . $stmt->error);
        json_fail('Could not delete the contact.');
    }

    json_fail('Unknown action.');
}

// Reads
$api = $_GET['api'] ?? '';

if ($api === 'get_patients') {
    $result = $conn->query('SELECT * FROM patients ORDER BY created_at DESC');
    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
    json_response($patients);
}

if ($api === 'get_stats') {
    $total    = (int) $conn->query('SELECT COUNT(*) AS c FROM patients')->fetch_assoc()['c'];
    $active   = (int) $conn->query("SELECT COUNT(*) AS c FROM patients WHERE status = 'Active'")->fetch_assoc()['c'];
    $inactive = (int) $conn->query("SELECT COUNT(*) AS c FROM patients WHERE status = 'Inactive'")->fetch_assoc()['c'];
    json_response(['total' => $total, 'active' => $active, 'inactive' => $inactive]);
}

if ($api === 'get_contacts') {
    // Bound, not interpolated: this WHERE clause used to be built by string concatenation.
    $patientId = (int) ($_GET['patientId'] ?? 0);
    $stmt = $conn->prepare('SELECT * FROM patient_contacts WHERE patientId = ? ORDER BY isPrimary DESC, created_at DESC');
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $contacts = [];
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
    json_response($contacts);
}

if ($api === 'get_all_contacts') {
    $result = $conn->query('
        SELECT pc.*, p.firstName AS patientFirstName, p.lastName AS patientLastName
        FROM patient_contacts pc
        JOIN patients p ON pc.patientId = p.id
        ORDER BY pc.created_at DESC
    ');
    $contacts = [];
    while ($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
    json_response($contacts);
}

json_fail('Unknown endpoint.', 404);
