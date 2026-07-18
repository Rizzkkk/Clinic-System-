<?php
// Serves an uploaded X-ray file from storage/xray/ (which is web-denied) through an
// authenticated, RBAC-checked endpoint. Read access = lab, doctor, admin (per rbac.php);
// cashier/reception get a 403. Usage: backend/api/xray_image.php?id=<studyId>

require_once __DIR__ . '/../auth/bootstrap.php';
require_once __DIR__ . '/../lib/response.php';
require_once __DIR__ . '/../auth/rbac.php';
require_module_access('xray_studies');

$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT imagePath FROM xray_studies WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row || empty($row['imagePath'])) {
    json_fail('Image not found.', 404);
}

$name = basename($row['imagePath']); // defend against traversal; we only stored a bare filename
$path = __DIR__ . '/../../storage/xray/' . $name;
if (!is_file($path)) {
    json_fail('Image file is missing.', 404);
}

$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
$types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf'];
$contentType = $types[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="xray-' . $id . '.' . $ext . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
