<?php
// Role-based access control. Requires the session to be started (bootstrap does that) and the
// current user's role in $_SESSION['user_role'] (set at login in login.php).
//
// Roles: admin, reception, lab, cashier, doctor. 'admin' is a superuser (always allowed).

// Per-module permissions (matches the matrix in docs/security.md). 'read' = roles that may view
// (GET ?api= / page load); 'write' = roles that may create/update/delete (POST action). admin is
// implicitly allowed everywhere. Read sets already include each module's write roles.
const MODULE_PERMISSIONS = [
    'patients'           => ['read' => ['reception', 'lab', 'cashier', 'doctor'], 'write' => ['reception']],
    'doctors'            => ['read' => ['reception', 'lab', 'doctor'],            'write' => []],
    'appointments'       => ['read' => ['reception', 'lab', 'cashier', 'doctor'], 'write' => ['reception']],
    'medical_records'    => ['read' => ['lab', 'doctor'],                          'write' => ['doctor']],
    'laboratory_results' => ['read' => ['lab', 'doctor'],                          'write' => ['lab']],
    'prescriptions'      => ['read' => ['lab', 'doctor'],                          'write' => ['doctor']],
    'billing'            => ['read' => ['reception', 'cashier'],                   'write' => ['cashier']],
    // Staff directories are admin-only (empty role lists; admin passes implicitly).
    'lab_technicians'    => ['read' => [], 'write' => []],
    'cashiers'           => ['read' => [], 'write' => []],
    'receptionists'      => ['read' => [], 'write' => []],
    // Diagnostic / clinical modules.
    'dental_records'     => ['read' => ['lab', 'doctor'],       'write' => ['doctor']],
    'psych_sessions'     => ['read' => ['doctor'],              'write' => ['doctor']],
    'xray_studies'       => ['read' => ['lab', 'doctor'],       'write' => ['lab']],
    'agency_referrals'   => ['read' => ['reception', 'doctor'], 'write' => ['reception', 'doctor']],
    // Patient portal signups awaiting identity verification. Reception does the matching because
    // they are the desk that already confirms who a walk-in is.
    'portal_accounts'    => ['read' => ['reception'],           'write' => ['reception']],
];

function current_role(): string
{
    // Default to '' (no role) when the session has no role, so a missing/stale session gets
    // NO access rather than superuser. Login (login.php) always sets $_SESSION['user_role'].
    return $_SESSION['user_role'] ?? '';
}

// Non-fatal permission check for UI rendering (e.g. filtering sidebar links, hiding buttons).
// Returns true if the current role may access $module in $mode ('read' or 'write'). admin always
// passes. Does not stop the request (unlike require_module_access).
function can_access(string $module, string $mode = 'read'): bool
{
    if (current_role() === 'admin') {
        return true;
    }
    $perms = MODULE_PERMISSIONS[$module] ?? ['read' => [], 'write' => []];
    $allowed = $perms[$mode] ?? [];
    return in_array(current_role(), $allowed, true);
}

// Stop the request: 403 JSON for API/POST, redirect to the dashboard for a page load.
function rbac_deny(): void
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
    header('Location: Dashboard.php');
    exit;
}

// Allow only if the current role is admin or in $allowed; otherwise stop.
function require_role(array $allowed): void
{
    $role = current_role();
    if ($role === 'admin' || in_array($role, $allowed, true)) {
        return;
    }
    rbac_deny();
}

// Enforce a module's permissions for the current request. Reads (GET ?api= / page load) check the
// module's 'read' roles; writes (POST action) check 'write'. admin always passes.
function require_module_access(string $module): void
{
    if (current_role() === 'admin') {
        return;
    }
    $perms = MODULE_PERMISSIONS[$module] ?? ['read' => [], 'write' => []];
    $isWrite = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']);
    $allowed = $isWrite ? $perms['write'] : $perms['read'];
    if (in_array(current_role(), $allowed, true)) {
        return;
    }
    rbac_deny();
}
