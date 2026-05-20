<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Manajemen Router / Server</h4>
        <p class="text-secondary small mb-0">Kelola daftar router MikroTik yang terhubung dengan sistem.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" id="btn-isolate-overdue" class="btn btn-outline-warning btn-sm px-3 fw-medium d-flex align-items-center gap-2 border-opacity-25">
            <i class="bi bi-shield-exclamation"></i> Isolasi Menunggak
        </button>
        <a href="<?php echo URLROOT; ?>/AdminRouterController/create" class="btn btn-primary btn-sm px-3 fw-medium d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> Tambah Router
        </a>
    </div>
</div>


<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="border-secondary border-opacity-25 text-secondary small text-uppercase" style="letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3 border-0">Nama Router</th>
                        <th class="py-3 border-0">Host IP</th>
                        <th class="py-3 border-0">API Username</th>
                        <th class="py-3 border-0">Port</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['routers'])): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <i class="bi bi-router fs-1 opacity-25 d-block mb-3"></i>
                            Belum ada data router.<br>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['routers'] as $router): ?>
                        <tr>
                            <td class="ps-4 text-white fw-medium">
                                <?php echo htmlspecialchars($router->name); ?>
                                <?php if ($router->description): ?>
                                    <div class="text-secondary small mt-1" style="font-weight: normal;"><?php echo htmlspecialchars($router->description); ?></div>
                                <?php endif; ?>
                                <?php if ($router->pppoe_interface): ?>
                                    <div class="mt-1"><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 small"><?php echo htmlspecialchars($router->pppoe_interface); ?></span></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="font-monospace text-info bg-info bg-opacity-10 px-2 py-1 rounded border border-info border-opacity-25"><?php echo htmlspecialchars($router->host_ip); ?></span></td>
                            <td class="text-white"><?php echo htmlspecialchars($router->api_username); ?></td>
                            <td><?php echo htmlspecialchars($router->api_port); ?></td>
                            <td class="text-center router-status-col" data-router-id="<?php echo $router->id; ?>" data-is-active="<?php echo $router->is_active; ?>">
                                <?php if ($router->is_active): ?>
                                    <span class="text-secondary small">
                                        <span class="spinner-border spinner-border-sm me-1 text-info" role="status" style="width: 12px; height: 12px;"></span>Checking...
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill">Mati</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <!-- Test Koneksi -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-info border-opacity-25 text-info btn-test-conn"
                                            data-router-id="<?php echo $router->id; ?>"
                                            data-router-name="<?php echo htmlspecialchars($router->name); ?>"
                                            title="Test Koneksi">
                                        <i class="bi bi-wifi"></i>
                                    </button>
                                    <a href="<?php echo URLROOT; ?>/AdminRouterController/edit/<?php echo $router->id; ?>" class="btn btn-sm btn-outline-warning border-opacity-25 text-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?php echo URLROOT; ?>/AdminRouterController/delete/<?php echo $router->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus router ini?');">
    <?php echo SecurityHelper::csrfField(); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-opacity-25 text-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Test Koneksi -->
<div class="modal fade" id="modalTestConn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title text-white"><i class="bi bi-wifi me-2"></i>Test Koneksi Router</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="test-conn-loading" class="text-center py-3">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="text-secondary mt-2 mb-0">Menghubungi router, mohon tunggu...</p>
                </div>
                <div id="test-conn-result" class="d-none">
                    <div id="test-conn-success" class="d-none">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-success bg-opacity-10 border border-success border-opacity-25 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-check-lg text-success fs-4"></i>
                            </div>
                            <div>
                                <div class="text-white fw-bold">Koneksi Berhasil!</div>
                                <div class="text-secondary small" id="tc-host">-</div>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="glass-card rounded p-3">
                                    <div class="text-secondary small">Identity</div>
                                    <div class="text-white fw-medium" id="tc-identity">-</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="glass-card rounded p-3">
                                    <div class="text-secondary small">RouterOS Version</div>
                                    <div class="text-white fw-medium" id="tc-version">-</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="glass-card rounded p-3">
                                    <div class="text-success small">Uptime Router</div>
                                    <div class="text-success fw-medium" id="tc-uptime">-</div>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="glass-card rounded p-3">
                                    <div class="text-info fw-bold small mb-2"><i class="bi bi-person-badge-fill me-1"></i>PPPoE Profiles (untuk MIKROTIK_PROFILE)</div>
                                    <div id="tc-profiles" class="d-flex flex-wrap gap-1 text-white small">-</div>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="glass-card rounded p-3">
                                    <div class="text-warning fw-bold small mb-2"><i class="bi bi-hdd-network-fill me-1"></i>Interfaces (untuk MIKROTIK_INTERFACE)</div>
                                    <div id="tc-interfaces" class="d-flex flex-wrap gap-1 text-white small">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="test-conn-error" class="d-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-danger bg-opacity-10 border border-danger border-opacity-25 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-x-lg text-danger fs-4"></i>
                            </div>
                            <div>
                                <div class="text-white fw-bold">Koneksi Gagal</div>
                                <div class="text-danger small" id="tc-error-msg">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Isolasi Overdue -->
<div class="modal fade" id="modalIsolateOverdue" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-warning border-opacity-25">
            <div class="modal-header border-warning border-opacity-25">
                <h5 class="modal-title text-warning"><i class="bi bi-shield-exclamation me-2"></i>Isolasi Pelanggan Menunggak</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="isolate-confirm" class="">
                    <p class="text-white">Tindakan ini akan <strong class="text-warning">menonaktifkan (disable)</strong> akun PPPoE semua pelanggan yang memiliki tagihan overdue secara otomatis.</p>
                    <p class="text-secondary small">Hanya pelanggan dengan paket <strong>auto_isolate = aktif</strong> yang akan diproses.</p>
                </div>
                <div id="isolate-loading" class="d-none text-center py-3">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="text-secondary mt-2 mb-0">Memproses isolasi, mohon tunggu...</p>
                </div>
                <div id="isolate-result" class="d-none">
                    <div class="row g-2 mb-3">
                        <div class="col-4 text-center">
                            <div class="glass-card rounded p-2">
                                <div class="text-success fs-4 fw-bold" id="ir-success">0</div>
                                <div class="text-secondary small">Berhasil</div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="glass-card rounded p-2">
                                <div class="text-danger fs-4 fw-bold" id="ir-failed">0</div>
                                <div class="text-secondary small">Gagal</div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="glass-card rounded p-2">
                                <div class="text-secondary fs-4 fw-bold" id="ir-skipped">0</div>
                                <div class="text-secondary small">Dilewati</div>
                            </div>
                        </div>
                    </div>
                    <div id="ir-details" class="small" style="max-height:200px;overflow-y:auto;"></div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="btn-confirm-isolate">
                    <i class="bi bi-shield-exclamation me-1"></i> Ya, Isolasi Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.router-status-col[data-is-active="1"]').forEach(function(col) {
        var routerId = col.dataset.routerId;
        fetch('<?php echo URLROOT; ?>/AdminRouterController/testConnection/' + routerId)
            .then(r => r.json())
            .then(function(data) {
                if (data.success) {
                    col.innerHTML = '<span class="badge bg-success bg-opacity-10 text-success px-2 py-1 border border-success border-opacity-25 rounded-pill">Aktif</span>';
                } else {
                    col.innerHTML = '<span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill">Mati</span>';
                }
            })
            .catch(function() {
                col.innerHTML = '<span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill">Mati</span>';
            });
    });
});

// ---- Test Koneksi per Router (dari DB) ----
document.querySelectorAll('.btn-test-conn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var routerId   = this.dataset.routerId;
        var routerName = this.dataset.routerName;
        var modal      = new bootstrap.Modal(document.getElementById('modalTestConn'));

        document.querySelector('#modalTestConn .modal-title').innerHTML = '<i class="bi bi-wifi me-2"></i>Test Koneksi: ' + routerName;
        document.getElementById('test-conn-loading').classList.remove('d-none');
        document.getElementById('test-conn-result').classList.add('d-none');
        document.getElementById('test-conn-success').classList.add('d-none');
        document.getElementById('test-conn-error').classList.add('d-none');

        modal.show();

        fetch('<?php echo URLROOT; ?>/AdminRouterController/testConnection/' + routerId)
            .then(r => r.json())
            .then(function(data) {
                document.getElementById('test-conn-loading').classList.add('d-none');
                document.getElementById('test-conn-result').classList.remove('d-none');

                if (data.success) {
                    document.getElementById('test-conn-success').classList.remove('d-none');
                    document.getElementById('tc-host').textContent     = data.host + ':' + data.port;
                    document.getElementById('tc-identity').textContent  = data.identity || '-';
                    document.getElementById('tc-version').textContent   = data.version  || '-';
                    document.getElementById('tc-uptime').textContent    = data.uptime   || '-';
                    
                    // Render profiles
                    var profilesHtml = '';
                    if (data.profiles && data.profiles.length > 0) {
                        data.profiles.forEach(function(p) {
                            profilesHtml += '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 m-1 rounded font-monospace">' + p + '</span>';
                        });
                    } else {
                        profilesHtml = '<span class="text-secondary small">Tidak ada profile</span>';
                    }
                    document.getElementById('tc-profiles').innerHTML = profilesHtml;

                    // Render interfaces
                    var interfacesHtml = '';
                    if (data.interfaces && data.interfaces.length > 0) {
                        data.interfaces.forEach(function(i) {
                            interfacesHtml += '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 m-1 rounded font-monospace">' + i + '</span>';
                        });
                    } else {
                        interfacesHtml = '<span class="text-secondary small">Tidak ada interface</span>';
                    }
                    document.getElementById('tc-interfaces').innerHTML = interfacesHtml;
                } else {
                    document.getElementById('test-conn-error').classList.remove('d-none');
                    document.getElementById('tc-error-msg').textContent = data.message;
                }
            })
            .catch(function(e) {
                document.getElementById('test-conn-loading').classList.add('d-none');
                document.getElementById('test-conn-result').classList.remove('d-none');
                document.getElementById('test-conn-error').classList.remove('d-none');
                document.getElementById('tc-error-msg').textContent = 'Request error: ' + e.message;
            });
    });
});

// ---- Isolasi Overdue ----
document.getElementById('btn-isolate-overdue').addEventListener('click', function() {
    document.getElementById('isolate-confirm').classList.remove('d-none');
    document.getElementById('isolate-loading').classList.add('d-none');
    document.getElementById('isolate-result').classList.add('d-none');
    document.getElementById('btn-confirm-isolate').classList.remove('d-none');
    new bootstrap.Modal(document.getElementById('modalIsolateOverdue')).show();
});

document.getElementById('btn-confirm-isolate').addEventListener('click', function() {
    document.getElementById('isolate-confirm').classList.add('d-none');
    document.getElementById('isolate-loading').classList.remove('d-none');
    document.getElementById('btn-confirm-isolate').classList.add('d-none');

    var formData = new FormData();
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch('<?php echo URLROOT; ?>/AdminRouterController/isolateOverdue', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(function(data) {
            document.getElementById('isolate-loading').classList.add('d-none');
            document.getElementById('isolate-result').classList.remove('d-none');
            document.getElementById('ir-success').textContent  = data.success  || 0;
            document.getElementById('ir-failed').textContent   = data.failed   || 0;
            document.getElementById('ir-skipped').textContent  = data.skipped  || 0;

            var detailsHtml = '';
            if (data.details && data.details.length > 0) {
                data.details.forEach(function(d) {
                    var color = d.status === 'isolated' ? 'text-success' : (d.status === 'failed' ? 'text-danger' : 'text-secondary');
                    var icon  = d.status === 'isolated' ? '✓' : (d.status === 'failed' ? '✗' : '–');
                    detailsHtml += '<div class="' + color + ' mb-1">' + icon + ' ' + d.name + (d.pppoe ? ' (' + d.pppoe + ')' : '') + (d.reason ? ' — ' + d.reason : '') + '</div>';
                });
            }
            document.getElementById('ir-details').innerHTML = detailsHtml || '<span class="text-secondary">Tidak ada detail.</span>';
        })
        .catch(function(e) {
            document.getElementById('isolate-loading').classList.add('d-none');
            alert('Error: ' + e.message);
        });
});
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
