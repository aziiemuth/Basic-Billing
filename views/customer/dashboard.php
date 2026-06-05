<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/style.css?v=<?php echo time(); ?>">
    
    <!-- PWA Settings -->
    <link rel="manifest" href="<?php echo URLROOT; ?>/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ISP Portal">
    <link rel="apple-touch-icon" href="<?php echo URLROOT; ?>/assets/icon-192.png">

    <!-- Anti-FOUC: terapkan tema sebelum render -->
    <script>
        (function(){
            var t = localStorage.getItem('billingapp_theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
            document.documentElement.setAttribute('data-bs-theme', t === 'light' ? 'light' : 'dark');
        })();
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: var(--body-color);
        }
        .navbar-glass {
            background: var(--topbar-bg) !important;
            backdrop-filter: var(--glass-blur);
            border-bottom: 1px solid var(--glass-border) !important;
        }
        .card-glass {
            background: var(--card-bg) !important;
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--card-border) !important;
            border-radius: 1rem;
            box-shadow: var(--glass-shadow);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">ISP Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item d-none" id="pwa-install-nav">
                    <a class="nav-link text-info fw-semibold me-3" href="#" id="pwa-install-btn">
                        <i class="bi bi-download me-1"></i> Install Aplikasi
                    </a>
                </li>
                <!-- Theme Toggle Button -->
                <li class="nav-item me-3">
                    <button id="themeToggleBtn" class="btn border-0" title="Ganti Tema" aria-label="Toggle Dark/Light Mode" style="width: 40px !important; height: 40px !important; padding: 0 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; background: none !important; color: var(--body-color) !important; border-radius: 50% !important;">
                        <i class="bi bi-sun-fill icon-dark fs-5 text-warning"></i>
                        <i class="bi bi-moon-stars-fill icon-light fs-5 text-info"></i>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="<?php echo URLROOT; ?>/CustomerAuthController/logout">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12 col-md-6 mb-3 mb-md-0">
            <h2 class="fw-bold">Selamat Datang, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</h2>
            <p class="text-secondary mb-0">ID Pelanggan: <?php echo htmlspecialchars($_SESSION['customer_code']); ?></p>
        </div>
        <div class="col-12 col-md-6 text-md-end">
            <div class="d-inline-flex align-items-center bg-dark p-2 px-3 rounded-pill border border-secondary border-opacity-25">
                <span class="me-2 text-secondary">Status Internet:</span>
                <?php if($data['online_status'] == 'online'): ?>
                    <span class="badge bg-success rounded-pill"><i class="bi bi-check-circle-fill"></i> ONLINE</span>
                    <small class="ms-2 text-success"><?php echo htmlspecialchars($data['uptime']); ?></small>
                <?php else: ?>
                    <span class="badge bg-danger rounded-pill"><i class="bi bi-x-circle-fill"></i> OFFLINE</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card card-glass h-100">
                <div class="card-body">
                    <h6 class="text-secondary mb-1">Paket Internet</h6>
                    <h4 class="fw-bold text-primary"><?php echo $data['package'] ? htmlspecialchars($data['package']->name) : 'Tidak ada paket'; ?></h4>
                    <p class="small text-secondary mb-0">Speed: <?php echo $data['package'] ? htmlspecialchars($data['package']->mikrotik_profile) : '-'; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card card-glass h-100">
                <div class="card-body">
                    <h6 class="text-secondary mb-1">Status Akun</h6>
                    <h4 class="fw-bold <?php echo ($data['customer']->status == 'active') ? 'text-success' : 'text-danger'; ?>">
                        <?php echo strtoupper($data['customer']->status); ?>
                    </h4>
                    <p class="small text-secondary mb-0">PPPoE: <?php echo $data['pppoe'] ? htmlspecialchars($data['pppoe']->username) : '-'; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-glass h-100">
                <div class="card-body">
                    <h6 class="text-secondary mb-1">Jatuh Tempo</h6>
                    <h4 class="fw-bold text-warning">Tgl <?php echo htmlspecialchars($data['customer']->due_date); ?></h4>
                    <p class="small text-secondary mb-0">Setiap bulan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-glass">
                <div class="card-header border-bottom border-secondary border-opacity-25 bg-transparent py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-receipt"></i> Tagihan Anda</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">No. Tagihan</th>
                                    <th>Periode</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Total Tagihan</th>
                                    <th>Status</th>
                                    <th class="pe-4 text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($data['invoices'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">Belum ada tagihan.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach($data['invoices'] as $inv): ?>
                                    <tr class="align-middle">
                                        <td class="ps-4 fw-medium"><?php echo htmlspecialchars($inv->invoice_number); ?></td>
                                        <td><?php echo date('M Y', strtotime($inv->billing_month . '-01')); ?></td>
                                        <td><?php echo date('d M Y', strtotime($inv->due_date)); ?></td>
                                        <td>Rp <?php echo number_format($inv->total_amount, 0, ',', '.'); ?></td>
                                        <td>
                                            <?php if($inv->status == 'paid'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">LUNAS</span>
                                            <?php elseif($inv->status == 'unpaid'): ?>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">BELUM LUNAS</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><?php echo strtoupper($inv->status); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-end text-nowrap">
                                            <?php if($inv->status == 'unpaid'): ?>
                                                <a href="<?php echo URLROOT; ?>/PaymentController/snap/<?php echo $inv->id; ?>" class="btn btn-sm btn-primary px-3">
                                                    <i class="bi bi-credit-card"></i> Bayar
                                                </a>
                                            <?php elseif($inv->status == 'paid'): ?>
                                                <?php
                                                $adminWa = '';
                                                if (!empty($data['settings']->company_whatsapp)) {
                                                    $cleanNum = preg_replace('/[^0-9]/', '', $data['settings']->company_whatsapp);
                                                    if (strpos($cleanNum, '0') === 0) {
                                                        $cleanNum = '62' . substr($cleanNum, 1);
                                                    }
                                                    $adminWa = $cleanNum;
                                                }
                                                if (!empty($adminWa)):
                                                    $periode = date('F Y', strtotime($inv->billing_month . '-01'));
                                                    $amount_formatted = number_format($inv->total_amount, 0, ',', '.');
                                                    $waMsg = "Halo Admin, saya sudah melakukan pembayaran tagihan internet.\n\n"
                                                           . "*Detail Tagihan:*\n"
                                                           . "• No. Tagihan: *{$inv->invoice_number}*\n"
                                                           . "• Nama Pelanggan: *{$_SESSION['customer_name']}*\n"
                                                           . "• ID Pelanggan: *{$_SESSION['customer_code']}*\n"
                                                           . "• Periode: *{$periode}*\n"
                                                           . "• Total Tagihan: *Rp {$amount_formatted}*\n\n"
                                                           . "Terima kasih!";
                                                    $waUrl = "https://api.whatsapp.com/send?phone=" . $adminWa . "&text=" . urlencode($waMsg);
                                                ?>
                                                    <a href="<?php echo $waUrl; ?>" target="_blank" class="btn btn-sm btn-outline-success px-3">
                                                        <i class="bi bi-whatsapp"></i> Konfirmasi
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary px-3" disabled>
                                                        <i class="bi bi-check-circle"></i> Lunas
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary px-3" disabled>
                                                    <i class="bi bi-dash-circle"></i> <?php echo strtoupper($inv->status); ?>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Vanilla JS -->
<script src="<?php echo URLROOT; ?>/assets/main.js"></script>
</body>
</html>
