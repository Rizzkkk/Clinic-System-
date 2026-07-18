<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
// Backward-compat: other pages (Dashboard, Appointment, Medical Records) and this page's own JS
// still call Patient.php?api=... or POST an action here. Delegate to the module's API handler.
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/patients.php';
}
// Guard the page load itself so the HTML shell doesn't render for a role without access.
require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('patients');
$canWritePatient = can_access('patients', 'write');
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>MedLab Pro - Patients</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface": "#f6faf8",
                        "tertiary": "#585d60",
                        "inverse-surface": "#2c3130",
                        "on-primary-fixed": "#00201c",
                        "on-background": "#171d1b",
                        "primary": "#00685d",
                        "surface-dim": "#d6dbd9",
                        "on-secondary-fixed": "#001e30",
                        "primary-fixed": "#8ff4e3",
                        "surface-container-lowest": "#ffffff",
                        "error-container": "#ffdad6",
                        "surface-variant": "#dfe4e1",
                        "primary-fixed-dim": "#72d8c8",
                        "surface-container-high": "#e4e9e7",
                        "surface-tint": "#006b5f",
                        "on-tertiary-fixed-variant": "#43474b",
                        "on-tertiary": "#ffffff",
                        "on-surface-variant": "#3d4946",
                        "on-secondary-container": "#345f80",
                        "background": "#f6faf8",
                        "on-tertiary-container": "#fbfcff",
                        "secondary": "#376283",
                        "surface-container-highest": "#dfe4e1",
                        "on-secondary-fixed-variant": "#1c4a6a",
                        "primary-container": "#008376",
                        "inverse-on-surface": "#edf2ef",
                        "outline-variant": "#bdc9c5",
                        "secondary-container": "#aed9ff",
                        "on-error": "#ffffff",
                        "on-error-container": "#93000a",
                        "on-primary-container": "#f4fffb",
                        "on-surface": "#171d1b",
                        "tertiary-container": "#707579",
                        "on-secondary": "#ffffff",
                        "error": "#ba1a1a",
                        "on-primary-fixed-variant": "#005047",
                        "surface-container-low": "#f0f5f2",
                        "on-primary": "#ffffff",
                        "outline": "#6d7a77",
                        "tertiary-fixed-dim": "#c3c7cb",
                        "surface-bright": "#f6faf8",
                        "secondary-fixed": "#cbe6ff",
                        "on-tertiary-fixed": "#171c1f",
                        "surface-container": "#eaefec",
                        "secondary-fixed-dim": "#a1cbf0",
                        "tertiary-fixed": "#dfe3e7",
                        "inverse-primary": "#72d8c8"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "card-gap": "16px",
                        "gutter": "20px",
                        "stack-sm": "8px",
                        "sidebar-width": "260px",
                        "container-padding": "24px",
                        "stack-md": "16px"
                    },
                    "fontFamily": {
                        "label-caps": ["Inter"],
                        "label-bold": ["Inter"],
                        "body-md": ["Inter"],
                        "body-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg": ["Manrope"],
                        "headline-md": ["Manrope"]
                    },
                    "fontSize": {
                        "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.08em", "fontWeight": "600"}],
                        "label-bold": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                        "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body { font-family: 'Inter', sans-serif; }
        .sidebar-active { 
            background-color: rgba(223, 228, 225, 0.2); 
            color: #f6faf8; 
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="bg-background text-on-surface overflow-hidden flex h-screen">
<!-- SideNavBar Component -->
<?php $active = 'patients'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>
<!-- Main Workspace -->
<main class="ml-[260px] flex-1 flex flex-col h-screen overflow-hidden">
<!-- TopNavBar Component -->
<header class="flex justify-between items-center h-16 px-gutter bg-surface sticky top-0 z-40 border-b border-outline-variant/30">
<div>
<h2 class="font-headline-lg text-headline-lg text-on-surface">Patients</h2>
<p class="text-body-sm text-tertiary">Registration and demographics</p>
</div>
<div class="flex items-center gap-4">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-body-lg" data-icon="search">search</span>
<input id="patientSearchInput" class="pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-primary w-72 text-body-md transition-all" placeholder="Search patient, MRN, order..." type="text"/>
</div>
<div class="relative">
<button id="notificationButton" class="p-2 hover:bg-surface-container-low rounded-full transition-all relative" type="button" aria-label="Notifications">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
<span id="notificationBadge" class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full hidden"></span>
</button>
<div id="notificationPanel" class="hidden absolute right-0 mt-3 w-96 bg-surface-container-lowest border border-outline-variant/50 rounded-xl shadow-2xl overflow-hidden z-50">
<div class="px-4 py-3 border-b border-outline-variant/30 flex items-center justify-between">
<div>
<p class="font-label-bold text-label-bold text-on-surface">Notifications</p>
<p class="text-body-sm text-tertiary">New patient activity</p>
</div>
<button id="clearNotificationsButton" type="button" class="text-label-bold text-label-bold text-primary hover:underline">Mark all read</button>
</div>
<div id="notificationList" class="max-h-72 overflow-y-auto"></div>
</div>
</div>
</div>
</header>
<!-- Content Canvas -->
<div class="flex-1 overflow-y-auto p-gutter bg-surface-container-low/30">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter max-w-[1600px] mx-auto">
<!-- Patient Registry Card -->


<div class="lg:col-span-8 bg-surface-container-lowest border border-outline-variant/50 rounded-xl overflow-hidden flex flex-col h-fit shadow-sm hover:shadow-md transition-shadow duration-300">
<div class="px-6 py-5 flex justify-between items-center bg-surface-container-lowest/50">
<h3 class="font-headline-md text-headline-md text-on-surface">Patient registry</h3>
<?php if ($canWritePatient): ?>
<button class="bg-primary hover:bg-primary-container text-white px-4 py-2 rounded-lg text-label-bold transition-colors" onclick="openModal()">
                            Register patient
                        </button>
<?php endif; ?>
</div>
<div class="overflow-x-auto w-full">
<table class="min-w-full w-full table-auto text-left border-collapse">
<thead>
<tr class="bg-surface-container-high/50">
<th class="px-6 py-3 text-label-caps text-on-surface-variant uppercase">MRN</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant uppercase">Name</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant uppercase">DOB</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant uppercase">Gender</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant uppercase">Phone</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant uppercase">Last Visit</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant uppercase text-right">Actions</th>

</tr>
</thead>
<tbody id="patientsTableBody" class="divide-y divide-outline-variant/20"></tbody>
</table>
</div>
</div>
<!-- Quick Registration Card -->
<div class="lg:col-span-4 space-y-gutter">
<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
<h3 class="font-headline-md text-headline-md text-on-surface mb-6">Quick registration</h3>
<form id="quickRegistrationForm" class="space-y-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Full name</label>
<input id="quickFullName" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Last, First Middle" type="text" required/>
</div>
<div class="grid grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Date of birth</label>
<div class="relative">
<input id="quickDob" class="w-full bg-surface border border-outline-variant/40 rounded-lg pl-4 pr-10 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="mm/dd/yyyy" type="date" required/>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant" data-icon="calendar_today">calendar_today</span>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Gender</label>
<div class="relative">
<select id="quickGender" class="w-full appearance-none bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all cursor-pointer" required>
<option value="">Select Gender</option>
<option value="Female">Female</option>
<option value="Male">Male</option>
<option value="Other">Other</option>
</select>
<span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none" data-icon="expand_more">expand_more</span>
</div>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Phone</label>
<input id="quickPhone" class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="(+69) 000-000-0000" type="tel" required/>
</div>
<button class="w-full bg-primary-container text-on-primary-container py-3 rounded-lg font-label-bold text-label-bold hover:brightness-110 transition-all shadow-sm mt-4 active:scale-[0.98]" type="submit">
                                Save patient
                            </button>
</form>
</div>
<!-- Statistics/Info Card -->
<div class="bg-gradient-to-br from-primary to-primary-container rounded-xl p-6 text-on-primary-container shadow-md hover:shadow-xl hover:scale-[1.02] transition-all duration-300 cursor-pointer">
<div class="flex justify-between items-start mb-4">
<div>
<h4 class="text-label-caps uppercase opacity-80 mb-1">Total Patients</h4>
<span id="patientTotalCount" class="text-3xl font-bold tracking-tight">0</span>
</div>
<span class="bg-white/20 p-2 rounded-lg material-symbols-outlined" data-icon="query_stats">query_stats</span>
</div>
<div class="flex items-center gap-2 text-body-sm">
<span class="bg-on-primary-fixed-variant px-1.5 py-0.5 rounded text-[10px] font-bold">0%</span>
<span class="opacity-70">growth this month</span>
</div>
</div>
</div>
</div>
</div>
</main>
<!-- Patient Registration Modal -->
<div id="patientModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
<div class="bg-surface-container-lowest rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4">
<div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center sticky top-0 bg-surface-container-lowest z-10">
<h3 class="font-headline-md text-headline-md text-on-surface">Register New Patient</h3>
<button onclick="closeModal()" class="p-2 hover:bg-surface-container-low rounded-full transition-colors">
<span class="material-symbols-outlined text-on-surface-variant">close</span>
</button>
</div>
<form id="patientForm" class="p-6 space-y-6">
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">First Name *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-3 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="firstName" placeholder="Enter first name" required/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Last Name *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-3 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="lastName" placeholder="Enter last name" required/>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Middle Name</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="middleName"/>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Date of Birth *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="date" name="dob" required/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Gender *</label>
<select class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" name="gender" required>
<option value="">Select Gender</option>
<option value="male">Male</option>
<option value="female">Female</option>
<option value="other">Other</option>
</select>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Phone Number *</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-3 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="phone" required placeholder="09xxxxxxxxx or +63xxxxxxxxx" maxlength="20"/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Email</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-3 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="email" name="email" placeholder="patient@example.com"/>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Address</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-3 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="address" placeholder="Street address, City, Province"/>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Emergency Contact Name</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-3 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="emergencyName" placeholder="Name of emergency contact"/>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Emergency Contact Phone</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-3 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="emergencyPhone" placeholder="09xxxxxxxxx or +63xxxxxxxxx" maxlength="20"/>
</div>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Blood Type</label>
<select class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-2.5 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" name="bloodType">
<option value="">Select Blood Type</option>
<option value="A+">A+</option>
<option value="A-">A-</option>
<option value="B+">B+</option>
<option value="B-">B-</option>
<option value="AB+">AB+</option>
<option value="AB-">AB-</option>
<option value="O+">O+</option>
<option value="O-">O-</option>
</select>
</div>
<div class="space-y-1.5">
<label class="text-label-bold text-on-surface-variant block">Allergies</label>
<input class="w-full bg-surface border border-outline-variant/40 rounded-lg px-4 py-3 text-body-md focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" type="text" name="allergies" placeholder="List any known allergies"/>
</div>
<div class="flex gap-3 pt-4">
<button type="button" onclick="closeModal()" class="flex-1 px-6 py-3 border border-outline-variant rounded-lg text-label-bold text-label-bold hover:bg-surface-container transition-colors">
Cancel
</button>
<button type="submit" class="flex-1 bg-primary text-on-primary px-6 py-3 rounded-lg font-label-bold text-label-bold hover:brightness-110 transition-all">
Register Patient
</button>
</div>
</form>
</div>
</div>
<script>
        // Simple micro-interactions
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.getAttribute('href') === '#') e.preventDefault();
            });
        });

        // Modal functionality
        function showPatientModal() {
            const modal = document.getElementById('patientModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        function openModal() {
            editingPatientId = null;
            const f = document.getElementById('patientForm');
            if (f) { f.reset(); const b = f.querySelector('button[type="submit"]'); if (b) b.textContent = 'Register Patient'; }
            showPatientModal();
        }
        function editPatient(id) {
            const p = patientsCache.find(x => String(x.id) === String(id));
            if (!p) return;
            editingPatientId = id;
            const f = document.getElementById('patientForm');
            const set = (n, v) => { const el = f.elements[n]; if (el) el.value = v ?? ''; };
            set('firstName', p.firstName); set('lastName', p.lastName); set('middleName', p.middleName);
            set('dob', p.dateOfBirth); set('gender', p.gender); set('phone', p.phone); set('email', p.email);
            set('address', p.address); set('emergencyName', p.emergencyContact); set('emergencyPhone', p.emergencyPhone);
            set('bloodType', p.bloodType); set('allergies', p.allergies);
            const b = f.querySelector('button[type="submit"]'); if (b) b.textContent = 'Update Patient';
            showPatientModal();
        }

        function closeModal() {
            const modal = document.getElementById('patientModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('patientModal').addEventListener('click', (e) => {
            if (e.target.id === 'patientModal') {
                closeModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        const quickForm = document.getElementById('quickRegistrationForm');
        const patientForm = document.getElementById('patientForm');
        const tableBody = document.getElementById('patientsTableBody');
        const totalCount = document.getElementById('patientTotalCount');
        const searchInput = document.getElementById('patientSearchInput');
        const notificationButton = document.getElementById('notificationButton');
        const notificationPanel = document.getElementById('notificationPanel');
        const notificationList = document.getElementById('notificationList');
        const notificationBadge = document.getElementById('notificationBadge');
        const clearNotificationsButton = document.getElementById('clearNotificationsButton');

        let searchTerm = '';
        let patientsCache = [];
        const CAN_WRITE = <?php echo $canWritePatient ? 'true' : 'false'; ?>;
        let editingPatientId = null;

        function normalizePatientRecord(patient) {
            return {
                ...patient,
                mrn: `MRN-${String(patient.id).padStart(4, '0')}`,
                fullName: `${patient.lastName}, ${patient.firstName}${patient.middleName ? ` ${patient.middleName}` : ''}`.trim(),
                dob: patient.dateOfBirth || '',
                gender: patient.gender || '',
                phone: patient.phone || '',
                email: patient.email || '',
                address: patient.address || '',
                emergencyName: patient.emergencyContact || '',
                emergencyPhone: patient.emergencyPhone || '',
                bloodType: patient.bloodType || '',
                allergies: patient.allergies || ''
            };
        }

        async function loadPatientsFromServer() {
            const response = await fetch('backend/api/patients.php?api=get_patients');
            if (!response.ok) {
                throw new Error('Unable to load patients');
            }

            const patients = await response.json();
            patientsCache = patients.map(normalizePatientRecord);
            return patientsCache;
        }

        function getNotifications() {
            const notifications = localStorage.getItem('asclepius_notifications');
            return notifications ? JSON.parse(notifications) : [];
        }

        function saveNotifications(notifications) {
            localStorage.setItem('asclepius_notifications', JSON.stringify(notifications));
        }

        function addNotification(message) {
            const notifications = getNotifications();
            notifications.unshift({
                id: Date.now().toString(),
                message,
                createdAt: new Date().toISOString(),
                read: false
            });
            saveNotifications(notifications.slice(0, 20));
            renderNotifications();
        }

        function markAllNotificationsRead() {
            const notifications = getNotifications().map(notification => ({ ...notification, read: true }));
            saveNotifications(notifications);
            renderNotifications();
        }

        async function savePatient(patientData) {
            if (editingPatientId) {
                const existing = patientsCache.find(p => String(p.id) === String(editingPatientId)) || {};
                patientData.medicalHistory = patientData.medicalHistory || existing.medicalHistory || '';
                patientData.insurance_provider = patientData.insurance_provider || existing.insurance_provider || '';
                patientData.insurance_number = patientData.insurance_number || existing.insurance_number || '';
            }
            const formData = new FormData();
            formData.append('action', editingPatientId ? 'update_patient' : 'add_patient');
            if (editingPatientId) formData.append('id', editingPatientId);
            formData.append('firstName', patientData.firstName || 'Unknown');
            formData.append('lastName', patientData.lastName || 'Patient');
            formData.append('middleName', patientData.middleName || '');
            formData.append('dateOfBirth', patientData.dateOfBirth || '');
            formData.append('gender', patientData.gender || '');
            formData.append('bloodType', patientData.bloodType || '');
            formData.append('phone', patientData.phone || '');
            formData.append('email', patientData.email || '');
            formData.append('address', patientData.address || '');
            formData.append('emergencyContact', patientData.emergencyContact || '');
            formData.append('emergencyPhone', patientData.emergencyPhone || '');
            formData.append('medicalHistory', patientData.medicalHistory || '');
            formData.append('allergies', patientData.allergies || '');
            formData.append('insurance_provider', patientData.insurance_provider || '');
            formData.append('insurance_number', patientData.insurance_number || '');

            const response = await fetch('backend/api/patients.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Unable to save patient');
            }

            await loadPatientsFromServer();
            return result;
        }

        function getCurrentPatientCount() {
            return patientsCache.length;
        }

        function updatePatientCount() {
            totalCount.textContent = String(getCurrentPatientCount());
        }

        function getFilteredPatients() {
            const term = searchTerm.trim().toLowerCase();
            const patients = patientsCache;
            if (!term) return patients;

            return patients.filter(patient => {
                return [patient.mrn, patient.fullName, patient.dob, patient.gender, patient.phone]
                    .some(value => String(value || '').toLowerCase().includes(term));
            });
        }

        async function deletePatient(id) {
            const patient = patientsCache.find(entry => String(entry.id) === String(id));
            if (!patient) return;

            const confirmed = window.confirm(`Delete patient ${patient.fullName}? This action cannot be undone.`);
            if (!confirmed) return;

            const formData = new FormData();
            formData.append('action', 'delete_patient');
            formData.append('id', id);

            const response = await fetch('backend/api/patients.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (!result.success) {
                showError(result.message || 'Unable to delete patient.');
                return;
            }

            await loadPatients();
            showToast('Patient deleted successfully.');
        }

        function createRegistryRow(patient) {
            const row = document.createElement('tr');
            row.className = 'hover:bg-surface-container-low/50 transition-colors cursor-pointer group';
            row.innerHTML = `
                <td class="px-6 py-4 text-body-sm text-on-surface-variant">${patient.mrn}</td>
                <td class="px-6 py-4 font-label-bold text-on-surface group-hover:text-primary transition-colors">${patient.fullName}</td>
                <td class="px-6 py-4 text-body-sm">${patient.dob}</td>
                <td class="px-6 py-4 text-body-sm">${patient.gender}</td>
                <td class="px-6 py-4 text-body-sm">${patient.phone}</td>
                <td class="px-6 py-4 text-body-sm">Just registered</td>
                <td class="px-6 py-4 text-right whitespace-nowrap">
                    ${CAN_WRITE ? `
                    <button type="button" onclick="event.stopPropagation(); editPatient('${patient.id}')" class="inline-flex items-center px-3 py-2 rounded-lg text-primary hover:bg-primary/10 transition-colors" title="Edit"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                    <button type="button" onclick="event.stopPropagation(); deletePatient('${patient.id}')" class="inline-flex items-center px-3 py-2 rounded-lg text-error hover:bg-error-container transition-colors" title="Delete"><span class="material-symbols-outlined text-[20px]">delete</span></button>
                    ` : ''}
                </td>
            `;
            return row;
        }

        function renderNotifications() {
            const notifications = getNotifications();
            const unreadCount = notifications.filter(notification => !notification.read).length;

            if (unreadCount > 0) {
                notificationBadge.classList.remove('hidden');
            } else {
                notificationBadge.classList.add('hidden');
            }

            if (!notifications.length) {
                notificationList.innerHTML = '<div class="px-4 py-6 text-body-sm text-tertiary">No notifications yet.</div>';
                return;
            }

            notificationList.innerHTML = notifications.map(notification => `
                <div class="px-4 py-3 border-b border-outline-variant/20 ${notification.read ? 'bg-surface-container-lowest' : 'bg-primary/5'}">
                    <p class="text-body-md text-on-surface">${notification.message}</p>
                    <p class="text-body-sm text-tertiary mt-1">${new Date(notification.createdAt).toLocaleString()}</p>
                </div>
            `).join('');
        }

        function toggleNotificationPanel() {
            notificationPanel.classList.toggle('hidden');
            if (!notificationPanel.classList.contains('hidden')) {
                markAllNotificationsRead();
            }
        }

        quickForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!quickForm.checkValidity()) {
                quickForm.reportValidity();
                return;
            }

            const fullName = document.getElementById('quickFullName').value.trim();
            const nameParts = fullName.includes(',')
                ? fullName.split(',').map(part => part.trim())
                : fullName.split(/\s+/).filter(Boolean);
            const firstName = fullName.includes(',') ? (nameParts[1] || 'Unknown').split(' ')[0] : (nameParts[0] || 'Unknown');
            const lastName = fullName.includes(',') ? (nameParts[0] || 'Patient') : (nameParts.slice(1).join(' ') || 'Patient');

            const btn = quickForm.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.innerHTML = '<span class="animate-spin inline-block mr-2 material-symbols-outlined align-middle" style="font-size: 1.2rem;">progress_activity</span> Saving...';
            btn.classList.add('opacity-80', 'pointer-events-none');
            btn.classList.remove('bg-primary-container');
            btn.classList.add('bg-emerald-600');

            try {
                await savePatient({
                    firstName,
                    lastName,
                    middleName: '',
                    dateOfBirth: document.getElementById('quickDob').value,
                    gender: document.getElementById('quickGender').value,
                    bloodType: '',
                    phone: document.getElementById('quickPhone').value.trim(),
                    email: '',
                    address: '',
                    emergencyContact: '',
                    emergencyPhone: '',
                    medicalHistory: '',
                    allergies: '',
                    insurance_provider: '',
                    insurance_number: ''
                });

                btn.innerHTML = '<span class="material-symbols-outlined align-middle mr-2">check_circle</span> Patient Saved';

                setTimeout(() => {
                    loadPatients();
                    updatePatientCount();
                    btn.innerText = originalText;
                    btn.classList.remove('bg-emerald-600');
                    btn.classList.add('bg-primary-container');
                    btn.classList.remove('opacity-80', 'pointer-events-none');
                    quickForm.reset();
                    addNotification(`New patient registered: ${fullName}`);
                    showToast('Patient registered successfully.');
                }, 1000);
            } catch (error) {
                showError(error.message || 'Unable to save patient.');
                btn.innerText = originalText;
                btn.classList.remove('bg-emerald-600', 'opacity-80', 'pointer-events-none');
                btn.classList.add('bg-primary-container');
            }
        });

        patientForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!patientForm.checkValidity()) {
                patientForm.reportValidity();
                return;
            }

            const btn = patientForm.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.innerHTML = '<span class="animate-spin inline-block mr-2 material-symbols-outlined align-middle" style="font-size: 1.2rem;">progress_activity</span> Saving...';
            btn.classList.add('opacity-80', 'pointer-events-none');

            const formData = new FormData(patientForm);
            const patient = {
                firstName: formData.get('firstName').trim(),
                lastName: formData.get('lastName').trim(),
                middleName: formData.get('middleName')?.trim() || '',
                dateOfBirth: formData.get('dob'),
                gender: formData.get('gender'),
                phone: formData.get('phone').trim(),
                email: formData.get('email')?.trim() || '',
                address: formData.get('address')?.trim() || '',
                emergencyContact: formData.get('emergencyName')?.trim() || '',
                emergencyPhone: formData.get('emergencyPhone')?.trim() || '',
                bloodType: formData.get('bloodType') || '',
                allergies: formData.get('allergies')?.trim() || '',
                medicalHistory: '',
                insurance_provider: '',
                insurance_number: ''
            };

            try {
                await savePatient(patient);
                btn.innerHTML = '<span class="material-symbols-outlined align-middle mr-2">check_circle</span> Patient Registered';
                btn.classList.replace('bg-primary', 'bg-emerald-600');

                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.classList.replace('bg-emerald-600', 'bg-primary');
                    btn.classList.remove('opacity-80', 'pointer-events-none');
                    editingPatientId = null;
                    patientForm.reset();
                    closeModal();
                    loadPatients();
                    showToast('Patient saved successfully.');
                }, 800);
            } catch (error) {
                showError(error.message || 'Unable to save patient.');
                btn.innerText = originalText;
                btn.classList.remove('opacity-80', 'pointer-events-none');
                btn.classList.add('bg-primary');
            }
        });

        async function loadPatients() {
            tableBody.innerHTML = '';
            await loadPatientsFromServer();
            const patients = getFilteredPatients();
            patients.forEach(patient => {
                tableBody.appendChild(createRegistryRow(patient));
            });

            if (!patients.length) {
                tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-10 text-center text-body-sm text-tertiary">No patients match your search.</td></tr>';
            }

            updatePatientCount();
            renderNotifications();
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                searchTerm = searchInput.value;
                loadPatients();
            });
        }

        if (notificationButton) {
            notificationButton.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleNotificationPanel();
            });
        }

        if (clearNotificationsButton) {
            clearNotificationsButton.addEventListener('click', () => {
                markAllNotificationsRead();
                notificationPanel.classList.add('hidden');
            });
        }

        document.addEventListener('click', (e) => {
            if (!notificationPanel.contains(e.target) && e.target !== notificationButton && !notificationButton.contains(e.target)) {
                notificationPanel.classList.add('hidden');
            }
        });

        function showToast(message) {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) return;
            const toast = document.createElement('div');
            toast.className = 'bg-inverse-surface text-inverse-on-surface px-6 py-3 rounded-lg shadow-2xl flex items-center gap-3 mb-3';
            toast.innerHTML = `
                <span class="material-symbols-outlined text-primary-fixed">check_circle</span>
                <span class="font-medium text-body-md">${message}</span>
            `;
            toastContainer.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        renderNotifications();
        loadPatients();
    </script>
</body></html>
