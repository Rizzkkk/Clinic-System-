<?php
// JSON response helpers. Each sends the response and exits.

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function json_ok(array $extra = []): void
{
    json_response(['success' => true] + $extra);
}

function json_fail(string $message, int $status = 400): void
{
    json_response(['success' => false, 'message' => $message], $status);
}
