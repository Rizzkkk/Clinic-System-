<?php
// Ends the staff session properly: clears session data, expires the cookie, destroys the session,
// then returns to the login page. (The sidebar Logout link points here.)

require_once __DIR__ . '/backend/lib/session.php';
asclepius_start_session();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();

header('Location: index.php');
exit;
