<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-0">Import Pelanggan dari MikroTik</h4>
        <p class="text-secondary mb-0">Tarik data akun PPPoE dari Router MikroTik ke dalam Database Billing.</p>
    </div>
    <a href="<?php echo URLROOT; ?>/AdminCustomerController" class="btn btn-outline-secondary btn-sm px-3 border-opacity-25">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<!-- Panel Pilihan Router -->
<div class="card glass-card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="<?php echo URLROOT; ?>/AdminCustomerController/importMikrotik" method="GET" class="row align-items-end g-3">
            <div class="col-md-5">
                <label class="form-label text-secondary small">Pilih Router MikroTik</label>
                <select name="router_id" class="form-select bg-dark text-white border-secondary border-opacity-25" required>
                    <option value="">-- Pilih Router --</option>
                    <?php foreach($data['routers'] as $router): ?>
                        <option value="<?php echo $router->id; ?>" <?php echo $data['router_id'] == $router->id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($router->name); ?> (<?php echo htmlspecialchars($router->host_ip); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-info px-4 fw-medium">
                    <i class="bi bi-cloud-arrow-down me-2"></i> Tarik Data PPPoE
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($data['error']): ?>
    <div class="alert alert-danger border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger rounded-3 p-3 mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($data['error']); ?>
    </div>
<?php endif; ?>

<?php if ($data['router_id'] && !$data['error']): ?>
<div class="card glass-card border-0 shadow-sm">
    <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-white mb-0"><i class="bi bi-list-check text-success me-2"></i>Daftar PPPoE Secrets yang Belum Terdaftar</h6>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"><?php echo count($data['secrets']); ?> Ditemukan</span>
        </div>
    </div>
    <div class="card-body p-4">
        <?php if (empty($data['secrets'])): ?>
            <div class="text-center py-5">
                <i class="bi bi-check-circle fs-1 text-success opacity-50 d-block mb-3"></i>
                <h5 class="text-white">Semua Tersinkronisasi</h5>
                <p class="text-secondary mb-0">Semua akun PPPoE di Router ini sudah ada di dalam database billing.</p>
            </div>
        <?php else: ?>
            <form action="<?php echo URLROOT; ?>/AdminCustomerController/storeImportMikrotik" method="POST" id="importForm">
    <?php echo SecurityHelper::csrfField(); ?>
                <input type="hidden" name="router_id" value="<?php echo htmlspecialchars($data['router_id']); ?>">
                
                <div class="row mb-4">
                    <div class="col-md-5">
                        <label class="form-label text-secondary small">Set Paket Default <span class="text-danger">*</span></label>
                        <?php if (empty($data['packages'])): ?>
                            <div class="alert alert-warning border-warning border-opacity-25 bg-warning bg-opacity-10 text-warning rounded-3 p-3 mb-2 small">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Belum ada paket internet yang terdaftar. <br>
                                <a href="<?php echo URLROOT; ?>/AdminPackageController/create" class="alert-link fw-bold text-decoration-underline">Buat Paket Internet Baru</a> terlebih dahulu agar dapat mengimport.
                            </div>
                            <select name="package_id" class="form-select bg-dark text-white border-secondary border-opacity-25" disabled required>
                                <option value="">-- Pilih Paket Internet --</option>
                            </select>
                        <?php else: ?>
                            <select name="package_id" class="form-select bg-dark text-white border-secondary border-opacity-25" required>
                                <option value="">-- Pilih Paket Internet --</option>
                                <?php foreach ($data['packages'] as $package) : ?>
                                    <option value="<?php echo $package->id; ?>"><?php echo $package->name; ?> (Rp <?php echo number_format($package->price, 0, ',', '.'); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <div class="form-text text-secondary opacity-75 small">Paket ini akan diterapkan ke semua pelanggan yang diimport. Anda bisa mengubahnya nanti.</div>
                    </div>
                </div>

                <div class="table-responsive bg-dark rounded border border-secondary border-opacity-25 mb-4">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="border-secondary border-opacity-25 text-secondary small">
                            <tr>
                                <th class="ps-3 py-3" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input bg-dark border-secondary" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th class="py-3">PPPoE Username</th>
                                <th class="py-3">Profile MikroTik</th>
                                <th class="py-3">Service</th>
                                <th class="pe-3 py-3 text-center">Status MikroTik</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['secrets'] as $s): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="form-check">
                                            <input class="form-check-input bg-dark border-secondary secret-checkbox" type="checkbox" name="secrets[]" value="<?php echo htmlspecialchars($s['name']); ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-white"><?php echo htmlspecialchars($s['name']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-25 fw-normal"><?php echo htmlspecialchars(isset($s['profile']) ? $s['profile'] : '-'); ?></span>
                                    </td>
                                    <td class="text-secondary small">
                                        <?php echo htmlspecialchars(isset($s['service']) ? $s['service'] : '-'); ?>
                                    </td>
                                    <td class="pe-3 text-center">
                                        <?php if (isset($s['disabled']) && $s['disabled'] == 'true'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Disabled</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Enabled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top border-secondary border-opacity-25 pt-4">
                    <div class="text-secondary small">
                        Terpilih: <strong id="selectedCount" class="text-white">0</strong> pelanggan
                    </div>
                    <button type="submit" class="btn btn-primary px-4 fw-medium" id="btnImport" disabled>
                        <i class="bi bi-cloud-arrow-down me-2"></i> Import Terpilih
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.secret-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const btnImport = document.getElementById('btnImport');
    const importForm = document.getElementById('importForm');

    if (selectAll && checkboxes.length > 0) {
        // Handle Select All
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateStatus();
        });

        // Handle Individual Checkbox
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                // If one is unchecked, uncheck "Select All"
                if (!this.checked) {
                    selectAll.checked = false;
                }
                
                // If all are checked, check "Select All"
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                if (allChecked) {
                    selectAll.checked = true;
                }
                
                updateStatus();
            });
        });
    }

    function updateStatus() {
        if (!selectedCount) return;
        const count = document.querySelectorAll('.secret-checkbox:checked').length;
        selectedCount.textContent = count;
        
        if (count > 0) {
            btnImport.disabled = false;
        } else {
            btnImport.disabled = true;
        }
    }

    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const count = document.querySelectorAll('.secret-checkbox:checked').length;
            if (count === 0) {
                e.preventDefault();
                alert('Pilih minimal satu pelanggan yang ingin diimport!');
                return;
            }
            if (!confirm(`Anda yakin ingin mengimport ${count} pelanggan ke database?`)) {
                e.preventDefault();
            } else {
                btnImport.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                btnImport.disabled = true;
            }
        });
    }
});
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
