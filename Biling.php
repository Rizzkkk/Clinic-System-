<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
// Backward-compat: this page's data API now lives in backend/api/billing.php.
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/billing.php';
}

require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('billing');
$canWriteBill = can_access('billing', 'write');

// --- Server-rendered table data below (uses $conn from bootstrap) ---
function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$billingRows = [];
$billingResult = $conn->query('
    SELECT b.id, b.patientId, b.description, b.amount, b.status, b.billingDate, b.paymentMethod, b.notes,
           p.firstName as patientFirstName, p.lastName as patientLastName,
           p.insurance_provider
    FROM billing b
    JOIN patients p ON b.patientId = p.id
    ORDER BY COALESCE(b.billingDate, DATE(b.created_at)) DESC, b.id DESC
');
if ($billingResult) {
    while ($row = $billingResult->fetch_assoc()) {
        $billingRows[] = $row;
    }
}
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Billing &amp; Invoicing | MedLab Pro</title>
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
                        "surface-container-low": "#f0f5f2",
                        "on-error-container": "#93000a",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-fixed-variant": "#43474b",
                        "on-secondary-fixed-variant": "#1c4a6a",
                        "tertiary": "#585d60",
                        "outline": "#6d7a77",
                        "outline-variant": "#bdc9c5",
                        "on-background": "#171d1b",
                        "tertiary-container": "#707579",
                        "surface-container-high": "#e4e9e7",
                        "primary-fixed": "#8ff4e3",
                        "on-surface": "#171d1b",
                        "background": "#f6faf8",
                        "on-primary-fixed": "#00201c",
                        "surface-container": "#eaefec",
                        "on-tertiary-container": "#fbfcff",
                        "surface-dim": "#d6dbd9",
                        "surface": "#f6faf8",
                        "surface-variant": "#dfe4e1",
                        "inverse-on-surface": "#edf2ef",
                        "primary": "#00685d",
                        "on-primary-container": "#f4fffb",
                        "secondary": "#376283",
                        "secondary-container": "#aed9ff",
                        "on-error": "#ffffff",
                        "on-secondary": "#ffffff",
                        "surface-container-highest": "#dfe4e1",
                        "surface-bright": "#f6faf8",
                        "secondary-fixed": "#cbe6ff",
                        "on-secondary-container": "#345f80",
                        "primary-container": "#008376",
                        "surface-tint": "#006b5f",
                        "error": "#ba1a1a",
                        "on-primary-fixed-variant": "#005047",
                        "on-primary": "#ffffff",
                        "tertiary-fixed": "#dfe3e7",
                        "primary-fixed-dim": "#72d8c8",
                        "inverse-surface": "#2c3130",
                        "inverse-primary": "#72d8c8",
                        "on-tertiary-fixed": "#171c1f",
                        "on-surface-variant": "#3d4946",
                        "secondary-fixed-dim": "#a1cbf0",
                        "tertiary-fixed-dim": "#c3c7cb",
                        "surface-container-lowest": "#ffffff",
                        "on-secondary-fixed": "#001e30",
                        "error-container": "#ffdad6"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "container-padding": "24px",
                        "sidebar-width": "260px",
                        "stack-md": "16px",
                        "stack-sm": "8px",
                        "gutter": "20px",
                        "card-gap": "16px"
                    },
                    "fontFamily": {
                        "headline-md": ["Manrope"],
                        "body-md": ["Inter"],
                        "label-caps": ["Inter"],
                        "label-bold": ["Inter"],
                        "body-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg": ["Manrope"]
                    },
                    "fontSize": {
                        "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.08em", "fontWeight": "600"}],
                        "label-bold": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}]
                    }
                }
            }
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            background-color: #f6faf8;
            color: #171d1b;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .bento-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .bento-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="font-body-md overflow-x-hidden">
<!-- SideNavBar -->
<?php $active = 'billing'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>
<!-- Main Content Shell -->
<main class="ml-[260px] min-h-screen flex flex-col">
<!-- TopAppBar -->
<header class="sticky top-0 z-40 bg-surface border-b border-outline-variant flex justify-between items-center h-16 px-container-padding">
<div class="flex items-center gap-6 flex-1">
<div class="relative w-full max-w-md group">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="w-full bg-surface-container border border-outline-variant rounded-full py-2 pl-10 pr-4 text-body-sm focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all" placeholder="Search Invoices, Patients, or IDs..." type="text"/>
</div>
<div class="flex items-center gap-4">
<button class="flex items-center gap-1 text-on-surface-variant hover:text-primary transition-colors">
<span class="font-label-bold text-label-bold">Filter By</span>
<span class="material-symbols-outlined text-sm">keyboard_arrow_down</span>
</button>
</div>
</div>
<div class="flex items-center gap-4">
<button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors relative">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full border-2 border-surface"></span>
</button>
</div>
</header>
<!-- Content Canvas -->
<div class="p-container-padding space-y-gutter">
<!-- Summary Bento Grid -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-card-gap">
<div class="bento-card p-6 rounded-xl">
<div class="w-12 h-12 rounded-xl bg-primary-fixed/30 flex items-center justify-center mb-4"><span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">account_balance_wallet</span></div>
<p class="text-on-surface-variant font-label-bold text-label-bold uppercase tracking-widest">Total Billed</p>
<h2 id="stat-total-amount" class="text-[32px] font-bold text-on-surface mt-1 leading-none">PHP 0.00</h2>
<p class="text-body-sm text-on-surface-variant mt-2"><span id="stat-total">0</span> bills on record</p>
</div>
<div class="bento-card p-6 rounded-xl">
<div class="w-12 h-12 rounded-xl bg-secondary-fixed/30 flex items-center justify-center mb-4"><span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">pending_actions</span></div>
<p class="text-on-surface-variant font-label-bold text-label-bold uppercase tracking-widest">Pending Bills</p>
<h2 id="stat-pending" class="text-[32px] font-bold text-on-surface mt-1 leading-none">0</h2>
<p class="text-body-sm text-on-surface-variant mt-2">Awaiting payment</p>
</div>
<div class="bento-card p-6 rounded-xl bg-inverse-surface border-none">
<div class="w-12 h-12 rounded-xl bg-primary-container flex items-center justify-center mb-4"><span class="material-symbols-outlined text-primary-fixed-dim" style="font-variation-settings:'FILL' 1;">payments</span></div>
<p class="text-surface-variant font-label-bold text-label-bold uppercase tracking-widest">Collected</p>
<h2 id="stat-paid-amount" class="text-[32px] font-bold text-surface-bright mt-1 leading-none">PHP 0.00</h2>
<p class="text-body-sm text-surface-variant opacity-80 mt-2"><span id="stat-paid">0</span> paid bills</p>
</div>
</section>
<!-- Main Workspace Grid -->
<div class="grid grid-cols-12 gap-gutter">
<!-- Table Area -->
<section class="col-span-12 lg:col-span-9 space-y-gutter">
<!-- Invoices Table -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
<table class="w-full text-left border-collapse">
<thead class="bg-[#F1F5F9] border-b border-outline-variant">
<tr>
<th class="px-6 py-4 text-label-caps font-label-caps uppercase text-on-surface-variant">Invoice ID</th>
<th class="px-6 py-4 text-label-caps font-label-caps uppercase text-on-surface-variant">Patient Name</th>
<th class="px-6 py-4 text-label-caps font-label-caps uppercase text-on-surface-variant">Service / Test</th>
<th class="px-6 py-4 text-label-caps font-label-caps uppercase text-on-surface-variant text-right">Amount</th>
<th class="px-6 py-4 text-label-caps font-label-caps uppercase text-on-surface-variant">Insurance</th>
<th class="px-6 py-4 text-label-caps font-label-caps uppercase text-on-surface-variant text-center">Status</th>
<th class="px-6 py-4"></th>
</tr>
</thead>
<tbody class="divide-y divide-[#F1F5F9]">
<?php if (empty($billingRows)): ?>
<tr><td colspan="7" class="px-6 py-10 text-center text-body-sm text-on-surface-variant">No billing records found.</td></tr>
<?php else: ?>
<?php foreach ($billingRows as $bill): ?>
<?php $statusClass = strtolower($bill['status']) === 'paid' ? 'bg-primary-fixed text-on-primary-fixed' : 'bg-surface-container-high text-on-surface-variant'; ?>
<tr class="hover:bg-surface-container-low transition-colors">
<td class="px-6 py-4"><span class="text-label-bold font-label-bold text-on-surface">#INV-<?php echo str_pad((string)$bill['id'], 4, '0', STR_PAD_LEFT); ?></span></td>
<td class="px-6 py-4">
<div class="flex flex-col">
<span class="text-body-md font-bold text-on-surface"><?php echo h($bill['patientFirstName'] . ' ' . $bill['patientLastName']); ?></span>
<span class="text-[11px] text-on-surface-variant">ID: PR-<?php echo h($bill['patientId']); ?></span>
</div>
</td>
<td class="px-6 py-4"><span class="text-body-sm text-on-surface-variant"><?php echo h($bill['description'] ?: 'General service'); ?></span></td>
<td class="px-6 py-4 text-right"><span class="text-body-md font-bold">PHP <?php echo number_format((float)$bill['amount'], 2); ?></span></td>
<td class="px-6 py-4"><span class="px-2 py-1 rounded bg-surface-variant text-on-surface-variant text-[10px] font-bold uppercase"><?php echo h($bill['insurance_provider'] ?: 'Self Pay'); ?></span></td>
<td class="px-6 py-4 text-center"><span class="px-2 py-1 rounded <?php echo $statusClass; ?> text-[10px] font-bold uppercase"><?php echo h($bill['status'] ?: 'Pending'); ?></span></td>
<td class="px-6 py-4 text-right whitespace-nowrap">
<a target="_blank" rel="noopener" href="backend/api/billing.php?api=bill_pdf&amp;id=<?php echo (int)$bill['id']; ?>" class="inline-flex text-primary hover:text-primary/80 transition-colors align-middle" title="Print receipt (PDF)"><span class="material-symbols-outlined text-lg">picture_as_pdf</span></a>
<?php if ($canWriteBill): ?><button onclick="billStartEdit(<?php echo (int)$bill['id']; ?>)" class="text-primary hover:text-primary/80 transition-colors align-middle ml-2" title="Edit"><span class="material-symbols-outlined text-lg">edit</span></button><?php endif; ?>
<button class="text-on-surface-variant hover:text-primary transition-colors align-middle ml-2" title="<?php echo h($bill['notes'] ?: 'View bill'); ?>">
<span class="material-symbols-outlined text-lg">visibility</span>
</button>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
<div class="px-6 py-4 bg-surface flex items-center justify-between border-t border-outline-variant">
<span class="text-body-sm text-on-surface-variant">Showing <?php echo count($billingRows); ?> billing records</span>

</div>
</div>
</section>
<!-- Sidebar Area -->
<aside class="col-span-12 lg:col-span-3 space-y-gutter">
<?php if ($canWriteBill): ?>
<section class="bento-card p-6 rounded-xl">
<div class="flex items-center gap-2 mb-6"><span class="material-symbols-outlined text-primary">bolt</span><h3 class="font-headline-md text-headline-md text-on-surface">Quick Bill</h3></div>
<form id="bill-form" class="space-y-4">
<div>
<label class="block text-label-bold font-label-bold text-on-surface-variant mb-1">Patient</label>
<select id="bill-patient" name="patientId" required class="w-full bg-surface border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-1 focus:ring-primary outline-none"><option value="">Select patient</option></select>
</div>
<div>
<label class="block text-label-bold font-label-bold text-on-surface-variant mb-1">Description</label>
<input name="description" placeholder="e.g. Lipid Panel" class="w-full bg-surface border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-1 focus:ring-primary outline-none"/>
</div>
<div class="grid grid-cols-2 gap-3">
<div>
<label class="block text-label-bold font-label-bold text-on-surface-variant mb-1">Amount</label>
<input name="amount" type="number" step="0.01" min="0" required placeholder="0.00" class="w-full bg-surface border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-1 focus:ring-primary outline-none"/>
</div>
<div>
<label class="block text-label-bold font-label-bold text-on-surface-variant mb-1">Status</label>
<select name="status" class="w-full bg-surface border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-1 focus:ring-primary outline-none"><option>Pending</option><option>Paid</option></select>
</div>
</div>
<div>
<label class="block text-label-bold font-label-bold text-on-surface-variant mb-1">Billing Date</label>
<input name="billingDate" type="date" class="w-full bg-surface border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-1 focus:ring-primary outline-none"/>
</div>
<div>
<label class="block text-label-bold font-label-bold text-on-surface-variant mb-1">Payment Method</label>
<input name="paymentMethod" placeholder="Cash / Card / Insurance" class="w-full bg-surface border border-outline-variant rounded-lg p-2 text-body-sm focus:ring-1 focus:ring-primary outline-none"/>
</div>
<button type="submit" class="w-full bg-primary text-on-primary py-2.5 rounded-lg font-label-bold text-label-bold hover:bg-primary/90 transition-all flex items-center justify-center gap-2"><span class="material-symbols-outlined text-sm">receipt</span> Create Bill</button>
<p id="bill-msg" class="text-sm text-center"></p>
</form>
</section>
<?php endif; ?>
</aside>
</div>
</div>
<!-- FAB for Mobile (Contextual suppression applied but added for UI demo if screen was smaller) -->
<!-- Hidden on large screens as per instructions to suppress navigation/FAB on desktop main pages if sidebar is present -->
</main>
<script>
    const BILL_API = 'backend/api/billing.php';
    const BILL_ROWS = <?php echo json_encode($billingRows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    let billEditingId = null;
    function billStartEdit(id) {
        const r = BILL_ROWS.find(x => Number(x.id) === Number(id));
        const f = document.getElementById('bill-form');
        if (!r || !f) return;
        billEditingId = id;
        ['patientId','description','amount','status','billingDate','paymentMethod'].forEach(k => {
            const el = f.elements[k]; if (el) el.value = (r[k] == null) ? '' : r[k];
        });
        const b = f.querySelector('button[type="submit"]'); if (b) b.textContent = 'Update Bill';
        const m = document.getElementById('bill-msg'); if (m) { m.textContent = 'Editing bill #' + id + ' - submit to save, or reload to cancel'; m.className = 'text-sm text-center text-on-surface-variant'; }
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function phpMoney(n){ return 'PHP ' + Number(n||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }
    async function billLoadStats(){
        const s = await fetch(BILL_API+'?api=get_stats').then(r=>r.json()).catch(()=>({}));
        const set=(id,v)=>{const el=document.getElementById(id); if(el) el.textContent=v;};
        set('stat-total-amount', phpMoney(s.totalAmount));
        set('stat-total', s.total ?? 0);
        set('stat-pending', s.pending ?? 0);
        set('stat-paid-amount', phpMoney(s.paidAmount));
        set('stat-paid', s.paid ?? 0);
    }
    async function billLoadPatients(){
        const sel=document.getElementById('bill-patient'); if(!sel) return;
        const list=await fetch('backend/api/patients.php?api=get_patients').then(r=>r.ok?r.json():[]).catch(()=>[]);
        list.forEach(p=>{const o=document.createElement('option');o.value=p.id;o.textContent=p.firstName+' '+p.lastName;sel.appendChild(o);});
    }
    const billForm=document.getElementById('bill-form');
    if(billForm){ billForm.addEventListener('submit', async e=>{
        e.preventDefault();
        const msg=document.getElementById('bill-msg'); msg.textContent='Saving...'; msg.className='text-sm text-center text-on-surface-variant';
        const fd=new FormData(billForm); fd.append('action', billEditingId ? 'update_bill' : 'add_bill'); if (billEditingId) fd.append('id', billEditingId);
        const res=await fetch(BILL_API,{method:'POST',body:fd}).then(r=>r.json()).catch(()=>({success:false,message:'Network error'}));
        if(res.success){ location.reload(); } else { msg.textContent=''; window.showError(res.message||'Failed to save'); }
    }); }
    billLoadStats(); billLoadPatients();
</script>
</body></html>
