<?php
/** @var array $data */
require_once APPROOT . '/views/layouts/admin_header.php';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Laporan Keuangan & Kas</h4>
        <p class="text-secondary small mb-0">Rangkuman pendapatan, tunggakan, visualisasi statistik, serta eksportasi laporan kas.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap justify-content-end">
        <a href="<?php echo URLROOT; ?>/AdminReportController/pdf?month=<?php echo $data['month']; ?>&year=<?php echo $data['year']; ?>" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-pdf"></i> <span class="d-none d-sm-inline">Cetak PDF</span>
        </a>
        <a href="<?php echo URLROOT; ?>/AdminReportController/export?month=<?php echo $data['month']; ?>&year=<?php echo $data['year']; ?>" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-sm-inline">Export CSV</span>
        </a>
    </div>
</div>

<!-- PANEL FILTER PERIODE -->
<div class="card glass-card border-0 shadow-sm mb-4 filter-card">
    <div class="card-body p-3">
        <form method="GET" action="<?php echo URLROOT; ?>/AdminReportController/index" class="row g-3 align-items-end">
            <div class="col-12 col-sm-5 col-md-4">
                <label class="form-label text-secondary small fw-semibold">Pilih Bulan</label>
                <select name="month" class="form-select bg-dark bg-opacity-50 text-white border-secondary border-opacity-25">
                    <?php 
                    $months = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                    foreach($months as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo $data['month'] == $num ? 'selected' : ''; ?>><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-sm-3 col-md-4">
                <label class="form-label text-secondary small fw-semibold">Pilih Tahun</label>
                <select name="year" class="form-select bg-dark bg-opacity-50 text-white border-secondary border-opacity-25">
                    <?php 
                    $current_year = date('Y');
                    $start_year = 2026;
                    $end_year = max($current_year, $start_year);
                    for($i = $end_year; $i >= $start_year; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo $data['year'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-2"></i> Tampilkan Laporan</button>
            </div>
        </form>
    </div>
</div>

<!-- KARTU RINGKASAN METRIK KEUANGAN -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card bg-success bg-opacity-10 border-success border-opacity-25 shadow-sm h-100 card-hover-animation">
            <div class="card-body p-3 text-center">
                <div class="mb-2"><i class="bi bi-wallet2 text-success" style="font-size: 1.75rem;"></i></div>
                <h6 class="text-success fw-bold small mb-1">Total Pemasukan</h6>
                <h4 class="fw-bold mb-0" style="font-size: clamp(0.9rem, 3vw, 1.25rem);">Rp <?php echo number_format($data['summary']['pemasukan'], 0, ',', '.'); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-danger bg-opacity-10 border-danger border-opacity-25 shadow-sm h-100 card-hover-animation">
            <div class="card-body p-3 text-center">
                <div class="mb-2"><i class="bi bi-exclamation-octagon text-danger" style="font-size: 1.75rem;"></i></div>
                <h6 class="text-danger fw-bold small mb-1">Total Tunggakan</h6>
                <h4 class="fw-bold mb-0" style="font-size: clamp(0.9rem, 3vw, 1.25rem);">Rp <?php echo number_format($data['summary']['tunggakan'], 0, ',', '.'); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-primary bg-opacity-10 border-primary border-opacity-25 shadow-sm h-100 card-hover-animation">
            <div class="card-body p-3 text-center">
                <div class="mb-2"><i class="bi bi-person-check text-primary" style="font-size: 1.75rem;"></i></div>
                <h6 class="text-primary fw-bold small mb-1">Pelanggan Lunas</h6>
                <h4 class="fw-bold mb-0"><?php echo $data['summary']['pelanggan_lunas']; ?> <span class="fs-6 text-secondary fw-normal">Layanan</span></h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card bg-warning bg-opacity-10 border-warning border-opacity-25 shadow-sm h-100 card-hover-animation">
            <div class="card-body p-3 text-center">
                <div class="mb-2"><i class="bi bi-person-x text-warning" style="font-size: 1.75rem;"></i></div>
                <h6 class="text-warning fw-bold small mb-1">Belum Bayar</h6>
                <h4 class="fw-bold mb-0"><?php echo $data['summary']['pelanggan_belum']; ?> <span class="fs-6 text-secondary fw-normal">Layanan</span></h4>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK INTERAKTIF (CHART.JS) -->
<div class="row g-3 mb-4">
    <!-- 1. Grafik Tren Pemasukan Bulanan -->
    <div class="col-12 col-md-6">
        <div class="card glass-card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-3">
                <h6 class="mb-0 text-white fw-bold"><i class="bi bi-graph-up me-2 text-primary"></i>Tren Pemasukan Bulanan (Tahun <?php echo htmlspecialchars($data['year']); ?>)</h6>
            </div>
            <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <div style="width: 100%; height: 220px;">
                    <canvas id="incomeTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Grafik Distribusi Pelanggan & Metode Pembayaran -->
    <div class="col-12 col-md-6">
        <div class="row g-3 h-100">
            <!-- Pie: Status Distribusi Pelanggan -->
            <div class="col-6">
                <div class="card glass-card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-secondary border-opacity-25 p-3">
                        <h6 class="mb-0 text-white fw-bold" style="font-size: 0.78rem;"><i class="bi bi-people me-1 text-success"></i>Distribusi Status</h6>
                    </div>
                    <div class="card-body p-2 d-flex align-items-center justify-content-center">
                        <div style="width: 100%; height: 160px; position: relative;">
                            <canvas id="customerGrowthChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doughnut: Pembayaran per Metode -->
            <div class="col-6">
                <div class="card glass-card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-secondary border-opacity-25 p-3">
                        <h6 class="mb-0 text-white fw-bold" style="font-size: 0.78rem;"><i class="bi bi-credit-card me-1 text-warning"></i>Metode Bayar</h6>
                    </div>
                    <div class="card-body p-2 d-flex align-items-center justify-content-center">
                        <div style="width: 100%; height: 160px; position: relative;">
                            <canvas id="paymentMethodsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL RIWAYAT TRANSAKSI KAS MASUK (CASHFLOW) -->
<div class="card glass-card border-0 shadow-sm">
    <div class="card-header bg-transparent border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 text-white fw-bold"><i class="bi bi-list-columns-reverse me-2 text-info"></i>Riwayat Transaksi Masuk (Cashflow)</h6>
        <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-25"><?php echo count($data['cashflow']); ?> Transaksi Terakhir</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle text-nowrap">
                <thead class="border-secondary border-opacity-25 text-secondary small">
                    <tr>
                        <th class="ps-4">Tgl Transaksi</th>
                        <th>No Invoice</th>
                        <th>Periode</th>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th class="pe-4 text-end">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['cashflow'])): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                            Belum ada transaksi cashflow pada bulan ini.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['cashflow'] as $row): ?>
                        <tr>
                            <td class="ps-4 text-white small"><?php echo date('d M Y, H:i', strtotime($row->updated_at)); ?></td>
                            <td class="font-monospace text-info small"><?php echo htmlspecialchars($row->invoice_number); ?></td>
                            <td class="text-secondary small"><?php echo date('M Y', strtotime($row->billing_month . '-01')); ?></td>
                            <td class="text-white fw-medium"><?php echo htmlspecialchars($row->customer_name); ?></td>
                            <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><?php echo $row->payment_method ? strtoupper(str_replace('_', ' ', $row->payment_method)) : 'MANUAL'; ?></span></td>
                            <td class="pe-4 text-end fw-bold text-success">+ <?php echo number_format($row->amount, 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination Footer -->
        <div class="card-footer bg-transparent border-secondary border-opacity-25 d-flex justify-content-between align-items-center py-3 flex-wrap gap-2 pagination-footer" id="pag-cashflow-footer" style="display: none;">
            <div class="text-secondary small pagination-info">
                Menampilkan 0 - 0 dari 0 data
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0 pagination-controls">
                    <!-- populated via JS -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- CODE UNTUK GRAPH CONFIGURATION (CHART.JS) -->
<script>
    // 1. Line Chart: Tren Pemasukan Bulanan
    const trendCtx = document.getElementById('incomeTrendChart').getContext('2d');
    
    // Map income trend data dari database
    const trendMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const trendTotals = new Array(12).fill(0);
    
    <?php foreach($data['incomeTrend'] as $item): ?>
        trendTotals[<?php echo (int)$item->month - 1; ?>] = <?php echo (float)$item->total; ?>;
    <?php endforeach; ?>

    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendMonths,
            datasets: [{
                label: 'Pemasukan (Rp)',
                data: trendTotals,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#888' }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: {
                        color: '#888',
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    // 2. Pie Chart: Distribusi Status Pelanggan
    const customerCtx = document.getElementById('customerGrowthChart').getContext('2d');
    
    let activeCust = 0;
    let isolatedCust = 0;
    let inactiveCust = 0;
    
    <?php foreach($data['customerGrowth'] as $item): ?>
        <?php if($item->status === 'active'): ?>
            activeCust = <?php echo $item->count; ?>;
        <?php elseif($item->status === 'isolated'): ?>
            isolatedCust = <?php echo $item->count; ?>;
        <?php else: ?>
            inactiveCust += <?php echo $item->count; ?>;
        <?php endif; ?>
    <?php endforeach; ?>

    new Chart(customerCtx, {
        type: 'pie',
        data: {
            labels: ['Aktif', 'Terisolir', 'Non-Aktif'],
            datasets: [{
                data: [activeCust, isolatedCust, inactiveCust],
                backgroundColor: ['#198754', '#dc3545', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#ccc', font: { size: 10 } }
                }
            }
        }
    });

    // 3. Doughnut Chart: Metode Pembayaran
    const payCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    
    const payLabels = [];
    const payTotals = [];
    
    <?php foreach($data['paymentMethods'] as $item): ?>
        payLabels.push('<?php echo strtoupper(str_replace('_', ' ', $item->method)); ?>');
        payTotals.push(<?php echo (int)$item->count; ?>);
    <?php endforeach; ?>

    // If empty, supply dummy
    if(payLabels.length === 0) {
        payLabels.push('Belum ada transaksi');
        payTotals.push(1);
    }

    new Chart(payCtx, {
        type: 'doughnut',
        data: {
            labels: payLabels,
            datasets: [{
                data: payTotals,
                backgroundColor: ['#ffc107', '#0dcaf0', '#fd7e14', '#0d6efd', '#20c997'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#ccc', font: { size: 10 } }
                }
            }
        }
    });

    // --- Cashflow Table Client-side Pagination (15 items) ---
    (function() {
        const tbody = document.querySelector('.table-responsive tbody');
        if (!tbody) return;

        // Skip placeholder rows
        const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => {
            return !row.cells[0].classList.contains('text-center') || row.cells.length > 1;
        });

        if (rows.length === 0) return;

        const itemsPerPage = 15;
        let currentPage = 1;

        const footer = document.getElementById('pag-cashflow-footer');
        const info = footer.querySelector('.pagination-info');
        const controls = footer.querySelector('.pagination-controls');

        function render() {
            const totalItems = rows.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            rows.forEach(r => r.style.display = 'none');

            const start = (currentPage - 1) * itemsPerPage;
            const end = Math.min(start + itemsPerPage, totalItems);

            for (let i = start; i < end; i++) {
                rows[i].style.display = '';
            }

            if (info) {
                info.textContent = `Menampilkan ${start + 1} - ${end} dari ${totalItems} data`;
            }

            if (footer) {
                footer.style.display = totalItems > itemsPerPage ? 'flex' : 'none';
            }

            if (controls) {
                controls.innerHTML = '';

                // Prev
                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link bg-dark border-secondary border-opacity-25 text-white" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
                prevLi.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        render();
                    }
                });
                controls.appendChild(prevLi);

                // Pages
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }

                for (let i = startPage; i <= endPage; i++) {
                    const pageLi = document.createElement('li');
                    pageLi.className = `page-item ${currentPage === i ? 'active' : ''}`;
                    const activeLinkClass = currentPage === i ? 'bg-primary border-primary text-white' : 'bg-dark border-secondary border-opacity-25 text-white';
                    pageLi.innerHTML = `<a class="page-link ${activeLinkClass}" href="#">${i}</a>`;
                    pageLi.addEventListener('click', function(e) {
                        e.preventDefault();
                        currentPage = i;
                        render();
                    });
                    controls.appendChild(pageLi);
                }

                // Next
                const nextLi = document.createElement('li');
                nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link bg-dark border-secondary border-opacity-25 text-white" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
                nextLi.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        render();
                    }
                });
                controls.appendChild(nextLi);
            }
        }

        render();
    })();
</script>

<!-- STYLING ANIMATION UTILITY -->
<style>
    .card-hover-animation {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .card-hover-animation:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
    }
</style>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
