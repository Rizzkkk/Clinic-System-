<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
// Backward-compat: this page's data API now lives in backend/api/medical_records.php.
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/medical_records.php';
}

require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('medical_records');
$canWriteMr = can_access('medical_records', 'write');

// --- Server-rendered table data below (uses $conn from bootstrap) ---
function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$medicalRecordRows = [];
$medicalRecordResult = $conn->query('
    SELECT mr.id, mr.patientId, mr.recordType, mr.recordDate, mr.description, mr.findings, mr.recommendations,
           mr.physician, mr.department, mr.chiefComplaint, mr.diagnosis, mr.clinicalNotes, mr.prescription,
           mr.bloodPressure, mr.heartRate, mr.temperature, mr.weight, mr.allergies, mr.attachments,
           p.firstName, p.lastName, p.dateOfBirth, p.gender, p.phone, p.email, p.address, p.emergencyContact, p.emergencyPhone, p.allergies as patientAllergies, p.status
    FROM medical_records mr
    JOIN patients p ON mr.patientId = p.id
    ORDER BY mr.recordDate DESC, mr.id DESC
');
if ($medicalRecordResult) {
    while ($row = $medicalRecordResult->fetch_assoc()) {
        $medicalRecordRows[] = $row;
    }
}

$totalPatients = (int) ($conn->query('SELECT COUNT(*) as count FROM patients')->fetch_assoc()['count'] ?? 0);
$activeCases = (int) ($conn->query('SELECT COUNT(*) as count FROM medical_records WHERE recordDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetch_assoc()['count'] ?? 0);
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Patient Records | MedLab Pro</title>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Google Fonts: Manrope & Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "primary-fixed": "#8ff4e3",
                    "on-primary-container": "#f4fffb",
                    "surface-container-highest": "#dfe4e1",
                    "inverse-primary": "#72d8c8",
                    "surface-dim": "#d6dbd9",
                    "on-tertiary-fixed": "#171c1f",
                    "inverse-on-surface": "#edf2ef",
                    "primary-container": "#008376",
                    "tertiary-fixed": "#dfe3e7",
                    "secondary-container": "#aed9ff",
                    "on-primary": "#ffffff",
                    "error": "#ba1a1a",
                    "surface-container-high": "#e4e9e7",
                    "on-error-container": "#93000a",
                    "background": "#f6faf8",
                    "tertiary-fixed-dim": "#c3c7cb",
                    "surface-variant": "#dfe4e1",
                    "outline-variant": "#bdc9c5",
                    "on-secondary-fixed": "#001e30",
                    "error-container": "#ffdad6",
                    "surface-container": "#eaefec",
                    "on-background": "#171d1b",
                    "on-surface": "#171d1b",
                    "on-secondary-fixed-variant": "#1c4a6a",
                    "on-primary-fixed-variant": "#005047",
                    "tertiary": "#585d60",
                    "on-tertiary-fixed-variant": "#43474b",
                    "secondary-fixed-dim": "#a1cbf0",
                    "on-tertiary-container": "#fbfcff",
                    "on-primary-fixed": "#00201c",
                    "on-surface-variant": "#3d4946",
                    "surface": "#f6faf8",
                    "primary": "#00685d",
                    "surface-bright": "#f6faf8",
                    "secondary-fixed": "#cbe6ff",
                    "on-tertiary": "#ffffff",
                    "on-secondary": "#ffffff",
                    "inverse-surface": "#2c3130",
                    "outline": "#6d7a77",
                    "surface-tint": "#006b5f",
                    "surface-container-low": "#f0f5f2",
                    "primary-fixed-dim": "#72d8c8",
                    "on-secondary-container": "#345f80",
                    "on-error": "#ffffff",
                    "tertiary-container": "#707579",
                    "secondary": "#376283",
                    "surface-container-lowest": "#ffffff"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "card-gap": "16px",
                    "container-padding": "24px",
                    "gutter": "20px",
                    "sidebar-width": "260px",
                    "stack-sm": "8px",
                    "stack-md": "16px"
            },
            "fontFamily": {
                    "body-md": ["Inter"],
                    "headline-md": ["Manrope"],
                    "label-bold": ["Inter"],
                    "body-sm": ["Inter"],
                    "headline-lg": ["Manrope"],
                    "label-caps": ["Inter"],
                    "body-lg": ["Inter"]
            },
            "fontSize": {
                    "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                    "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                    "label-bold": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                    "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                    "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                    "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.08em", "fontWeight": "600"}],
                    "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #bdc9c5; border-radius: 10px; }
        .table-row-hover:hover { background-color: rgba(0, 107, 95, 0.02); }
    </style>
</head>
<body class="bg-background text-on-background font-body-md overflow-hidden">
<!-- SideNavBar -->
<?php $active = 'medical_records'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>
<!-- Main Workspace -->
<main class="ml-[260px] h-screen flex flex-col">
<!-- TopAppBar -->
<header class="h-16 bg-surface border-b border-outline-variant flex items-center justify-between px-container-padding shrink-0 z-40">
<div class="flex items-center gap-6 flex-1">
<div class="relative w-full max-w-md">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" data-icon="search">search</span>
<input class="w-full bg-surface-container-low border border-outline-variant rounded-lg pl-10 pr-4 py-2 text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Search by MRN or Patient Name..." type="text"/>
</div>
<div class="flex items-center gap-2">
<button class="flex items-center gap-2 px-3 py-2 bg-surface-container border border-outline-variant rounded text-on-surface-variant text-body-sm hover:bg-surface-container-high transition-colors">
<span class="material-symbols-outlined text-[18px]" data-icon="filter_list">filter_list</span>
                        Department
                    </button>
<button class="flex items-center gap-2 px-3 py-2 bg-surface-container border border-outline-variant rounded text-on-surface-variant text-body-sm hover:bg-surface-container-high transition-colors">
<span class="material-symbols-outlined text-[18px]" data-icon="calendar_month">calendar_month</span>
                        Date Range
                    </button>
</div>
</div>
<div class="flex items-center gap-4">
<div class="h-8 w-[1px] bg-outline-variant mx-2"></div>
<div class="flex items-center gap-3">
<button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors relative">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
<span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full border-2 border-surface"></span>
</button>
</div>
</div>
</header>
<!-- Content Area -->
<section class="flex-1 p-container-padding overflow-y-auto">
<div class="mb-6 flex items-end justify-between">
<div>
<h2 class="text-headline-lg font-headline-lg text-on-surface">Patient Records</h2>
<p class="text-on-surface-variant text-body-md">Manage clinical data and medical histories for all registered patients.</p>
</div>
<?php if ($canWriteMr): ?>
<button class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-bold text-label-bold uppercase flex items-center gap-2 shadow-md hover:opacity-95 transition-all active:scale-95" onclick="openModal()">
<span class="material-symbols-outlined" data-icon="person_add">person_add</span>
                    Create New Record
                </button>
<?php endif; ?>
</div>
<!-- Stats Overview -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-card-gap mb-8">
<div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.02)]">
<div class="flex justify-between items-start mb-4">
<div class="p-2 bg-primary/10 text-primary rounded-lg">
<span class="material-symbols-outlined" data-icon="group">group</span>
</div>
</div>
<p class="text-on-surface-variant text-label-bold uppercase">Total Patients</p>
<h3 class="text-headline-lg font-headline-lg mt-1"><?php echo h($totalPatients); ?></h3>
</div>
<div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.02)]">
<div class="flex justify-between items-start mb-4">
<div class="p-2 bg-secondary/10 text-secondary rounded-lg">
<span class="material-symbols-outlined" data-icon="clinical_notes">clinical_notes</span>
</div>
</div>
<p class="text-on-surface-variant text-label-bold uppercase">Total Records</p>
<h3 class="text-headline-lg font-headline-lg mt-1"><?php echo h(count($medicalRecordRows)); ?></h3>
</div>
<div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.02)] border-l-4 border-l-primary">
<div class="flex justify-between items-start mb-4">
<div class="p-2 bg-primary/10 text-primary rounded-lg">
<span class="material-symbols-outlined" data-icon="event_available">event_available</span>
</div>
</div>
<p class="text-on-surface-variant text-label-bold uppercase">Records (Last 30 Days)</p>
<h3 class="text-headline-lg font-headline-lg mt-1"><?php echo h($activeCases); ?></h3>
</div>
</div>
<!-- Data Table Container -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow duration-300">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead class="bg-surface-container-low border-b border-outline-variant">
<tr>
<th class="px-6 py-4 text-label-bold font-label-bold text-on-surface-variant uppercase tracking-wider">Patient Name / MRN</th>
<th class="px-6 py-4 text-label-bold font-label-bold text-on-surface-variant uppercase tracking-wider">Date of Birth</th>
<th class="px-6 py-4 text-label-bold font-label-bold text-on-surface-variant uppercase tracking-wider">Last Visit</th>
<th class="px-6 py-4 text-label-bold font-label-bold text-on-surface-variant uppercase tracking-wider">Primary Physician</th>
<th class="px-6 py-4 text-label-bold font-label-bold text-on-surface-variant uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-label-bold font-label-bold text-on-surface-variant uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-surface-container">
<?php if (empty($medicalRecordRows)): ?>
<tr>
<td colspan="6" class="px-6 py-12 text-center">
<p class="text-body-md text-on-surface-variant">No patient records found</p>
</td>
</tr>
<?php else: ?>
<?php foreach ($medicalRecordRows as $record): ?>
<tr class="hover:bg-surface-container-low transition-colors">
<td class="px-6 py-4">
<div class="font-bold text-on-surface"><?php echo h($record['firstName'] . ' ' . $record['lastName']); ?></div>
<div class="text-xs text-on-surface-variant">MRN: MR-<?php echo h($record['patientId']); ?> | <?php echo h($record['recordType'] ?: 'General record'); ?></div>
</td>
<td class="px-6 py-4 text-body-sm text-on-surface-variant"><?php echo h($record['dateOfBirth'] ?: 'Not set'); ?></td>
<td class="px-6 py-4 text-body-sm text-on-surface-variant"><?php echo h($record['recordDate']); ?></td>
<td class="px-6 py-4 text-body-sm text-on-surface-variant"><?php echo h($record['physician'] ?: 'Not assigned'); ?></td>
<td class="px-6 py-4"><span class="px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed text-label-bold uppercase"><?php echo h($record['status'] ?: 'Active'); ?></span></td>
<td class="px-6 py-4 text-right whitespace-nowrap">
<a target="_blank" rel="noopener" href="Medical Records.php?api=record_pdf&amp;id=<?php echo (int)$record['id']; ?>" class="inline-flex p-2 rounded hover:bg-surface-container-high text-primary align-middle" title="Print medical report (PDF)"><span class="material-symbols-outlined">picture_as_pdf</span></a>
<?php if ($canWriteMr): ?><button type="button" onclick="editMedicalRecord(<?php echo (int)$record['id']; ?>)" class="p-2 rounded hover:bg-surface-container-high text-primary align-middle" title="Edit"><span class="material-symbols-outlined">edit</span></button><?php endif; ?>
<button type="button" onclick="openRecordDetailModal(<?php echo h($record['id']); ?>)" class="p-2 rounded hover:bg-surface-container-high text-on-surface-variant align-middle" title="View patient and physician details">
<span class="material-symbols-outlined">visibility</span>
</button>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
<!-- Pagination Footer -->
<div class="bg-surface-container-low px-6 py-4 border-t border-outline-variant flex items-center justify-between">
<p class="text-body-sm text-on-surface-variant">Showing <?php echo count($medicalRecordRows); ?> records</p>
</div>
</section>
</main>
<!-- Medical Record Modal -->
<div id="medicalRecordModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
<div class="bg-surface-container-lowest rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4">
<div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center sticky top-0 bg-surface-container-lowest z-10">
<h3 class="font-headline-md text-headline-md text-on-surface">Create New Medical Record</h3>
<button onclick="closeModal()" class="p-2 hover:bg-surface-container-low rounded-full transition-colors">
<span class="material-symbols-outlined text-on-surface-variant">close</span>
</button>
</div>
<form id="medicalRecordForm" class="p-6 space-y-6">
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Patient Name *</label>
<select id="patientSelect" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" required>
    <option value="">Select patient</option>
</select>
<input type="hidden" id="selectedPatientId" name="patientId" />
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">MRN *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="mrn" required placeholder="MRN-XXXXX"/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Date of Birth</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="date" name="dob"/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Gender</label>
<select class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" name="gender">
<option value="">Select Gender</option>
<option value="male">Male</option>
<option value="female">Female</option>
<option value="other">Other</option>
</select>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Primary Physician *</label>
<select id="physicianSelect" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" name="physician" required>
<option value="">Select physician</option>
</select>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Department *</label>
<select class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" name="department" required>
<option value="">Select Department</option>
<option value="general">General Medicine</option>
<option value="radiology">Radiology</option>
<option value="pathology">Pathology</option>
<option value="laboratory">Laboratory</option>
<option value="emergency">Emergency</option>
</select>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Chief Complaint *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="chiefComplaint" required placeholder="Reason for visit"/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Diagnosis *</label>
<textarea class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all resize-none" name="diagnosis" rows="3" required placeholder="Enter diagnosis"></textarea>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Clinical Notes</label>
<textarea class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all resize-none" name="clinicalNotes" rows="4" placeholder="Detailed clinical observations and notes"></textarea>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Prescription / Treatment</label>
<textarea class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all resize-none" name="prescription" rows="3" placeholder="Prescribed medications or treatments"></textarea>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Blood Pressure</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="bloodPressure" placeholder="120/80 mmHg"/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Heart Rate</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="heartRate" placeholder="72 bpm"/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Temperature</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="temperature" placeholder="37.0 °C"/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Weight</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="weight" placeholder="70 kg"/>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Allergies</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="allergies" placeholder="List any known allergies"/>
</div>
<div class="flex gap-3 pt-4">
<button type="button" onclick="closeModal()" class="flex-1 px-6 py-3 border border-outline-variant rounded-lg text-label-bold text-label-bold hover:bg-surface-container transition-colors">
Cancel
</button>
<button type="submit" class="flex-1 bg-primary text-on-primary px-6 py-3 rounded-lg font-label-bold text-label-bold hover:brightness-110 transition-all">
Create Record
</button>
</div>
</form>
</div>
</div>

<!-- Record Detail Modal -->
<div id="recordDetailModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-surface-container-lowest rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto m-4">
        <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center sticky top-0 bg-surface-container-lowest z-10">
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface">Record Details</h3>
                <p class="text-body-sm text-on-surface-variant">Patient and clinical information</p>
            </div>
            <button type="button" onclick="closeRecordDetailModal()" class="p-2 hover:bg-surface-container-low rounded-full transition-colors">
                <span class="material-symbols-outlined text-on-surface-variant">close</span>
            </button>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-label-bold text-on-surface mb-2">Patient</h4>
                    <div class="space-y-2 text-body-md text-on-surface-variant">
                        <div><span class="font-bold text-on-surface">Name:</span> <span id="detailPatientName"></span></div>
                        <div><span class="font-bold text-on-surface">DOB:</span> <span id="detailPatientDob"></span></div>
                        <div><span class="font-bold text-on-surface">Gender:</span> <span id="detailPatientGender"></span></div>
                        <div><span class="font-bold text-on-surface">Phone:</span> <span id="detailPatientPhone"></span></div>
                        <div><span class="font-bold text-on-surface">Email:</span> <span id="detailPatientEmail"></span></div>
                        <div><span class="font-bold text-on-surface">Status:</span> <span id="detailPatientStatus"></span></div>
                    </div>
                </div>
                <div>
                    <h4 class="text-label-bold text-on-surface mb-2">Medical Record</h4>
                    <div class="space-y-2 text-body-md text-on-surface-variant">
                                <div><span class="font-bold text-on-surface">MRN:</span> <span id="detailPatientMrn"></span></div>
                        <div><span class="font-bold text-on-surface">Physician:</span> <span id="detailPhysician"></span></div>
                        <div><span class="font-bold text-on-surface">Department:</span> <span id="detailDepartment"></span></div>
                        <div><span class="font-bold text-on-surface">Record Type:</span> <span id="detailRecordType"></span></div>
                        <div><span class="font-bold text-on-surface">Record Date:</span> <span id="detailRecordDate"></span></div>
                        <div><span class="font-bold text-on-surface">Description:</span> <span id="detailDescription"></span></div>
                        <div><span class="font-bold text-on-surface">Findings:</span> <span id="detailFindings"></span></div>
                        <div><span class="font-bold text-on-surface">Recommendations:</span> <span id="detailRecommendations"></span></div>
                    </div>
                </div>
            </div>
            <div>
                <h4 class="text-label-bold text-on-surface mb-2">Address & Emergency Contact</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-body-md text-on-surface-variant">
                    <div><span class="font-bold text-on-surface">Address:</span> <span id="detailPatientAddress"></span></div>
                    <div><span class="font-bold text-on-surface">Emergency Contact:</span> <span id="detailPatientEmergency"></span></div>
                    <div><span class="font-bold text-on-surface">Emergency Phone:</span> <span id="detailPatientEmergencyPhone"></span></div>
                    <div><span class="font-bold text-on-surface">Allergies:</span> <span id="detailPatientAllergies"></span></div>
                </div>
            </div>
            <div>
                <h4 class="text-label-bold text-on-surface mb-2">Attachment Summary</h4>
                <p id="detailAttachments" class="text-body-md text-on-surface-variant"></p>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeRecordDetailModal()" class="px-6 py-3 border border-outline-variant rounded-lg text-label-bold text-label-bold hover:bg-surface-container transition-colors">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
        const medicalRecords = <?php echo json_encode($medicalRecordRows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        let editingMrId = null;
        let editingMrDate = null;

        function openRecordDetailModal(recordId) {
            const record = medicalRecords.find(r => parseInt(r.id, 10) === parseInt(recordId, 10));
            if (!record) return;

            document.getElementById('detailPatientName').textContent = `${record.firstName || ''} ${record.lastName || ''}`.trim() || 'N/A';
            document.getElementById('detailPatientDob').textContent = record.dateOfBirth || 'N/A';
            document.getElementById('detailPatientGender').textContent = record.gender || 'N/A';
            document.getElementById('detailPatientPhone').textContent = record.phone || 'N/A';
            document.getElementById('detailPatientEmail').textContent = record.email || 'N/A';
            document.getElementById('detailPatientStatus').textContent = record.status || 'N/A';
            document.getElementById('detailPatientMrn').textContent = `MR-${record.patientId}`;
            document.getElementById('detailPhysician').textContent = record.physician || 'N/A';
            document.getElementById('detailDepartment').textContent = record.department || 'N/A';
            document.getElementById('detailRecordType').textContent = record.recordType || 'General';
            document.getElementById('detailRecordDate').textContent = record.recordDate || 'N/A';
            document.getElementById('detailDescription').textContent = record.description || 'N/A';
            document.getElementById('detailFindings').textContent = record.findings || 'N/A';
            document.getElementById('detailRecommendations').textContent = record.recommendations || 'N/A';
            document.getElementById('detailPatientAddress').textContent = record.address || 'N/A';
            document.getElementById('detailPatientEmergency').textContent = record.emergencyContact || 'N/A';
            document.getElementById('detailPatientEmergencyPhone').textContent = record.emergencyPhone || 'N/A';
            document.getElementById('detailPatientAllergies').textContent = record.patientAllergies || 'N/A';
            document.getElementById('detailAttachments').textContent = record.attachments || 'None';

            const modal = document.getElementById('recordDetailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeRecordDetailModal() {
            const modal = document.getElementById('recordDetailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('recordDetailModal').addEventListener('click', (e) => {
            if (e.target.id === 'recordDetailModal') {
                closeRecordDetailModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !document.getElementById('recordDetailModal').classList.contains('hidden')) {
                closeRecordDetailModal();
            }
        });

        // Modal functionality
        function openModal() {
            editingMrId = null;
            editingMrDate = null;
            const _f = document.getElementById('medicalRecordForm');
            if (_f) { _f.reset(); const _b = _f.querySelector('button[type="submit"]'); if (_b) _b.textContent = 'Create Record'; }
            const modal = document.getElementById('medicalRecordModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
                    // Load patients + physicians list when modal opens
                    loadPatientsForModal();
                    loadDoctorsForModal();
        }

        // Open the modal pre-filled to edit an existing record (from the medicalRecords cache).
        async function editMedicalRecord(id) {
            const r = medicalRecords.find(x => Number(x.id) === Number(id));
            if (!r) return;
            editingMrId = id;
            editingMrDate = r.recordDate || null;
            const modal = document.getElementById('medicalRecordModal');
            modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow = 'hidden';
            const hid = document.getElementById('selectedPatientId'); if (hid) hid.value = r.patientId || '';
            await loadPatientsForModal();
            await loadDoctorsForModal();
            const f = document.getElementById('medicalRecordForm');
            const set = (n, v) => { const el = f.elements[n]; if (el) el.value = (v == null) ? '' : v; };
            set('mrn', 'MR-' + (r.patientId || ''));
            set('dob', (r.dateOfBirth || '').slice(0, 10));
            const gsel = f.elements['gender'];
            if (gsel && r.gender) { const g = String(r.gender).toLowerCase(); gsel.value = g.startsWith('m') ? 'male' : (g.startsWith('f') ? 'female' : 'other'); }
            const phys = document.getElementById('physicianSelect');
            if (phys) {
                phys.value = r.physician || '';
                if (r.physician && phys.value !== r.physician) { const o = document.createElement('option'); o.value = r.physician; o.textContent = r.physician; phys.appendChild(o); phys.value = r.physician; }
            }
            set('department', r.department);
            set('chiefComplaint', r.chiefComplaint);
            set('diagnosis', r.diagnosis);
            set('clinicalNotes', r.clinicalNotes);
            set('prescription', r.prescription);
            set('bloodPressure', r.bloodPressure);
            set('heartRate', r.heartRate);
            set('temperature', r.temperature);
            set('weight', r.weight);
            set('allergies', r.allergies);
            const b = f.querySelector('button[type="submit"]'); if (b) b.textContent = 'Update Record';
        }

        function closeModal() {
            const modal = document.getElementById('medicalRecordModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('medicalRecordModal').addEventListener('click', (e) => {
            if (e.target.id === 'medicalRecordModal') {
                closeModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Form submission handling
        const medicalRecordForm = document.getElementById('medicalRecordForm');
        medicalRecordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = medicalRecordForm.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.innerHTML = '<span class="animate-spin inline-block mr-2 material-symbols-outlined align-middle" style="font-size: 1.2rem;">progress_activity</span> Saving...';
            btn.classList.add('opacity-80', 'pointer-events-none');

            const formValues = new FormData(medicalRecordForm);
            // Prefer the explicit hidden patientId (set by the select). Fallback to parsing MRN.
            let patientId = String(formValues.get('patientId') || '').trim();
            if (!patientId) {
                patientId = String(formValues.get('mrn') || '').replace(/\D/g, '');
            }
            const requestData = new FormData();
            requestData.append('action', editingMrId ? 'update_record' : 'add_record');
            if (editingMrId) requestData.append('id', editingMrId);
            requestData.append('patientId', patientId);
            requestData.append('recordType', formValues.get('department') || 'General');
            requestData.append('recordDate', (editingMrId && editingMrDate) ? editingMrDate : new Date().toISOString().slice(0, 10));
            requestData.append('description', formValues.get('chiefComplaint') || '');
            requestData.append('findings', `${formValues.get('diagnosis') || ''}\n${formValues.get('clinicalNotes') || ''}`.trim());
            requestData.append('recommendations', formValues.get('prescription') || '');
            requestData.append('physician', formValues.get('physician') || '');
            requestData.append('department', formValues.get('department') || '');
            requestData.append('chiefComplaint', formValues.get('chiefComplaint') || '');
            requestData.append('diagnosis', formValues.get('diagnosis') || '');
            requestData.append('clinicalNotes', formValues.get('clinicalNotes') || '');
            requestData.append('prescription', formValues.get('prescription') || '');
            requestData.append('bloodPressure', formValues.get('bloodPressure') || '');
            requestData.append('heartRate', formValues.get('heartRate') || '');
            requestData.append('temperature', formValues.get('temperature') || '');
            requestData.append('weight', formValues.get('weight') || '');
            requestData.append('allergies', formValues.get('allergies') || '');
            requestData.append('attachments', [
                formValues.get('bloodPressure') ? `BP: ${formValues.get('bloodPressure')}` : '',
                formValues.get('heartRate') ? `HR: ${formValues.get('heartRate')}` : '',
                formValues.get('temperature') ? `Temp: ${formValues.get('temperature')}` : '',
                formValues.get('weight') ? `Weight: ${formValues.get('weight')}` : '',
                formValues.get('allergies') ? `Allergies: ${formValues.get('allergies')}` : ''
            ].filter(Boolean).join(' | '));

            if (!patientId) {
                btn.innerText = 'Enter a valid patient ID';
                btn.classList.remove('opacity-80', 'pointer-events-none');
                setTimeout(() => { btn.innerText = originalText; }, 1800);
                return;
            }

            try {
                const response = await fetch('Medical Records.php', { method: 'POST', body: requestData });
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.message || 'Unable to save record');
                }
                btn.innerHTML = '<span class="material-symbols-outlined align-middle mr-2">check_circle</span> Record Created';
                btn.classList.replace('bg-primary', 'bg-emerald-600');
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } catch (error) {
                btn.innerText = error.message;
                btn.classList.remove('opacity-80', 'pointer-events-none');
                setTimeout(() => { btn.innerText = originalText; }, 2200);
            }
        });

        // Simple micro-interaction for search bar focus
        const searchInput = document.querySelector('input[type="text"]');
        searchInput.addEventListener('focus', () => {
            searchInput.parentElement.classList.add('scale-[1.02]');
            searchInput.parentElement.classList.add('shadow-md');
        });
        searchInput.addEventListener('blur', () => {
            searchInput.parentElement.classList.remove('scale-[1.02]');
            searchInput.parentElement.classList.remove('shadow-md');
        });

        // Populate patient select inside the modal and fill DOB/gender on selection
        async function loadPatientsForModal() {
            const select = document.getElementById('patientSelect');
            const mrnInput = document.querySelector('input[name="mrn"]');
            const hiddenPatientId = document.getElementById('selectedPatientId');
            const dobInput = document.querySelector('input[name="dob"]');
            const genderSelect = document.querySelector('select[name="gender"]');
            if (!select) return;

            try {
                const res = await fetch('Patient.php?api=get_patients');
                if (!res.ok) throw new Error('Failed to load patients');
                const patients = await res.json();

                // Clear existing options except the placeholder
                const selectedPatientValue = hiddenPatientId ? hiddenPatientId.value : '';
                select.innerHTML = '<option value="">Select patient</option>';
                const patientsMap = {};
                patients.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `${p.firstName || ''} ${p.lastName || ''}`.trim() + (p.phone ? ` — ${p.phone}` : '');
                    if (selectedPatientValue && String(p.id) === String(selectedPatientValue)) {
                        opt.selected = true;
                    }
                    select.appendChild(opt);
                    patientsMap[String(p.id)] = p;
                });

                select.addEventListener('change', () => {
                    const id = select.value;
                    const patient = patientsMap[String(id)];

                    if (hiddenPatientId) {
                        hiddenPatientId.value = id || '';
                    }
                    if (mrnInput) {
                        mrnInput.value = (patient && (patient.mrn || patient.MRN)) ? (patient.mrn || patient.MRN) : (id ? `MR-${id}` : '');
                    }
                    if (dobInput) {
                        const dob = patient && (patient.dateOfBirth || patient.dob || patient.birthDate);
                        dobInput.value = dob ? String(dob).slice(0,10) : '';
                    }
                    if (genderSelect) {
                        const gender = patient && (patient.gender || patient.sex);
                        if (gender) {
                            const g = String(gender).toLowerCase();
                            if (g.startsWith('m')) genderSelect.value = 'male';
                            else if (g.startsWith('f')) genderSelect.value = 'female';
                            else genderSelect.value = 'other';
                        } else {
                            genderSelect.value = '';
                        }
                    }
                });
            } catch (err) {
                console.error('Error loading patients for modal:', err);
            }
        }

        // Populate physician select from the doctors table (value = doctor display name)
        let doctorsLoaded = false;
        async function loadDoctorsForModal() {
            const select = document.getElementById('physicianSelect');
            if (!select || doctorsLoaded) return;
            try {
                const res = await fetch('backend/api/doctors.php?api=get_doctors');
                if (!res.ok) throw new Error('Failed to load doctors');
                const doctors = await res.json();
                doctors.forEach(d => {
                    const name = `Dr. ${d.firstName || ''} ${d.lastName || ''}`.trim();
                    const opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name + (d.specialty ? ` — ${d.specialty}` : '');
                    select.appendChild(opt);
                });
                doctorsLoaded = true;
            } catch (err) {
                console.error('Error loading doctors for modal:', err);
            }
        }

        // Simulating record clicks
        document.querySelectorAll('.table-row-hover').forEach(row => {
            row.addEventListener('click', () => {
                row.classList.add('bg-primary/5');
                setTimeout(() => row.classList.remove('bg-primary/5'), 200);
            });
        });
    </script>
</body></html>
