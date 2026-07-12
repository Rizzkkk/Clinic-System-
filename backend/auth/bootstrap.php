<?php
// Shared bootstrap for pages and API handlers: starts the session, opens the DB
// ($conn), and enforces login. Include this once at the very top of a page/handler:
//
//     require_once __DIR__ . '/backend/auth/bootstrap.php';      // from a root page
//     require_once __DIR__ . '/../auth/bootstrap.php';           // from backend/api/*
//
// Replaces the copy-pasted session_start() + require db.php + login-guard block.

require_once __DIR__ . '/../lib/session.php';
asclepius_start_session();

require_once __DIR__ . '/../db/connection.php';
$conn = asclepius_db();

// CSRF defense: reject state-changing requests that come from another origin.
// A same-origin fetch() sends a matching Origin/Referer; a cross-site forgery won't.
// (No header at all -> non-browser client, which isn't a CSRF vector -> allowed.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expectedHost = $_SERVER['HTTP_HOST'] ?? '';
    $source = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
    if ($source !== '') {
        $sourceHost = (string) (parse_url($source, PHP_URL_HOST) ?? '');
        $sourcePort = parse_url($source, PHP_URL_PORT);
        if ($sourcePort) {
            $sourceHost .= ':' . $sourcePort;
        }
        if (!hash_equals($expectedHost, $sourceHost)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Cross-origin request blocked']);
            exit;
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    // API/fetch calls get a JSON 401; normal page loads are redirected to login.
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $isApiRequest = !empty($_GET['api'])
        || $_SERVER['REQUEST_METHOD'] === 'POST'
        || strpos($scriptName, '/backend/api/') !== false;

    if ($isApiRequest) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    header('Location: index.php');
    exit;
}

// Accounts without a recognized staff role (e.g. self-registered 'pending' users) get NO access
// until an administrator assigns a real role. This also keeps them off the Dashboard.
$validRoles = ['admin', 'doctor', 'reception', 'lab', 'cashier'];
if (!in_array($_SESSION['user_role'] ?? '', $validRoles, true)) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $isApiRequest = !empty($_GET['api'])
        || $_SERVER['REQUEST_METHOD'] === 'POST'
        || strpos($scriptName, '/backend/api/') !== false;

    if ($isApiRequest) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Your account has no role assigned. Contact an administrator.']);
        exit;
    }

    http_response_code(403);
    echo '<!doctype html><meta charset="utf-8"><title>Account pending</title>'
       . '<div style="font-family:Inter,system-ui,sans-serif;max-width:34rem;margin:12vh auto;padding:2rem;text-align:center">'
       . '<h1 style="color:#00685d;margin-bottom:.5rem">Account pending</h1>'
       . '<p style="color:#3d4946">Your account was created but an administrator has not assigned a role yet. '
       . 'Please contact your administrator to get access.</p>'
       . '<p style="margin-top:1.5rem"><a href="logout.php" style="color:#00685d;font-weight:600">Back to login</a></p>'
       . '</div>';
    exit;
}
