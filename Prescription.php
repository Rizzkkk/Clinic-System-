<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
// Backward-compat: this page's data API now lives in backend/api/prescriptions.php.
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/prescriptions.php';
}

require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('prescriptions');
$canWriteRx = can_access('prescriptions', 'write');

// --- Server-rendered table data below (uses $conn from bootstrap) ---
function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$prescriptionRows = [];
$prescriptionResult = $conn->query('
    SELECT p.id, p.patientId, p.doctorId, p.medicationName, p.dosage, p.frequency, p.duration, p.prescriptionDate, p.expiryDate, p.status, p.notes,
           pt.firstName as patientFirstName, pt.lastName as patientLastName,
           d.firstName as doctorFirstName, d.lastName as doctorLastName
    FROM prescriptions p
    JOIN patients pt ON p.patientId = pt.id
    JOIN doctors d ON p.doctorId = d.id
    ORDER BY p.prescriptionDate DESC, p.id DESC
');
if ($prescriptionResult) {
    while ($row = $prescriptionResult->fetch_assoc()) {
        $prescriptionRows[] = $row;
    }
}
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Prescription Management - MedLab Pro</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container": "#eaefec",
                        "on-primary-fixed": "#00201c",
                        "secondary-fixed-dim": "#a1cbf0",
                        "on-error": "#ffffff",
                        "inverse-primary": "#72d8c8",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e4e9e7",
                        "on-primary-fixed-variant": "#005047",
                        "on-primary": "#ffffff",
                        "surface-tint": "#006b5f",
                        "background": "#f6faf8",
                        "tertiary-fixed-dim": "#c3c7cb",
                        "secondary": "#376283",
                        "on-secondary-fixed": "#001e30",
                        "secondary-fixed": "#cbe6ff",
                        "secondary-container": "#aed9ff",
                        "primary-container": "#008376",
                        "surface-bright": "#f6faf8",
                        "on-secondary-container": "#345f80",
                        "surface": "#f6faf8",
                        "on-tertiary-fixed": "#171c1f",
                        "on-background": "#171d1b",
                        "surface-container-highest": "#dfe4e1",
                        "inverse-on-surface": "#edf2ef",
                        "on-tertiary-container": "#fbfcff",
                        "on-tertiary-fixed-variant": "#43474b",
                        "tertiary-fixed": "#dfe3e7",
                        "on-secondary-fixed-variant": "#1c4a6a",
                        "inverse-surface": "#2c3130",
                        "tertiary": "#585d60",
                        "surface-variant": "#dfe4e1",
                        "outline": "#6d7a77",
                        "error": "#ba1a1a",
                        "primary-fixed-dim": "#72d8c8",
                        "outline-variant": "#bdc9c5",
                        "primary-fixed": "#8ff4e3",
                        "on-surface-variant": "#3d4946",
                        "on-error-container": "#93000a",
                        "on-tertiary": "#ffffff",
                        "on-secondary": "#ffffff",
                        "tertiary-container": "#707579",
                        "surface-dim": "#d6dbd9",
                        "error-container": "#ffdad6",
                        "primary": "#00685d",
                        "on-primary-container": "#f4fffb",
                        "surface-container-low": "#f0f5f2"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "stack-md": "16px",
                        "gutter": "20px",
                        "stack-sm": "8px",
                        "container-padding": "24px",
                        "sidebar-width": "260px",
                        "card-gap": "16px"
                    },
                    "fontFamily": {
                        "body-sm": ["Inter"],
                        "label-caps": ["Inter"],
                        "label-bold": ["Inter"],
                        "body-md": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg": ["Manrope"],
                        "headline-md": ["Manrope"]
                    },
                    "fontSize": {
                        "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.08em", "fontWeight": "600"}],
                        "label-bold": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                        "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}]
                    }
                }
            }
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .sidebar-active {
            opacity: 1;
        }
        body {
            background-color: #f6faf8;
        }
    </style>
</head>
<body class="font-body-md text-on-background">
<!-- SideNavBar Component -->
<?php $active = 'prescriptions'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>
<!-- TopAppBar Component -->
<header class="fixed top-0 left-[260px] right-0 h-16 bg-surface-bright border-b border-outline-variant flex items-center justify-between px-6 z-40 shadow-sm">
<div class="flex items-center flex-1 max-w-xl">
<div class="relative w-full">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
<input class="w-full bg-surface-container-low border-none rounded-full py-2 pl-10 pr-4 focus:ring-2 focus:ring-primary text-body-md" placeholder="Search patient, medication, or order ID..." type="text"/>
</div>
</div>
<div class="flex items-center gap-4">
<button class="hover:bg-surface-container-low rounded-full p-2 transition-all">
<span class="material-symbols-outlined text-primary">notifications</span>
</button>


</div>
</div>
</header>
<!-- Main Content Canvas -->
<main class="ml-[260px] mt-16 p-6 min-h-screen">
<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
<div>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Prescriptions</h2>
<p class="text-body-sm text-on-surface-variant mt-1">Create and review patient prescriptions.</p>
</div>
</div>
<!-- Summary Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
<div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="p-2 bg-primary/10 rounded-lg text-primary"><span class="material-symbols-outlined">medication</span></div>
<span id="stat-total" class="text-headline-md font-bold text-on-surface">0</span>
</div>
<p class="text-on-surface-variant font-label-bold">TOTAL PRESCRIPTIONS</p>
</div>
<div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="p-2 bg-primary/10 rounded-lg text-primary"><span class="material-symbols-outlined">check_circle</span></div>
<span id="stat-active" class="text-headline-md font-bold text-on-surface">0</span>
</div>
<p class="text-on-surface-variant font-label-bold">ACTIVE</p>
</div>
<div class="bg-white border border-outline-variant rounded-xl p-5 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="p-2 bg-error-container/30 rounded-lg text-error"><span class="material-symbols-outlined">schedule</span></div>
<span id="stat-expired" class="text-headline-md font-bold text-on-surface">0</span>
</div>
<p class="text-on-surface-variant font-label-bold">EXPIRED</p>
</div>
</div>
<div class="grid grid-cols-12 gap-6 items-start">
<!-- Left: Prescription Table -->
<div class="col-span-12 lg:col-span-8 space-y-6">
<div class="bg-white border border-outline-variant rounded-xl overflow-hidden shadow-sm">
<div class="px-6 py-4 border-b border-outline-variant bg-surface-container-low flex items-center justify-between">
<h3 class="font-headline-md text-headline-md">Current Prescriptions</h3>
<button class="p-2 hover:bg-surface-container text-on-surface-variant rounded-lg" onclick="window.print()"><span class="material-symbols-outlined">print</span></button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low/50">
<th class="px-6 py-3 text-label-caps text-on-surface-variant border-b border-outline-variant">MEDICATION</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant border-b border-outline-variant">PATIENT</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant border-b border-outline-variant">DOSAGE</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant border-b border-outline-variant">FREQUENCY</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant border-b border-outline-variant">PRESCRIBED BY</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant border-b border-outline-variant text-center">STATUS</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant border-b border-outline-variant text-right">ACTIONS</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/30">
<?php if (empty($prescriptionRows)): ?>
<tr><td colspan="7" class="px-6 py-10 text-center text-body-sm text-on-surface-variant">No prescriptions found.</td></tr>
<?php else: ?>
<?php foreach ($prescriptionRows as $prescription): ?>
<?php
$isExpired = !empty($prescription['expiryDate']) && $prescription['expiryDate'] < date('Y-m-d');
$status = $isExpired ? 'Expired' : ($prescription['status'] ?: 'Active');
$statusClass = $isExpired ? 'bg-error-container text-on-error-container' : 'bg-primary-fixed text-on-primary-fixed';
?>
<tr class="hover:bg-surface-container-low/30 transition-colors">
<td class="px-6 py-4 font-bold text-on-surface"><?php echo h($prescription['medicationName']); ?></td>
<td class="px-6 py-4 text-on-surface"><?php echo h($prescription['patientFirstName'] . ' ' . $prescription['patientLastName']); ?></td>
<td class="px-6 py-4 text-on-surface"><?php echo h($prescription['dosage']); ?></td>
<td class="px-6 py-4 text-on-surface"><?php echo h($prescription['frequency']); ?></td>
<td class="px-6 py-4 text-on-surface"><?php echo h('Dr. ' . $prescription['doctorFirstName'] . ' ' . $prescription['doctorLastName']); ?></td>
<td class="px-6 py-4 text-center"><span class="inline-block px-3 py-1 rounded-full <?php echo $statusClass; ?> text-label-bold uppercase"><?php echo h($status); ?></span></td>
<td class="px-6 py-4 text-right whitespace-nowrap">
<a target="_blank" rel="noopener" href="backend/api/prescriptions.php?api=prescription_pdf&amp;id=<?php echo (int)$prescription['id']; ?>" class="inline-flex text-primary hover:bg-primary/10 rounded-lg p-1.5 align-middle" title="Print prescription (PDF)"><span class="material-symbols-outlined">picture_as_pdf</span></a>
<?php if ($canWriteRx): ?><button onclick="rxStartEdit(<?php echo (int)$prescription['id']; ?>)" class="text-primary hover:bg-primary/10 rounded-lg p-1.5 align-middle" title="Edit"><span class="material-symbols-outlined">edit</span></button><button onclick="rxDelete(<?php echo (int)$prescription['id']; ?>)" class="text-error hover:bg-error/10 rounded-lg p-1.5 align-middle" title="Delete"><span class="material-symbols-outlined">delete</span></button><?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
<!-- Right: New Prescription (write access only) -->
<?php if ($canWriteRx): ?>
<div class="col-span-12 lg:col-span-4 space-y-6">
<div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm sticky top-24">
<h3 class="font-headline-md text-headline-md mb-6">New Prescription</h3>
<form id="rx-form" class="space-y-4">
<div>
<label class="block text-label-bold text-on-surface-variant mb-1.5 uppercase">Patient</label>
<select id="rx-patient" name="patientId" required class="w-full bg-white border border-outline-variant rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-primary text-body-md"><option value="">Select patient</option></select>
</div>
<div>
<label class="block text-label-bold text-on-surface-variant mb-1.5 uppercase">Prescribing Doctor</label>
<select id="rx-doctor" name="doctorId" required class="w-full bg-white border border-outline-variant rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-primary text-body-md"><option value="">Select doctor</option></select>
</div>
<div>
<label class="block text-label-bold text-on-surface-variant mb-1.5 uppercase">Medication</label>
<input name="medicationName" required placeholder="e.g. Amoxicillin" class="w-full bg-white border border-outline-variant rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-primary text-body-md"/>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-label-bold text-on-surface-variant mb-1.5 uppercase">Dosage</label>
<input name="dosage" placeholder="e.g. 250mg" class="w-full bg-white border border-outline-variant rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-primary text-body-md"/>
</div>
<div>
<label class="block text-label-bold text-on-surface-variant mb-1.5 uppercase">Frequency</label>
<select name="frequency" class="w-full bg-white border border-outline-variant rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-primary text-body-md">
<option value="">Select</option><option>Daily</option><option>BID (2x/day)</option><option>TID (3x/day)</option><option>QID (4x/day)</option><option>As needed</option>
</select>
</div>
</div>
<div>
<label class="block text-label-bold text-on-surface-variant mb-1.5 uppercase">Duration</label>
<input name="duration" placeholder="e.g. 10 Days" class="w-full bg-white border border-outline-variant rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-primary text-body-md"/>
</div>
<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-label-bold text-on-surface-variant mb-1.5 uppercase">Prescribed</label>
<input name="prescriptionDate" type="date" required class="w-full bg-white border border-outline-variant rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-primary text-body-md"/>
</div>
<div>
<label class="block text-label-bold text-on-surface-variant mb-1.5 uppercase">Expires</label>
<input name="expiryDate" type="date" class="w-full bg-white border border-outline-variant rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-primary text-body-md"/>
</div>
</div>
<div>
<label class="block text-label-bold text-on-surface-variant mb-1.5 uppercase">Notes</label>
<textarea name="notes" rows="2" placeholder="Additional instructions..." class="w-full bg-white border border-outline-variant rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-primary text-body-md"></textarea>
</div>
<button type="submit" class="w-full bg-primary text-on-primary font-label-bold py-3 rounded-lg hover:opacity-90 transition-all shadow-md">CREATE PRESCRIPTION</button>
<p id="rx-msg" class="text-sm text-center"></p>
</form>
</div>
</div>
<?php endif; ?>
</div>
</main>
<script>
    const RX_API = 'backend/api/prescriptions.php';
    const RX_ROWS = <?php echo json_encode($prescriptionRows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    let rxEditingId = null;
    function rxStartEdit(id) {
        const r = RX_ROWS.find(x => Number(x.id) === Number(id));
        const f = document.getElementById('rx-form');
        if (!r || !f) return;
        rxEditingId = id;
        ['patientId','doctorId','medicationName','dosage','frequency','duration','prescriptionDate','expiryDate','notes'].forEach(k => {
            const el = f.elements[k]; if (el) el.value = (r[k] == null) ? '' : r[k];
        });
        const b = f.querySelector('button[type="submit"]'); if (b) b.textContent = 'UPDATE PRESCRIPTION';
        const m = document.getElementById('rx-msg'); if (m) { m.textContent = 'Editing prescription #' + id + ' - submit to save, or reload to cancel'; m.className = 'text-sm text-center text-on-surface-variant'; }
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    async function rxLoadStats() {
        const s = await fetch(RX_API + '?api=get_stats').then(r => r.json()).catch(() => ({}));
        document.getElementById('stat-total').textContent = s.total ?? 0;
        document.getElementById('stat-active').textContent = s.active ?? 0;
        document.getElementById('stat-expired').textContent = s.expired ?? 0;
    }
    async function rxLoadPatients() {
        const sel = document.getElementById('rx-patient');
        if (!sel) return;
        const list = await fetch('backend/api/patients.php?api=get_patients').then(r => r.ok ? r.json() : []).catch(() => []);
        list.forEach(p => { const o = document.createElement('option'); o.value = p.id; o.textContent = p.firstName + ' ' + p.lastName; sel.appendChild(o); });
    }
    async function rxLoadDoctors() {
        const sel = document.getElementById('rx-doctor');
        if (!sel) return;
        const list = await fetch('backend/api/doctors.php?api=get_doctors').then(r => r.ok ? r.json() : []).catch(() => []);
        list.forEach(d => { const o = document.createElement('option'); o.value = d.id; o.textContent = 'Dr. ' + d.firstName + ' ' + d.lastName; sel.appendChild(o); });
    }
    const rxForm = document.getElementById('rx-form');
    if (rxForm) {
        rxForm.addEventListener('submit', async e => {
            e.preventDefault();
            const msg = document.getElementById('rx-msg');
            msg.textContent = 'Saving...'; msg.className = 'text-sm text-center text-on-surface-variant';
            const fd = new FormData(rxForm);
            fd.append('action', rxEditingId ? 'update_prescription' : 'add_prescription');
            if (rxEditingId) fd.append('id', rxEditingId);
            const res = await fetch(RX_API, { method: 'POST', body: fd }).then(r => r.json()).catch(() => ({ success: false, message: 'Network error' }));
            if (res.success) { location.reload(); }
            else { msg.textContent = ''; window.showError(res.message || 'Failed to save'); }
        });
    }
    async function rxDelete(id) {
        if (!(await window.confirmAction('Delete this prescription?'))) return;
        const fd = new FormData();
        fd.append('action', 'delete_prescription'); fd.append('id', id);
        const res = await fetch(RX_API, { method: 'POST', body: fd }).then(r => r.json()).catch(() => ({ success: false }));
        if (res.success) location.reload(); else showError(res.message || 'Delete failed');
    }
    rxLoadStats(); rxLoadPatients(); rxLoadDoctors();
</script>
</body></html>
