<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
// Backward-compat: this page's data API lives in backend/api/lab_technicians.php.
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/lab_technicians.php';
}
require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('lab_technicians');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Lab Technicians - ASCLEPIUS</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    body { font-family: 'Inter', sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
    .input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #bdc9c5; border-radius: 0.5rem; font-size: 14px; }
    .input:focus { outline: none; border-color: #00685d; }
    .nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; margin: 0.25rem 0.5rem; color: rgba(223,228,225,0.7); border-radius: 0.5rem; font-size: 12px; font-weight: 700; }
    .nav-link:hover { color: #fff; background: rgba(223,228,225,0.1); }
    .nav-link.active { color: #fff; background: rgba(223,228,225,0.15); }
</style>
</head>
<body class="bg-[#f6faf8] text-[#171d1b] min-h-screen">

<?php $active = 'lab_technicians'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>

<main class="ml-[260px] p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold" style="font-family:'Manrope',sans-serif;">Lab Technicians</h2>
            <p class="text-sm text-[#3d4946]">Manage the Lab Technician registry. Admin access only.</p>
        </div>
        <span class="material-symbols-outlined text-4xl text-[#00685d]">biotech</span>
    </div>

    <div class="grid grid-cols-2 gap-4 max-w-md mb-8">
        <div class="bg-white rounded-xl border border-[#dfe4e1] p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-[#6d7a77]">Total</p>
            <p id="statTotal" class="text-3xl font-bold mt-1">0</p>
        </div>
        <div class="bg-white rounded-xl border border-[#dfe4e1] p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-[#6d7a77]">Active</p>
            <p id="statActive" class="text-3xl font-bold mt-1">0</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-[#dfe4e1] p-6 mb-8">
        <h3 class="font-bold mb-4">Add Lab Technician</h3>
        <form id="staffForm" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input name="firstName" class="input" placeholder="First name *" required>
            <input name="lastName" class="input" placeholder="Last name *" required>
            <input name="middleName" class="input" placeholder="Middle name">
            <input name="employeeId" class="input" placeholder="Employee ID *" required>
            <select name="section" class="input"><option value="">Section</option><option>Hematology</option><option>Biochemistry</option><option>Microbiology</option></select>
            <input name="licenseNumber" class="input" placeholder="License No. (optional)">
            <select name="shift" class="input"><option value="">Shift</option><option value="morning">Morning</option><option value="evening">Evening</option></select>
            <input name="phone" class="input" placeholder="Phone">
            <input name="email" class="input" type="email" placeholder="Email">
            <input name="dob" class="input" type="date">
            <div class="md:col-span-3">
                <button type="submit" class="px-5 py-2.5 rounded-lg text-white font-semibold" style="background:#00685d;">Register</button>
                <span id="formMsg" class="ml-3 text-sm"></span>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-[#dfe4e1] overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wider text-[#6d7a77] border-b border-[#dfe4e1]">
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Employee ID</th>
                    <th class="px-5 py-3">Section</th>
                    <th class="px-5 py-3">Shift</th>
                    <th class="px-5 py-3">Phone</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="staffRows"></tbody>
        </table>
        <p id="emptyMsg" class="px-5 py-6 text-sm text-[#6d7a77] hidden">No records yet.</p>
    </div>
</main>

<script>
    const API = 'backend/api/lab_technicians.php';

    function esc(v) {
        return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    let STAFF = [];
    let editingId = null;
    function startEditStaff(id) {
        const r = STAFF.find(x => Number(x.id) === Number(id));
        if (!r) return;
        editingId = id;
        const f = document.getElementById('staffForm');
        if (!f) return;
        Object.keys(r).forEach(k => { const el = f.elements[k]; if (el && el.type !== 'file') { el.value = (r[k] == null) ? '' : r[k]; } });
        const b = f.querySelector('button[type="submit"]'); if (b) { if (!b.dataset.orig) b.dataset.orig = b.textContent; b.textContent = 'Update'; }
        const m = document.getElementById('formMsg'); if (m) { m.textContent = 'Editing #' + id + ' - submit to save, or reload to cancel'; m.style.color = '#00685d'; }
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    async function loadStats() {
        const s = await fetch(API + '?api=get_stats').then(r => r.json()).catch(() => ({total: 0, active: 0}));
        document.getElementById('statTotal').textContent = s.total ?? 0;
        document.getElementById('statActive').textContent = s.active ?? 0;
    }

    async function loadStaff() {
        const rows = await fetch(API + '?api=get_staff').then(r => r.json()).catch(() => []);
        STAFF = rows;
        const tbody = document.getElementById('staffRows');
        document.getElementById('emptyMsg').classList.toggle('hidden', rows.length > 0);
        tbody.innerHTML = rows.map(r => `
            <tr class="border-b border-[#eaefec] hover:bg-[#f6faf8]">
                <td class="px-5 py-3 font-semibold">${esc(r.firstName)} ${esc(r.lastName)}</td>
                <td class="px-5 py-3">${esc(r.employeeId)}</td>
                <td class="px-5 py-3">${esc(r.section || '-')}</td>
                <td class="px-5 py-3 capitalize">${esc(r.shift || '-')}</td>
                <td class="px-5 py-3">${esc(r.phone || '-')}</td>
                <td class="px-5 py-3"><span class="px-2 py-1 rounded text-[10px] font-bold uppercase" style="background:#8ff4e3;color:#00201c;">${esc(r.status || 'Active')}</span></td>
                <td class="px-5 py-3 text-right">
                    <button onclick="startEditStaff(${Number(r.id)})" class="text-[#00685d] hover:underline text-xs font-bold mr-3">Edit</button><button onclick="removeStaff(${Number(r.id)})" class="text-red-700 hover:underline text-xs font-bold">Delete</button>
                </td>
            </tr>`).join('');
    }

    async function removeStaff(id) {
        if (!(await window.confirmAction('Delete this record? This cannot be undone.'))) return;
        const fd = new FormData();
        fd.append('action', 'delete_staff');
        fd.append('id', id);
        const res = await fetch(API, { method: 'POST', body: fd }).then(r => r.json());
        if (!res.success) { showError(res.message || 'Delete failed.'); return; }
        loadStaff(); loadStats();
    }

    document.getElementById('staffForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);
        fd.append('action', editingId ? 'update_staff' : 'add_staff'); if (editingId) fd.append('id', editingId);
        const msg = document.getElementById('formMsg');
        msg.textContent = 'Saving...';
        const res = await fetch(API, { method: 'POST', body: fd }).then(r => r.json()).catch(() => ({success: false, message: 'Network error'}));
        msg.textContent = res.message || (res.success ? 'Saved.' : 'Failed.');
        msg.style.color = res.success ? '#00685d' : '#ba1a1a';
        if (!res.success) window.showError(res.message || 'Failed to save.');
        if (res.success) { form.reset(); editingId = null; const _b = form.querySelector('button[type="submit"]'); if (_b) _b.textContent = _b.dataset.orig || 'Save'; loadStaff(); loadStats(); }
    });

    loadStaff();
    loadStats();
</script>
</body>
</html>