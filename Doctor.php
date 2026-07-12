<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
// Backward-compat: legacy callers (or this page's own JS) may still hit Doctor.php?api=... or
// POST an action here. Delegate those to the module's API handler so old URLs keep working.
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/doctors.php';
}
// Guard the page load itself so the HTML shell doesn't render for a role without access.
require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('doctors');
$canWriteDoctor = can_access('doctors', 'write');
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Doctor Directory - MedLab Pro</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Manrope:wght@600;700&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "tertiary": "#585d60",
                    "on-error-container": "#93000a",
                    "secondary-fixed-dim": "#a1cbf0",
                    "surface-container-low": "#f0f5f2",
                    "on-tertiary-fixed-variant": "#43474b",
                    "on-primary-container": "#f4fffb",
                    "background": "#f6faf8",
                    "on-background": "#171d1b",
                    "inverse-primary": "#72d8c8",
                    "surface-dim": "#d6dbd9",
                    "tertiary-fixed": "#dfe3e7",
                    "surface-container-highest": "#dfe4e1",
                    "inverse-on-surface": "#edf2ef",
                    "surface-tint": "#006b5f",
                    "primary": "#00685d",
                    "secondary-container": "#aed9ff",
                    "on-primary-fixed-variant": "#005047",
                    "surface-container-lowest": "#ffffff",
                    "on-error": "#ffffff",
                    "error-container": "#ffdad6",
                    "surface-container-high": "#e4e9e7",
                    "tertiary-fixed-dim": "#c3c7cb",
                    "on-secondary-fixed": "#001e30",
                    "on-tertiary": "#ffffff",
                    "on-primary": "#ffffff",
                    "surface-variant": "#dfe4e1",
                    "inverse-surface": "#2c3130",
                    "on-tertiary-fixed": "#171c1f",
                    "surface-container": "#eaefec",
                    "secondary": "#376283",
                    "error": "#ba1a1a",
                    "surface-bright": "#f6faf8",
                    "secondary-fixed": "#cbe6ff",
                    "on-primary-fixed": "#00201c",
                    "outline": "#6d7a77",
                    "on-secondary-container": "#345f80",
                    "on-tertiary-container": "#fbfcff",
                    "primary-fixed": "#8ff4e3",
                    "outline-variant": "#bdc9c5",
                    "tertiary-container": "#707579",
                    "on-surface-variant": "#3d4946",
                    "on-surface": "#171d1b",
                    "surface": "#f6faf8",
                    "primary-fixed-dim": "#72d8c8",
                    "on-secondary-fixed-variant": "#1c4a6a",
                    "primary-container": "#008376",
                    "on-secondary": "#ffffff"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "stack-sm": "8px",
                    "container-padding": "24px",
                    "sidebar-width": "260px",
                    "stack-md": "16px",
                    "gutter": "20px",
                    "card-gap": "16px"
            },
            "fontFamily": {
                    "label-bold": ["Inter"],
                    "headline-md": ["Manrope"],
                    "headline-lg": ["Manrope"],
                    "label-caps": ["Inter"],
                    "body-md": ["Inter"],
                    "body-sm": ["Inter"],
                    "body-lg": ["Inter"]
            },
            "fontSize": {
                    "label-bold": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                    "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                    "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                    "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.08em", "fontWeight": "600"}],
                    "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                    "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                    "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .w-sidebar { width: 260px; }
        .ml-sidebar { margin-left: 260px; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #bdc9c5; border-radius: 10px; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen">
<!-- SideNavBar Component -->
<?php $active = 'doctors'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>
<!-- TopNavBar Component -->
<header class="ml-sidebar h-16 bg-surface border-b border-outline-variant/30 sticky top-0 flex justify-between items-center px-8 z-40">
<div></div>
<div class="flex items-center gap-6">
<button class="relative text-on-surface-variant hover:bg-surface-container-low p-2 rounded-full transition-all">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full border-2 border-surface"></span>
</button>
</div>
</header>
<!-- Main Workspace -->
<main class="ml-sidebar p-8">
<div class="max-w-7xl mx-auto space-y-8">
<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
<div>
<h2 class="font-headline-lg text-headline-lg text-on-background">Doctor Directory</h2>
<p class="text-on-surface-variant font-body-md mt-1">Manage clinical staff and department assignments</p>
</div>
<?php if ($canWriteDoctor): ?>
<button class="bg-primary text-on-primary px-6 py-2.5 rounded shadow-sm font-bold flex items-center gap-2 hover:bg-primary-container transition-all" onclick="openModal()">
<span class="material-symbols-outlined">person_add</span>
                    Add new doctor
                </button>
<?php endif; ?>
</div>
<!-- Summary Bento Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-card-gap">
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/30 flex items-center gap-5 shadow-sm hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer">
<div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary">
<span class="material-symbols-outlined text-3xl">groups</span>
</div>
<div>
<p class="text-label-caps text-outline uppercase">Total Doctors</p>
<h3 id="totalDoctorsCount" class="text-3xl font-bold text-on-surface">0</h3>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/30 flex items-center gap-5 shadow-sm hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer">
<div class="w-14 h-14 rounded-full bg-secondary-container/30 flex items-center justify-center text-on-secondary-container">
<span class="material-symbols-outlined text-3xl">verified_user</span>
</div>
<div>
<p class="text-label-caps text-outline uppercase">Currently On Duty</p>
<h3 id="onDutyDoctorsCount" class="text-3xl font-bold text-on-surface">0</h3>
</div>
</div>
<div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/30 flex items-center gap-5 shadow-sm hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer">
<div class="w-14 h-14 rounded-full bg-tertiary-fixed flex items-center justify-center text-on-tertiary-fixed-variant">
<span class="material-symbols-outlined text-3xl">pending_actions</span>
</div>
<div>
<p class="text-label-caps text-outline uppercase">Department Load</p>
<h3 id="departmentLoadCount" class="text-3xl font-bold text-on-surface">0%</h3>
</div>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-4 gap-gutter items-start">
<!-- Main Table Section -->
<div class="lg:col-span-3 space-y-gutter">
<!-- Filters Row -->
<div class="bg-surface-container-low p-4 rounded-lg flex flex-wrap items-center gap-4 hover:shadow-md transition-shadow duration-300">
<div class="relative flex-1 min-w-[200px]">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
<input id="searchInput" class="w-full bg-surface border-outline-variant/30 rounded py-2 pl-10 pr-4 text-body-sm focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all" placeholder="Filter by name or ID..." type="text"/>
</div>
<select id="departmentFilter" class="bg-surface border-outline-variant/30 rounded py-2 px-3 text-body-sm text-on-surface-variant focus:ring-primary/20">
<option value="">Department: All</option>
<option value="radiology">Radiology</option>
<option value="pathology">Pathology</option>
<option value="general">General Medicine</option>
</select>
<select id="specialtyFilter" class="bg-surface border-outline-variant/30 rounded py-2 px-3 text-body-sm text-on-surface-variant focus:ring-primary/20">
<option value="">Specialty: All</option>
<option value="general">General Medicine</option>
<option value="radiologist">Radiologist</option>
<option value="hematologist">Hematologist</option>
<option value="cardiologist">Cardiologist</option>
<option value="pathologist">Pathologist</option>
<option value="pediatrician">Pediatrician</option>
<option value="surgeon">Surgeon</option>
<option value="other">Other</option>
</select>
<select id="statusFilter" class="bg-surface border-outline-variant/30 rounded py-2 px-3 text-body-sm text-on-surface-variant focus:ring-primary/20">
<option value="">Status: All</option>
<option value="On Duty">On Duty</option>
<option value="Offline">Offline</option>
<option value="On Leave">On Leave</option>
</select>
</div>
<!-- Data Table -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container/50 border-b border-outline-variant/30">
<th class="px-6 py-4 text-label-caps text-outline uppercase tracking-wider">Doctor Name</th>
<th class="px-6 py-4 text-label-caps text-outline uppercase tracking-wider">ID / Emp No.</th>
<th class="px-6 py-4 text-label-caps text-outline uppercase tracking-wider">Specialty</th>
<th class="px-6 py-4 text-label-caps text-outline uppercase tracking-wider">Department</th>
<th class="px-6 py-4 text-label-caps text-outline uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-label-caps text-outline uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/20">
</tbody>
</table>
<div class="px-6 py-4 bg-surface-container/30 flex items-center justify-end">
<div id="paginationContainer" class="flex gap-2">
<button id="prevBtn" class="px-3 py-1 border border-outline-variant/30 rounded hover:bg-surface text-on-surface-variant transition-all">Previous</button>
<div id="pageNumbers" class="flex gap-1"></div>
<button id="nextBtn" class="px-3 py-1 border border-outline-variant/30 rounded hover:bg-surface text-on-surface-variant transition-all">Next</button>
</div>
</div>
</div>
</div>
<!-- Sidebar/Widget: Shift Schedule -->
<aside class="space-y-gutter">
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 shadow-sm p-6 overflow-hidden hover:shadow-md transition-shadow duration-300">
<div class="flex items-center justify-between mb-6">
<h4 class="font-headline-md text-on-surface">Shift Schedule</h4>
<span class="material-symbols-outlined text-primary">event_note</span>
</div>
<div class="space-y-6">
<div>
<p class="text-label-caps text-outline uppercase mb-3 flex items-center gap-2">
<span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                    Morning Shift (08:00 - 16:00)
                                </p>
<div class="space-y-3">
</div>
</div>
<div>
<p class="text-label-caps text-outline uppercase mb-3 flex items-center gap-2">
<span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
                                    Evening Shift (16:00 - 00:00)
                                </p>
<div class="space-y-3">
</div>
</div>
</div>
<button id="viewRosterButton" class="w-full mt-6 py-2 border border-primary text-primary font-bold rounded hover:bg-primary/5 transition-all text-body-sm">
                            View Full Roster
                        </button>
</div>
<div class="bg-primary p-6 rounded-xl shadow-lg relative overflow-hidden group">
<div class="relative z-10 text-on-primary">
<h5 class="font-bold text-lg mb-2">Staff Training</h5>
<p class="text-xs opacity-90 mb-4">New LIS protocol training starts tomorrow. 0% completion rate achieved.</p>
<button class="text-[11px] font-bold uppercase tracking-widest flex items-center gap-1 hover:gap-2 transition-all">
                                Update Progress <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined text-9xl">school</span>
</div>
</div>
</aside>
</div>
</div>
</main>
<!-- Doctor Registration Modal -->
<div id="doctorModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
<div class="bg-surface-container-lowest rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4">
<div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center sticky top-0 bg-surface-container-lowest z-10">
<h3 class="font-headline-md text-headline-md text-on-surface">Register New Doctor</h3>
<button onclick="closeModal()" class="p-2 hover:bg-surface-container-low rounded-full transition-colors">
<span class="material-symbols-outlined text-on-surface-variant">close</span>
</button>
</div>
<form id="doctorForm" class="p-6 space-y-6">
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">First Name *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="firstName" required/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Last Name *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="lastName" required/>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Middle Name</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="middleName"/>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Specialty *</label>
<select class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" name="specialty" required>
<option value="">Select Specialty</option>
<option value="general">General Medicine</option>
<option value="radiologist">Radiologist</option>
<option value="hematologist">Hematologist</option>
<option value="cardiologist">Cardiologist</option>
<option value="pathologist">Pathologist</option>
<option value="pediatrician">Pediatrician</option>
<option value="surgeon">Surgeon</option>
<option value="other">Other</option>
</select>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Department *</label>
<select class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" name="department" required>
<option value="">Select Department</option>
<option value="radiology">Radiology</option>
<option value="pathology">Pathology</option>
<option value="general">General Medicine</option>
<option value="laboratory">Laboratory</option>
<option value="emergency">Emergency</option>
<option value="surgery">Surgery</option>
</select>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Shift Assignment *</label>
<select class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" name="shift" required>
<option value="">Select Shift</option>
<option value="morning">Morning Shift (08:00 - 16:00)</option>
<option value="evening">Evening Shift (16:00 - 00:00)</option>
</select>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">License Number *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="licenseNumber" required placeholder="MD-XXXXX"/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Employee ID *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="employeeId" required placeholder="EMP-XXXXX"/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Phone Number *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="tel" name="phone" required placeholder="(+63) 912-345-6789"/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Email *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="email" name="email" required placeholder="doctor@example.com"/>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Address</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="address" placeholder="Street address, City, Province"/>
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
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Education / Qualifications</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="education" placeholder="Medical School, Degree, Year"/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Notes</label>
<textarea class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all resize-none" name="notes" rows="3" placeholder="Additional notes or comments"></textarea>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Signature image (optional) &mdash; JPG only, used on the doctor's PDF reports</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="file" name="signature" accept=".jpg,.jpeg,image/jpeg"/>
</div>
<div class="flex gap-3 pt-4">
<button type="button" onclick="closeModal()" class="flex-1 px-6 py-3 border border-outline-variant rounded-lg text-label-bold text-label-bold hover:bg-surface-container transition-colors">
Cancel
</button>
<button type="submit" class="flex-1 bg-primary text-on-primary px-6 py-3 rounded-lg font-label-bold text-label-bold hover:brightness-110 transition-all">
Register Doctor
</button>
</div>
</form>
</div>
</div>
<!-- Success Message Micro-interaction Container -->
<div class="fixed bottom-8 right-8 z-[60]" id="toast-container"></div>
<script>
        const CAN_WRITE = <?php echo $canWriteDoctor ? 'true' : 'false'; ?>;
        let DOCTORS_CACHE = [];
        let editingDoctorId = null;
        // Modal functionality
        function openModal() {
            const modal = document.getElementById('doctorModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('doctorModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('doctorModal').addEventListener('click', (e) => {
            if (e.target.id === 'doctorModal') {
                closeModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Doctor data management - using database API
        function getDoctors() {
            return fetch('backend/api/doctors.php?api=get_doctors')
                .then(r => r.json())
                .catch(() => []);
        }

        // Sends the real form FormData (so the signature file input is included), tagging the
        // action as add or update depending on edit mode.
        function saveDoctor(fd) {
            fd.append('action', editingDoctorId ? 'update_doctor' : 'add_doctor');
            if (editingDoctorId) fd.append('id', editingDoctorId);
            return fetch('backend/api/doctors.php', { method: 'POST', body: fd }).then(r => r.json());
        }

        function editDoctor(id) {
            const d = (DOCTORS_CACHE || []).find(x => Number(x.id) === Number(id));
            if (!d) return;
            editingDoctorId = id;
            const f = document.getElementById('doctorForm');
            ['firstName','lastName','middleName','specialty','department','shift','licenseNumber','employeeId','phone','email','address','dob','gender','education','notes'].forEach(k => { const el = f.elements[k]; if (el) el.value = (d[k] == null) ? '' : d[k]; });
            openModal();
        }

        function deleteDoctor(id) {
            const formData = new FormData();
            formData.append('action', 'delete_doctor');
            formData.append('id', id);
            
            return fetch('backend/api/doctors.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json());
        }

        // Form submission handling
        const doctorForm = document.getElementById('doctorForm');
        doctorForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = doctorForm.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.innerHTML = '<span class="animate-spin inline-block mr-2 material-symbols-outlined align-middle" style="font-size: 1.2rem;">progress_activity</span> Saving...';
            btn.classList.add('opacity-80', 'pointer-events-none');
            
            // Send the real FormData (includes the optional signature file).
            const fd = new FormData(doctorForm);
            saveDoctor(fd).then(result => {
                if (result.success) {
                    btn.innerHTML = '<span class="material-symbols-outlined align-middle mr-2">check_circle</span> Doctor Registered';
                    btn.classList.replace('bg-primary', 'bg-emerald-600');
                    
                    // Show success toast
                    const toast = document.createElement('div');
                    toast.className = 'bg-inverse-surface text-inverse-on-surface px-6 py-3 rounded-lg shadow-2xl flex items-center gap-3';
                    toast.innerHTML = `
                        <span class="material-symbols-outlined text-primary-fixed">check_circle</span>
                        <span class="font-medium text-body-md">New doctor successfully registered.</span>
                    `;
                    document.getElementById('toast-container').appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                    
                    setTimeout(() => {
                        btn.innerText = originalText;
                        btn.classList.replace('bg-emerald-600', 'bg-primary');
                        btn.classList.remove('opacity-80', 'pointer-events-none');
                        editingDoctorId = null;
                        doctorForm.reset();
                        closeModal();
                        loadDoctors();
                        updateShiftSchedule();
                        updateStatistics();
                    }, 1500);
                } else {
                    btn.innerHTML = `<span class="material-symbols-outlined align-middle mr-2">error</span> Error: ${result.message}`;
                    btn.classList.replace('bg-primary', 'bg-error');
                }
            });
        });

        // Load doctors into table
        async function loadDoctors() {
            const doctors = await getDoctors();
            currentPage = 1;
            displayPage(doctors, currentPage);
        }

        // Update shift schedule widget
        async function updateShiftSchedule() {
            const doctors = await getDoctors();
            const morningShift = doctors.filter(d => d.shift === 'morning');
            const eveningShift = doctors.filter(d => d.shift === 'evening');
            
            const morningContainer = document.querySelector('.space-y-6 > div:nth-child(1) > div:nth-child(2)');
            const eveningContainer = document.querySelector('.space-y-6 > div:nth-child(2) > div:nth-child(2)');
            
            morningContainer.innerHTML = morningShift.length ? '' : '<p class="text-sm text-outline/60 italic">No doctors assigned</p>';
            eveningContainer.innerHTML = eveningShift.length ? '' : '<p class="text-sm text-outline/60 italic">No doctors assigned</p>';
            
            morningShift.forEach(doctor => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 p-2 rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer';
                div.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold">
                        ${doctor.firstName.charAt(0)}${doctor.lastName.charAt(0)}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-on-surface">${doctor.firstName} ${doctor.lastName}</p>
                        <p class="text-xs text-outline">${doctor.specialty || 'General'}</p>
                    </div>
                `;
                morningContainer.appendChild(div);
            });
            
            eveningShift.forEach(doctor => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 p-2 rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer';
                div.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-outline/20 flex items-center justify-center text-outline text-xs font-bold">
                        ${doctor.firstName.charAt(0)}${doctor.lastName.charAt(0)}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-on-surface">${doctor.firstName} ${doctor.lastName}</p>
                        <p class="text-xs text-outline">${doctor.specialty || 'General'}</p>
                    </div>
                `;
                eveningContainer.appendChild(div);
            });
        }

        // Update statistics
        async function updateStatistics() {
            const response = await fetch('backend/api/doctors.php?api=get_stats').then(r => r.json());
            document.getElementById('totalDoctorsCount').textContent = response.total || 0;
            document.getElementById('onDutyDoctorsCount').textContent = response.onDuty || 0;
            document.getElementById('departmentLoadCount').textContent = (response.load || 0) + '%';
        }

        // Full Roster Modal
        async function openRosterModal() {
            const doctors = await getDoctors();
            const modal = document.createElement('div');
            modal.id = 'rosterModal';
            modal.className = 'fixed inset-0 bg-black/50 z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-surface-container-lowest rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto m-4">
                    <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center sticky top-0 bg-surface-container-lowest z-10">
                        <h3 class="font-headline-md text-headline-md text-on-surface">Full Doctor Roster</h3>
                        <button onclick="closeRosterModal()" class="p-2 hover:bg-surface-container-low rounded-full transition-colors">
                            <span class="material-symbols-outlined text-on-surface-variant">close</span>
                        </button>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container/50 border-b border-outline-variant/30">
                                    <th class="px-4 py-3 text-label-caps text-outline uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-label-caps text-outline uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-label-caps text-outline uppercase tracking-wider">Specialty</th>
                                    <th class="px-4 py-3 text-label-caps text-outline uppercase tracking-wider">Department</th>
                                    <th class="px-4 py-3 text-label-caps text-outline uppercase tracking-wider">Shift</th>
                                    <th class="px-4 py-3 text-label-caps text-outline uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/20">
                                ${doctors.length ? doctors.map(doctor => `
                                    <tr class="hover:bg-surface-container-low/50 transition-colors">
                                        <td class="px-4 py-3 font-medium text-on-surface">${doctor.firstName} ${doctor.lastName}</td>
                                        <td class="px-4 py-3 text-on-surface-variant">${doctor.employeeId || 'N/A'}</td>
                                        <td class="px-4 py-3 text-on-surface-variant">${doctor.specialty || 'N/A'}</td>
                                        <td class="px-4 py-3 text-on-surface-variant">${doctor.department || 'N/A'}</td>
                                        <td class="px-4 py-3 text-on-surface-variant">${doctor.shift === 'morning' ? 'Morning (08:00-16:00)' : 'Evening (16:00-00:00)'}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-bold ${doctor.status === 'On Duty' ? 'bg-emerald-100 text-emerald-700' : 'bg-surface-container text-outline'}">
                                                ${doctor.status || 'Offline'}
                                            </span>
                                        </td>
                                    </tr>
                                `).join('') : '<tr><td colspan="6" class="px-4 py-8 text-center text-outline">No doctors registered yet</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            modal.addEventListener('click', (e) => {
                if (e.target.id === 'rosterModal') {
                    closeRosterModal();
                }
            });
        }

        function closeRosterModal() {
            const modal = document.getElementById('rosterModal');
            if (modal) {
                modal.remove();
                document.body.style.overflow = 'auto';
            }
        }

        // Wire up View Full Roster button
        const rosterButton = document.getElementById('viewRosterButton');
        if (rosterButton) {
            rosterButton.addEventListener('click', openRosterModal);
        }

        // Pagination variables
        let currentPage = 1;
        const itemsPerPage = 5;

        // Display paginated results
        function displayPage(doctors, page) {
            DOCTORS_CACHE = doctors;
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedDoctors = doctors.slice(start, end);
            
            const tbody = document.querySelector('tbody');
            tbody.innerHTML = '';
            
            if (paginatedDoctors.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-outline">No doctors found</td></tr>';
                return;
            }
            
            paginatedDoctors.forEach(doctor => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-surface-container-low/50 transition-colors';
                row.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                ${doctor.firstName.charAt(0)}${doctor.lastName.charAt(0)}
                            </div>
                            <div>
                                <p class="font-medium text-on-surface">${doctor.firstName} ${doctor.lastName}</p>
                                <p class="text-sm text-outline">${doctor.email || ''}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-on-surface-variant">${doctor.employeeId || 'N/A'}</td>
                    <td class="px-6 py-4 text-on-surface-variant">${doctor.specialty || 'N/A'}</td>
                    <td class="px-6 py-4 text-on-surface-variant">${doctor.department || 'N/A'}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold ${doctor.status === 'On Duty' ? 'bg-emerald-100 text-emerald-700' : 'bg-surface-container text-outline'}">
                            ${doctor.status || 'Offline'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        ${CAN_WRITE ? `
                        <button onclick="editDoctor(${doctor.id})" class="p-2 hover:bg-primary/10 rounded-full transition-all text-primary" title="Edit"><span class="material-symbols-outlined">edit</span></button>
                        <button onclick="deleteDoctor(${doctor.id}).then(() => { loadDoctors(); updateShiftSchedule(); updateStatistics(); })" class="p-2 hover:bg-error-container rounded-full transition-all text-error hover:text-on-error-container"><span class="material-symbols-outlined">delete</span></button>
                        ` : ''}
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            updatePaginationButtons(doctors.length);
        }

        // Update pagination buttons
        function updatePaginationButtons(totalItems) {
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const pageNumbersContainer = document.getElementById('pageNumbers');
            pageNumbersContainer.innerHTML = '';
            
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = `px-3 py-1 rounded transition-all ${
                    i === currentPage
                        ? 'bg-primary text-on-primary font-bold'
                        : 'border border-outline-variant/30 text-on-surface-variant hover:bg-surface'
                }`;
                btn.onclick = () => {
                    currentPage = i;
                    applyFilters();
                };
                pageNumbersContainer.appendChild(btn);
            }
            
            document.getElementById('prevBtn').disabled = currentPage === 1;
            document.getElementById('nextBtn').disabled = currentPage === totalPages || totalPages === 0;
        }

        // Handle previous/next buttons
        document.getElementById('prevBtn').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                applyFilters();
            }
        });

        document.getElementById('nextBtn').addEventListener('click', async () => {
            const doctors = await getDoctors();
            const totalPages = Math.ceil(doctors.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                applyFilters();
            }
        });

        // Filter functionality
        async function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const departmentFilter = document.getElementById('departmentFilter').value.toLowerCase();
            const specialtyFilter = document.getElementById('specialtyFilter').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            
            const doctors = await getDoctors();
            
            const filtered = doctors.filter(doctor => {
                const matchesSearch = !searchTerm || 
                    doctor.firstName.toLowerCase().includes(searchTerm) ||
                    doctor.lastName.toLowerCase().includes(searchTerm) ||
                    (doctor.employeeId && doctor.employeeId.toLowerCase().includes(searchTerm));
                
                const matchesDepartment = !departmentFilter || (doctor.department && doctor.department.toLowerCase() === departmentFilter);
                const matchesSpecialty = !specialtyFilter || (doctor.specialty && doctor.specialty.toLowerCase() === specialtyFilter);
                const matchesStatus = !statusFilter || doctor.status === statusFilter;
                
                return matchesSearch && matchesDepartment && matchesSpecialty && matchesStatus;
            });
            
            currentPage = 1; // Reset to first page when filtering
            displayPage(filtered, currentPage);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadDoctors();
            updateShiftSchedule();
            updateStatistics();
            
            // Attach filter listeners
            document.getElementById('searchInput').addEventListener('input', applyFilters);
            document.getElementById('departmentFilter').addEventListener('change', applyFilters);
            document.getElementById('specialtyFilter').addEventListener('change', applyFilters);
            document.getElementById('statusFilter').addEventListener('change', applyFilters);
        });

        // Simple Hover Effects for Table Rows
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const btn = row.querySelector('button');
                if (btn) {
                    row.addEventListener('mouseenter', () => {
                        btn.classList.add('scale-110');
                    });
                    row.addEventListener('mouseleave', () => {
                        btn.classList.remove('scale-110');
                    });
                }
            });
        });
    </script>
</body></html>
