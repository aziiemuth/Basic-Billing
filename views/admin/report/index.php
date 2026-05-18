<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Laporan Keuangan & Kas</h4>
        <p class="text-secondary small mb-0">Rangkuman pendapatan, tunggakan, serta arus kas (cashflow) pelanggan.</p>
    </div>
    <div>
        <a href="<?php echo URLROOT; ?>/AdminReportController/export?month=<?php echo $data['month']; ?>&year=<?php echo $data['year']; ?>" class="btn btn-success rounded-pill px-4 shadow-sm">
            <i class="bi bi-file-earmark-excel me-2"></i> Export Laporan
        </a>
    </div>
</div>

<div class="card glass-card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="<?php echo URLROOT; ?>/AdminReportController/index" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-secondary small">Pilih Bulan</label>
                <select name="month" class="form-select bg-dark bg-opacity-50 text-white border-secondary border-opacity-25">
                    <?php 
                    $months = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                    foreach($months as $num => $name): ?>
                        <option value="<?php echo $num; ?>" <?php echo $data['month'] == $num ? 'selected' : ''; ?>><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-secondary small">Pilih Tahun</label>
                <select name="year" class="form-select bg-dark bg-opacity-50 text-white border-secondary border-opacity-25">
                    <?php 
                    $current_year = date('Y');
                    for($i = $current_year; $i >= $current_year - 5; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo $data['year'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-2"></i> Tampilkan Laporan</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-success bg-opacity-10 border-success border-opacity-25 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-2"><i class="bi bi-wallet2 text-success" style="font-size: 2rem;"></i></div>
                <h6 class="text-success fw-bold">Total Pemasukan</h6>
                <h4 class="fw-bold text-white mb-0">Rp <?php echo number_format($data['summary']['pemasukan'], 0, ',', '.'); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger bg-opacity-10 border-danger border-opacity-25 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-2"><i class="bi bi-exclamation-octagon text-danger" style="font-size: 2rem;"></i></div>
                <h6 class="text-danger fw-bold">Total Tunggakan</h6>
                <h4 class="fw-bold text-white mb-0">Rp <?php echo number_format($data['summary']['tunggakan'], 0, ',', '.'); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary bg-opacity-10 border-primary border-opacity-25 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-2"><i class="bi bi-person-check text-primary" style="font-size: 2rem;"></i></div>
                <h6 class="text-primary fw-bold">Pelanggan Lunas</h6>
                <h4 class="fw-bold text-white mb-0"><?php echo $data['summary']['pelanggan_lunas']; ?> Orang</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning bg-opacity-10 border-warning border-opacity-25 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-2"><i class="bi bi-person-x text-warning" style="font-size: 2rem;"></i></div>
                <h6 class="text-warning fw-bold">Belum Bayar</h6>
                <h4 class="fw-bold text-white mb-0"><?php echo $data['summary']['pelanggan_belum']; ?> Orang</h4>
            </div>
        </div>
    </div>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-header bg-transparent border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-white fw-bold"><i class="bi bi-list-columns-reverse me-2 text-info"></i>Riwayat Transaksi (Cashflow)</h6>
        <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-25"><?php echo count($data['cashflow']); ?> Transaksi</span>
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
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
