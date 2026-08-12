<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle = View::hasSection('title') 
            ? View::getSection('title') 
            : (!empty($title) ? $title : (isset($headerTitle) ? $headerTitle : null));

        if (empty($pageTitle)) {
            $pageTitle = app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard';
        }
    @endphp
    <title>{{ $pageTitle }}</title>

    <!-- Bootstrap 5.3 CSS (RTL / LTR) -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @endif

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Select2 CSS & Bootstrap 5 Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

    <!-- App Design System Styles / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dark Mode Theme Script -->
    <script>
        (function() {
            if (localStorage.getItem('costs_theme') === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        })();

        function applyDarkModePreference() {
            const isDark = localStorage.getItem('costs_theme') === 'dark';
            const htmlEl = document.documentElement;
            const bodyEl = document.body;
            const iconEl = document.getElementById('darkModeIcon');

            if (isDark) {
                htmlEl.setAttribute('data-bs-theme', 'dark');
                if (bodyEl) bodyEl.classList.add('dark-mode');
                if (iconEl) iconEl.className = 'bi bi-sun-fill fs-5 text-warning';
            } else {
                htmlEl.setAttribute('data-bs-theme', 'light');
                if (bodyEl) bodyEl.classList.remove('dark-mode');
                if (iconEl) iconEl.className = 'bi bi-moon-stars fs-5 text-slate-700';
            }
        }

        function toggleDarkMode() {
            if (localStorage.getItem('costs_theme') === 'dark') {
                localStorage.setItem('costs_theme', 'light');
            } else {
                localStorage.setItem('costs_theme', 'dark');
            }
            applyDarkModePreference();
        }

        document.addEventListener('DOMContentLoaded', applyDarkModePreference);
    </script>

    @php
        $primaryColor = setting('primary_color', '#2563eb');
        $secondaryColor = setting('secondary_color', '#0f172a');
        $accentColor = setting('accent_color', '#10b981');
        $sidebarBg = setting('sidebar_bg', '#0f172a');
        $sidebarIconColor = setting('sidebar_icon_color', '#3b82f6');
        
        $hexToRgb = function($hex) {
            $hex = ltrim($hex, '#');
            if (strlen($hex) == 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "$r, $g, $b";
        };
        $primaryRgb = $hexToRgb($primaryColor);
        $secondaryRgb = $hexToRgb($secondaryColor);
        $accentRgb = $hexToRgb($accentColor);
        $sidebarBgRgb = $hexToRgb($sidebarBg);
    @endphp
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --secondary-color: {{ $secondaryColor }};
            --accent-color: {{ $accentColor }};
            --sidebar-bg: {{ $sidebarBg }};
            --sidebar-icon-color: {{ $sidebarIconColor }};
            --color-primary: {{ $primaryColor }};
            --color-primary-hover: {{ $primaryColor }};
            --color-primary-light: rgba({{ $primaryRgb }}, 0.12);
            --color-primary-rgb: {{ $primaryRgb }};
            --color-accent: {{ $accentColor }};
            --color-accent-rgb: {{ $accentRgb }};
            --bg-sidebar: {{ $sidebarBg }};
            --bg-sidebar-active: {{ $primaryColor }};
            --bs-primary: {{ $primaryColor }};
            --bs-primary-rgb: {{ $primaryRgb }};
            --bs-secondary: {{ $secondaryColor }};
            --bs-secondary-rgb: {{ $secondaryRgb }};
            --bs-success: {{ $accentColor }};
            --bs-success-rgb: {{ $accentRgb }};
        }
        #sidebar, .sidebar-wrapper, .bg-sidebar {
            background-color: var(--sidebar-bg) !important;
        }
        #sidebar .sidebar-nav-link i,
        #sidebar .sidebar-group-btn i,
        #sidebar .sidebar-sub-link i,
        #sidebar .sidebar-icon,
        #sidebar .text-primary {
            color: var(--sidebar-icon-color) !important;
        }
        #sidebar .sidebar-nav-link.active i,
        #sidebar .sidebar-sub-link.active i {
            color: #60a5fa !important;
        }
        .btn-primary, .btn-primary-custom {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: #ffffff !important;
        }
        .btn-primary:hover, .btn-primary-custom:hover {
            filter: brightness(0.92) !important;
        }
        .btn-accent, .btn-emerald-custom {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
            color: #ffffff !important;
        }
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        .text-primary {
            color: var(--primary-color) !important;
        }
        .border-primary {
            border-color: var(--primary-color) !important;
        }
        .badge.bg-primary-subtle, .bg-primary-subtle {
            background-color: rgba({{ $primaryRgb }}, 0.15) !important;
            color: var(--primary-color) !important;
        }
        .badge.bg-success-subtle, .bg-success-subtle {
            background-color: rgba({{ $accentRgb }}, 0.15) !important;
            color: var(--accent-color) !important;
        }
        #sidebar .sidebar-nav-link.active i,
        #sidebar .sidebar-sub-link.active i {
            color: var(--primary-color, #60a5fa) !important;
        }
        [dir="rtl"] .sidebar-nav-link.active,
        [dir="rtl"] .sidebar-sub-link.active {
            border-right: 3px solid var(--primary-color) !important;
            border-left: none !important;
        }
        [dir="ltr"] .sidebar-nav-link.active,
        [dir="ltr"] .sidebar-sub-link.active {
            border-left: 3px solid var(--primary-color) !important;
            border-right: none !important;
        }
        .sidebar-nav-link.active,
        .sidebar-sub-link.active {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            font-weight: 600 !important;
            box-shadow: none !important;
        }
        .nav-pills .nav-link.active, .page-item.active .page-link, .form-check-input:checked {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        .text-accent, .text-success {
            color: var(--accent-color) !important;
        }
        .text-secondary {
            color: var(--secondary-color) !important;
        }
        .bg-secondary {
            background-color: var(--secondary-color) !important;
            color: #ffffff !important;
        }
        .btn-secondary {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
            color: #ffffff !important;
        }
        .btn-outline-secondary {
            color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
        }
        .btn-outline-secondary:hover {
            background-color: var(--secondary-color) !important;
            color: #ffffff !important;
        }
        .badge.bg-secondary-subtle, .bg-secondary-subtle {
            background-color: rgba({{ $secondaryRgb }}, 0.15) !important;
            color: var(--secondary-color) !important;
        }
        header, .navbar-custom {
            border-top: 3px solid var(--secondary-color) !important;
        }
        .user-role-badge, .badge-secondary-accent {
            background-color: rgba({{ $secondaryRgb }}, 0.12) !important;
            color: var(--secondary-color) !important;
            border: 1px solid rgba({{ $secondaryRgb }}, 0.2) !important;
        }
        /* Sleek Modern Sidebar Custom Scrollbar */
        #sidebar, .sidebar-sticky-wrapper, .custom-scrollbar, .sidebar-menu-wrapper {
            scrollbar-width: thin !important;
            scrollbar-color: rgba(255, 255, 255, 0.25) transparent !important;
            overscroll-behavior-y: contain !important;
            -webkit-overflow-scrolling: touch !important;
        }
        #sidebar::-webkit-scrollbar, .sidebar-sticky-wrapper::-webkit-scrollbar, .custom-scrollbar::-webkit-scrollbar, .sidebar-menu-wrapper::-webkit-scrollbar {
            width: 5px !important;
        }
        #sidebar::-webkit-scrollbar-track, .sidebar-sticky-wrapper::-webkit-scrollbar-track, .custom-scrollbar::-webkit-scrollbar-track, .sidebar-menu-wrapper::-webkit-scrollbar-track {
            background: transparent !important;
        }
        #sidebar::-webkit-scrollbar-thumb, .sidebar-sticky-wrapper::-webkit-scrollbar-thumb, .custom-scrollbar::-webkit-scrollbar-thumb, .sidebar-menu-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2) !important;
            border-radius: 10px !important;
            transition: background 0.2s ease !important;
        }
        #sidebar::-webkit-scrollbar-thumb:hover, .sidebar-sticky-wrapper::-webkit-scrollbar-thumb:hover, .custom-scrollbar::-webkit-scrollbar-thumb:hover, .sidebar-menu-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5) !important;
        }
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
        }
        main {
            min-width: 0 !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }
        .content-wrapper {
            min-width: 0 !important;
            max-width: 100% !important;
        }
        .dataTables_wrapper {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: auto !important;
        }
        .hover-lift {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
        }

        /* Align table headers with data cells (start / right in Arabic) */
        .table th:not(.text-center):not(.text-end),
        .table thead th:not(.text-center):not(.text-end),
        table.dataTable thead th:not(.text-center):not(.text-end) {
            text-align: start !important;
        }

        /* Select2 Custom Polish */
        .select2-container--bootstrap-5 .select2-selection {
            font-family: inherit;
            font-size: 0.875rem;
            min-height: 38px;
            border-color: #cbd5e1;
            border-radius: 0.375rem;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #1e293b;
            line-height: 2.2;
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: #cbd5e1;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            z-index: 1060 !important;
        }
        .select2-container--bootstrap-5 .select2-search__field {
            font-size: 0.875rem;
            border-radius: 0.25rem;
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
            background-color: var(--color-primary, #2563eb);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-body">

    <!-- Unified Header -->
    <x-header />

    <!-- Main Outer Wrapper -->
    <div class="d-flex flex-grow-1 position-relative align-items-stretch" style="min-width: 0; max-width: 100vw; overflow-x: hidden; min-height: 100vh;">
        <!-- Sidebar Navigation -->
        <x-sidebar />

        <!-- Main Content Area -->
        <main class="flex-grow-1 p-3 p-md-4 min-vh-100-minus-header" style="min-width: 0; max-width: 100%; overflow-x: hidden;">
            <div class="container-fluid max-w-7xl mx-auto" style="min-width: 0; max-width: 100%;">
                
                <!-- Unified Flash Alerts -->
                <x-alerts />

                <!-- Page Header Component or Header Slot -->
                @if (isset($header))
                    @php
                        $isRtl = app()->getLocale() == 'ar';
                        $circlePosition = $isRtl ? 'left: -10px;' : 'right: -10px;';
                    @endphp
                    <div class="page-header-card card border-0 shadow-sm rounded-3 px-3 px-md-4 py-2.5 py-md-2.5 mb-3 position-relative" style="z-index: 10;">
                        <!-- Compact Semi-circle Backdrop Shape in Corner with Emerald Accent Gradient -->
                        <div style="position: absolute; inset: 0; overflow: hidden; border-radius: inherit; pointer-events: none; z-index: 0;">
                            <div style="position: absolute; top: -10px; {{ $circlePosition }} width: 65px; height: 65px; border-radius: 50%; background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(59, 130, 246, 0.10)); pointer-events: none;"></div>
                        </div>

                        <div class="position-relative" style="z-index: 2;">
                            {{ $header }}
                        </div>
                    </div>
                @endif

                <!-- Main Page View Content -->
                <div class="content-wrapper" style="min-width: 0; max-width: 100%;">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <x-footer />

    <!-- Global System Toast Container (Top-End Fixed) -->
    <div id="system-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery, DataTables & Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Global Web Audio API Sound Synthesizer & Toast Alert Engine -->
    <script>
        (function() {
            let audioCtx = null;

            function getAudioContext() {
                if (!audioCtx) {
                    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    if (AudioContextClass) {
                        audioCtx = new AudioContextClass();
                    }
                }
                if (audioCtx && audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                return audioCtx;
            }

            window.playAudioAlert = function(type = 'notification') {
                try {
                    const ctx = getAudioContext();
                    if (!ctx) return;

                    const now = ctx.currentTime;
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    if (type === 'success') {
                        // Cashier Sale / Payment Completion: Double Crisp Chime (C5 -> G5)
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(523.25, now);
                        osc.frequency.exponentialRampToValueAtTime(783.99, now + 0.12);
                        gain.gain.setValueAtTime(0.2, now);
                        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.35);
                        osc.start(now);
                        osc.stop(now + 0.35);
                    } else if (type === 'warning') {
                        // Low Stock Reorder Level / Warning: Dual-tone warning (A4 -> F4)
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(440.00, now);
                        osc.frequency.setValueAtTime(349.23, now + 0.12);
                        gain.gain.setValueAtTime(0.25, now);
                        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.4);
                        osc.start(now);
                        osc.stop(now + 0.4);
                    } else {
                        // Due Cheque / Notification: Soft Bell Chime (D5 -> A5)
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(587.33, now);
                        osc.frequency.exponentialRampToValueAtTime(880.00, now + 0.15);
                        gain.gain.setValueAtTime(0.18, now);
                        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.45);
                        osc.start(now);
                        osc.stop(now + 0.45);
                    }
                } catch (e) {
                    console.warn('Audio notice:', e);
                }
            };

            window.showSystemToast = function(title, message, type = 'info', actionUrl = null) {
                const container = document.getElementById('system-toast-container');
                if (!container) return;

                const bgClasses = {
                    'success': 'bg-white border-start border-4 border-success text-slate-900',
                    'warning': 'bg-white border-start border-4 border-warning text-slate-900',
                    'danger':  'bg-white border-start border-4 border-danger text-slate-900',
                    'info':    'bg-white border-start border-4 border-primary text-slate-900',
                };

                const iconClasses = {
                    'success': 'bi-check-circle-fill text-success',
                    'warning': 'bi-exclamation-triangle-fill text-warning',
                    'danger':  'bi-x-circle-fill text-danger',
                    'info':    'bi-bell-fill text-primary',
                };

                const toastId = 'toast-' + Math.random().toString(36).substring(2, 9);
                const toastHtml = `
                    <div id="${toastId}" class="toast align-items-center shadow-lg rounded-3 border-0 ${bgClasses[type] || bgClasses.info} mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex p-3">
                            <div class="fs-4 me-3 d-flex align-items-center">
                                <i class="bi ${iconClasses[type] || iconClasses.info}"></i>
                            </div>
                            <div class="toast-body p-0 flex-grow-1">
                                <h6 class="mb-1 font-bold fs-7">${title}</h6>
                                <p class="mb-0 fs-8 text-slate-600">${message}</p>
                                ${actionUrl ? `<a href="${actionUrl}" class="btn btn-xs btn-link p-0 text-primary font-bold mt-1 fs-8 text-decoration-none">عرض التفاصيل <i class="bi bi-arrow-left"></i></a>` : ''}
                            </div>
                            <button type="button" class="btn-close ms-2 me-0 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', toastHtml);
                const toastEl = document.getElementById(toastId);
                if (toastEl && typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                    const bsToast = new bootstrap.Toast(toastEl, { delay: 6000 });
                    bsToast.show();
                    window.playAudioAlert(type);
                }
            };
        })();

        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const STORAGE_KEY = 'costs_sidebar_collapsed';

            // Create Backdrop Overlay for Mobile Drawer
            let backdrop = document.querySelector('.sidebar-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'sidebar-backdrop';
                document.body.appendChild(backdrop);
            }

            function openMobileSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-collapsed');
                sidebar.classList.add('show-mobile');
                backdrop.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('show-mobile');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }

            function toggleSidebar() {
                if (!sidebar) return;
                if (window.innerWidth < 992) {
                    if (sidebar.classList.contains('show-mobile')) {
                        closeMobileSidebar();
                    } else {
                        openMobileSidebar();
                    }
                } else {
                    sidebar.classList.toggle('sidebar-collapsed');
                    document.body.classList.toggle('sidebar-collapsed');
                    const collapsedNow = sidebar.classList.contains('sidebar-collapsed');
                    localStorage.setItem(STORAGE_KEY, collapsedNow ? 'true' : 'false');
                }
            }

            // Bind click handler to ALL toggle buttons across header and menus
            document.querySelectorAll('#sidebarToggle, .sidebar-toggle-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    toggleSidebar();
                });
            });

            // Close mobile sidebar on backdrop click or close button click
            backdrop.addEventListener('click', closeMobileSidebar);

            const closeBtn = document.getElementById('closeMobileSidebar');
            if (closeBtn) {
                closeBtn.addEventListener('click', closeMobileSidebar);
            }

            // Initial State setup
            if (window.innerWidth >= 992) {
                const isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
                if (isCollapsed && sidebar) {
                    sidebar.classList.add('sidebar-collapsed');
                    document.body.classList.add('sidebar-collapsed');
                }
            } else if (sidebar) {
                sidebar.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-collapsed');
            }

            // Handle window resize events
            window.addEventListener('resize', function () {
                if (window.innerWidth < 992) {
                    if (sidebar) {
                        sidebar.classList.remove('sidebar-collapsed');
                        document.body.classList.remove('sidebar-collapsed');
                    }
                } else {
                    closeMobileSidebar();
                    const isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
                    if (isCollapsed && sidebar) {
                        sidebar.classList.add('sidebar-collapsed');
                        document.body.classList.add('sidebar-collapsed');
                    }
                }
            });

            // Desktop Auto Un-collapse: Expand sidebar if user clicks a menu group button while collapsed
            if (sidebar) {
                sidebar.addEventListener('click', function (e) {
                    const groupBtn = e.target.closest('.sidebar-group-btn, .sidebar-nav-link');
                    if (groupBtn && window.innerWidth >= 992 && sidebar.classList.contains('sidebar-collapsed')) {
                        sidebar.classList.remove('sidebar-collapsed');
                        document.body.classList.remove('sidebar-collapsed');
                        localStorage.setItem(STORAGE_KEY, 'false');
                    }
                });
            }

            // Isolated Independent Sidebar Wheel Scroll
            if (sidebar) {
                sidebar.addEventListener('wheel', function(e) {
                    const targetContainer = sidebar.querySelector('.sidebar-sticky-wrapper') || sidebar.querySelector('.sidebar-menu-wrapper') || sidebar;
                    if (targetContainer) {
                        targetContainer.scrollTop += e.deltaY;
                    }
                    e.preventDefault();
                    e.stopPropagation();
                }, { passive: false });
            }

            // Initialize Bootstrap Tooltips globally
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {
                        trigger: 'hover'
                    });
                });
            }

            // Initialize DataTables automatically on tables with .datatable class or standard data tables
            if (window.jQuery && $.fn.DataTable) {
                $.fn.dataTable.ext.errMode = 'none';
                const isAr = '{{ app()->getLocale() }}' === 'ar';
                const arabicTranslation = {
                    "sProcessing": "جاري التحميل...",
                    "sLengthMenu": "عرض _MENU_",
                    "sZeroRecords": "لم يتم العثور على سجلات",
                    "sInfo": "عرض _START_-_END_ من _TOTAL_",
                    "sInfoEmpty": "عرض 0 من 0",
                    "sInfoFiltered": "(من _MAX_)",
                    "sSearch": "بحث:",
                    "oPaginate": {
                        "sFirst": "الأول",
                        "sPrevious": "السابق",
                        "sNext": "التالي",
                        "sLast": "الأخير"
                    }
                };

                $('.datatable, table.datatable-init').each(function() {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({
                            language: isAr ? arabicTranslation : {},
                            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, isAr ? "عرض الكل" : "All"]],
                            pageLength: 25,
                            responsive: true,
                            ordering: true,
                            autoWidth: false
                        });
                    }
                });
            }

            // Initialize Select2 with internal search on all <select> elements globally
            window.initSelect2 = function(container) {
                if (!window.jQuery || !$.fn.select2) return;
                const $scope = container ? $(container) : $(document.body);
                const $selects = $scope.is('select') ? $scope : $scope.find('select');
                $selects.filter(':not(.no-select2):not(.select2-hidden-accessible)').each(function() {
                    const $select = $(this);
                    if ($select.parents('.dataTables_length').length) return;

                    const $modal = $select.closest('.modal');
                    $select.select2({
                        theme: 'bootstrap-5',
                        dir: '{{ app()->getLocale() }}' === 'ar' ? 'rtl' : 'ltr',
                        width: '100%',
                        dropdownParent: $modal.length ? $modal : $(document.body),
                        language: {
                            noResults: function() {
                                return '{{ app()->getLocale() }}' === 'ar' ? "لا توجد نتائج" : "No results found";
                            },
                            searching: function() {
                                return '{{ app()->getLocale() }}' === 'ar' ? "جاري البحث..." : "Searching...";
                            }
                        }
                    });
                });
            };

            window.initSelect2();

            $(document).on('shown.bs.modal', function(e) {
                window.initSelect2(e.target);
            });
        });
    </script>
</body>
</html>

