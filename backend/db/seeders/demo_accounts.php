<?php
// Creates (or resets) one demo login per role, plus the patient records the portal demo needs.
// Safe to re-run: accounts are matched by email, so a re-run resets passwords/roles and puts the
// pending signup back in the reception queue. The password comes from DEMO_PASSWORD, never from
// this file - the repo is public.
//
//   DEMO_PASSWORD='...' php backend/db/seeders/demo_accounts.php

if (PHP_SAPI !== 'cli') {
    // backend/ is .htaccess-denied, but that does nothing under nginx.
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../connection.php';

$password = getenv('DEMO_PASSWORD') ?: '';
if (strlen($password) < 8) {
    fwrite(STDERR, "Set DEMO_PASSWORD (at least 8 characters, same rule as Staff Accounts).\n");
    exit(1);
}

$conn = asclepius_db();
$conn->begin_transaction();

function demo_fail(mysqli $conn, string $what): void
{
    $conn->rollback();
    fwrite(STDERR, "Failed: $what: {$conn->error}\n");
    exit(1);
}

// Two patient records: one already linked to a portal login, one for the pending signup to be
// matched against, because users.patientId is unique and a second link to the first would fail.
function demo_patient(mysqli $conn, array $p): int
{
    $find = $conn->prepare('SELECT id FROM patients WHERE email = ? LIMIT 1');
    $find->bind_param('s', $p['email']);
    $find->execute();
    $row = $find->get_result()->fetch_assoc();
    if ($row) {
        return (int) $row['id'];
    }
    $insert = $conn->prepare(
        'INSERT INTO patients (firstName, lastName, dateOfBirth, gender, phone, email, address, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, \'Active\')'
    );
    $insert->bind_param('sssssss', $p['first'], $p['last'], $p['dob'], $p['gender'], $p['phone'], $p['email'], $p['address']);
    if (!$insert->execute()) {
        demo_fail($conn, 'patient ' . $p['email']);
    }
    return (int) $conn->insert_id;
}

$linkedPatient = demo_patient($conn, [
    'first' => 'Juan', 'last' => 'Demo', 'dob' => '1990-01-15', 'gender' => 'Male',
    'phone' => '09170000001', 'email' => 'demo.patient@asclepius.demo', 'address' => 'Demo record - not a real person',
]);
demo_patient($conn, [
    'first' => 'Maria', 'last' => 'Demo', 'dob' => '1988-06-20', 'gender' => 'Female',
    'phone' => '09170000002', 'email' => 'demo.pending@asclepius.demo', 'address' => 'Demo record - not a real person',
]);

$hash = password_hash($password, PASSWORD_DEFAULT);

$accounts = [
    ['Demo Admin',        'demo.admin@asclepius.demo',     'admin'],
    ['Demo Doctor',       'demo.doctor@asclepius.demo',    'doctor'],
    ['Demo Receptionist', 'demo.reception@asclepius.demo', 'reception'],
    ['Demo Lab Tech',     'demo.lab@asclepius.demo',       'lab'],
    ['Demo Cashier',      'demo.cashier@asclepius.demo',   'cashier'],
];

$staff = $conn->prepare(
    'INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), password_hash = VALUES(password_hash),
       role = VALUES(role), patientId = NULL, linkedAt = NULL, linkedBy = NULL'
);
foreach ($accounts as [$name, $email, $role]) {
    $staff->bind_param('ssss', $name, $email, $hash, $role);
    if (!$staff->execute()) {
        demo_fail($conn, $email);
    }
}

$adminId = (int) $conn->query("SELECT id FROM users WHERE email = 'demo.admin@asclepius.demo'")->fetch_assoc()['id'];

$portal = $conn->prepare(
    'INSERT INTO users (full_name, email, password_hash, role, patientId, claimedDob, claimedPhone, linkedAt, linkedBy)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), password_hash = VALUES(password_hash),
       role = VALUES(role), patientId = VALUES(patientId), claimedDob = VALUES(claimedDob),
       claimedPhone = VALUES(claimedPhone), linkedAt = VALUES(linkedAt), linkedBy = VALUES(linkedBy)'
);
$portalAccounts = [
    ['Juan Demo',  'demo.patient@asclepius.demo', 'patient',         $linkedPatient, '1990-01-15', '09170000001', date('Y-m-d H:i:s'), $adminId],
    // Left unlinked on purpose so reception can approve it live in Portal Accounts.
    ['Maria Demo', 'demo.pending@asclepius.demo', 'patient_pending', null,           '1988-06-20', '09170000002', null,                null],
];
foreach ($portalAccounts as [$name, $email, $role, $patientId, $dob, $phone, $linkedAt, $linkedBy]) {
    $portal->bind_param('ssssisssi', $name, $email, $hash, $role, $patientId, $dob, $phone, $linkedAt, $linkedBy);
    if (!$portal->execute()) {
        demo_fail($conn, $email);
    }
}

$conn->commit();

echo "Demo accounts ready (password = DEMO_PASSWORD):\n";
foreach (array_merge($accounts, $portalAccounts) as $a) {
    printf("  %-32s %s\n", $a[1], $a[2]);
}
