<?php
// Starts the PHP session with hardened cookie attributes:
//   HttpOnly  - not readable by JavaScript
//   SameSite=Lax - the cookie is not sent on cross-site POSTs (CSRF defense)
//   Secure    - only sent over HTTPS (auto-enabled when the request is HTTPS)
//
// Use this everywhere instead of a bare session_start().

function asclepius_start_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $secure,
        'samesite' => 'Lax',
    ]);

    session_start();
}
