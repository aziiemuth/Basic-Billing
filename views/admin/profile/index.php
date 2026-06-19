<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show bg-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> bg-opacity-10 border-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> border-opacity-25 text-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> mb-4 shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?php 
            echo $_SESSION['flash_message']; 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <p class="text-secondary small mb-0">Kelola informasi profil admin, konfigurasi logo usaha, timezone, format mata uang, isolir, notifikasi, dan cek koneksi MikroTik.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Profil Admin -->
    <div class="col-lg-4">
        <div class="card glass-card border-0 shadow-sm mb-4">
            <div class="card-body p-4 text-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 32px; font-weight: bold;">
                    <?php echo strtoupper(substr($data['adminName'], 0, 1)); ?>
                </div>
                <h5 class="text-white fw-bold mb-1"><?php echo htmlspecialchars($data['adminName']); ?></h5>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 mb-4">
                    <i class="bi bi-shield-lock me-1"></i> <?php echo ucfirst($data['adminRole']); ?>
                </span>

                <div class="text-start mt-4">
                    <div class="small text-secondary mb-1 text-uppercase" style="letter-spacing: 0.5px;">Informasi Akun</div>
                    <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small"><i class="bi bi-person me-2"></i>Username</span>
                            <span class="text-white fw-medium"><?php echo htmlspecialchars($data['adminName']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small"><i class="bi bi-clock-history me-2"></i>Login Terakhir</span>
                            <span class="text-white fw-medium small">Saat ini</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="<?php echo URLROOT; ?>/AdminAuthController/logout" class="btn btn-outline-danger w-100">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Status MikroTik & Pengaturan Sistem -->
    <div class="col-lg-8">
        <!-- 1. Status MikroTik -->
        <div class="card glass-card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 text-white fw-bold"><i class="bi bi-router me-2 text-info"></i>Status Koneksi MikroTik</h6>
                <button type="button" id="btn-retest-mt" class="btn btn-sm btn-outline-info border-opacity-25 d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline">Test Ulang</span>
                </button>
            </div>
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-5 text-center pb-3 pb-md-0">
                        <div id="mt-status-icon" class="mb-3">
                            <?php if ($data['mtResult']['success']): ?>
                                <div class="rounded-circle bg-success bg-opacity-10 border border-success border-opacity-25 d-flex align-items-center justify-content-center mx-auto" style="width:72px;height:72px;">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 36px;"></i>
                                </div>
                            <?php else: ?>
                                <div class="rounded-circle bg-danger bg-opacity-10 border border-danger border-opacity-25 d-flex align-items-center justify-content-center mx-auto" style="width:72px;height:72px;">
                                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 36px;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h4 id="mt-status-text" class="fw-bold <?php echo $data['mtResult']['success'] ? 'text-success' : 'text-danger'; ?> mb-1">
                            <?php echo $data['mtResult']['success'] ? 'Terhubung' : 'Tidak Terhubung'; ?>
                        </h4>
                        <div class="text-secondary small" id="mt-checked-time">Diverifikasi saat halaman dimuat</div>
                    </div>
                    
                    <div class="col-12 col-md-7">
                        <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25 mb-3">
                            <div class="row g-2">
                                <div class="col-sm-5 text-secondary small">Sumber Konfigurasi</div>
                                <div class="col-sm-7 text-white fw-medium"><span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25">config.php</span></div>
                                
                                <div class="col-sm-5 text-secondary small mt-2">Host / IP API</div>
                                <div class="col-sm-7 text-white font-monospace small mt-2" id="mt-host"><?php echo htmlspecialchars($data['mtResult']['host'] ?? MIKROTIK_HOST); ?></div>
                                
                                <div class="col-sm-5 text-secondary small mt-2">Port API</div>
                                <div class="col-sm-7 text-white font-monospace small mt-2" id="mt-port"><?php echo htmlspecialchars($data['mtResult']['port'] ?? MIKROTIK_PORT); ?></div>
                            </div>
                        </div>

                        <div id="mt-details-box" class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25 <?php echo $data['mtResult']['success'] ? '' : 'd-none'; ?>">
                            <div class="row g-2">
                                <div class="col-sm-5 text-secondary small">Router Identity</div>
                                <div class="col-sm-7 text-info fw-medium" id="mt-identity"><?php echo htmlspecialchars($data['mtResult']['identity'] ?? '-'); ?></div>
                                
                                <div class="col-sm-5 text-secondary small mt-2">RouterOS Version</div>
                                <div class="col-sm-7 text-white small mt-2" id="mt-version"><?php echo htmlspecialchars($data['mtResult']['version'] ?? '-'); ?></div>
                                
                                <div class="col-sm-5 text-secondary small mt-2">Uptime</div>
                                <div class="col-sm-7 text-success small mt-2" id="mt-uptime"><?php echo htmlspecialchars($data['mtResult']['uptime'] ?? '-'); ?></div>

                                <div class="col-sm-5 text-secondary small mt-2">CPU Load</div>
                                <div class="col-sm-7 text-warning small mt-2" id="mt-cpu"><?php echo htmlspecialchars($data['mtResult']['cpu_load'] ?? '0'); ?>%</div>

                                <div class="col-sm-5 text-secondary small mt-2">RAM Usage</div>
                                <div class="col-sm-7 text-warning small mt-2" id="mt-ram">
                                    <?php 
                                        $free = $data['mtResult']['free_memory'] ?? 0;
                                        $total = $data['mtResult']['total_memory'] ?? 0;
                                        if ($total > 0) {
                                            $used = $total - $free;
                                            $ramPct = round(($used / $total) * 100);
                                            $usedMb = round($used / 1048576, 1);
                                            $totalMb = round($total / 1048576, 1);
                                            echo $ramPct . '% (' . $usedMb . ' MB / ' . $totalMb . ' MB)';
                                        } else {
                                            echo '0%';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div id="mt-error-box" class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger small mb-0 <?php echo $data['mtResult']['success'] ? 'd-none' : ''; ?>">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <span id="mt-error-msg"><?php echo htmlspecialchars($data['mtResult']['message'] ?? 'Koneksi gagal'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Form Pengaturan Sistem -->
        <div class="card glass-card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-3">
                <h6 class="mb-0 text-white fw-bold"><i class="bi bi-gear-fill me-2 text-primary"></i>Pengaturan Sistem &amp; Profil Bisnis</h6>
            </div>
            <div class="card-body p-4">
                <form action="<?php echo URLROOT; ?>/AdminProfileController/updateSettings" method="POST" enctype="multipart/form-data">
                    <?php echo SecurityHelper::csrfField(); ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Nama Usaha</label>
                            <input type="text" class="form-control bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" name="company_name" value="<?php echo htmlspecialchars($data['settings']->company_name); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Logo Usaha</label>
                            <div class="d-flex align-items-center gap-3">
                                <?php if(!empty($data['settings']->company_logo)): ?>
                                    <div class="bg-dark bg-opacity-50 p-1 rounded border border-secondary border-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px;">
                                        <img src="<?php echo URLROOT; ?>/public/uploads/logo/<?php echo $data['settings']->company_logo; ?>" alt="Logo Usaha" class="img-fluid rounded" style="max-height: 100%;">
                                    </div>
                                <?php else: ?>
                                    <div class="bg-dark bg-opacity-50 rounded border border-secondary border-opacity-25 d-flex align-items-center justify-content-center text-secondary flex-shrink-0" style="width: 45px; height: 45px;">
                                        <i class="bi bi-image" style="font-size: 1.25rem;"></i>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" name="company_logo" accept="image/*">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Nomor WhatsApp Usaha</label>
                            <input type="text" class="form-control bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" name="company_whatsapp" value="<?php echo htmlspecialchars($data['settings']->company_whatsapp); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Email Bisnis</label>
                            <input type="email" class="form-control bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" name="company_email" value="<?php echo htmlspecialchars($data['settings']->company_email); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Timezone (Wilayah Waktu)</label>
                            <select class="form-select bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" name="timezone">
                                <option value="Asia/Jakarta" <?php echo $data['settings']->timezone == 'Asia/Jakarta' ? 'selected' : ''; ?>>Asia/Jakarta (WIB)</option>
                                <option value="Asia/Makassar" <?php echo $data['settings']->timezone == 'Asia/Makassar' ? 'selected' : ''; ?>>Asia/Makassar (WITA)</option>
                                <option value="Asia/Jayapura" <?php echo $data['settings']->timezone == 'Asia/Jayapura' ? 'selected' : ''; ?>>Asia/Jayapura (WIT)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Format Mata Uang</label>
                            <input type="text" class="form-control bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" name="currency_format" value="<?php echo htmlspecialchars($data['settings']->currency_format); ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-secondary small">Alamat Usaha Lengkap</label>
                            <textarea class="form-control bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" name="company_address" rows="2"><?php echo htmlspecialchars($data['settings']->company_address); ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-secondary small">Catatan Footer Invoice</label>
                            <textarea class="form-control bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" name="invoice_footer" rows="2"><?php echo htmlspecialchars($data['settings']->invoice_footer); ?></textarea>
                        </div>

                        <div class="col-md-6 mt-4">
                            <label class="form-label text-secondary small">Jadwal Pengingat WA (Hari Sebelum Jatuh Tempo)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary">H-</span>
                                <input type="number" class="form-control bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" name="wa_reminder_days" value="<?php echo htmlspecialchars($data['settings']->wa_reminder_days); ?>">
                                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary">Hari</span>
                            </div>
                        </div>

                        <div class="col-md-6 mt-4 d-flex align-items-end">
                            <div class="form-check form-switch w-100 p-3 rounded bg-dark bg-opacity-25 border border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                                <label class="form-check-label text-white small" for="auto_isolate">Isolir Otomatis Pelanggan</label>
                                <input class="form-check-input" type="checkbox" role="switch" id="auto_isolate" name="auto_isolate" value="1" <?php echo $data['settings']->auto_isolate ? 'checked' : ''; ?>>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i> Simpan Pengaturan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Tambahan Router Database -->
        <?php if (!empty($data['dbRouters'])): ?>
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 text-white fw-bold"><i class="bi bi-server me-2 text-secondary"></i>Router Tambahan (Database)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="border-secondary border-opacity-25 text-secondary small">
                            <tr>
                                <th class="ps-4 py-3 border-0">Nama Router</th>
                                <th class="py-3 border-0">Host / IP</th>
                                <th class="py-3 border-0 text-center">Status</th>
                                <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['dbRouters'] as $router): ?>
                            <tr>
                                <td class="ps-4 text-white"><?php echo htmlspecialchars($router->name); ?></td>
                                <td class="font-monospace text-secondary small"><?php echo htmlspecialchars($router->host_ip); ?>:<?php echo $router->api_port; ?></td>
                                <td class="text-center">
                                    <?php if ($router->is_active): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary border-opacity-25 btn-test-db-router" data-id="<?php echo $router->id; ?>" data-name="<?php echo htmlspecialchars($router->name); ?>">
                                        <i class="bi bi-wifi me-1"></i><span class="d-none d-sm-inline">Test</span>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.getElementById('btn-retest-mt').addEventListener('click', function() {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...';
    
    fetch(APP_URLROOT + '/AdminProfileController/testMikrotik')
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline">Test Ulang</span>';
            
            document.getElementById('mt-checked-time').textContent = 'Diperbarui: ' + (data.checked_at || 'Baru saja');
            document.getElementById('mt-host').textContent = data.host;
            document.getElementById('mt-port').textContent = data.port;
            
            if (data.success) {
                document.getElementById('mt-status-icon').innerHTML = '<div class="rounded-circle bg-success bg-opacity-10 border border-success border-opacity-25 d-flex align-items-center justify-content-center mx-auto" style="width:72px;height:72px;"><i class="bi bi-check-circle-fill text-success" style="font-size: 36px;"></i></div>';
                document.getElementById('mt-status-text').className = 'fw-bold text-success mb-1';
                document.getElementById('mt-status-text').textContent = 'Terhubung';
                document.getElementById('mt-details-box').classList.remove('d-none');
                document.getElementById('mt-error-box').classList.add('d-none');
                document.getElementById('mt-identity').textContent = data.identity || '-';
                document.getElementById('mt-version').textContent = data.version || '-';
                document.getElementById('mt-uptime').textContent = data.uptime || '-';
                if (data.cpu_load !== undefined) document.getElementById('mt-cpu').textContent = data.cpu_load + '%';
                if (data.total_memory > 0) {
                    let used = data.total_memory - data.free_memory;
                    let pct = Math.round((used / data.total_memory) * 100);
                    let usedMb = (used / 1048576).toFixed(1);
                    let totalMb = (data.total_memory / 1048576).toFixed(1);
                    document.getElementById('mt-ram').textContent = pct + '% (' + usedMb + ' MB / ' + totalMb + ' MB)';
                }
            } else {
                document.getElementById('mt-status-icon').innerHTML = '<div class="rounded-circle bg-danger bg-opacity-10 border border-danger border-opacity-25 d-flex align-items-center justify-content-center mx-auto" style="width:72px;height:72px;"><i class="bi bi-x-circle-fill text-danger" style="font-size: 36px;"></i></div>';
                document.getElementById('mt-status-text').className = 'fw-bold text-danger mb-1';
                document.getElementById('mt-status-text').textContent = 'Tidak Terhubung';
                document.getElementById('mt-details-box').classList.add('d-none');
                document.getElementById('mt-error-box').classList.remove('d-none');
                document.getElementById('mt-error-msg').textContent = data.message;
            }
        })
        .catch(e => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline">Test Ulang</span>';
            alert('Request failed: ' + e.message);
        });
});

// Test router DB
document.querySelectorAll('.btn-test-db-router').forEach(btn => {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        var name = this.dataset.name;
        var originalText = this.innerHTML;
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        
        fetch(APP_URLROOT + '/AdminProfileController/testRouter/' + id)
            .then(r => r.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = originalText;
                
                if (data.success) {
                    alert('✅ Koneksi ke "' + name + '" BERHASIL!\nIdentity: ' + data.identity + '\nUptime: ' + data.uptime);
                } else {
                    alert('❌ Koneksi ke "' + name + '" GAGAL!\nError: ' + data.message);
                }
            })
            .catch(e => {
                this.disabled = false;
                this.innerHTML = originalText;
                alert('Request failed: ' + e.message);
            });
    });
});

// Real-time polling for CPU, RAM, and Uptime every 2 seconds
setInterval(function() {
    fetch(APP_URLROOT + '/AdminProfileController/mikrotikResource')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('mt-uptime').textContent = data.uptime || '-';
                document.getElementById('mt-cpu').textContent = (data.cpu_load || 0) + '%';
                if (data.total_memory > 0) {
                    let used = data.total_memory - data.free_memory;
                    let pct = Math.round((used / data.total_memory) * 100);
                    let usedMb = (used / 1048576).toFixed(1);
                    let totalMb = (data.total_memory / 1048576).toFixed(1);
                    document.getElementById('mt-ram').textContent = pct + '% (' + usedMb + ' MB / ' + totalMb + ' MB)';
                }
            }
        })
        .catch(e => { /* silent fail for background polling */ });
}, 2000);
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
