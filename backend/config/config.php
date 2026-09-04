<?php
// Central configuration. Reads DB credentials from the environment / a .env file.
// NEVER hardcode secrets here. See backend/config/.env.example.

/**
 * Loads KEY=VALUE pairs from a .env file into the process environment.
 * Existing environment variables are NOT overwritten (real env wins over .env).
 */
function asclepius_load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $name = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        // Strip a single pair of surrounding quotes, if present.
        if (strlen($value) >= 2
            && ($value[0] === '"' || $value[0] === "'")
            && $value[strlen($value) - 1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        if ($name !== '' && getenv($name) === false) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

asclepius_load_env(__DIR__ . '/.env');

/**
 * Returns the application configuration as an array.
 */
function asclepius_config(): array
{
    $get = static function (string $key, ?string $default = null): ?string {
        $value = getenv($key);
        return ($value === false || $value === '') ? $default : $value;
    };

    return [
        'app_env' => $get('APP_ENV', 'production'), // 'production' | 'development'
        'db' => [
            'host'     => $get('DB_HOST', 'localhost'),
            'name'     => $get('DB_NAME'),
            'user'     => $get('DB_USER'),
            'password' => $get('DB_PASSWORD', ''),
            'port'     => $get('DB_PORT', '3306'),
            'ssl_ca'   => $get('DB_SSL_CA'), // path to CA cert; set when the cloud DB requires TLS
        ],
    ];
}

// Environment-aware error handling. In production, never render errors/stack traces to the
// browser (they could leak patient data) - log them instead. Dev shows them for debugging.
error_reporting(E_ALL);
@ini_set('log_errors', '1');
@ini_set('display_errors', asclepius_config()['app_env'] === 'development' ? '1' : '0');
