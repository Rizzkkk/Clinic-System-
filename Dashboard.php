<?php
require_once __DIR__ . '/backend/auth/bootstrap.php';

// Get statistics
$doctorsCount = $conn->query('SELECT COUNT(*) as count FROM doctors')->fetch_assoc()['count'];
$patientsCount = $conn->query('SELECT COUNT(*) as count FROM patients')->fetch_assoc()['count'];
$appointmentsCount = $conn->query('SELECT COUNT(*) as count FROM appointments WHERE status = "Scheduled"')->fetch_assoc()['count'];
$labResultsCount = $conn->query('SELECT COUNT(*) as count FROM laboratory_results')->fetch_assoc()['count'];

// Additional metrics for dashboard
$completedAppointments = $conn->query('SELECT COUNT(*) as count FROM appointments WHERE status = "Completed"')->fetch_assoc()['count'];
$totalAppointments = $conn->query('SELECT COUNT(*) as count FROM appointments')->fetch_assoc()['count'];
$pendingReviews = $conn->query('SELECT COUNT(*) as count FROM laboratory_results WHERE abnormalFlag = "Y"')->fetch_assoc()['count'];
$accuracyRate = $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100) : 0;

// Get recent appointments
$recentAppointments = [];
$result = $conn->query('
    SELECT a.id, a.appointmentDate, a.appointmentTime, p.firstName, p.lastName, d.firstName as docFirst, d.lastName as docLast, a.status 
    FROM appointments a 
    JOIN patients p ON a.patientId = p.id 
    JOIN doctors d ON a.doctorId = d.id 
    ORDER BY a.appointmentDate DESC 
    LIMIT 5
');
while ($row = $result->fetch_assoc()) {
    $recentAppointments[] = $row;
}

// Get recent patients
$recentPatients = [];
$result = $conn->query('SELECT id, firstName, lastName, phone, email, created_at FROM patients ORDER BY created_at DESC LIMIT 5');
while ($row = $result->fetch_assoc()) {
    $recentPatients[] = $row;
}

// Get recent laboratory orders/results for the dashboard table
$recentTestOrders = [];
$result = $conn->query('
    SELECT lr.id, lr.patientId, lr.testType, lr.testDate, lr.abnormalFlag, p.firstName, p.lastName
    FROM laboratory_results lr
    JOIN patients p ON lr.patientId = p.id
    ORDER BY lr.testDate DESC, lr.id DESC
    LIMIT 5
');
while ($row = $result->fetch_assoc()) {
    $recentTestOrders[] = $row;
}

$dashboardPatientsJson = json_encode(array_map(function ($patient) {
    return [
        'fullName' => trim($patient['firstName'] . ' ' . $patient['lastName']),
        'mrn' => 'MR-' . $patient['id'],
        'createdAt' => $patient['created_at'],
    ];
}, $recentPatients));
$dashboardOrdersJson = json_encode(array_map(function ($order) {
    return [
        'orderId' => 'LAB-' . str_pad((string)$order['id'], 4, '0', STR_PAD_LEFT),
        'patient' => trim($order['firstName'] . ' ' . $order['lastName']),
        'test' => $order['testType'] ?: 'Laboratory test',
        'priority' => strtoupper((string)$order['abnormalFlag']) === 'Y' ? 'High' : 'Routine',
        'status' => 'Released',
        'time' => $order['testDate'],
    ];
}, $recentTestOrders));
$dashboardMetricsJson = json_encode([
    'patients' => (int)$patientsCount,
    'appointments' => (int)$appointmentsCount,
    'labResults' => (int)$labResultsCount,
    'completedAppointments' => (int)$completedAppointments,
    'totalAppointments' => (int)$totalAppointments,
    'pendingReviews' => (int)$pendingReviews,
    'accuracyRate' => (int)$accuracyRate
]);
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>MedLab Pro Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@600;700;800&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
        .w-sidebar { width: 260px; }
        .ml-sidebar { margin-left: 260px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md overflow-hidden">
<!-- SideNavBar (Execution from JSON) -->
<?php $active = 'dashboard'; require __DIR__ . '/frontend/partials/sidebar.php'; ?>
<!-- Main Workspace -->
<main class="ml-sidebar h-screen flex flex-col overflow-y-auto overflow-x-hidden no-scrollbar">
<!-- TopNavBar (Execution from JSON) -->
<header class="bg-surface sticky top-0 flex justify-between items-center h-16 px-gutter w-full border-b border-outline-variant/30 z-40">
<div class="flex flex-col">
<h2 class="font-headline-lg text-headline-lg text-primary">Dashboard</h2>
<p class="text-body-sm text-on-surface-variant">Laboratory operations overview</p>
</div>
<div class="flex items-center gap-4">
<div class="relative w-80">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">search</span>
<input id="dashboardSearchInput" class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-lg focus:ring-2 focus:ring-primary text-body-md" placeholder="Search patient, MRN, order" type="text"/>
</div>
<div class="relative">
<button id="dashboardNotificationButton" class="p-2 text-on-surface-variant hover:bg-surface-container-low rounded-full transition-all relative" type="button" aria-label="Notifications">
<span class="material-symbols-outlined">notifications</span>
<span id="dashboardNotificationBadge" class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full hidden"></span>
</button>
<div id="dashboardNotificationPanel" class="hidden absolute right-0 mt-3 w-96 bg-surface-container-lowest border border-outline-variant/50 rounded-xl shadow-2xl overflow-hidden z-50">
<div class="px-4 py-3 border-b border-outline-variant/30 flex items-center justify-between">
<div>
<p class="font-label-bold text-label-bold text-on-surface">Notifications</p>
<p class="text-body-sm text-tertiary">Recent patient activity</p>
</div>
<button id="dashboardMarkReadButton" type="button" class="text-label-bold text-primary hover:underline">Mark all read</button>
</div>
<div id="dashboardNotificationList" class="max-h-72 overflow-y-auto"></div>
</div>
</div>
</div>
</header>
<!-- Content Canvas -->
<div class="p-gutter space-y-gutter pb-20">
<!-- Bento Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-gutter">
<div class="bg-surface-container-lowest border border-outline-variant/30 p-container-padding rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer">
<div class="flex items-center gap-3 mb-2">
<span class="material-symbols-outlined text-primary text-[24px]">group</span>
<p class="text-on-surface-variant text-body-sm">Total Patients</p>
</div>
<div class="flex items-end justify-between mt-4">
<h3 id="totalPatientsCount" class="text-[40px] font-bold leading-tight">0</h3>
<span class="text-on-secondary-fixed-variant font-label-bold text-label-bold mb-1">+12%</span>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant/30 p-container-padding rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer">
<div class="flex items-center gap-3 mb-2">
<span class="material-symbols-outlined text-secondary text-[24px]">folder_open</span>
<p class="text-on-surface-variant text-body-sm">Active Cases</p>
</div>
<div class="flex items-end justify-between mt-4">
<h3 id="activeCasesCount" class="text-[40px] font-bold leading-tight">0</h3>
<span class="text-primary font-label-bold text-label-bold mb-1">Ongoing</span>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant/30 p-container-padding rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer">
<div class="flex items-center gap-3 mb-2">
<span class="material-symbols-outlined text-tertiary text-[24px]">assignment</span>
<p class="text-on-surface-variant text-body-sm">Pending Reviews</p>
</div>
<div class="flex items-end justify-between mt-4">
<h3 id="pendingReviewsCount" class="text-[40px] font-bold leading-tight">0</h3>
<span class="text-error font-label-bold text-label-bold mb-1">Requires action</span>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant/30 p-container-padding rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer">
<div class="flex items-center gap-3 mb-2">
<span class="material-symbols-outlined text-primary text-[24px]">check_circle</span>
<p class="text-on-surface-variant text-body-sm">Accuracy Rate</p>
</div>
<div class="flex items-end justify-between mt-4">
<h3 id="accuracyRateCount" class="text-[40px] font-bold leading-tight">0%</h3>
<span class="text-primary font-label-bold text-label-bold mb-1">Performance</span>
</div>
</div>
</div>
<!-- Workflow Pipeline -->
<div class="bg-surface-container-lowest border border-outline-variant/30 p-container-padding rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-md transition-shadow duration-300">
<div class="flex justify-between items-center mb-8">
<h4 class="font-headline-md text-headline-md">Workflow pipeline</h4>
<span class="text-body-sm text-on-surface-variant opacity-60">Live queue by stage</span>
</div>
<div class="grid grid-cols-6 gap-6">
<!-- Workflow Stages -->
<div class="space-y-2 hover:bg-surface-container-low p-2 rounded-lg transition-colors cursor-pointer">
<p class="text-label-bold text-on-surface-variant/70">Registration</p>
<p id="registrationStageCount" class="font-bold text-body-md">0</p>
<div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
<div id="registrationStageBar" class="h-full w-0 bg-primary rounded-full transition-all duration-500"></div>
</div>
</div>
<div class="space-y-2 hover:bg-surface-container-low p-2 rounded-lg transition-colors cursor-pointer">
<p class="text-label-bold text-on-surface-variant/70">Collection</p>
<p id="collectionStageCount" class="font-bold text-body-md">0</p>
<div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
<div id="collectionStageBar" class="h-full w-0 bg-primary rounded-full transition-all duration-500"></div>
</div>
</div>
<div class="space-y-2 hover:bg-surface-container-low p-2 rounded-lg transition-colors cursor-pointer">
<p class="text-label-bold text-on-surface-variant/70">Processing</p>
<p id="processingStageCount" class="font-bold text-body-md">0</p>
<div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
<div id="processingStageBar" class="h-full w-0 bg-primary rounded-full transition-all duration-500"></div>
</div>
</div>
<div class="space-y-2 hover:bg-surface-container-low p-2 rounded-lg transition-colors cursor-pointer">
<p class="text-label-bold text-on-surface-variant/70">Analysis</p>
<p id="analysisStageCount" class="font-bold text-body-md">0</p>
<div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
<div id="analysisStageBar" class="h-full w-0 bg-primary rounded-full transition-all duration-500"></div>
</div>
</div>
<div class="space-y-2 hover:bg-surface-container-low p-2 rounded-lg transition-colors cursor-pointer">
<p class="text-label-bold text-on-surface-variant/70">Verification</p>
<p id="verificationStageCount" class="font-bold text-body-md">0</p>
<div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
<div id="verificationStageBar" class="h-full w-0 bg-primary rounded-full transition-all duration-500"></div>
</div>
</div>
<div class="space-y-2 hover:bg-surface-container-low p-2 rounded-lg transition-colors cursor-pointer">
<p class="text-label-bold text-on-surface-variant/70">Release</p>
<p id="releaseStageCount" class="font-bold text-body-md">0</p>
<div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
<div id="releaseStageBar" class="h-full w-0 bg-primary rounded-full transition-all duration-500"></div>
</div>
</div>
</div>
</div>
<!-- Main Workspace Bottom Area -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start w-full">
<!-- Recent Test Orders -->
<div class="lg:col-span-9 bg-surface-container-lowest border border-outline-variant/30 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] overflow-hidden hover:shadow-md transition-shadow duration-300">
<div class="p-6 border-b border-outline-variant/20 flex justify-between items-center">
<h4 class="font-headline-md text-headline-md">Recent test orders</h4>
<button class="px-4 py-1.5 border border-outline-variant text-on-surface font-label-bold text-label-bold rounded-lg hover:bg-surface-container-low transition-colors">View all</button>
</div>
<table class="w-full text-left border-collapse">
<thead class="bg-surface-container-low">
<tr>
<th class="px-6 py-3 text-label-caps text-on-surface-variant/60 uppercase">Order</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant/60 uppercase">Patient</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant/60 uppercase">Tests</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant/60 uppercase text-center">Priority</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant/60 uppercase text-center">Status</th>
<th class="px-6 py-3 text-label-caps text-on-surface-variant/60 uppercase text-right">Time</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant/10">
<tr id="ordersEmptyState">
<td colspan="6" class="px-6 py-8 text-center text-body-sm text-on-surface-variant">No test orders yet.</td>
</tr>
</tbody>
</table>
</div>
<!-- Activity Feed -->
<div class="lg:col-span-3 bg-surface-container-lowest border border-outline-variant/30 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col h-full hover:shadow-md transition-shadow duration-300">
<div class="p-6 border-b border-outline-variant/20">
<h4 class="font-headline-md text-headline-md">Activity feed</h4>
</div>
<div id="activityFeed" class="p-6 space-y-8 flex-1 overflow-y-auto no-scrollbar">
</div>
</div>
</div>
</div>
</main>
<!-- Micro-interaction Script -->
<script>
        const dashboardSearchInput = document.getElementById('dashboardSearchInput');
        const dashboardNotificationButton = document.getElementById('dashboardNotificationButton');
        const dashboardNotificationPanel = document.getElementById('dashboardNotificationPanel');
        const dashboardNotificationList = document.getElementById('dashboardNotificationList');
        const dashboardNotificationBadge = document.getElementById('dashboardNotificationBadge');
        const dashboardMarkReadButton = document.getElementById('dashboardMarkReadButton');
        const ordersEmptyState = document.getElementById('ordersEmptyState');
        const activityFeed = document.getElementById('activityFeed');

        const dashboardInitialPatients = <?php echo $dashboardPatientsJson ?: '[]'; ?>;
        const dashboardInitialOrders = <?php echo $dashboardOrdersJson ?: '[]'; ?>;
        const dashboardInitialMetrics = <?php echo $dashboardMetricsJson ?: '{"patients":0,"appointments":0,"labResults":0}'; ?>;

        let dashboardPatients = [];
        let dashboardOrders = [];
        let dashboardNotifications = [];

        function getPatients() {
            const value = localStorage.getItem('asclepius_patients');
            return value ? JSON.parse(value) : [];
        }

        function getNotifications() {
            const value = localStorage.getItem('asclepius_notifications');
            return value ? JSON.parse(value) : [];
        }

        function saveNotifications(notifications) {
            localStorage.setItem('asclepius_notifications', JSON.stringify(notifications));
        }

        function formatDateTime(value) {
            const date = new Date(value);
            return Number.isNaN(date.getTime()) ? '' : date.toLocaleString();
        }

        async function fetchPatientStats() {
            try {
                const resp = await fetch('Patient.php?api=get_stats');
                if (!resp.ok) {
                    console.warn('Patient stats fetch failed:', resp.status);
                    return;
                }
                const data = await resp.json();
                // Update the in-memory metrics used by renderMetrics
                if (data && typeof data.total !== 'undefined') {
                    dashboardInitialMetrics.patients = Number(data.total);
                }
                if (data && typeof data.active !== 'undefined') {
                    dashboardInitialMetrics.active = Number(data.active);
                }
                if (data && typeof data.inactive !== 'undefined') {
                    dashboardInitialMetrics.inactive = Number(data.inactive);
                }
            } catch (err) {
                console.error('Error fetching patient stats:', err);
            }
        }

        function renderMetrics() {
            const totalPatients = Number(dashboardInitialMetrics.patients || dashboardPatients.length);
            const unreadNotifications = dashboardNotifications.filter(notification => !notification.read).length;
            const activeCases = Number(dashboardInitialMetrics.appointments || 0);
            const pendingReviews = Number(dashboardInitialMetrics.pendingReviews || 0);
            const accuracyRate = Number(dashboardInitialMetrics.accuracyRate || 0);

            // Update new metrics
            const totalEl = document.getElementById('totalPatientsCount');
            if (totalEl) totalEl.textContent = String(totalPatients);
            const activeEl = document.getElementById('activeCasesCount');
            if (activeEl) activeEl.textContent = String(activeCases);
            const pendingEl = document.getElementById('pendingReviewsCount');
            if (pendingEl) pendingEl.textContent = String(pendingReviews);
            const accuracyEl = document.getElementById('accuracyRateCount');
            if (accuracyEl) accuracyEl.textContent = String(accuracyRate) + '%';

            const stageValues = [
                ['registrationStageCount', 'registrationStageBar', totalPatients],
                ['collectionStageCount', 'collectionStageBar', Math.max(totalPatients - 1, 0)],
                ['processingStageCount', 'processingStageBar', Math.max(totalPatients - 2, 0)],
                ['analysisStageCount', 'analysisStageBar', Math.max(totalPatients - 3, 0)],
                ['verificationStageCount', 'verificationStageBar', Math.max(totalPatients - 4, 0)],
                ['releaseStageCount', 'releaseStageBar', Math.max(totalPatients - 5, 0)]
            ];

            const peak = Math.max(totalPatients, 1);
            stageValues.forEach(([countId, barId, count]) => {
                const el = document.getElementById(countId);
                if (el) el.textContent = String(count);
                const bar = document.getElementById(barId);
                if (bar) bar.style.width = `${Math.max(Math.round((count / peak) * 100), count > 0 ? 12 : 0)}%`;
            });
        }

        function renderOrders() {
            const tbody = document.querySelector('tbody');
            const rows = dashboardOrders.slice(0, 5).map((order) => `
                <tr class="table-row-hover">
                    <td class="px-6 py-4 text-body-sm text-on-surface-variant">${order.orderId}</td>
                    <td class="px-6 py-4 font-medium text-on-surface">${order.patient || 'Unknown'}</td>
                    <td class="px-6 py-4 text-body-sm text-on-surface-variant">${order.test}</td>
                    <td class="px-6 py-4 text-center"><span class="px-2 py-1 rounded-full text-xs font-bold ${order.priority === 'High' ? 'bg-error-container text-on-error-container' : 'bg-primary/10 text-primary'}">${order.priority}</span></td>
                    <td class="px-6 py-4 text-center"><span class="px-2 py-1 rounded-full text-xs font-bold bg-secondary/10 text-secondary">${order.status}</span></td>
                    <td class="px-6 py-4 text-right text-body-sm text-on-surface-variant">${order.time}</td>
                </tr>
            `).join('');

            tbody.innerHTML = rows || '<tr id="ordersEmptyState"><td colspan="6" class="px-6 py-8 text-center text-body-sm text-on-surface-variant">No test orders yet.</td></tr>';
        }

        function renderActivityFeed() {
            if (!dashboardNotifications.length && !dashboardPatients.length) {
                activityFeed.innerHTML = '<div class="text-body-sm text-on-surface-variant">No recent activity.</div>';
                return;
            }

            const notificationItems = dashboardNotifications.slice(0, 3).map(notification => `
                <div class="flex gap-3">
                    <div class="w-2 h-2 mt-2 rounded-full ${notification.read ? 'bg-surface-dim' : 'bg-error'}"></div>
                    <div>
                        <p class="text-body-md text-on-surface">${notification.message}</p>
                        <p class="text-body-sm text-on-surface-variant">${formatDateTime(notification.createdAt)}</p>
                    </div>
                </div>
            `);

            const patientItems = dashboardPatients.slice(0, 2).map(patient => `
                <div class="flex gap-3">
                    <div class="w-2 h-2 mt-2 rounded-full bg-primary"></div>
                    <div>
                        <p class="text-body-md text-on-surface">Registered patient ${patient.fullName}</p>
                        <p class="text-body-sm text-on-surface-variant">${patient.mrn || 'MRN pending'} | ${formatDateTime(patient.createdAt)}</p>
                    </div>
                </div>
            `);

            activityFeed.innerHTML = [...notificationItems, ...patientItems].join('') || '<div class="text-body-sm text-on-surface-variant">No recent activity.</div>';
        }

        function renderNotifications() {
            const unreadCount = dashboardNotifications.filter(notification => !notification.read).length;
            dashboardNotificationBadge.classList.toggle('hidden', unreadCount === 0);

            if (!dashboardNotifications.length) {
                dashboardNotificationList.innerHTML = '<div class="px-4 py-6 text-body-sm text-tertiary">No notifications yet.</div>';
                return;
            }

            dashboardNotificationList.innerHTML = dashboardNotifications.map(notification => `
                <div class="px-4 py-3 border-b border-outline-variant/20 ${notification.read ? 'bg-surface-container-lowest' : 'bg-primary/5'}">
                    <p class="text-body-md text-on-surface">${notification.message}</p>
                    <p class="text-body-sm text-tertiary mt-1">${formatDateTime(notification.createdAt)}</p>
                </div>
            `).join('');
        }

        function refreshDashboard() {
            dashboardPatients = getPatients();
            dashboardPatients = dashboardPatients.length ? dashboardPatients : dashboardInitialPatients;
            dashboardOrders = dashboardInitialOrders;
            dashboardNotifications = getNotifications();
            renderMetrics();
            renderOrders();
            renderActivityFeed();
            renderNotifications();
        }

        function markAllNotificationsRead() {
            saveNotifications(getNotifications().map(notification => ({ ...notification, read: true })));
            refreshDashboard();
        }

        function toggleNotificationPanel() {
            dashboardNotificationPanel.classList.toggle('hidden');
            if (!dashboardNotificationPanel.classList.contains('hidden')) {
                markAllNotificationsRead();
            }
        }

        function applySearchFilter() {
            const term = dashboardSearchInput.value.trim().toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                if (row.id === 'ordersEmptyState') return;
                row.classList.toggle('hidden', Boolean(term) && !row.textContent.toLowerCase().includes(term));
            });

            const visibleRows = Array.from(rows).filter(row => row.id !== 'ordersEmptyState' && !row.classList.contains('hidden'));
            if (visibleRows.length === 0) {
                document.querySelector('tbody').innerHTML = `<tr id="ordersEmptyState"><td colspan="6" class="px-6 py-8 text-center text-body-sm text-on-surface-variant">${term ? 'No matching records found.' : 'No test orders yet.'}</td></tr>`;
            }
        }

        dashboardSearchInput.addEventListener('input', applySearchFilter);
        dashboardNotificationButton.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleNotificationPanel();
        });
        dashboardMarkReadButton.addEventListener('click', markAllNotificationsRead);

        document.addEventListener('click', (event) => {
            if (!dashboardNotificationPanel.contains(event.target) && !dashboardNotificationButton.contains(event.target)) {
                dashboardNotificationPanel.classList.add('hidden');
            }
        });

        document.querySelectorAll('tr').forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.cursor = 'pointer';
            });
        });

        // Simple Fade-in animation for cards
        const observerOptions = {
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('opacity-100', 'translate-y-0');
                    entry.target.classList.remove('opacity-0', 'translate-y-4');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.bg-surface-container-lowest').forEach(el => {
            el.classList.add('transition-all', 'duration-500', 'opacity-0', 'translate-y-4');
            observer.observe(el);
        });

        // Fetch latest patient stats from the server, then render dashboard
        fetchPatientStats().then(() => {
            refreshDashboard();
            applySearchFilter();
        }).catch((err) => {
            console.error('Failed to fetch patient stats on init:', err);
            refreshDashboard();
            applySearchFilter();
        });

        // Poll patient stats periodically so the dashboard reflects database changes
        setInterval(fetchPatientStats, 10000);
    </script>
</body></html>
