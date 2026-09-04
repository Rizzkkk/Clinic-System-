<?php
// Shared left sidebar for all module pages. Replaces the previously duplicated (and
// inconsistent) hardcoded <aside> in every page. Links are filtered by role via can_access()
// so a user only sees what they may open.
//
// Usage (as the first element inside <body> of each page): set $active to the current page's
// module key (or 'dashboard'), then require this file. $active highlights the active link.
// NOTE: do not write a literal PHP close tag in this comment - it would end the PHP block early.
//
// Styling uses only universal Tailwind classes (text-white/opacity utilities), so it renders
// identically regardless of each page's custom Tailwind theme config. All pages load Tailwind
// (CDN) and the Material Symbols font. This file also loads SweetAlert2 (CDN) once and exposes
// window.showError / window.showSuccess / window.confirmAction helpers used across pages.
//
// Requires an active session + rbac.php (pages include bootstrap + rbac first; the require_once
// below is a defensive no-op if already loaded).
require_once __DIR__ . '/../../backend/auth/rbac.php';
require_once __DIR__ . '/../../backend/config/clinic.php';

$active = $active ?? '';
$clinic = clinic_info();
$clinicLines = clinic_contact_lines();

// [module key, href, Material Symbol, label]. module '' = always shown (Dashboard).
$sidebarLinks = [
    ['',                   'Dashboard.php',         'dashboard',        'Dashboard'],
    ['patients',           'Patient.php',           'group',            'Patients'],
    ['doctors',            'Doctor.php',            'stethoscope',      'Doctors'],
    ['lab_technicians',    'Lab Technicians.php',   'biotech',          'Lab Technicians'],
    ['cashiers',           'Cashiers.php',          'point_of_sale',    'Cashiers'],
    ['receptionists',      'Receptionists.php',     'support_agent',    'Receptionists'],
    ['appointments',       'Appointment.php',       'calendar_month',   'Appointment'],
    ['medical_records',    'Medical Records.php',   'clinical_notes',   'Medical Records'],
    ['laboratory_results', 'Laboratory Result.php', 'science',          'Laboratory Results'],
    ['agency_referrals',   'Agency Referral.php',   'forward_to_inbox', 'Agency Referral'],
    ['prescriptions',      'Prescription.php',      'medication',       'Prescription'],
    ['billing',            'Biling.php',            'payments',         'Billing'],
    ['dental_records',     'Dental.php',            'dentistry',        'Dental'],
    ['xray_studies',       'X-ray.php',             'radiology',        'X-ray'],
    ['psych_sessions',     'Psych.php',             'psychology',       'Psych'],
    ['portal_accounts',    'Portal Accounts.php',   'how_to_reg',       'Portal Accounts'],
    ['staff_accounts',     'Staff Accounts.php',    'manage_accounts',  'Staff Accounts'],
];

$linkBase = 'flex items-center gap-3 px-3 py-2 mx-2 my-1 rounded-lg transition-colors no-underline text-xs font-bold';
$linkIdle = 'text-white/70 hover:text-white hover:bg-white/10';
$linkActive = 'bg-white/20 text-white asc-active';
?>
<style>
/* Sleek, scoped sidebar scrollbar (replaces the chunky OS default on the teal panel). */
.asc-sidebar-nav { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.28) transparent; scroll-behavior: smooth; }
.asc-sidebar-nav::-webkit-scrollbar { width: 6px; }
.asc-sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.asc-sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.25); border-radius: 9999px; }
.asc-sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.45); }
.asc-sidebar-nav a.asc-active { box-shadow: inset 3px 0 0 #8ff4e3; }
</style>
<aside class="fixed left-0 top-0 h-screen w-[260px] flex flex-col py-6 z-50" style="background-color:#00685D;">
<div class="px-6 mb-8">
<img src="<?php echo htmlspecialchars($clinic['logoWeb'], ENT_QUOTES); ?>" alt="Clinic logo" class="h-12 w-auto mb-2 rounded bg-white/90 p-1"/>
<h1 class="text-base font-bold text-white leading-tight"><?php echo htmlspecialchars($clinic['name'], ENT_QUOTES); ?></h1>
<p class="text-[11px] font-bold text-white/60 uppercase tracking-wider mt-0.5"><?php echo htmlspecialchars($clinic['tagline'], ENT_QUOTES); ?></p>
<?php foreach ($clinicLines as $line): ?>
<p class="text-[10px] text-white/50 leading-tight mt-0.5"><?php echo htmlspecialchars($line, ENT_QUOTES); ?></p>
<?php endforeach; ?>
</div>
<nav class="asc-sidebar-nav flex-1 px-2 overflow-y-auto">
<?php foreach ($sidebarLinks as $link): ?>
<?php list($mod, $href, $icon, $label) = $link; ?>
<?php if ($mod !== '' && !can_access($mod)) { continue; } ?>
<?php $isActive = ($active === $mod) || ($active === 'dashboard' && $mod === ''); ?>
<a class="<?php echo $linkBase . ' ' . ($isActive ? $linkActive : $linkIdle); ?>" href="<?php echo htmlspecialchars($href, ENT_QUOTES); ?>">
<span class="material-symbols-outlined"><?php echo $icon; ?></span>
<span><?php echo htmlspecialchars($label, ENT_QUOTES); ?></span>
</a>
<?php endforeach; ?>
</nav>
<div class="px-2 pt-2">
<a class="<?php echo $linkBase; ?> text-red-300 hover:text-red-200 hover:bg-red-500/10" href="logout.php" onclick="localStorage.clear();">
<span class="material-symbols-outlined">logout</span>
<span>Logout</span>
</a>
</div>
</aside>
<!-- SweetAlert2 (shared) + global alert helpers used by the module pages -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.showError = function (message) {
    if (window.Swal) {
        Swal.fire({ icon: 'error', title: 'Something went wrong', text: message || 'Please try again.', confirmButtonColor: '#00685d' });
    } else {
        alert(message || 'Something went wrong');
    }
};
window.showSuccess = function (message) {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: message || 'Saved', showConfirmButton: false, timer: 2000, timerProgressBar: true });
    }
};
// Returns a Promise<boolean>; use with: if (!(await confirmAction('...'))) return;
window.confirmAction = function (message) {
    if (window.Swal) {
        return Swal.fire({ icon: 'warning', title: 'Are you sure?', text: message || '', showCancelButton: true, confirmButtonColor: '#ba1a1a', cancelButtonColor: '#6d7a77', confirmButtonText: 'Yes' }).then(function (r) { return r.isConfirmed; });
    }
    return Promise.resolve(window.confirm(message || 'Are you sure?'));
};
</script>
