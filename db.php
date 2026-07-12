<?php
// Backward-compatible database include for the legacy flat pages.
//
// Connection and configuration now live in backend/db/connection.php and
// backend/config/ (credentials come from backend/config/.env, never hardcoded).
//
// This file previously (a) hardcoded live DB credentials and (b) re-ran every
// CREATE TABLE on each request. Both have been removed. The single canonical
// schema is backend/db/schema.sql, applied deliberately (see docs/database/migrations.md).
//
// Legacy pages still do `require_once __DIR__ . '/db.php';` and use $conn — that keeps working.

require_once __DIR__ . '/backend/db/connection.php';

$conn = asclepius_db();
