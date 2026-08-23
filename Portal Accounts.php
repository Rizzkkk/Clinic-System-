<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/portal_accounts.php';
}
require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('portal_accounts');
$canWritePortalAccounts = can_access('portal_accounts', 'write');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Portal Accounts - ASCLEPIUS</title>
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
<?php $active = 'portal_accounts'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>

<main class="ml-[260px] p-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold" style="font-family:'Manrope',sans-serif;">Portal Accounts</h2>
            <p class="text-sm text-[#3d4946]">Verify patient portal signups and link them to the right clinic record.</p>
        </div>
        <span class="material-symbols-outlined text-4xl text-[#00685d]">how_to_reg</span>
    </div>

    <div class="rounded-xl border border-[#f0c98a] bg-[#fdf6e9] p-4 mb-8 flex gap-3">
        <span class="material-symbols-outlined text-[#9a5c11]">shield_person</span>
        <div class="text-sm text-[#5c4415]">
            <p class="font-bold mb-1">Confirm identity before linking.</p>
            <p>
                The date of birth and mobile number below are what the applicant typed &mdash; they are a claim,
                not proof. Call them or check ID in person first. Linking gives that login permanent access to
                the patient's full medical history.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 max-w-md mb-8">
        <div class="bg-white rounded-xl border border-[#dfe4e1] p-4"><p class="text-xs font-bold uppercase tracking-wider text-[#6d7a77]">Awaiting review</p><p id="statPending" class="text-3xl font-bold mt-1">0</p></div>
        <div class="bg-white rounded-xl border border-[#dfe4e1] p-4"><p class="text-xs font-bold uppercase tracking-wider text-[#6d7a77]">Linked accounts</p><p id="statLinked" class="text-3xl font-bold mt-1">0</p></div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-[#dfe4e1] p-6">
            <h3 class="font-bold mb-4">Signups awaiting review</h3>
            <div id="pendingList" class="space-y-2"><p class="text-sm text-[#6d7a77]">Loading...</p></div>
        </div>

        <div class="bg-white rounded-xl border border-[#dfe4e1] p-6">
            <h3 class="font-bold mb-1">Matching clinic records</h3>
            <p id="matchSubtitle" class="text-sm text-[#6d7a77] mb-4">Select a signup on the left to see possible matches.</p>

            <div id="searchBox" class="mb-4 hidden">
                <label class="text-xs font-bold uppercase tracking-wider text-[#6d7a77]" for="patientSearch">No match? Search all patients</label>
                <input id="patientSearch" class="input mt-1" placeholder="Name, phone, or email">
            </div>

            <div id="candidateList" class="space-y-2"></div>

            <div id="rejectBox" class="mt-4 pt-4 border-t border-[#dfe4e1] hidden">
                <button type="button" id="rejectBtn"
                    class="rounded-lg px-3 py-2 text-xs font-bold text-[#a32020] hover:bg-[#fde8e8]"
                    <?php echo $canWritePortalAccounts ? '' : 'disabled'; ?>>Reject this signup</button>
                <p class="text-xs text-[#6d7a77] mt-1">Use when no clinic record belongs to this person, or their identity cannot be confirmed.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-[#dfe4e1] p-6">
        <h3 class="font-bold mb-4">Linked portal accounts</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-[#6d7a77] border-b border-[#dfe4e1]">
                        <th class="py-2 pr-3">Account</th>
                        <th class="py-2 pr-3">Linked to patient</th>
                        <th class="py-2 pr-3">Date of birth</th>
                        <th class="py-2 pr-3">Linked</th>
                        <th class="py-2 pr-3">By</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody id="linkedBody"></tbody>
            </table>
        </div>
    </div>
</main>

<script>
const canWrite = <?php echo $canWritePortalAccounts ? 'true' : 'false'; ?>;
let selectedUser = null;

const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
));

// For DATE columns (claimedDob, dateOfBirth). These arrive as 'YYYY-MM-DD' and must be printed
// verbatim: new Date('1990-05-12') is parsed as UTC midnight, so toLocaleDateString() would show
// the previous day to any reviewer west of UTC - on the very field used to confirm identity.
const fmtDay = (v) => (v ? esc(v) : '&mdash;');

// For DATETIME columns (created_at, linkedAt), where local time is what the reviewer wants.
const fmtDate = (v) => {
    if (!v) return '&mdash;';
    const d = new Date(v);
    return isNaN(d) ? esc(v) : d.toLocaleString();
};

async function api(params) {
    const res = await fetch('Portal Accounts.php?api=' + params);
    if (!res.ok) throw new Error('Request failed');
    return res.json();
}

async function post(body) {
    const res = await fetch('Portal Accounts.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(body)
    });
    return res.json();
}

async function loadStats() {
    const s = await api('get_stats');
    document.getElementById('statPending').textContent = s.pending;
    document.getElementById('statLinked').textContent = s.linked;
}

async function loadPending() {
    const rows = await api('get_pending');
    const box = document.getElementById('pendingList');

    if (!rows.length) {
        box.innerHTML = '<p class="text-sm text-[#6d7a77]">No signups are waiting for review.</p>';
        return;
    }

    box.innerHTML = rows.map((r) => `
        <button type="button" data-user="${r.id}"
            class="pending-item w-full text-left rounded-lg border border-[#dfe4e1] p-3 hover:border-[#00685d] transition-colors">
            <p class="font-bold text-sm">${esc(r.full_name)}
                ${r.role === 'patient_rejected' ? '<span class="ml-1 rounded bg-[#fde8e8] px-1.5 py-0.5 text-[10px] font-bold text-[#a32020]">REJECTED</span>' : ''}</p>
            <p class="text-xs text-[#6d7a77]">${esc(r.email)}</p>
            <p class="text-xs text-[#3d4946] mt-1">
                Claims DOB <strong>${fmtDay(r.claimedDob)}</strong> &middot; mobile <strong>${esc(r.claimedPhone)}</strong>
            </p>
            <p class="text-xs text-[#6d7a77] mt-1">Signed up ${fmtDate(r.created_at)}</p>
        </button>
    `).join('');

    box.querySelectorAll('.pending-item').forEach((el) => {
        el.addEventListener('click', () => selectUser(rows.find((r) => String(r.id) === el.dataset.user)));
    });
}

function renderCandidates(rows) {
    const box = document.getElementById('candidateList');

    if (!rows.length) {
        box.innerHTML = '<p class="text-sm text-[#6d7a77]">No clinic record matched this signup. Search below, or reject it.</p>';
        return;
    }

    box.innerHTML = rows.map((p) => {
        const linked = Number(p.alreadyLinked) === 1;
        const name = [p.lastName, p.firstName].filter(Boolean).join(', ');
        return `
        <div class="rounded-lg border border-[#dfe4e1] p-3 flex items-start justify-between gap-3">
            <div>
                <p class="font-bold text-sm">${esc(name)} ${esc(p.middleName || '')}</p>
                <p class="text-xs text-[#3d4946]">DOB ${fmtDay(p.dateOfBirth)} &middot; ${esc(p.gender || '')}</p>
                <p class="text-xs text-[#6d7a77]">${esc(p.phone || 'no phone')} &middot; ${esc(p.email || 'no email')}</p>
                ${linked ? '<p class="text-xs font-bold text-[#a32020] mt-1">Already linked to another account</p>' : ''}
            </div>
            <button type="button" data-patient="${p.id}" data-name="${esc(name)}" data-dob="${esc(p.dateOfBirth || '')}"
                class="link-btn shrink-0 rounded-lg px-3 py-2 text-xs font-bold ${linked || !canWrite ? 'bg-[#dfe4e1] text-[#6d7a77] cursor-not-allowed' : 'bg-[#00685d] text-white'}"
                ${linked || !canWrite ? 'disabled' : ''}>Link</button>
        </div>`;
    }).join('');

    box.querySelectorAll('.link-btn:not([disabled])').forEach((el) => {
        el.addEventListener('click', () => approve(el.dataset.patient, el.dataset.name, el.dataset.dob));
    });
}

async function selectUser(user) {
    if (!user) return;
    selectedUser = user;

    document.getElementById('matchSubtitle').innerHTML =
        `Possible records for <strong>${esc(user.full_name)}</strong>, who claims DOB ` +
        `<strong>${fmtDay(user.claimedDob)}</strong> and mobile <strong>${esc(user.claimedPhone)}</strong>.`;
    document.getElementById('searchBox').classList.remove('hidden');
    // Reject only applies to a signup still awaiting review.
    document.getElementById('rejectBox').classList.toggle('hidden', user.role === 'patient_rejected');
    document.getElementById('candidateList').innerHTML = '<p class="text-sm text-[#6d7a77]">Loading...</p>';

    document.querySelectorAll('.pending-item').forEach((el) => {
        el.classList.toggle('border-[#00685d]', el.dataset.user === String(user.id));
        el.classList.toggle('bg-[#f0f7f5]', el.dataset.user === String(user.id));
    });

    try {
        renderCandidates(await api('get_candidates&userId=' + encodeURIComponent(user.id)));
    } catch (e) {
        showError('Could not load matching records.');
    }
}

async function approve(patientId, patientName, dob) {
    if (!selectedUser) return;

    const confirmed = await confirmAction(
        `Link the portal account "${selectedUser.full_name}" (${selectedUser.email}) to the clinic record ` +
        `for ${patientName}, date of birth ${dob || 'unknown'} (they claimed ${selectedUser.claimedDob || 'unknown'})?\n\n` +
        `This grants permanent access to that patient's full medical history. Only continue if you have ` +
        `confirmed their identity by phone or in person.`
    );
    if (!confirmed) return;

    const r = await post({ action: 'approve', userId: selectedUser.id, patientId: patientId });
    if (!r.success) { showError(r.message); return; }

    showSuccess(r.message);
    resetSelection();
    refresh();
}

async function reject() {
    if (!selectedUser) return;
    if (!(await confirmAction(`Reject the signup from ${selectedUser.full_name} (${selectedUser.email})?`))) return;

    const r = await post({ action: 'reject', userId: selectedUser.id });
    if (!r.success) { showError(r.message); return; }

    showSuccess(r.message);
    resetSelection();
    refresh();
}

async function unlink(userId, label) {
    if (!(await confirmAction(
        `Unlink ${label}?\n\nThey will immediately lose access to all records on their next page load. ` +
        `Use this if the wrong record was linked.`
    ))) return;

    const r = await post({ action: 'unlink', userId: userId });
    if (!r.success) { showError(r.message); return; }

    showSuccess(r.message);
    refresh();
}

function resetSelection() {
    selectedUser = null;
    document.getElementById('matchSubtitle').textContent = 'Select a signup on the left to see possible matches.';
    document.getElementById('candidateList').innerHTML = '';
    document.getElementById('searchBox').classList.add('hidden');
    document.getElementById('rejectBox').classList.add('hidden');
    document.getElementById('patientSearch').value = '';
}

async function loadLinked() {
    const rows = await api('get_linked');
    const body = document.getElementById('linkedBody');

    if (!rows.length) {
        body.innerHTML = '<tr><td colspan="6" class="py-4 text-sm text-[#6d7a77]">No portal accounts are linked yet.</td></tr>';
        return;
    }

    body.innerHTML = rows.map((r) => `
        <tr class="border-b border-[#eef2f0]">
            <td class="py-2 pr-3"><p class="font-bold">${esc(r.full_name)}</p><p class="text-xs text-[#6d7a77]">${esc(r.email)}</p></td>
            <td class="py-2 pr-3">${r.patientId
                ? esc([r.lastName, r.firstName].filter(Boolean).join(', '))
                : '<span class="font-bold text-[#a32020]">patient record deleted</span>'}</td>
            <td class="py-2 pr-3">${fmtDay(r.dateOfBirth)}</td>
            <td class="py-2 pr-3">${fmtDate(r.linkedAt)}</td>
            <td class="py-2 pr-3">${r.linkedByName ? esc(r.linkedByName) : '&mdash;'}</td>
            <td class="py-2 text-right">
                ${canWrite ? `<button type="button" data-user="${r.id}" data-label="${esc(r.full_name)}"
                    class="unlink-btn rounded-lg px-3 py-1.5 text-xs font-bold text-[#a32020] hover:bg-[#fde8e8]">Unlink</button>` : ''}
            </td>
        </tr>
    `).join('');

    body.querySelectorAll('.unlink-btn').forEach((el) => {
        el.addEventListener('click', () => unlink(el.dataset.user, el.dataset.label));
    });
}

document.getElementById('rejectBtn').addEventListener('click', reject);

let searchTimer = null;
document.getElementById('patientSearch').addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    const term = e.target.value.trim();
    searchTimer = setTimeout(async () => {
        if (term.length < 2) return;
        try {
            renderCandidates(await api('search_patients&q=' + encodeURIComponent(term)));
        } catch (err) {
            showError('Search failed.');
        }
    }, 300);
});

function refresh() {
    loadStats();
    loadPending();
    loadLinked();
}

refresh();
</script>
</body>
</html>
