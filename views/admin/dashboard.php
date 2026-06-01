<?php
/** @var array $data */
require_once APPROOT . '/views/layouts/admin_header.php';
?>

<!-- STAT CARDS ROW -->
<div class="row g-3 g-xl-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card glass-card h-100 border-0 shadow-sm">
            <div class="card-body p-3 p-xl-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2 gap-sm-3">
                <div class="stat-icon bg-primary text-white shadow-sm flex-shrink-0">
                    <i class="bi bi-people"></i>
                </div>
                <div class="min-w-0 w-100">
                    <div class="stat-number text-wrap text-break lh-sm mb-1" style="font-size: clamp(1.2rem, 3vw, 1.6rem);"><?php echo $data['customerStats']->total; ?></div>
                    <p class="text-secondary small mb-1 lh-sm text-truncate" title="Total Pelanggan">Total Pelanggan</p>
                    <div class="text-success text-truncate" style="font-size:0.75rem;">
                        <i class="bi bi-check-circle"></i> <?php echo $data['customerStats']->active; ?> Aktif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card glass-card h-100 border-0 shadow-sm">
            <div class="card-body p-3 p-xl-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2 gap-sm-3">
                <div class="stat-icon bg-success bg-opacity-25 text-success shadow-sm flex-shrink-0">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="min-w-0 w-100">
                    <div class="stat-number text-wrap text-break lh-sm mb-1" style="font-size: clamp(1rem, 2.5vw, 1.4rem);">
                        Rp <?php echo number_format($data['revenueThisMonth'], 0, ',', '.'); ?>
                    </div>
                    <p class="text-secondary small mb-1 lh-sm text-truncate" title="Pendapatan Bulan Ini">Pendapatan Bulan Ini</p>
                    <div class="text-success text-truncate" style="font-size:0.75rem;">
                        <i class="bi bi-arrow-up-right"></i> Terbayar
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card glass-card h-100 border-0 shadow-sm">
            <div class="card-body p-3 p-xl-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2 gap-sm-3">
                <div class="stat-icon bg-warning bg-opacity-25 text-warning shadow-sm flex-shrink-0">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="min-w-0 w-100">
                    <div class="stat-number text-wrap text-break lh-sm mb-1" style="font-size: clamp(1.2rem, 3vw, 1.6rem);"><?php echo $data['unpaidInvoices']; ?></div>
                    <p class="text-secondary small mb-1 lh-sm text-truncate" title="Tagihan Belum Bayar">Tagihan Belum Bayar</p>
                    <div class="text-warning text-truncate" style="font-size:0.75rem;">
                        <i class="bi bi-exclamation-circle"></i> Menunggu
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card glass-card h-100 border-0 shadow-sm">
            <div class="card-body p-3 p-xl-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2 gap-sm-3">
                <div class="stat-icon bg-danger bg-opacity-25 text-danger shadow-sm flex-shrink-0">
                    <i class="bi bi-wifi-off"></i>
                </div>
                <div class="min-w-0 w-100">
                    <div class="stat-number text-wrap text-break lh-sm mb-1" style="font-size: clamp(1.2rem, 3vw, 1.6rem);"><?php echo ($data['customerStats']->inactive + $data['customerStats']->isolated); ?></div>
                    <p class="text-secondary small mb-1 lh-sm text-truncate" title="Pelanggan Off/Isolir">Pelanggan Off/Isolir</p>
                    <div class="text-danger text-truncate" style="font-size:0.75rem;">
                        <i class="bi bi-dash-circle"></i> Perlu ditindak
                    </div>
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
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25">
                <h6 class="fw-bold mb-0">Tren Pendapatan (6 Bulan Terakhir)</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" style="min-height: clamp(180px, 30vw, 300px);"></canvas>
            </div>
        </div>
    </div>

    <!-- Customer Statistics -->
    <div class="col-12 col-lg-4">
        <div class="card glass-card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25">
                <h6 class="fw-bold mb-0">Statistik Pelanggan</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="position: relative; height: clamp(180px, 30vw, 250px); width: 100%;">
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
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 border border-secondary border-opacity-25 rounded-pill d-inline-flex align-items-center"><i class="bi bi-dash-circle me-1"></i> Nonaktif</span>
                                        <?php elseif($router->is_online): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 border border-success border-opacity-25 rounded-pill d-inline-flex align-items-center"><i class="bi bi-check-circle me-1"></i> Online / Terhubung</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill d-inline-flex align-items-center"><i class="bi bi-x-circle me-1"></i> Offline / Terputus</span>
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
