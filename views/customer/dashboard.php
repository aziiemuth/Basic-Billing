<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
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
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
        }
        .navbar-glass {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card-glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">ISP Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
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
                                            <?php elseif($inv->status == 'expired'): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">KEDALUWARSA</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><?php echo strtoupper($inv->status); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-end text-nowrap">
                                            <a href="<?php echo URLROOT; ?>/CustomerDashboardController/invoice/<?php echo $inv->id; ?>" target="_blank" class="btn btn-sm btn-outline-info px-2 me-1" title="Cetak Invoice">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <?php if($inv->status == 'unpaid'): ?>
                                                <a href="<?php echo URLROOT; ?>/PaymentController/snap/<?php echo $inv->id; ?>" class="btn btn-sm btn-primary px-3">
                                                    <i class="bi bi-credit-card"></i> Bayar
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary px-3" disabled>
                                                    <i class="bi bi-check-circle"></i> Selesai
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
