<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
// Backward-compat: this page's data API now lives in backend/api/laboratory_results.php.
// Delegate ?api= / POST action requests there so existing callers keep working.
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/laboratory_results.php';
}

require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('laboratory_results');
$canWriteLab = can_access('laboratory_results', 'write');

// Table data rendered server-side below, using $conn from bootstrap.
function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$laboratoryRows = [];
$labIndex = [];
$laboratoryResult = $conn->query('
    SELECT lr.id, lr.patientId, lr.orderedBy, lr.testDate, lr.remarks,
           p.firstName as patientFirstName, p.lastName as patientLastName,
           d.firstName as doctorFirstName, d.lastName as doctorLastName
    FROM laboratory_results lr
    JOIN patients p ON lr.patientId = p.id
    LEFT JOIN doctors d ON lr.orderedBy = d.id
    ORDER BY lr.testDate DESC, lr.id DESC
');
if ($laboratoryResult) {
    while ($row = $laboratoryResult->fetch_assoc()) {
        $row['items'] = [];
        $laboratoryRows[] = $row;
        $labIndex[(int)$row['id']] = count($laboratoryRows) - 1;
    }
}
// Attach each order's individual tests (one order -> many tests).
if ($laboratoryRows) {
    $labItemsResult = $conn->query('SELECT resultId, testType, results, referenceRange, abnormalFlag FROM laboratory_result_items ORDER BY id');
    if ($labItemsResult) {
        while ($it = $labItemsResult->fetch_assoc()) {
            $rid = (int)$it['resultId'];
            if (isset($labIndex[$rid])) { $laboratoryRows[$labIndex[$rid]]['items'][] = $it; }
        }
    }
}
?>
<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>MedLab Pro | Laboratory Results Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Manrope:wght@600;700;800&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
<script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "on-tertiary": "#ffffff",
                      "surface-dim": "#d6dbd9",
                      "surface": "#f6faf8",
                      "surface-variant": "#dfe4e1",
                      "on-tertiary-fixed": "#171c1f",
                      "surface-bright": "#f6faf8",
                      "secondary-container": "#aed9ff",
                      "outline": "#6d7a77",
                      "on-background": "#171d1b",
                      "surface-container-lowest": "#ffffff",
                      "tertiary-fixed-dim": "#c3c7cb",
                      "surface-tint": "#006b5f",
                      "primary-fixed-dim": "#72d8c8",
                      "secondary-fixed": "#cbe6ff",
                      "secondary": "#376283",
                      "on-tertiary-fixed-variant": "#43474b",
                      "on-error-container": "#93000a",
                      "on-surface": "#171d1b",
                      "tertiary": "#585d60",
                      "secondary-fixed-dim": "#a1cbf0",
                      "on-surface-variant": "#3d4946",
                      "inverse-on-surface": "#edf2ef",
                      "inverse-primary": "#72d8c8",
                      "background": "#f6faf8",
                      "surface-container-low": "#f0f5f2",
                      "on-error": "#ffffff",
                      "surface-container": "#eaefec",
                      "on-primary-fixed": "#00201c",
                      "outline-variant": "#bdc9c5",
                      "on-tertiary-container": "#fbfcff",
                      "tertiary-container": "#707579",
                      "tertiary-fixed": "#dfe3e7",
                      "primary": "#00685d",
                      "primary-fixed": "#8ff4e3",
                      "surface-container-high": "#e4e9e7",
                      "on-secondary-fixed": "#001e30",
                      "on-primary-fixed-variant": "#005047",
                      "primary-container": "#008376",
                      "surface-container-highest": "#dfe4e1",
                      "on-secondary-fixed-variant": "#1c4a6a",
                      "on-primary": "#ffffff",
                      "on-primary-container": "#f4fffb",
                      "inverse-surface": "#2c3130",
                      "error-container": "#ffdad6",
                      "error": "#ba1a1a",
                      "on-secondary-container": "#345f80",
                      "on-secondary": "#ffffff"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "spacing": {
                      "sidebar-width": "260px",
                      "card-gap": "16px",
                      "container-padding": "24px",
                      "stack-sm": "8px",
                      "stack-md": "16px",
                      "gutter": "20px"
              },
              "fontFamily": {
                      "headline-lg": ["Manrope"],
                      "label-bold": ["Inter"],
                      "body-sm": ["Inter"],
                      "headline-md": ["Manrope"],
                      "body-lg": ["Inter"],
                      "label-caps": ["Inter"],
                      "body-md": ["Inter"]
              },
              "fontSize": {
                      "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                      "label-bold": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                      "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                      "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                      "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                      "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.08em", "fontWeight": "600"}],
                      "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}]
              }
            },
          },
        }
      </script>
</head>
<body class="bg-background text-on-surface font-body-md overflow-hidden">
<!-- Fixed Sidebar -->
<?php $active = 'laboratory_results'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>
<!-- Main Content Area -->
<main class="ml-sidebar-width flex flex-col h-screen overflow-hidden">
<!-- TopAppBar -->
<header class="h-16 bg-surface border-b border-outline-variant flex items-center justify-between px-container-padding shrink-0 z-40">
<div class="flex items-center flex-1 max-w-xl">
<div class="relative w-full">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
<input class="w-full bg-surface-container-low border border-outline-variant rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-primary focus:border-primary transition-all text-body-md outline-none" placeholder="Search Patient Name, MRN, or Order ID..." type="text"/>
</div>
</div>
<div class="flex items-center space-x-4">
<div class="flex items-center space-x-2 border-l border-outline-variant pl-4">
<button class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:bg-surface-container-high transition-colors relative">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
</button>
</div>
</div>
</header>
<!-- Scrollable Dashboard Body -->
<div class="flex-1 overflow-y-auto bg-background p-container-padding scrollbar-hide">
<!-- Page Header & Global Controls -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
<div>
<h2 class="text-headline-lg font-headline-lg text-on-surface">Laboratory Results</h2>
<p class="text-body-md text-on-surface-variant mt-1">Review and verify clinical diagnostic findings.</p>
</div>
<?php if ($canWriteLab): ?>
<button type="button" onclick="document.getElementById('lab-form-card').classList.toggle('hidden')" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-label-bold text-label-bold hover:bg-primary-container shadow-sm flex items-center space-x-2">
<span class="material-symbols-outlined text-[20px]">add</span><span>Add Result</span>
</button>
<?php endif; ?>
</div>
<!-- Bento Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-8">
<div class="bg-white border border-outline-variant p-container-padding rounded-xl shadow-sm">
<div class="flex items-center justify-between mb-2"><span class="text-label-bold text-on-surface-variant uppercase">Total Results</span><div class="w-8 h-8 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary"><span class="material-symbols-outlined text-[20px]">science</span></div></div>
<div id="lab-stat-total" class="text-[28px] font-bold text-on-surface">0</div>
</div>
<div class="bg-white border border-outline-variant p-container-padding rounded-xl shadow-sm border-l-4 border-l-error">
<div class="flex items-center justify-between mb-2"><span class="text-label-bold text-error uppercase">Abnormal Flags</span><div class="w-8 h-8 rounded-lg bg-error-container/30 flex items-center justify-center text-error"><span class="material-symbols-outlined text-[20px]">warning</span></div></div>
<div id="lab-stat-abnormal" class="text-[28px] font-bold text-on-surface">0</div>
<div class="text-body-sm text-on-surface-variant mt-1">Requiring review</div>
</div>
<div class="bg-white border border-outline-variant p-container-padding rounded-xl shadow-sm">
<div class="flex items-center justify-between mb-2"><span class="text-label-bold text-on-surface-variant uppercase">Last 30 Days</span><div class="w-8 h-8 rounded-lg bg-secondary-container/30 flex items-center justify-center text-secondary"><span class="material-symbols-outlined text-[20px]">calendar_today</span></div></div>
<div id="lab-stat-recent" class="text-[28px] font-bold text-on-surface">0</div>
</div>
</div>
<?php if ($canWriteLab): ?>
<!-- Add Result form (write access only) -->
<div id="lab-form-card" class="bg-white border border-outline-variant rounded-xl shadow-sm p-container-padding mb-8 hidden">
<h3 class="text-headline-md font-headline-md mb-4">Add Laboratory Result</h3>
<form id="lab-form" class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div><label class="block text-label-bold text-on-surface-variant mb-1 uppercase">Patient</label>
<select id="lab-patient" name="patientId" required class="w-full bg-white border border-outline-variant rounded-lg py-2 px-3 text-body-md focus:ring-2 focus:ring-primary outline-none"><option value="">Select patient</option></select></div>
<div><label class="block text-label-bold text-on-surface-variant mb-1 uppercase">Ordered By (Doctor)</label>
<select id="lab-doctor" name="orderedBy" class="w-full bg-white border border-outline-variant rounded-lg py-2 px-3 text-body-md focus:ring-2 focus:ring-primary outline-none"><option value="">Not assigned</option></select></div>
<div><label class="block text-label-bold text-on-surface-variant mb-1 uppercase">Test Date</label>
<input name="testDate" type="date" required class="w-full bg-white border border-outline-variant rounded-lg py-2 px-3 text-body-md focus:ring-2 focus:ring-primary outline-none"/></div>
<!-- Tests: one row per test, each with its own value, reference range, and Normal/Abnormal flag -->
<div class="md:col-span-3">
<div class="flex items-center justify-between mb-2">
<label class="block text-label-bold text-on-surface-variant uppercase">Tests</label>
<button type="button" onclick="labAddTestRow()" class="inline-flex items-center gap-1 text-primary text-label-bold hover:text-primary-container"><span class="material-symbols-outlined text-[18px]">add</span>Add test</button>
</div>
<div id="lab-tests" class="space-y-2"></div>
<datalist id="labTests"><option value="HbA1c"></option><option value="CBC"></option><option value="FBS"></option><option value="Lipid Panel"></option><option value="Urinalysis"></option><option value="Creatinine"></option><option value="TSH"></option><option value="SGPT/ALT"></option><option value="Blood Typing"></option></datalist>
</div>
<div class="md:col-span-3"><label class="block text-label-bold text-on-surface-variant mb-1 uppercase">Remarks</label>
<textarea name="remarks" rows="2" placeholder="Test-specific notes (e.g. fasting required, specimen notes)" class="w-full bg-white border border-outline-variant rounded-lg py-2 px-3 text-body-md focus:ring-2 focus:ring-primary outline-none"></textarea></div>
<div class="md:col-span-3 flex items-center gap-3">
<button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-bold hover:bg-primary-container shadow-sm">Save Result</button>
<span id="lab-msg" class="text-sm"></span>
</div>
</form>
</div>
<?php endif; ?>
<!-- Results Data Table Area -->
<div class="bg-white border border-outline-variant rounded-xl shadow-sm overflow-hidden flex flex-col lg:flex-row hover:shadow-md transition-shadow duration-300">
<!-- Main Table Section -->
<div class="flex-1 overflow-x-auto min-h-[600px]">
<table class="w-full text-left border-collapse">
<thead class="bg-surface-container-low border-b border-outline-variant">
<tr>
<th class="px-6 py-4"><input class="rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/></th>
<th class="px-4 py-4 text-label-bold text-on-surface-variant uppercase whitespace-nowrap">Order ID</th>
<th class="px-4 py-4 text-label-bold text-on-surface-variant uppercase whitespace-nowrap">Patient Details</th>
<th class="px-4 py-4 text-label-bold text-on-surface-variant uppercase whitespace-nowrap">Test Description</th>
<th class="px-4 py-4 text-label-bold text-on-surface-variant uppercase whitespace-nowrap text-center">Result / Units</th>
<th class="px-4 py-4 text-label-bold text-on-surface-variant uppercase whitespace-nowrap">Flag</th>
<th class="px-4 py-4 text-label-bold text-on-surface-variant uppercase whitespace-nowrap">Status</th>
<th class="px-4 py-4 text-label-bold text-on-surface-variant uppercase whitespace-nowrap text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-surface-container">
<?php if (empty($laboratoryRows)): ?>
<tr><td colspan="8" class="px-6 py-12 text-center text-body-sm text-on-surface-variant">No laboratory results found.</td></tr>
<?php else: ?>
<?php foreach ($laboratoryRows as $result): ?>
<?php
$items = $result['items'] ?? [];
$isAbnormal = false;
foreach ($items as $it) { if (strtoupper((string)$it['abnormalFlag']) === 'Y') { $isAbnormal = true; break; } }
$flagText = $isAbnormal ? 'Abnormal' : 'Normal';
$flagClass = $isAbnormal ? 'bg-error-container text-on-error-container' : 'bg-primary-fixed text-on-primary-fixed';
?>
<tr class="hover:bg-surface-container-low transition-colors">
<td class="px-6 py-4"><input class="rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/></td>
<td class="px-4 py-4 font-bold text-on-surface">LAB-<?php echo str_pad((string)$result['id'], 4, '0', STR_PAD_LEFT); ?></td>
<td class="px-4 py-4">
<div class="font-bold text-on-surface"><?php echo h($result['patientFirstName'] . ' ' . $result['patientLastName']); ?></div>
<div class="text-xs text-on-surface-variant">MRN: MR-<?php echo h($result['patientId']); ?> | <?php echo h($result['testDate']); ?></div>
</td>
<td class="px-4 py-4 text-body-sm text-on-surface-variant align-top">
<?php if (empty($items)): ?><div>Laboratory test</div><?php else: ?>
<?php foreach ($items as $it): ?><div class="leading-6 text-on-surface"><?php echo h($it['testType'] ?: 'Laboratory test'); ?></div><?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($result['remarks'])): ?><div class="text-xs text-on-surface-variant/70 mt-1"><?php echo h($result['remarks']); ?></div><?php endif; ?></td>
<td class="px-4 py-4 text-center align-top">
<?php if (empty($items)): ?><div class="font-bold text-on-surface leading-6">Pending</div><?php else: ?>
<?php foreach ($items as $it): ?><div class="leading-6"><span class="font-bold text-on-surface"><?php echo h($it['results'] ?: 'Pending'); ?></span><?php if (!empty($it['referenceRange'])): ?> <span class="text-xs text-on-surface-variant">(<?php echo h($it['referenceRange']); ?>)</span><?php endif; ?></div><?php endforeach; ?>
<?php endif; ?>
</td>
<td class="px-4 py-4"><span class="px-3 py-1 rounded-full <?php echo $flagClass; ?> text-label-bold uppercase"><?php echo h($flagText); ?></span></td>
<td class="px-4 py-4"><span class="px-3 py-1 rounded-full bg-surface-container-high text-on-surface-variant text-label-bold uppercase">Released</span></td>
<td class="px-4 py-4 text-right">
<a class="inline-flex p-2 rounded hover:bg-surface-container-high text-on-surface-variant" title="Download PDF report" target="_blank" href="backend/api/laboratory_results.php?api=lab_report_pdf&amp;id=<?php echo (int)$result['id']; ?>">
<span class="material-symbols-outlined">picture_as_pdf</span>
</a>
<?php if ($canWriteLab): ?><button onclick="labStartEdit(<?php echo (int)$result['id']; ?>)" class="p-2 rounded hover:bg-surface-container-high text-primary" title="Edit"><span class="material-symbols-outlined">edit</span></button><?php endif; ?>
<button class="p-2 rounded hover:bg-surface-container-high text-on-surface-variant" title="<?php echo h('Ordered by Dr. ' . trim(($result['doctorFirstName'] ?? '') . ' ' . ($result['doctorLastName'] ?? ''))); ?>">
<span class="material-symbols-outlined">visibility</span>
</button>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
</main>
<script>
    const LAB_API = 'backend/api/laboratory_results.php';
    const LAB_ROWS = <?php echo json_encode($laboratoryRows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    let labEditingId = null;
    // Append one test row to the repeater, optionally pre-filled (used on load and when editing).
    function labAddTestRow(values) {
        const wrap = document.getElementById('lab-tests'); if (!wrap) return;
        const v = values || {};
        const row = document.createElement('div');
        row.className = 'lab-test-row grid grid-cols-1 md:grid-cols-12 gap-2 items-center';
        row.innerHTML =
            '<input name="testType[]" list="labTests" placeholder="Test, e.g. HbA1c" class="md:col-span-4 w-full bg-white border border-outline-variant rounded-lg py-2 px-3 text-body-md focus:ring-2 focus:ring-primary outline-none"/>' +
            '<input name="results[]" placeholder="Value / result" class="md:col-span-3 w-full bg-white border border-outline-variant rounded-lg py-2 px-3 text-body-md focus:ring-2 focus:ring-primary outline-none"/>' +
            '<input name="referenceRange[]" placeholder="Ref. range" class="md:col-span-2 w-full bg-white border border-outline-variant rounded-lg py-2 px-3 text-body-md focus:ring-2 focus:ring-primary outline-none"/>' +
            '<select name="abnormalFlag[]" class="md:col-span-2 w-full bg-white border border-outline-variant rounded-lg py-2 px-3 text-body-md focus:ring-2 focus:ring-primary outline-none"><option value="N">Normal</option><option value="Y">Abnormal</option></select>' +
            '<button type="button" onclick="labRemoveTestRow(this)" title="Remove test" class="md:col-span-1 flex items-center justify-center p-2 rounded hover:bg-error-container text-on-surface-variant hover:text-on-error-container"><span class="material-symbols-outlined text-[20px]">delete</span></button>';
        wrap.appendChild(row);
        row.querySelector('[name="testType[]"]').value = v.testType || '';
        row.querySelector('[name="results[]"]').value = v.results || '';
        row.querySelector('[name="referenceRange[]"]').value = v.referenceRange || '';
        row.querySelector('[name="abnormalFlag[]"]').value = (String(v.abnormalFlag).toUpperCase() === 'Y') ? 'Y' : 'N';
    }
    function labRemoveTestRow(btn) {
        const wrap = document.getElementById('lab-tests');
        const rows = wrap.querySelectorAll('.lab-test-row');
        const row = btn.closest('.lab-test-row');
        if (rows.length <= 1) { // keep at least one row: clear it instead of removing
            row.querySelectorAll('input').forEach(i => i.value = '');
            const s = row.querySelector('select'); if (s) s.value = 'N';
            return;
        }
        row.remove();
    }
    function labResetTests() { const wrap = document.getElementById('lab-tests'); if (wrap) { wrap.innerHTML = ''; labAddTestRow(); } }
    function labStartEdit(id) {
        const r = LAB_ROWS.find(x => Number(x.id) === Number(id));
        const f = document.getElementById('lab-form');
        if (!r || !f) return;
        labEditingId = id;
        ['patientId','orderedBy','testDate','remarks'].forEach(k => {
            const el = f.elements[k]; if (el) el.value = (r[k] == null) ? '' : r[k];
        });
        const wrap = document.getElementById('lab-tests'); if (wrap) wrap.innerHTML = '';
        const items = r.items || [];
        if (items.length) { items.forEach(it => labAddTestRow(it)); } else { labAddTestRow(); }
        const b = f.querySelector('button[type="submit"]'); if (b) b.textContent = 'Update Result';
        const m = document.getElementById('lab-msg'); if (m) { m.textContent = 'Editing result #' + id + ' - submit to save, or reload to cancel'; m.className = 'text-sm'; }
        document.getElementById('lab-form-card').classList.remove('hidden');
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    async function labLoadStats(){
        const s = await fetch(LAB_API+'?api=get_stats').then(r=>r.json()).catch(()=>({}));
        const set=(id,v)=>{const el=document.getElementById(id); if(el) el.textContent=v;};
        set('lab-stat-total', s.total ?? 0); set('lab-stat-abnormal', s.abnormal ?? 0); set('lab-stat-recent', s.recentMonth ?? 0);
    }
    async function labLoadPatients(){ const sel=document.getElementById('lab-patient'); if(!sel) return;
        const list=await fetch('backend/api/patients.php?api=get_patients').then(r=>r.ok?r.json():[]).catch(()=>[]);
        list.forEach(p=>{const o=document.createElement('option');o.value=p.id;o.textContent=p.firstName+' '+p.lastName;sel.appendChild(o);}); }
    async function labLoadDoctors(){ const sel=document.getElementById('lab-doctor'); if(!sel) return;
        const list=await fetch('backend/api/doctors.php?api=get_doctors').then(r=>r.ok?r.json():[]).catch(()=>[]);
        list.forEach(d=>{const o=document.createElement('option');o.value=d.id;o.textContent='Dr. '+d.firstName+' '+d.lastName;sel.appendChild(o);}); }
    const labForm=document.getElementById('lab-form');
    if(labForm){ labForm.addEventListener('submit', async e=>{
        e.preventDefault();
        const msg=document.getElementById('lab-msg'); msg.textContent='Saving...'; msg.className='text-sm text-on-surface-variant';
        const hasTest = Array.from(labForm.querySelectorAll('[name="testType[]"]')).some(i => i.value.trim() !== '');
        if (!hasTest) { msg.textContent=''; window.showError('Add at least one test.'); return; }
        const fd=new FormData(labForm); fd.append('action', labEditingId ? 'update_result' : 'add_result'); if (labEditingId) fd.append('id', labEditingId);
        const res=await fetch(LAB_API,{method:'POST',body:fd}).then(r=>r.json()).catch(()=>({success:false,message:'Network error'}));
        if(res.success){ location.reload(); } else { msg.textContent=''; window.showError(res.message||'Failed to save'); }
    }); }
    labResetTests(); labLoadStats(); labLoadPatients(); labLoadDoctors();
</script>
</body></html>
