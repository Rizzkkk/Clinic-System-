<?php
// Patient portal access control. This is the portal's equivalent of rbac.php, and it is a
// deliberately separate file: rbac.php is MODULE-scoped (a role may read every row of a module),
// while the portal is ROW-scoped (a patient may read only their own rows). Mixing the two would
// invite someone to reach for can_access() in portal code, where it means nothing.
//
// Every portal page starts with exactly these two lines:
//
//     require_once __DIR__ . '/backend/auth/portal.php';
//     $patientId = require_patient();
//
// require_patient() RETURNS the id rather than setting a global, so a query author has to
// physically hold it. Skip the guard and there is nothing to bind. Every statement in a portal
// page must be a prepared statement binding that id -- see docs/security.md for the review rule.

// Must be defined BEFORE bootstrap runs: bootstrap uses it to tell a legitimate portal request
// apart from a patient account trying to open a staff page or a backend/api/ handler.
define('ASCLEPIUS_PORTAL', true);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../db/connection.php';

// Portal pages render PHI. Clinics and households share devices, so keep it out of the browser
// cache and out of the back button after logout.
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
// Portal pages carry state-changing forms (contact details, appointment requests), so they must
// not be framable: otherwise a patient can be clickjacked into rewriting their own contact email.
header('X-Frame-Options: DENY');
header("Content-Security-Policy: frame-ancestors 'none'");
header('Referrer-Policy: same-origin');

// Stop the request: 403 JSON for an API-shaped call, redirect to the public site for a page load.
// Mirrors rbac_deny(), but sends staff to their own dashboard rather than looping through here.
function portal_deny(): void
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $isApiRequest = !empty($_GET['api'])
        || $_SERVER['REQUEST_METHOD'] === 'POST'
        || strpos($scriptName, '/backend/api/') !== false;

    if ($isApiRequest) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'You do not have access to this resource.']);
        exit;
    }

    // A logged-in staff member who wanders onto a portal page belongs on the staff dashboard.
    if (in_array($_SESSION['user_role'] ?? '', ['admin', 'doctor', 'reception', 'lab', 'cashier'], true)) {
        header('Location: Dashboard.php');
        exit;
    }

    header('Location: login.php');
    exit;
}

// Render a standalone status screen for an account that may log in but may NOT see any records
// yet, then stop. No PHI query runs on this path.
function portal_status_page(string $heading, string $message): void
{
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    $name = $_SESSION['user_name'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo h($heading); ?> - ASCLEPIUS</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="frontend/assets/css/landing.css">
  <link rel="stylesheet" href="frontend/assets/css/portal.css">
</head>
<body>
  <main class="portal-status">
    <div class="portal-status-card">
      <h1><?php echo h($heading); ?></h1>
      <?php if ($name !== ''): ?>
      <p class="portal-status-who">Signed in as <?php echo h($name); ?></p>
      <?php endif; ?>
      <p><?php echo h($message); ?></p>
      <p class="portal-status-actions">
        <a class="btn btn-primary" href="index.php">Back to website</a>
        <a class="btn btn-outline-dark" href="logout.php">Sign out</a>
      </p>
    </div>
  </main>
</body>
</html>
    <?php
    exit;
}

// Return the patients.id this session may read, or stop the request. Never returns 0.
//
// The role AND the link are read from the database on every request, never from the session.
// The session only says "this is some kind of portal account"; the users row decides what that
// account may actually see. That is what makes reception's decisions take effect on the account's
// very next page load -- an approval grants access without the patient re-logging in, and an
// unlink revokes it immediately rather than whenever they happen to log out. It also means there
// is no cached authorization in the session for anyone to tamper with. The cost is one
// primary-key lookup per request.
function require_patient(): int
{
    require_once __DIR__ . '/../lib/escape.php';

    // Staff who wander onto a portal URL are turned away here; bootstrap lets them through
    // because ASCLEPIUS_PORTAL is defined, but the portal is not theirs.
    if (!in_array($_SESSION['user_role'] ?? '', PORTAL_ROLES, true)) {
        portal_deny();
    }

    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        portal_deny();
    }

    $conn = asclepius_db();
    $stmt = $conn->prepare('SELECT role, patientId FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);

    $row = null;
    if ($stmt->execute()) {
        $row = $stmt->get_result()->fetch_assoc();
    } else {
        error_log('require_patient lookup failed: ' . $stmt->error);
    }
    $stmt->close();

    // Fail closed on anything unexpected: a deleted account, or a row whose role moved out of the
    // portal entirely. Never fall back to 0 or to an unfiltered query.
    if (!$row) {
        portal_deny();
    }

    $role = (string) ($row['role'] ?? '');
    $patientId = (int) ($row['patientId'] ?? 0);

    if ($role === 'patient_pending') {
        portal_status_page(
            'Verification pending',
            'Thanks for signing up. Our reception team still needs to verify your details against '
            . 'your clinic record before your information becomes visible. This is a manual check, '
            . 'so please allow a little time, or call the clinic if it is urgent.'
        );
    }

    if ($role === 'patient_rejected') {
        portal_status_page(
            'We could not verify your account',
            'We were unable to match your details to a clinic record. Please contact reception so '
            . 'they can confirm your identity and set up your access.'
        );
    }

    if ($role !== 'patient') {
        portal_deny();
    }

    // role 'patient' with no link means the patient record was deleted out from under the account
    // (the FK's ON DELETE SET NULL), so there is nothing this login may legitimately read.
    if ($patientId <= 0) {
        portal_status_page(
            'Your portal access is not active',
            'Your account is not currently linked to a clinic record. Please contact reception.'
        );
    }

    return $patientId;
}
