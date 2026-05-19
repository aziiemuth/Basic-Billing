<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<!-- STAT CARDS ROW -->
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card glass-card h-100 border-0 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded p-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-people fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1"><?php echo $data['customerStats']->total; ?></h3>
                    <p class="text-secondary small mb-1">Total Pelanggan</p>
                    <span class="text-success small fw-medium d-flex align-items-center gap-1">
                        <i class="bi bi-check-circle"></i> <?php echo $data['customerStats']->active; ?> Aktif
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card glass-card h-100 border-0 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded p-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-wallet2 fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.5rem;">Rp <?php echo number_format($data['revenueThisMonth'], 0, ',', '.'); ?></h3>
                    <p class="text-secondary small mb-1">Pendapatan Bulan Ini</p>
                    <span class="text-success small fw-medium d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-up-right"></i> Terbayar
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card glass-card h-100 border-0 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning rounded p-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-receipt fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1"><?php echo $data['unpaidInvoices']; ?></h3>
                    <p class="text-secondary small mb-1">Tagihan Belum Bayar</p>
                    <span class="text-warning small fw-medium d-flex align-items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i> Menunggu
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card glass-card h-100 border-0 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <div class="bg-danger bg-opacity-10 text-danger rounded p-3 d-flex align-items-center justify-content-center">
                    <i class="bi bi-wifi-off fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-bold text-white mb-1"><?php echo ($data['customerStats']->inactive + $data['customerStats']->isolated); ?></h3>
                    <p class="text-secondary small mb-1">Pelanggan Off/Isolir</p>
                    <span class="text-danger small fw-medium d-flex align-items-center gap-1">
                        <i class="bi bi-dash-circle"></i> Perlu ditindak
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHARTS ROW -->
<div class="row g-4 mb-4">
    <!-- Payment Chart -->
    <div class="col-12 col-lg-8">
        <div class="card glass-card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                <h6 class="fw-bold text-white mb-0">Tren Pendapatan (6 Bulan Terakhir)</h6>
            </div>
            <div class="card-body p-4">
                <canvas id="revenueChart" style="min-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Customer Statistics -->
    <div class="col-12 col-lg-4">
        <div class="card glass-card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                <h6 class="fw-bold text-white mb-0">Statistik Pelanggan</h6>
            </div>
            <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="customerChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROUTERS ROW -->
<div class="row g-4">
    <div class="col-12">
        <div class="card glass-card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-white mb-0">Status Router MikroTik</h6>
                    <small class="text-secondary">Koneksi dan statistik real-time dari router aktif Anda.</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="border-secondary border-opacity-25 text-secondary small text-uppercase" style="letter-spacing: 0.5px;">
                            <tr>
                                <th class="ps-4 py-3 border-0">Nama Router</th>
                                <th class="py-3 border-0">IP Host</th>
                                <th class="py-3 border-0">Status Koneksi</th>
                                <th class="py-3 border-0 text-end pe-4">PPPoE Aktif (Live)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($data['routers'])): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary">Belum ada router terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($data['routers'] as $router): ?>
                                <tr>
                                    <td class="ps-4 text-white fw-medium">
                                        <i class="bi bi-hdd-network text-info me-2"></i> <?php echo htmlspecialchars($router->name); ?>
                                    </td>
                                    <td class="font-monospace text-secondary"><?php echo htmlspecialchars($router->host_ip); ?></td>
                                    <td>
                                        <?php if(!$router->is_active): ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 border border-secondary border-opacity-25 rounded-pill"><i class="bi bi-dash-circle"></i> Nonaktif</span>
                                        <?php elseif($router->is_online): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 border border-success border-opacity-25 rounded-pill"><i class="bi bi-check-circle"></i> Online / Terhubung</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill"><i class="bi bi-x-circle"></i> Offline / Terputus</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4 text-white fw-bold">
                                        <?php if($router->is_online): ?>
                                            <span class="text-success"><?php echo $router->active_pppoe_count; ?></span> <span class="text-secondary small fw-normal">Sesi</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
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

<!-- Chart.js and Initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Shared Chart.js Defaults for Dark Mode
    Chart.defaults.color = '#a1a1aa';
    Chart.defaults.font.family = 'Inter, sans-serif';

    // REVENUE LINE CHART
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    
    // Create gradient
    let gradient = ctxRevenue.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.5)'); // success color
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
    
    const revenueLabels = <?php echo json_encode($data['chartData']['labels']); ?>;
    const revenueData = <?php echo json_encode($data['chartData']['data']); ?>;

    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: revenueData,
                borderColor: '#10b981',
                backgroundColor: gradient,
                borderWidth: 2,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#10b981',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });

    // CUSTOMER DOUGHNUT CHART
    const ctxCustomer = document.getElementById('customerChart').getContext('2d');
    
    const activeCustomers = <?php echo $data['customerStats']->active ?? 0; ?>;
    const inactiveCustomers = <?php echo $data['customerStats']->inactive ?? 0; ?>;
    const isolatedCustomers = <?php echo $data['customerStats']->isolated ?? 0; ?>;

    new Chart(ctxCustomer, {
        type: 'doughnut',
        data: {
            labels: ['Aktif', 'Nonaktif', 'Terisolir'],
            datasets: [{
                data: [activeCustomers, inactiveCustomers, isolatedCustomers],
                backgroundColor: [
                    '#10b981', // success
                    '#6c757d', // secondary
                    '#ef4444'  // danger
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            }
        }
    });
});
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
