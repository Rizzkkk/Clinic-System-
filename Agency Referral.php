<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/agency_referrals.php';
}
require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('agency_referrals');
$canWrite = can_access('agency_referrals', 'write');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Agency Referrals - ASCLEPIUS</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    body { font-family: 'Inter', sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
    .input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #bdc9c5; border-radius: 0.5rem; font-size: 14px; background:#fff; }
    .input:focus { outline: none; border-color: #00685d; }
    .nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; margin: 0.25rem 0.5rem; color: rgba(223,228,225,0.7); border-radius: 0.5rem; font-size: 12px; font-weight: 700; text-decoration:none; }
    .nav-link:hover { color: #fff; background: rgba(223,228,225,0.1); }
    .nav-link.active { color: #fff; background: rgba(223,228,225,0.15); }
</style>
</head>
<body class="bg-[#f6faf8] text-[#171d1b] min-h-screen">
<?php $active = 'agency_referrals'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>

<main class="ml-[260px] p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold" style="font-family:'Manrope',sans-serif;">Agency Referrals</h2>
            <p class="text-sm text-[#3d4946]">Record and review agency referrals per patient.</p>
        </div>
        <span class="material-symbols-outlined text-4xl text-[#00685d]">assignment</span>
    </div>

    <div class="grid grid-cols-2 gap-4 max-w-md mb-8">
        <div class="bg-white rounded-xl border border-[#dfe4e1] p-4"><p class="text-xs font-bold uppercase tracking-wider text-[#6d7a77]">Total</p><p id="statTotal" class="text-3xl font-bold mt-1">0</p></div>
        <div class="bg-white rounded-xl border border-[#dfe4e1] p-4"><p class="text-xs font-bold uppercase tracking-wider text-[#6d7a77]">Last 30 days</p><p id="statRecent" class="text-3xl font-bold mt-1">0</p></div>
    </div>

    <div class="bg-white rounded-xl border border-[#dfe4e1] p-6 mb-8">
        <h3 class="font-bold mb-4">Add Referral</h3>
        <form id="recForm" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <select name="patientId" id="patientSelect" class="input" required><option value="">Select patient *</option></select>
            <input name="referralDate" type="date" class="input" title="Referral Date" aria-label="Referral Date">
            <input name="agencyName" class="input" placeholder="Agency Name">
            <input name="referredBy" class="input" placeholder="Referred By">
            <textarea name="reason" class="input md:col-span-3" rows="2" placeholder="Reason"></textarea>
            <textarea name="notes" class="input md:col-span-3" rows="2" placeholder="Notes"></textarea>
            <select name="status" class="input"><option value="">Status</option><option>Pending</option><option>Sent</option><option>Completed</option></select>
            <div class="md:col-span-3">
                <button type="submit" class="px-5 py-2.5 rounded-lg text-white font-semibold" style="background:#00685d;">Save</button>
                <span id="formMsg" class="ml-3 text-sm"></span>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-[#dfe4e1] overflow-hidden">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-xs uppercase tracking-wider text-[#6d7a77] border-b border-[#dfe4e1]">
                <th class="px-5 py-3">Patient</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Agency</th>
                <th class="px-5 py-3">Referred By</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody id="recRows"></tbody>
        </table>
        <p id="emptyMsg" class="px-5 py-6 text-sm text-[#6d7a77] hidden">No records yet.</p>
    </div>
</main>

<script>
    const API = 'backend/api/agency_referrals.php';
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const CAN_WRITE = <?php echo $canWrite ? 'true' : 'false'; ?>;
    let RECORDS = [];
    let editingId = null;
    function startEdit(id) {
        const r = RECORDS.find(x => Number(x.id) === Number(id));
        if (!r) return;
        editingId = id;
        const f = document.getElementById('recForm');
        if (!f) return;
        Object.keys(r).forEach(k => { const el = f.elements[k]; if (el && el.type !== 'file') { el.value = (r[k] == null) ? '' : r[k]; } });
        const b = f.querySelector('button[type="submit"]'); if (b) b.textContent = 'Update';
        const m = document.getElementById('formMsg'); if (m) { m.textContent = 'Editing record #' + id + ' - submit to save, or reload to cancel'; m.style.color = '#00685d'; }
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    async function loadPatients() {
        const list = await fetch('backend/api/patients.php?api=get_patients').then(r => r.ok ? r.json() : []).catch(() => []);
        const sel = document.getElementById('patientSelect');
        list.forEach(p => {
            const o = document.createElement('option');
            o.value = p.id;
            o.textContent = `${p.firstName} ${p.lastName}`;
            sel.appendChild(o);
        });
    }
    async function loadStats() {
        const s = await fetch(API + '?api=get_stats').then(r => r.json()).catch(() => ({total:0, recentMonth:0}));
        document.getElementById('statTotal').textContent = s.total ?? 0;
        document.getElementById('statRecent').textContent = s.recentMonth ?? 0;
    }
    async function loadRecords() {
        const rows = await fetch(API + '?api=get_records').then(r => r.json()).catch(() => []);
        RECORDS = rows;
        document.getElementById('emptyMsg').classList.toggle('hidden', rows.length > 0);
        document.getElementById('recRows').innerHTML = rows.map(r => `
            <tr class="border-b border-[#eaefec] hover:bg-[#f6faf8]">
                <td class="px-5 py-3 font-semibold">${esc(r.patientFirstName)} ${esc(r.patientLastName)}</td>
                <td class="px-5 py-3">${esc(r.referralDate || '-')}</td>
                <td class="px-5 py-3">${esc(r.agencyName || '-')}</td>
                <td class="px-5 py-3">${esc(r.referredBy || '-')}</td>
                <td class="px-5 py-3">${esc(r.status || '-')}</td>
                <td class="px-5 py-3 text-right whitespace-nowrap">${CAN_WRITE ? `<button onclick="startEdit(${Number(r.id)})" class="text-[#00685d] hover:underline text-xs font-bold mr-3">Edit</button><button onclick="del(${Number(r.id)})" class="text-red-700 hover:underline text-xs font-bold">Delete</button>` : ''}</td>
            </tr>`).join('');
    }
    async function del(id) {
        if (!(await window.confirmAction('Delete this record?'))) return;
        const fd = new FormData(); fd.append('action', 'delete_record'); fd.append('id', id);
        const res = await fetch(API, { method:'POST', body:fd }).then(r => r.json());
        if (!res.success) { showError(res.message || 'Delete failed.'); return; }
        loadRecords(); loadStats();
    }
    document.getElementById('recForm').addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(e.target); fd.append('action', editingId ? 'update_record' : 'add_record'); if (editingId) fd.append('id', editingId);
        const msg = document.getElementById('formMsg'); msg.textContent = 'Saving...';
        const res = await fetch(API, { method:'POST', body:fd }).then(r => r.json()).catch(() => ({success:false, message:'Network error'}));
        msg.textContent = res.message || (res.success ? 'Saved.' : 'Failed.');
        msg.style.color = res.success ? '#00685d' : '#ba1a1a';
        if (!res.success) window.showError(res.message || 'Failed to save.');
        if (res.success) { e.target.reset(); editingId = null; const _b = e.target.querySelector('button[type="submit"]'); if (_b) _b.textContent = 'Save'; loadRecords(); loadStats(); }
    });
    loadPatients(); loadRecords(); loadStats();
</script>
</body>
</html>