<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';
// Backward-compat: this page's data API now lives in backend/api/appointments.php.
if (!empty($_GET['api']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))) {
    require __DIR__ . '/backend/api/appointments.php';
}
// Guard the page load itself so the HTML shell doesn't render for a role without access.
require_once __DIR__ . '/backend/auth/rbac.php';
require_module_access('appointments');
$canWriteAppt = can_access('appointments', 'write');
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>MedLab Pro | Appointment Schedule</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Manrope:wght@600;700;800&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-primary": "#ffffff",
                        "on-surface-variant": "#3d4946",
                        "on-primary-fixed": "#00201c",
                        "surface-tint": "#006b5f",
                        "primary-container": "#008376",
                        "surface-container-high": "#e4e9e7",
                        "on-tertiary-fixed-variant": "#43474b",
                        "surface-container-highest": "#dfe4e1",
                        "surface-bright": "#f6faf8",
                        "on-secondary": "#ffffff",
                        "tertiary-container": "#707579",
                        "outline-variant": "#bdc9c5",
                        "on-tertiary-container": "#fbfcff",
                        "on-secondary-fixed": "#001e30",
                        "secondary-fixed": "#cbe6ff",
                        "on-secondary-container": "#345f80",
                        "tertiary-fixed": "#dfe3e7",
                        "error-container": "#ffdad6",
                        "secondary-container": "#aed9ff",
                        "tertiary": "#585d60",
                        "inverse-surface": "#2c3130",
                        "on-surface": "#171d1b",
                        "outline": "#6d7a77",
                        "surface-container": "#eaefec",
                        "secondary": "#376283",
                        "on-secondary-fixed-variant": "#1c4a6a",
                        "inverse-primary": "#72d8c8",
                        "primary": "#00685d",
                        "on-tertiary": "#ffffff",
                        "surface-container-low": "#f0f5f2",
                        "surface": "#f6faf8",
                        "on-primary-container": "#f4fffb",
                        "surface-container-lowest": "#ffffff",
                        "secondary-fixed-dim": "#a1cbf0",
                        "primary-fixed-dim": "#72d8c8",
                        "on-error": "#ffffff",
                        "on-primary-fixed-variant": "#005047",
                        "background": "#f6faf8",
                        "tertiary-fixed-dim": "#c3c7cb",
                        "surface-variant": "#dfe4e1",
                        "surface-dim": "#d6dbd9",
                        "on-background": "#171d1b",
                        "primary-fixed": "#8ff4e3",
                        "on-tertiary-fixed": "#171c1f",
                        "error": "#ba1a1a",
                        "on-error-container": "#93000a",
                        "inverse-on-surface": "#edf2ef"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "stack-md": "16px",
                        "sidebar-width": "260px",
                        "container-padding": "24px",
                        "gutter": "20px",
                        "card-gap": "16px",
                        "stack-sm": "8px"
                    },
                    "fontFamily": {
                        "body-lg": ["Inter"],
                        "headline-md": ["Manrope"],
                        "label-bold": ["Inter"],
                        "body-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "label-caps": ["Inter"],
                        "headline-lg": ["Manrope"]
                    },
                    "fontSize": {
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "headline-md": ["18px", {"lineHeight": "24px", "fontWeight": "600"}],
                        "label-bold": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "label-caps": ["11px", {"lineHeight": "16px", "letterSpacing": "0.08em", "fontWeight": "600"}],
                        "headline-lg": ["24px", {"lineHeight": "32px", "fontWeight": "700"}]
                    }
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .w-sidebar { width: 260px; }
        .ml-sidebar { margin-left: 260px; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #bdc9c5; border-radius: 10px; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md overflow-x-hidden">
<!-- SideNavBar (Shared Component) -->
<?php $active = 'appointments'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>
<!-- TopNavBar (Shared Component) -->
<header class="ml-sidebar h-16 sticky top-0 bg-surface dark:bg-surface-dim border-b border-outline-variant/30 flex justify-between items-center px-gutter z-40">
<div class="flex items-center">
<h2 class="font-headline-lg text-headline-lg text-primary">Appointments</h2>
</div>
<div class="flex items-center gap-6">
<button class="flex items-center text-on-surface-variant hover:bg-surface-container-low p-2 rounded-full transition-all">
<span class="material-symbols-outlined">notifications</span>
</button>
</div>
</header>
<main class="ml-sidebar p-gutter flex flex-col gap-gutter">
<!-- Metrics Bento Grid -->
<section class="grid grid-cols-1 md:grid-cols-4 gap-card-gap">
<div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-xl flex items-center gap-4 transition-transform hover:scale-[1.01]">
<div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary">
<span class="material-symbols-outlined">event_note</span>
</div>
<div>
<p class="text-label-caps text-on-surface-variant mb-1 uppercase">Today's Appointments</p>
<h3 class="text-headline-lg font-headline-lg text-on-surface" id="todayAppointmentsCount">0</h3>
</div>
<div class="ml-auto text-primary font-bold text-label-bold bg-primary/10 px-2 py-1 rounded" id="todayPercentageValue">
                    0%
                </div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-xl flex items-center gap-4 transition-transform hover:scale-[1.01]">
<div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center text-secondary">
<span class="material-symbols-outlined">how_to_reg</span>
</div>
<div>
<p class="text-label-caps text-on-surface-variant mb-1 uppercase">Pending Check-ins</p>
<h3 class="text-headline-lg font-headline-lg text-on-surface" id="pendingCheckinsCount">0</h3>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-xl flex items-center gap-4 transition-transform hover:scale-[1.01]">
<div class="w-12 h-12 bg-error/10 rounded-full flex items-center justify-center text-error">
<span class="material-symbols-outlined">check_circle</span>
</div>
<div>
<p class="text-label-caps text-on-surface-variant mb-1 uppercase">Finish Patient</p>
<h3 class="text-headline-lg font-headline-lg text-on-surface" id="cancellationsCount">0</h3>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant/30 p-6 rounded-xl flex items-center gap-4 transition-transform hover:scale-[1.01]">
<div class="w-12 h-12 bg-tertiary/10 rounded-full flex items-center justify-center text-tertiary">
<span class="material-symbols-outlined">pending_actions</span>
</div>
<div>
<p class="text-label-caps text-on-surface-variant mb-1 uppercase">Portal Requests</p>
<h3 class="text-headline-lg font-headline-lg text-on-surface" id="requestedCount">0</h3>
<p class="text-label-caps text-on-surface-variant">Awaiting confirmation</p>
</div>
</div>
</section>
<div class="grid grid-cols-12 gap-gutter">
<!-- Main Schedule Table Area -->
<section class="col-span-12 lg:col-span-9 flex flex-col gap-gutter">
<!-- Split Containers for Appointment Sources -->
<div class="grid grid-cols-1 gap-gutter">
<!-- Online Bookings -->
<div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl overflow-hidden hover:shadow-md transition-shadow duration-300">
<div class="px-6 py-4 border-b border-outline-variant/20 bg-surface-container-low flex justify-between items-center">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary">language</span>
<h4 class="font-headline-md text-headline-md">Online Bookings</h4>
</div>
<span id="onlineTotalCount" class="bg-primary/10 text-primary px-3 py-1 rounded-full text-label-bold font-bold">Total: 0</span>
</div>
<div class="max-h-[520px] overflow-auto">
<table class="w-full text-left border-collapse">
<thead class="sticky top-0 z-10">
<tr class="border-b border-outline-variant/10 bg-surface-container-lowest">
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider">Time</th>
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider">Patient</th>
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider">Doctor</th>
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider">Status</th>
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody id="onlineAppointmentsBody" class="divide-y divide-outline-variant/10">
</tbody>
</table>
</div>
</div>
<!-- Walk-in Appointments -->
<div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl overflow-hidden hover:shadow-md transition-shadow duration-300">
<div class="px-6 py-4 border-b border-outline-variant/20 bg-surface-container-low flex justify-between items-center">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-secondary">how_to_reg</span>
<h4 class="font-headline-md text-headline-md">Walk-ins</h4>
</div>
<span id="walkInTotalCount" class="bg-secondary/10 text-secondary px-3 py-1 rounded-full text-label-bold font-bold">Total: 0</span>
</div>
<div class="max-h-[360px] overflow-auto">
<table class="w-full text-left border-collapse">
<thead class="sticky top-0 z-10">
<tr class="border-b border-outline-variant/10 bg-surface-container-lowest">
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider">Time</th>
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider">Patient</th>
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider">Doctor</th>
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider">Status</th>
<th class="px-6 py-3 font-label-bold text-label-bold text-on-surface-variant uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody id="walkInAppointmentsBody" class="divide-y divide-outline-variant/10">
</tbody>
</table>
</div>
</div>
</div>
</section>
<!-- Sidebar Contextual Area -->
<aside class="col-span-12 lg:col-span-3 flex flex-col gap-gutter">
<?php if ($canWriteAppt): ?>
<!-- Quick Booking Card -->
<div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-6 mb-gutter hover:shadow-md transition-shadow duration-300">
<div class="flex items-center gap-2 mb-4">
<span class="material-symbols-outlined text-primary">person_add</span>
<h4 class="font-label-bold text-label-bold uppercase text-on-surface-variant tracking-wider">Walk-in Registration   </h4>
</div>
<p class="text-body-sm text-on-surface-variant mb-4">Create an online booked appointment for a registered patient.</p>
<button id="walkInBtn" type="button" class="w-full flex items-center justify-center gap-2 bg-secondary text-on-secondary py-2.5 rounded-lg font-label-bold text-label-bold hover:brightness-110 transition-all shadow-sm">
<span class="material-symbols-outlined text-[20px]">how_to_reg</span>
        Register Walk-in
    </button>
</div>
<!-- Online Booking Card -->
<div class="bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-6 mb-gutter hover:shadow-md transition-shadow duration-300">
<div class="flex items-center gap-2 mb-4">
<span class="material-symbols-outlined text-secondary">how_to_reg</span>
<h4 class="font-label-bold text-label-bold uppercase text-on-surface-variant tracking-wider">Online Booking</h4>
</div>
<p class="text-body-sm text-on-surface-variant mb-4">Create a scheduled online appointment for a registered patient.</p>
<button id="bookSlotBtn" type="button" class="w-full flex items-center justify-center gap-2 bg-secondary text-on-secondary py-2.5 rounded-lg font-label-bold text-label-bold hover:brightness-110 transition-all shadow-sm">
<span class="material-symbols-outlined text-[20px]">how_to_reg</span>
        Book Online
    </button>
</div>
<?php endif; ?>
<!-- Daily Pipeline View -->

</aside>
</div>
</main>
<!-- Walk-in Booking Modal -->
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4" id="walkInModal">
<div class="w-full max-w-lg rounded-2xl bg-surface-container-lowest shadow-2xl border border-outline-variant/30">
<div class="flex items-center justify-between border-b border-outline-variant/20 px-6 py-4">
<div>
<h3 class="font-headline-md text-headline-md" id="walkInModalTitle">Register Walk-in</h3>
<p class="text-body-sm text-on-surface-variant" id="walkInModalDescription">Create an appointment for a patient arriving today.</p>
</div>
<button class="rounded-full p-2 hover:bg-surface-container" id="closeWalkInModal" type="button">
<span class="material-symbols-outlined">close</span>
</button>
</div>
<form class="space-y-4 px-6 py-5" id="walkInForm">
<div class="grid gap-4 sm:grid-cols-2">
<div class="space-y-1">
<label class="text-label-caps text-on-surface-variant" for="walkInPatient">Patient</label>
<select class="w-full rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-3 py-2 text-body-sm outline-none focus:ring-1 focus:ring-primary" id="walkInPatient" required></select>
</div>
<div class="space-y-1">
<label class="text-label-caps text-on-surface-variant" for="walkInDoctor">Doctor</label>
<select class="w-full rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-3 py-2 text-body-sm outline-none focus:ring-1 focus:ring-primary" id="walkInDoctor" required></select>
</div>
</div>
<div class="rounded-xl border border-outline-variant/30 bg-surface-container-low px-4 py-3">
<div class="flex items-center justify-between mb-2">
<p class="text-label-bold text-label-bold uppercase text-on-surface-variant tracking-wider">Selected Patient</p>
<span class="text-label-bold text-label-bold text-primary" id="walkInPatientCount">0 patients</span>
</div>
<div id="walkInPatientList" class="max-h-44 overflow-y-auto space-y-2 pr-1"></div>
</div>
<div class="grid gap-4 sm:grid-cols-2">
<div class="space-y-1">
<label class="text-label-caps text-on-surface-variant" for="walkInDate">Date</label>
<input class="w-full rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-3 py-2 text-body-sm outline-none focus:ring-1 focus:ring-primary" id="walkInDate" type="date" required/>
</div>
<div class="space-y-1">
<label class="text-label-caps text-on-surface-variant" for="walkInTime">Time</label>
<input class="w-full rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-3 py-2 text-body-sm outline-none focus:ring-1 focus:ring-primary" id="walkInTime" type="time" required/>
</div>
</div>
<div class="space-y-1">
<label class="text-label-caps text-on-surface-variant" for="walkInReason">Reason</label>
<input class="w-full rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-3 py-2 text-body-sm outline-none focus:ring-1 focus:ring-primary" id="walkInReason" placeholder="e.g. General consultation" type="text"/>
</div>
<div class="space-y-1">
<label class="text-label-caps text-on-surface-variant" for="walkInNotes">Notes</label>
<textarea class="w-full rounded-lg border border-outline-variant/50 bg-surface-container-lowest px-3 py-2 text-body-sm outline-none focus:ring-1 focus:ring-primary" id="walkInNotes" rows="3" placeholder="Optional notes"></textarea>
</div>
<div class="flex items-center justify-end gap-3 pt-2">
<button class="rounded-lg border border-outline-variant/40 px-4 py-2 text-body-sm hover:bg-surface-container" id="cancelWalkIn" type="button">Cancel</button>
<button class="rounded-lg bg-primary px-4 py-2 text-body-sm font-semibold text-on-primary hover:brightness-110" id="saveBookingButton" type="submit">Save Walk-in</button>
</div>
</form>
</div>
</div>
<!-- Success Notification Micro-interaction Mockup -->
<div class="fixed bottom-gutter right-gutter translate-y-20 opacity-0 bg-inverse-surface text-inverse-on-surface px-6 py-3 rounded-lg shadow-xl transition-all duration-500 flex items-center gap-3 z-50" id="toast">
<span class="material-symbols-outlined text-primary-fixed">check_circle</span>
<span class="text-body-sm font-medium">New appointment confirmed successfully.</span>
</div>
<script>
        const walkInBtn = document.getElementById('walkInBtn');
        const walkInModal = document.getElementById('walkInModal');
        const closeWalkInModal = document.getElementById('closeWalkInModal');
        const cancelWalkIn = document.getElementById('cancelWalkIn');
        const walkInForm = document.getElementById('walkInForm');
        const walkInPatient = document.getElementById('walkInPatient');
        const walkInDoctor = document.getElementById('walkInDoctor');
        const walkInDate = document.getElementById('walkInDate');
        const walkInTime = document.getElementById('walkInTime');
        const walkInReason = document.getElementById('walkInReason');
        const walkInNotes = document.getElementById('walkInNotes');
        const walkInPatientList = document.getElementById('walkInPatientList');
        const walkInPatientCount = document.getElementById('walkInPatientCount');
        const onlineAppointmentsBody = document.getElementById('onlineAppointmentsBody');
        const walkInAppointmentsBody = document.getElementById('walkInAppointmentsBody');
        const onlinePatientSearch = document.getElementById('onlinePatientSearch');
        const onlineServiceType = document.getElementById('onlineServiceType');
        const saveBookingButton = document.getElementById('saveBookingButton');
        const walkInModalTitle = document.getElementById('walkInModalTitle');
        const walkInModalDescription = document.getElementById('walkInModalDescription');

        let walkInPatientsCache = [];
        let currentBookingMode = 'walkin';
        const CAN_WRITE_APPT = <?php echo $canWriteAppt ? 'true' : 'false'; ?>;
        let editingAppointmentId = null;
        const APPTS = {};

        function filterPatientList(query) {
            const normalizedQuery = String(query || '').toLowerCase().trim();
            const filteredPatients = normalizedQuery
                ? walkInPatientsCache.filter((patient) => {
                    const fullName = `${patient.firstName || ''} ${patient.lastName || ''}`.toLowerCase();
                    return fullName.includes(normalizedQuery);
                })
                : walkInPatientsCache;

            renderWalkInPatientList(filteredPatients);
        }

        function setBookingMode(mode) {
            currentBookingMode = mode;

            if (mode === 'online') {
                if (walkInModalTitle) walkInModalTitle.textContent = 'Register Online Booking';
                if (walkInModalDescription) walkInModalDescription.textContent = 'Create a scheduled appointment for a registered patient.';
                if (saveBookingButton) saveBookingButton.textContent = 'Save Booking';
                if (onlineServiceType && walkInReason) walkInReason.value = onlineServiceType.value || '';
            } else {
                if (walkInModalTitle) walkInModalTitle.textContent = 'Register Walk-in';
                if (walkInModalDescription) walkInModalDescription.textContent = 'Create an appointment for a patient arriving today.';
                if (saveBookingButton) saveBookingButton.textContent = 'Save Walk-in';
                if (walkInReason) walkInReason.value = '';
            }
        }

        // Define helper functions first
        function isWalkInAppointment(appointment) {
            // Check if this is a walk-in by looking at the reason field
            // Walk-in appointments should have "Walk-in" or "walk-in" in the reason
            const reason = String(appointment.reason || '').toLowerCase();
            const isWalkIn = reason.includes('walk-in') || reason.includes('walk in') || reason.includes('walkin');
            console.log(`Checking appointment - ID: ${appointment.id}, Reason: "${appointment.reason}" → Is Walk-in: ${isWalkIn}`);
            return isWalkIn;
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[char]));
        }

        function createAppointmentRow(appointment) {
            const patientName = `${appointment.patientFirstName || ''} ${appointment.patientLastName || ''}`.trim();
            const doctorName = `${appointment.doctorFirstName || ''} ${appointment.doctorLastName || ''}`.trim();
            const statusLabel = appointment.status || 'Scheduled';
            const safePatientName = escapeHtml(patientName || 'Unknown patient');
            const safeDoctorName = escapeHtml(doctorName || 'Unknown doctor');
            const safeStatusLabel = escapeHtml(statusLabel);
            // A patient-portal request is not a booking until reception confirms it.
            const isRequest = statusLabel === 'Requested';
            const safeAppointmentTime = escapeHtml(appointment.appointmentTime || '--:--');
            const safeAppointmentId = escapeHtml(appointment.id);

            return `
                <tr class="hover:bg-surface-container-low/50 transition-colors">
                    <td class="px-6 py-4 text-body-sm text-on-surface-variant">${safeAppointmentTime}</td>
                    <td class="px-6 py-4 text-body-sm font-semibold text-on-surface">${safePatientName}</td>
                    <td class="px-6 py-4 text-body-sm text-on-surface-variant">${safeDoctorName}</td>
                    <td class="px-6 py-4 text-body-sm">
                        <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold ${isRequest ? 'bg-tertiary/20 text-tertiary' : 'bg-primary/10 text-primary'}">${safeStatusLabel}</span>
                    </td>
                    <td class="px-6 py-4 text-right text-body-sm text-on-surface-variant">
                        <div class="flex gap-2 justify-end">
                            ${isRequest && CAN_WRITE_APPT ? `<button type="button" class="inline-flex items-center justify-center rounded-lg bg-primary px-3 py-2 text-[12px] font-semibold text-on-primary hover:opacity-90 transition-opacity" data-confirm-appointment-id="${safeAppointmentId}" data-patient-name="${safePatientName}">Confirm</button>` : ''}
                            ${CAN_WRITE_APPT ? `<button type="button" class="inline-flex items-center justify-center rounded-lg bg-surface-container px-3 py-2 text-[12px] font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors" data-edit-appointment-id="${safeAppointmentId}">Edit</button>` : ''}
                            <button type="button" class="inline-flex items-center justify-center rounded-lg bg-primary/10 px-3 py-2 text-[12px] font-semibold text-primary hover:bg-primary/20 transition-colors" data-remove-appointment-id="${safeAppointmentId}" data-patient-name="${safePatientName}">
                                Finish Patient
                            </button>
                            <button type="button" class="inline-flex items-center justify-center rounded-lg bg-error/10 px-3 py-2 text-[12px] font-semibold text-error hover:bg-error/20 transition-colors" data-delete-appointment-id="${safeAppointmentId}" data-patient-name="${safePatientName}">
                                Remove
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        async function editAppointment(id) {
            const a = APPTS[String(id)];
            if (!a) return;
            editingAppointmentId = id;
            await openBookingModal('walkin');
            if (walkInPatient) walkInPatient.value = String(a.patientId || '');
            if (walkInDoctor) walkInDoctor.value = String(a.doctorId || '');
            if (walkInDate) walkInDate.value = (a.appointmentDate || '').slice(0, 10);
            if (walkInTime) walkInTime.value = a.appointmentTime || '';
            if (walkInReason) walkInReason.value = a.reason || '';
            if (walkInNotes) walkInNotes.value = a.notes || '';
            const title = document.getElementById('walkInModalTitle'); if (title) title.textContent = 'Edit Appointment';
            if (saveBookingButton) saveBookingButton.textContent = 'Update Appointment';
        }

        async function removeAppointment(appointmentId, patientName) {
            if (!appointmentId) {
                return;
            }

            const confirmed = confirm(`Mark ${patientName} as finished?`);
            if (!confirmed) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', appointmentId);
            formData.append('status', 'Completed');

            try {
                const response = await fetch('Appointment.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    if (toast) {
                        toast.querySelector('span.text-body-sm.font-medium').textContent = 'Patient marked as finished successfully.';
                        toast.classList.remove('translate-y-20', 'opacity-0');
                        toast.classList.add('translate-y-0', 'opacity-100');

                        setTimeout(() => {
                            toast.classList.add('translate-y-20', 'opacity-0');
                            toast.classList.remove('translate-y-0', 'opacity-100');
                        }, 3000);
                    }

                    await loadAppointmentTables();
                    await loadMetrics();
                } else {
                    showError(result.message || 'Unable to remove appointment.');
                }
            } catch (error) {
                console.error('Failed to remove appointment:', error);
                showError('Unable to remove appointment. Please check the console for details.');
            }
        }

        async function deleteAppointment(appointmentId, patientName) {
            if (!appointmentId) {
                return;
            }

            const confirmed = confirm(`Remove ${patientName} from the appointment list?`);
            if (!confirmed) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_appointment');
            formData.append('id', appointmentId);

            try {
                const response = await fetch('Appointment.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    if (toast) {
                        toast.querySelector('span.text-body-sm.font-medium').textContent = 'Appointment removed successfully.';
                        toast.classList.remove('translate-y-20', 'opacity-0');
                        toast.classList.add('translate-y-0', 'opacity-100');

                        setTimeout(() => {
                            toast.classList.add('translate-y-20', 'opacity-0');
                            toast.classList.remove('translate-y-0', 'opacity-100');
                        }, 3000);
                    }

                    await loadAppointmentTables();
                    await loadMetrics();
                } else {
                    showError(result.message || 'Unable to remove appointment.');
                }
            } catch (error) {
                console.error('Failed to delete appointment:', error);
                showError('Unable to remove appointment. Please check the console for details.');
            }
        }

        // Turns a patient-portal request into a real booking. The patient sees the status change on
        // their next page load; nothing else about the row is altered.
        async function confirmAppointmentRequest(appointmentId, patientName) {
            if (!appointmentId) {
                return;
            }
            if (!confirm(`Confirm the appointment request for ${patientName}?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', appointmentId);
            formData.append('status', 'Scheduled');

            try {
                const response = await fetch('Appointment.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    await loadAppointmentTables();
                    await loadMetrics();
                } else {
                    showError(result.message || 'Could not confirm the request.');
                }
            } catch (error) {
                console.error('Error confirming appointment request:', error);
                showError('Could not confirm the request.');
            }
        }

        function handleAppointmentTableClick(event) {
            const confirmButton = event.target.closest('[data-confirm-appointment-id]');
            if (confirmButton) {
                confirmAppointmentRequest(
                    confirmButton.getAttribute('data-confirm-appointment-id'),
                    confirmButton.getAttribute('data-patient-name') || 'this patient'
                );
                return;
            }
            const editButton = event.target.closest('[data-edit-appointment-id]');
            if (editButton) {
                editAppointment(editButton.getAttribute('data-edit-appointment-id'));
                return;
            }
            const finishButton = event.target.closest('[data-remove-appointment-id]');
            const removeButton = event.target.closest('[data-delete-appointment-id]');

            if (finishButton) {
                removeAppointment(
                    finishButton.getAttribute('data-remove-appointment-id'),
                    finishButton.getAttribute('data-patient-name') || 'this patient'
                );
            } else if (removeButton) {
                deleteAppointment(
                    removeButton.getAttribute('data-delete-appointment-id'),
                    removeButton.getAttribute('data-patient-name') || 'this patient'
                );
            }
        }

        function openWalkInModal() {
            walkInModal.classList.remove('hidden');
            walkInModal.classList.add('flex');
        }

        function closeWalkInModalFn() {
            walkInModal.classList.add('hidden');
            walkInModal.classList.remove('flex');
            // Leave edit mode so the next open (add) is a fresh booking.
            editingAppointmentId = null;
        }

        function setDefaultWalkInDateTime() {
            const now = new Date();
            walkInDate.value = now.toISOString().slice(0, 10);
            
            // Format time as HH:MM for time input
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            walkInTime.value = `${hours}:${minutes}`;
            
            console.log('Set default walk-in date/time:', {
                date: walkInDate.value,
                time: walkInTime.value
            });
        }

        async function loadWalkInOptions() {
            try {
                const [patientsResponse, doctorsResponse] = await Promise.all([
                    fetch('Appointment.php?api=get_patients'),
                    fetch('Appointment.php?api=get_doctors')
                ]);

                const patients = await patientsResponse.json();
                const doctors = await doctorsResponse.json();

                if (!patients.length) {
                    await migrateLegacyPatientsFromLocalStorage();
                    const refreshedPatientsResponse = await fetch('Appointment.php?api=get_patients');
                    const refreshedPatients = await refreshedPatientsResponse.json();
                    walkInPatientsCache = refreshedPatients;
                    renderWalkInPatientList(refreshedPatients);
                    populatePatientSelect(refreshedPatients);
                } else {
                    walkInPatientsCache = patients;
                    renderWalkInPatientList(patients);
                    populatePatientSelect(patients);
                }

                walkInDoctor.innerHTML = '<option value="">Select doctor</option>';

                doctors.forEach((doctor) => {
                    const option = document.createElement('option');
                    option.value = doctor.id;
                    option.textContent = `${doctor.firstName} ${doctor.lastName}`;
                    walkInDoctor.appendChild(option);
                });
            } catch (error) {
                console.error('Failed to load walk-in options:', error);
            }
        }

        async function loadAppointmentTables() {
            try {
                console.log('Loading appointments...');
                const response = await fetch('Appointment.php?api=get_appointments');
                
                if (!response.ok) {
                    console.error('Failed to fetch appointments. HTTP Status:', response.status);
                    throw new Error(`HTTP Error: ${response.status}`);
                }
                
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                let appointments;
                try {
                    appointments = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('Failed to parse JSON:', jsonError, 'Response:', responseText);
                    throw new Error('Invalid JSON response from server');
                }

                console.log('All appointments:', appointments);
                
                appointments.forEach((a) => { APPTS[String(a.id)] = a; });
                const onlineAppointments = appointments.filter((appointment) => !isWalkInAppointment(appointment));
                const walkInAppointments = appointments.filter((appointment) => isWalkInAppointment(appointment));

                console.log('Walk-in appointments:', walkInAppointments);
                console.log('Online appointments:', onlineAppointments);

                if (onlineAppointmentsBody) {
                    onlineAppointmentsBody.innerHTML = onlineAppointments.length
                        ? onlineAppointments.map(createAppointmentRow).join('')
                        : '<tr><td colspan="5" class="px-6 py-10 text-center text-body-sm text-tertiary">No online bookings found.</td></tr>';
                }

                if (walkInAppointmentsBody) {
                    walkInAppointmentsBody.innerHTML = walkInAppointments.length
                        ? walkInAppointments.map(createAppointmentRow).join('')
                        : '<tr><td colspan="5" class="px-6 py-10 text-center text-body-sm text-tertiary">No walk-ins found.</td></tr>';
                }

                const onlineTotalCount = document.getElementById('onlineTotalCount');
                const walkInTotalCount = document.getElementById('walkInTotalCount');

                if (onlineTotalCount) {
                    onlineTotalCount.textContent = `Total: ${onlineAppointments.length}`;
                }

                if (walkInTotalCount) {
                    walkInTotalCount.textContent = `Total: ${walkInAppointments.length}`;
                }
            } catch (error) {
                console.error('Error loading appointment tables:', error);
                if (walkInAppointmentsBody) {
                    walkInAppointmentsBody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-body-sm text-red-500">Error loading appointments: ${error.message}</td></tr>`;
                }
            }
        }

        function populatePatientSelect(patients) {
            walkInPatient.innerHTML = '<option value="">Select patient</option>';

            patients.forEach((patient) => {
                const option = document.createElement('option');
                option.value = patient.id;
                option.textContent = `${patient.firstName} ${patient.lastName}`;
                walkInPatient.appendChild(option);
            });
        }

        async function migrateLegacyPatientsFromLocalStorage() {
            const legacyPatients = JSON.parse(localStorage.getItem('asclepius_patients') || '[]');

            if (!legacyPatients.length) {
                return;
            }

            for (const legacyPatient of legacyPatients) {
                const formData = new FormData();
                formData.append('action', 'add_patient');

                const nameParts = String(legacyPatient.fullName || '').split(',').map(part => part.trim()).filter(Boolean);
                const fallbackParts = String(legacyPatient.fullName || '').trim().split(/\s+/).filter(Boolean);
                const firstName = nameParts.length > 1 ? nameParts[1] : (fallbackParts[0] || 'Unknown');
                const lastName = nameParts.length > 0 ? nameParts[0] : (fallbackParts.slice(1).join(' ') || 'Patient');

                formData.append('firstName', firstName);
                formData.append('lastName', lastName);
                formData.append('middleName', '');
                formData.append('dateOfBirth', legacyPatient.dob || '');
                formData.append('gender', legacyPatient.gender || '');
                formData.append('bloodType', legacyPatient.bloodType || '');
                formData.append('phone', legacyPatient.phone || '');
                formData.append('email', legacyPatient.email || '');
                formData.append('address', legacyPatient.address || '');
                formData.append('emergencyContact', legacyPatient.emergencyName || '');
                formData.append('emergencyPhone', legacyPatient.emergencyPhone || '');
                formData.append('medicalHistory', legacyPatient.medicalHistory || '');
                formData.append('allergies', legacyPatient.allergies || '');
                formData.append('insurance_provider', legacyPatient.insurance_provider || '');
                formData.append('insurance_number', legacyPatient.insurance_number || '');

                await fetch('Patient.php', {
                    method: 'POST',
                    body: formData
                });
            }

            localStorage.removeItem('asclepius_patients');
        }

        function renderWalkInPatientList(patients, excludePatientId = null) {
            if (walkInPatientCount) {
                walkInPatientCount.textContent = `${patients.length} patients`;
            }

            if (!walkInPatientList) {
                return;
            }

            // Filter out the excluded patient if specified
            const filteredPatients = excludePatientId 
                ? patients.filter(p => String(p.id) !== String(excludePatientId))
                : patients;

            if (!filteredPatients.length) {
                walkInPatientList.innerHTML = '<div class="rounded-lg border border-dashed border-outline-variant/40 px-3 py-4 text-body-sm text-tertiary">No other patients available.</div>';
                return;
            }

            walkInPatientList.innerHTML = filteredPatients.map((patient) => {
                const fullName = `${patient.firstName} ${patient.lastName}`.trim();
                const contactLine = patient.phone || patient.emergencyPhone || 'No phone available';

                return `
                    <button type="button" class="w-full rounded-lg border border-outline-variant/30 bg-surface-container-lowest px-3 py-3 text-left hover:border-primary/40 hover:bg-primary/5 transition-colors" data-patient-id="${patient.id}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-body-sm font-semibold text-on-surface">${fullName}</p>
                                <p class="text-[11px] text-on-surface-variant mt-1">${patient.gender || 'Unknown gender'} · ${patient.dateOfBirth || 'No DOB'}</p>
                                <p class="text-[11px] text-tertiary mt-1">${contactLine}</p>
                            </div>
                            <span class="rounded-full bg-primary/10 px-2 py-1 text-[10px] font-semibold text-primary">${patient.status || 'Active'}</span>
                        </div>
                    </button>
                `;
            }).join('');

            walkInPatientList.querySelectorAll('[data-patient-id]').forEach((button) => {
                button.addEventListener('click', () => {
                    walkInPatient.value = button.getAttribute('data-patient-id');
                    walkInPatient.dispatchEvent(new Event('change'));
                });
            });
        }

        function updateSelectedPatientPreview(patientId) {
            if (!walkInPatientList) {
                return;
            }

            const selectedPatient = walkInPatientsCache.find((patient) => String(patient.id) === String(patientId));

            if (!selectedPatient) {
                renderWalkInPatientList(walkInPatientsCache);
                return;
            }

            const fullName = `${selectedPatient.firstName} ${selectedPatient.lastName}`.trim();
            
            // Create a wrapper that shows the selected patient and filtered list
            walkInPatientList.innerHTML = `
                <div class="rounded-xl border border-primary/30 bg-primary/5 p-4 mb-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <p class="text-body-md font-semibold text-on-surface">${fullName}</p>
                            <p class="text-body-sm text-on-surface-variant mt-1">${selectedPatient.gender || 'Unknown gender'} · ${selectedPatient.dateOfBirth || 'No DOB'}</p>
                            <p class="text-body-sm text-on-surface-variant mt-1">${selectedPatient.phone || 'No phone'} · ${selectedPatient.bloodType || 'No blood type'}</p>
                            <p class="text-body-sm text-on-surface-variant mt-1">Emergency: ${selectedPatient.emergencyContact || 'Not set'} ${selectedPatient.emergencyPhone ? `(${selectedPatient.emergencyPhone})` : ''}</p>
                        </div>
                        <button type="button" id="clearSelectedPatient" class="rounded-lg border border-outline-variant/30 px-3 py-1.5 text-[11px] font-semibold text-on-surface-variant hover:bg-surface-container whitespace-nowrap">Change Patient</button>
                    </div>
                </div>
                <div id="remainingPatientsList" class="space-y-2"></div>
            `;

            // Render remaining patients (excluding the selected one)
            const remainingPatientsList = document.getElementById('remainingPatientsList');
            const remainingPatients = walkInPatientsCache.filter(p => String(p.id) !== String(patientId));

            if (remainingPatients.length === 0) {
                remainingPatientsList.innerHTML = '<div class="rounded-lg border border-dashed border-outline-variant/40 px-3 py-4 text-body-sm text-tertiary">No other patients available.</div>';
            } else {
                remainingPatientsList.innerHTML = remainingPatients.map((patient) => {
                    const pFullName = `${patient.firstName} ${patient.lastName}`.trim();
                    const pContactLine = patient.phone || patient.emergencyPhone || 'No phone available';

                    return `
                        <button type="button" class="w-full rounded-lg border border-outline-variant/30 bg-surface-container-lowest px-3 py-3 text-left hover:border-primary/40 hover:bg-primary/5 transition-colors remaining-patient-btn" data-patient-id="${patient.id}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-body-sm font-semibold text-on-surface">${pFullName}</p>
                                    <p class="text-[11px] text-on-surface-variant mt-1">${patient.gender || 'Unknown gender'} · ${patient.dateOfBirth || 'No DOB'}</p>
                                    <p class="text-[11px] text-tertiary mt-1">${pContactLine}</p>
                                </div>
                                <span class="rounded-full bg-primary/10 px-2 py-1 text-[10px] font-semibold text-primary">${patient.status || 'Active'}</span>
                            </div>
                        </button>
                    `;
                }).join('');

                // Attach click handlers to remaining patients
                remainingPatientsList.querySelectorAll('.remaining-patient-btn').forEach((button) => {
                    button.addEventListener('click', () => {
                        walkInPatient.value = button.getAttribute('data-patient-id');
                        walkInPatient.dispatchEvent(new Event('change'));
                    });
                });
            }

            const clearButton = document.getElementById('clearSelectedPatient');
            if (clearButton) {
                clearButton.addEventListener('click', () => {
                    walkInPatient.value = '';
                    renderWalkInPatientList(walkInPatientsCache);
                });
            }
        }

        if (walkInPatient) {
            walkInPatient.addEventListener('change', () => {
                updateSelectedPatientPreview(walkInPatient.value);
            });
        }

        async function openBookingModal(mode = 'walkin') {
            setBookingMode(mode);
            openWalkInModal();
            setDefaultWalkInDateTime();
            await loadWalkInOptions();
            walkInPatient.value = '';
            if (onlinePatientSearch) onlinePatientSearch.value = '';
            renderWalkInPatientList(walkInPatientsCache);
        }

        if (walkInBtn) {
            walkInBtn.addEventListener('click', async () => {
                await openBookingModal('walkin');
            });
        }

        if (closeWalkInModal) {
            closeWalkInModal.addEventListener('click', closeWalkInModalFn);
        }

        if (cancelWalkIn) {
            cancelWalkIn.addEventListener('click', closeWalkInModalFn);
        }

        if (walkInModal) {
            walkInModal.addEventListener('click', (event) => {
                if (event.target === walkInModal) {
                    closeWalkInModalFn();
                }
            });
        }

        if (walkInForm) {
            walkInForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                // Validate required fields
                if (!walkInPatient.value) {
                    showError('Please select a patient.');
                    walkInPatient.focus();
                    return;
                }
                if (!walkInDoctor.value) {
                    showError('Please select a doctor.');
                    walkInDoctor.focus();
                    return;
                }
                if (!walkInDate.value) {
                    showError('Please select a date.');
                    walkInDate.focus();
                    return;
                }
                if (!walkInTime.value) {
                    showError('Please select a time.');
                    walkInTime.focus();
                    return;
                }

                const formData = new FormData();
                formData.append('action', editingAppointmentId ? 'update_appointment' : 'add_appointment');
                if (editingAppointmentId) formData.append('id', editingAppointmentId);
                formData.append('patientId', walkInPatient.value);
                formData.append('doctorId', walkInDoctor.value);
                formData.append('appointmentDate', walkInDate.value);
                formData.append('appointmentTime', walkInTime.value);
                const enteredReason = walkInReason.value.trim();
                const reason = editingAppointmentId
                    ? enteredReason
                    : (currentBookingMode === 'online'
                        ? (enteredReason && enteredReason.toLowerCase().includes('online booking') ? enteredReason : `Online Booking${enteredReason ? ` - ${enteredReason}` : ''}`)
                        : (enteredReason && enteredReason.toLowerCase().includes('walk-in') ? enteredReason : `Walk-in${enteredReason ? ` - ${enteredReason}` : ''}`));
                formData.append('reason', reason);
                formData.append('notes', walkInNotes.value || '');
                if (editingAppointmentId) { const ap = APPTS[String(editingAppointmentId)]; formData.append('status', (ap && ap.status) ? ap.status : 'Scheduled'); }

                try {
                    const response = await fetch('Appointment.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    console.log('Save response:', result);

                    if (result.success) {
                        closeWalkInModalFn();
                        const toastMessage = currentBookingMode === 'online'
                            ? 'Online booking saved successfully.'
                            : 'Walk-in appointment saved successfully.';
                        toast.querySelector('span.text-body-sm.font-medium').textContent = toastMessage;
                        toast.classList.remove('translate-y-20', 'opacity-0');
                        toast.classList.add('translate-y-0', 'opacity-100');

                        setTimeout(() => {
                            toast.classList.add('translate-y-20', 'opacity-0');
                            toast.classList.remove('translate-y-0', 'opacity-100');
                        }, 3000);

                        // Reset form
                        walkInForm.reset();
                        
                        // Small delay to ensure database is updated, then reload appointments and metrics
                        setTimeout(async () => {
                            console.log('Reloading appointments after save...');
                            await loadAppointmentTables();
                            await loadMetrics();
                        }, 500);
                    } else {
                        console.error('Failed to save:', result.message);
                        showError(result.message || 'Unable to save walk-in appointment.');
                    }
                } catch (error) {
                    console.error('Failed to save walk-in appointment:', error);
                    showError('Unable to save walk-in appointment. Please check the console for details.');
                }
            });
        }

        // Simple micro-interaction for the 'Book Slot' button
        const bookBtn = document.getElementById('bookSlotBtn');
        const toast = document.getElementById('toast');

        if (onlineAppointmentsBody) {
            onlineAppointmentsBody.addEventListener('click', handleAppointmentTableClick);
        }

        if (walkInAppointmentsBody) {
            walkInAppointmentsBody.addEventListener('click', handleAppointmentTableClick);
        }

        if (bookBtn) {
            bookBtn.addEventListener('click', async () => {
                await openBookingModal('online');
            });
        }

        if (onlinePatientSearch) {
            onlinePatientSearch.addEventListener('input', (event) => {
                filterPatientList(event.target.value);
            });
        }

        if (onlineServiceType) {
            onlineServiceType.addEventListener('change', () => {
                if (currentBookingMode === 'online' && walkInReason) {
                    walkInReason.value = onlineServiceType.value;
                }
            });
        }

        // Expose test functions for debugging
        window.testWalkInFlow = async function() {
            console.log('=== WALK-IN TEST FLOW ===');
            console.log('1. Loading appointments to verify current state...');
            await loadAppointmentTables();
            console.log('2. Opening modal...');
            openWalkInModal();
            console.log('3. Setting default date/time...');
            setDefaultWalkInDateTime();
            console.log('4. Loading options (patients & doctors)...');
            await loadWalkInOptions();
            console.log('Test setup complete. Form is ready for input.');
            console.log('Fill in the patient and doctor fields, then call window.testWalkInFlow.submit()');
        };

        window.testOnlineBookingFlow = async function() {
            console.log('=== ONLINE BOOKING TEST FLOW ===');
            console.log('1. Loading appointments to verify current state...');
            await loadAppointmentTables();
            console.log('2. Opening online booking modal...');
            await openBookingModal('online');
            console.log('3. Setting default date/time...');
            setDefaultWalkInDateTime();
            console.log('4. Loading options (patients & doctors)...');
            await loadWalkInOptions();
            console.log('Test setup complete. Form is ready for input.');
            console.log('Fill in the patient and doctor fields, then call window.testWalkInFlow.submit()');
        };

        window.testWalkInFlow.submit = async function() {
            console.log('=== SUBMITTING TEST WALK-IN ===');
            const patientId = walkInPatient.value;
            const doctorId = walkInDoctor.value;
            const date = walkInDate.value;
            const time = walkInTime.value;
            const reason = walkInReason.value || 'Walk-in';

            if (!patientId || !doctorId || !date || !time) {
                console.error('Missing required fields:', { patientId, doctorId, date, time });
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add_appointment');
            formData.append('patientId', patientId);
            formData.append('doctorId', doctorId);
            formData.append('appointmentDate', date);
            formData.append('appointmentTime', time);
            formData.append('reason', reason);
            formData.append('notes', walkInNotes.value || '');

            console.log('Submitting:', { patientId, doctorId, date, time, reason });

            try {
                const response = await fetch('Appointment.php', { method: 'POST', body: formData });
                const result = await response.json();
                console.log('Server response:', result);

                if (result.success) {
                    console.log('✓ Save successful! Reloading appointments in 500ms...');
                    closeWalkInModalFn();
                    setTimeout(async () => {
                        await loadAppointmentTables();
                        console.log('✓ Appointments reloaded');
                    }, 500);
                } else {
                    console.error('✗ Save failed:', result.message);
                }
            } catch (error) {
                console.error('✗ Error:', error);
            }
        };

        window.testWalkInFlow.reload = async function() {
            console.log('Reloading appointments...');
            await loadAppointmentTables();
            console.log('Done');
        };

        async function loadMetrics() {
            try {
                const response = await fetch('Appointment.php?api=get_stats');
                
                if (!response.ok) {
                    console.error('Failed to fetch metrics. HTTP Status:', response.status);
                    return;
                }
                
                const stats = await response.json();
                console.log('Metrics loaded:', stats);
                
                // Update metrics in the UI
                const todayAppointmentsCount = document.getElementById('todayAppointmentsCount');
                const todayPercentageValue = document.getElementById('todayPercentageValue');
                const pendingCheckinsCount = document.getElementById('pendingCheckinsCount');
                const cancellationsCount = document.getElementById('cancellationsCount');
                const requestedCount = document.getElementById('requestedCount');
                
                if (todayAppointmentsCount) {
                    todayAppointmentsCount.textContent = stats.todayAppointments;
                }
                
                if (todayPercentageValue) {
                    todayPercentageValue.textContent = stats.todayPercentage + '%';
                }
                
                if (pendingCheckinsCount) {
                    pendingCheckinsCount.textContent = stats.pendingCheckins;
                }
                
                if (cancellationsCount) {
                    cancellationsCount.textContent = stats.completed;
                }

                // Patient-portal requests land in the list below with status 'Requested'; reception
                // turns one into a real booking by editing it and setting the status to Scheduled.
                if (requestedCount) {
                    requestedCount.textContent = stats.requested ?? 0;
                }
            } catch (error) {
                console.error('Error loading metrics:', error);
            }
        }

        // Initialize walk-ins table immediately on page load
        console.log('Page loaded - initializing walk-ins display');
        loadMetrics().then(() => {
            console.log('Metrics loaded');
        }).catch(err => {
            console.error('Failed to load metrics on init:', err);
        });
        
        loadAppointmentTables().then(() => {
            console.log('Initial walk-ins load complete');
        }).catch(err => {
            console.error('Failed to load walk-ins on init:', err);
        });
    </script>
</body></html>
