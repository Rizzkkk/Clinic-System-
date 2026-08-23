<?php
// Admin-only creation and role assignment for clinic staff logins.
//
// Staff self-registration used to be public (register.php, linked from the login page), which let
// anyone mint a users row on a system holding medical records. Account creation is now an
// administrator action only; the public login page offers the patient portal signup alone.

require_once __DIR__ . '/backend/auth/bootstrap.php';
require_once __DIR__ . '/backend/auth/rbac.php';
require_once __DIR__ . '/backend/lib/validation.php';
require_once __DIR__ . '/backend/lib/escape.php';
require_module_access('staff_accounts');

const STAFF_ROLES = ['admin', 'doctor', 'reception', 'lab', 'cashier'];

$pageError = '';
$pageSuccess = '';
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? '') : '';

if ($action === 'create_staff') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $email === '' || $role === '' || $password === '' || $confirmPassword === '') {
        $pageError = 'Please fill in all fields.';
    } elseif (!is_valid_name($fullName)) {
        $pageError = 'Please enter a valid full name (letters, spaces, hyphens, apostrophes, and periods only).';
    } elseif (!is_valid_email($email)) {
        $pageError = 'Please enter a valid email address.';
    } elseif (!in_array($role, STAFF_ROLES, true)) {
        $pageError = 'Please choose a valid staff role.';
    } elseif (strlen($password) < 8) {
        $pageError = 'The password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $pageError = 'Passwords do not match.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();
        $check->close();

        if ($exists) {
            $pageError = 'An account with this email already exists.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $insert->bind_param('ssss', $fullName, $email, $passwordHash, $role);
            if ($insert->execute()) {
                $pageSuccess = 'Staff account created for ' . $fullName . ' (' . $role . ').';
                $_POST = [];
            } else {
                error_log('staff account insert failed: ' . $insert->error);
                $pageError = 'Unable to create the account right now. Please try again.';
            }
            $insert->close();
        }
    }
}

if ($action === 'assign_role') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $role = $_POST['role'] ?? '';

    if (!$userId || !in_array($role, STAFF_ROLES, true)) {
        $pageError = 'Choose a valid staff role.';
    } elseif ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
        // Without this an admin can drop their own role and lock the last administrator out.
        $pageError = 'You cannot change your own role.';
    } else {
        // The role predicate keeps this statement off portal (patient) accounts entirely, so a
        // forged request can never turn a patient login into staff.
        $stmt = $conn->prepare(
            "UPDATE users SET role = ?
              WHERE id = ? AND role IN ('pending', 'admin', 'doctor', 'reception', 'lab', 'cashier')"
        );
        $stmt->bind_param('si', $role, $userId);
        if ($stmt->execute()) {
            $pageSuccess = $stmt->affected_rows > 0
                ? 'Role updated.'
                : 'No change was made. That account may not be a staff account.';
        } else {
            error_log('staff role update failed: ' . $stmt->error);
            $pageError = 'Unable to update the role right now.';
        }
        $stmt->close();
    }
}

$accounts = $conn->query(
    "SELECT id, full_name, email, role
       FROM users
      WHERE role IN ('pending', 'admin', 'doctor', 'reception', 'lab', 'cashier')
      ORDER BY (role = 'pending') DESC, full_name ASC"
)->fetch_all(MYSQLI_ASSOC);

$currentUserId = (int) ($_SESSION['user_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Staff Accounts - ASCLEPIUS</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    body { font-family: 'Inter', sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
    .input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #bdc9c5; border-radius: 0.5rem; font-size: 14px; background:#fff; }
    .input:focus { outline: none; border-color: #00685d; }
</style>
</head>
<body class="bg-[#f6faf8] text-[#171d1b] min-h-screen">
<?php $active = 'staff_accounts'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>

<main class="ml-[260px] p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold" style="font-family:'Manrope',sans-serif;">Staff Accounts</h2>
            <p class="text-sm text-[#3d4946]">Create clinic staff logins and assign their role. Administrators only.</p>
        </div>
        <span class="material-symbols-outlined text-4xl text-[#00685d]">manage_accounts</span>
    </div>

    <div class="rounded-xl border border-[#f0c98a] bg-[#fdf6e9] p-4 mb-8 flex gap-3">
        <span class="material-symbols-outlined text-[#9a5c11]">shield_person</span>
        <div class="text-sm text-[#5c4415]">
            <p class="font-bold mb-1">Staff logins are not self-service.</p>
            <p>Every account created here can reach patient data as soon as it has a role. Create one only for a person
               you have confirmed works at the clinic, and give them the narrowest role that fits their job.</p>
        </div>
    </div>

    <section class="bg-white rounded-xl border border-[#dbe5e1] p-6 mb-8 max-w-3xl">
        <h3 class="font-bold mb-4">Create a staff account</h3>
        <form method="post" action="" autocomplete="off" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="action" value="create_staff"/>
            <div>
                <label class="block text-xs font-bold mb-1" for="full_name">Full name</label>
                <input class="input" id="full_name" name="full_name" type="text" required
                       pattern="[A-Za-z&#xC0;-&#xFF;][A-Za-z&#xC0;-&#xFF; '.-]*" title="Letters, spaces, hyphens, apostrophes, and periods only."
                       value="<?php echo h($_POST['full_name'] ?? ''); ?>"/>
            </div>
            <div>
                <label class="block text-xs font-bold mb-1" for="email">Email</label>
                <input class="input" id="email" name="email" type="email" required
                       value="<?php echo h($_POST['email'] ?? ''); ?>"/>
            </div>
            <div>
                <label class="block text-xs font-bold mb-1" for="role">Role</label>
                <select class="input" id="role" name="role" required>
                    <option value="">Select a role</option>
                    <?php foreach (STAFF_ROLES as $roleOption): ?>
                    <option value="<?php echo h($roleOption); ?>"><?php echo h(ucfirst($roleOption)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div></div>
            <div>
                <label class="block text-xs font-bold mb-1" for="password">Temporary password</label>
                <input class="input" id="password" name="password" type="password" minlength="8" required/>
            </div>
            <div>
                <label class="block text-xs font-bold mb-1" for="confirm_password">Confirm password</label>
                <input class="input" id="confirm_password" name="confirm_password" type="password" minlength="8" required/>
            </div>
            <div class="md:col-span-2">
                <button class="px-5 py-2 rounded-lg bg-[#00685d] text-white font-bold text-sm" type="submit">Create account</button>
            </div>
        </form>
    </section>

    <section class="bg-white rounded-xl border border-[#dbe5e1] p-6">
        <h3 class="font-bold mb-4">Existing staff accounts</h3>
        <?php if (!$accounts): ?>
        <p class="text-sm text-[#3d4946]">No staff accounts yet.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[#3d4946] border-b border-[#dbe5e1]">
                    <th class="py-2 pr-4">Name</th>
                    <th class="py-2 pr-4">Email</th>
                    <th class="py-2 pr-4">Role</th>
                    <th class="py-2">Change role</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($accounts as $account): ?>
                <tr class="border-b border-[#eef3f1]">
                    <td class="py-2 pr-4"><?php echo h($account['full_name']); ?></td>
                    <td class="py-2 pr-4"><?php echo h($account['email']); ?></td>
                    <td class="py-2 pr-4">
                        <?php if ($account['role'] === 'pending'): ?>
                        <span class="px-2 py-0.5 rounded-full bg-[#fdf6e9] text-[#9a5c11] text-xs font-bold">No role assigned</span>
                        <?php else: ?>
                        <?php echo h(ucfirst($account['role'])); ?>
                        <?php endif; ?>
                    </td>
                    <td class="py-2">
                        <?php if ((int) $account['id'] === $currentUserId): ?>
                        <span class="text-xs text-[#3d4946]">This is you</span>
                        <?php else: ?>
                        <form method="post" action="" class="flex gap-2 items-center">
                            <input type="hidden" name="action" value="assign_role"/>
                            <input type="hidden" name="user_id" value="<?php echo (int) $account['id']; ?>"/>
                            <select class="input max-w-[10rem]" name="role" required>
                                <?php foreach (STAFF_ROLES as $roleOption): ?>
                                <option value="<?php echo h($roleOption); ?>" <?php echo $account['role'] === $roleOption ? 'selected' : ''; ?>><?php echo h(ucfirst($roleOption)); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="px-3 py-2 rounded-lg border border-[#00685d] text-[#00685d] font-bold text-xs" type="submit">Save</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php if ($pageError !== ''): ?>
<script>window.showError(<?php echo json_encode($pageError); ?>);</script>
<?php endif; ?>
<?php if ($pageSuccess !== ''): ?>
<script>window.showSuccess(<?php echo json_encode($pageSuccess); ?>);</script>
<?php endif; ?>
</body>
</html>
