<?php
// Opens the shared MySQL connection from configuration. No DDL here —
// the canonical schema lives in backend/db/schema.sql and is applied via migrations.

require_once __DIR__ . '/../config/config.php';

/**
 * Returns a single shared mysqli connection (opened once per request).
 */
function asclepius_db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    $cfg = asclepius_config()['db'];

    // We check errors explicitly below instead of throwing mysqli exceptions.
    mysqli_report(MYSQLI_REPORT_OFF);

    $port = (int) ($cfg['port'] ?: 3306);
    $conn = mysqli_init();

    if (!empty($cfg['ssl_ca'])) {
        // Managed cloud databases (Aiven, TiDB Cloud, etc.) often require TLS.
        $conn->ssl_set(null, null, $cfg['ssl_ca'], null, null);
        $ok = @$conn->real_connect($cfg['host'], $cfg['user'], $cfg['password'], $cfg['name'], $port, null, MYSQLI_CLIENT_SSL);
    } else {
        $ok = @$conn->real_connect($cfg['host'], $cfg['user'], $cfg['password'], $cfg['name'], $port);
    }

    if (!$ok || $conn->connect_error) {
        // Log the real reason; never leak connection details to the client.
        error_log('Asclepius DB connection failed: ' . mysqli_connect_error());
        http_response_code(500);
        die('Service temporarily unavailable. Please try again later.');
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
