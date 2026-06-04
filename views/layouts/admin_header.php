<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo SecurityHelper::generateCsrfToken(); ?>">
    <title><?php echo isset($data['title']) ? $data['title'] : SITENAME; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/style.css?v=<?php echo time(); ?>">
    <!-- Anti-FOUC: terapkan tema sebelum render -->
    <script>
        (function(){
            var t = localStorage.getItem('billingapp_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
            document.documentElement.setAttribute('data-bs-theme', t === 'light' ? 'light' : 'dark');
        })();

        // Define dynamic base path for AJAX calls to prevent CORS errors on localhost/mobile
        const APP_URLROOT = '<?php echo rtrim(parse_url(URLROOT, PHP_URL_PATH) ?: "", "/"); ?>';
    </script>
</head>
<body class="admin-layout">
    <div class="d-flex h-100 wrapper">
        
        <!-- SIDEBAR -->
        <aside class="sidebar d-flex flex-column border-end border-secondary border-opacity-25 shadow-sm">
            <div class="p-4 border-bottom border-secondary border-opacity-25 d-flex align-items-center gap-3">
                <div class="bg-primary bg-gradient rounded p-2 text-white shadow-sm flex-shrink-0">
                    <i class="bi bi-router fs-5"></i>
                </div>
                <div class="sidebar-brand-text overflow-hidden">
                    <h6 class="mb-0 fw-bold text-white text-truncate">Billing App</h6>
                    <small class="text-secondary text-truncate d-block" style="font-size: 0.75rem;">ISP Management</small>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto p-3 sidebar-menu-container">
                <div class="small fw-semibold text-secondary mb-2 mt-2 px-2 text-uppercase sidebar-group-title" style="letter-spacing: 1px;">Utama</div>
                <nav class="nav flex-column mb-4">
                    <a href="<?php echo URLROOT; ?>/AdminDashboardController" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminDashboard') !== false || $_SERVER['REQUEST_URI'] == URLROOT.'/' || $_SERVER['REQUEST_URI'] == URLROOT ? 'active' : ''; ?> fw-medium d-flex align-items-center gap-2">
                        <i class="bi bi-grid flex-shrink-0"></i> <span>Dashboard</span>
                    </a>
                    <a href="<?php echo URLROOT; ?>/AdminCustomerController" class="nav-link fw-medium d-flex align-items-center gap-2 <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminCustomer') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-people flex-shrink-0"></i> <span>Pelanggan</span>
                    </a>
                    <a href="<?php echo URLROOT; ?>/AdminPackageController" class="nav-link fw-medium d-flex align-items-center gap-2 <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminPackage') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-box-seam flex-shrink-0"></i> <span>Paket Internet</span>
                    </a>
                    <a href="<?php echo URLROOT; ?>/AdminRouterController" class="nav-link fw-medium d-flex align-items-center gap-2 <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminRouter') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-router flex-shrink-0"></i> <span>Router / Server</span>
                    </a>
                    <a href="<?php echo URLROOT; ?>/AdminPppoeController" class="nav-link fw-medium d-flex align-items-center gap-2 <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminPppoe') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-hdd-network flex-shrink-0"></i> <span>Data PPPoE (MikroTik)</span>
                    </a>
                </nav>

                <div class="small fw-semibold text-secondary mb-2 px-2 text-uppercase sidebar-group-title" style="letter-spacing: 1px;">Keuangan</div>
                <nav class="nav flex-column mb-4">
                    <a href="<?php echo URLROOT; ?>/AdminInvoiceController/generate" class="nav-link fw-medium d-flex align-items-center gap-2 <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminInvoice/generate') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-receipt flex-shrink-0"></i> <span>Generate Tagihan (Otomatis)</span>
                    </a>
                    <a href="<?php echo URLROOT; ?>/AdminInvoiceController/manual" class="nav-link fw-medium d-flex align-items-center gap-2 <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminInvoice/manual') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-whatsapp flex-shrink-0"></i> <span>Tagihan Manual (Direct WA)</span>
                    </a>
                    <a href="<?php echo URLROOT; ?>/AdminReportController" class="nav-link fw-medium d-flex align-items-center gap-2 <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminReport') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-graph-up flex-shrink-0"></i> <span>Laporan & Arus Kas</span>
                    </a>
                    <a href="<?php echo URLROOT; ?>/AdminPaymentHistoryController" class="nav-link fw-medium d-flex align-items-center gap-2 <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminPaymentHistory') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-clock-history flex-shrink-0"></i> <span>Histori Pembayaran</span>
                    </a>
                </nav>

                <div class="small fw-semibold text-secondary mb-2 px-2 text-uppercase sidebar-group-title" style="letter-spacing: 1px;">Kelola User Admin</div>
                <nav class="nav flex-column mb-4">
                    <a href="<?php echo URLROOT; ?>/AdminUserController" class="nav-link fw-medium d-flex align-items-center gap-2 <?php echo strpos($_SERVER['REQUEST_URI'], 'AdminUser') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-person-gear flex-shrink-0"></i> <span>User Login</span>
                    </a>
                </nav>
            </div>

            <div class="p-3 border-top border-secondary border-opacity-25 mt-auto">
                <!-- Theme Toggle Button -->
                <button id="themeToggleBtn" title="Ganti Tema" aria-label="Toggle Dark/Light Mode"
                    class="w-100 btn btn-sm mb-3 d-flex align-items-center justify-content-center gap-2"
                    style="background: var(--glass-bg); border: 1px solid var(--glass-border); color: var(--body-color);">
                    <i class="bi bi-sun-fill icon-dark"></i>
                    <i class="bi bi-moon-stars-fill icon-light"></i>
                    <span class="icon-dark">Mode Terang</span>
                    <span class="icon-light">Mode Gelap</span>
                </button>
                <a href="<?php echo URLROOT; ?>/AdminProfileController" class="text-decoration-none d-block">
                    <div class="d-flex align-items-center gap-2 mb-3 bg-dark bg-opacity-50 p-2 rounded border border-secondary border-opacity-25 overflow-hidden sidebar-user-box" style="transition: all 0.2s ease;">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm flex-shrink-0" style="width: 36px; height: 36px;">
                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div class="overflow-hidden sidebar-user-text">
                            <div class="fw-semibold text-white text-truncate small"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></div>
                            <div class="text-info text-truncate" style="font-size: 0.7rem;"><i class="bi bi-gear-fill me-1"></i>Pengaturan Sistem</div>
                        </div>
                    </div>
                </a>
                <a href="<?php echo URLROOT; ?>/AdminAuthController/logout" class="btn btn-outline-danger w-100 btn-sm fw-medium d-flex align-items-center justify-content-center gap-2 sidebar-logout-btn">
                    <i class="bi bi-box-arrow-right flex-shrink-0"></i> <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <main class="main-content flex-grow-1 bg-transparent">
            <!-- TOPBAR -->
            <header class="topbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button id="sidebarToggle" class="btn btn-outline-secondary border-0 d-lg-none" aria-label="Toggle Sidebar">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <div>
                        <h4 class="mb-0 fw-bold text-white fs-5 fs-md-4"><?php echo isset($data['title']) ? $data['title'] : 'Dashboard'; ?></h4>
                        <p class="text-secondary small mb-0 d-none d-md-block">Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>!</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <div class="d-inline-flex align-items-center gap-2 px-2 py-1 px-md-3 rounded-pill" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;" role="status" aria-hidden="true"></span>
                        <span class="text-success small fw-semibold d-none d-sm-inline">Sistem Online</span>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <div class="page-content">
