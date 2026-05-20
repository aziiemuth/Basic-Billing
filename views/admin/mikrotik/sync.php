<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1"><i class="bi bi-arrow-repeat me-2 text-info"></i>Sinkronisasi PPPoE MikroTik</h4>
        <p class="text-secondary small mb-0">Pantau status koneksi online/offline pelanggan secara realtime dari MikroTik.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo URLROOT; ?>/AdminCustomerController/importMikrotik<?php echo $data['router_id'] ? '?router_id='.$data['router_id'] : ''; ?>" class="btn btn-outline-info btn-sm px-3 border-opacity-25 fw-medium d-flex align-items-center gap-1">
            <i class="bi bi-cloud-download"></i> Tarik Data (Import) dari MikroTik
        </a>
        <a href="<?php echo URLROOT; ?>/AdminRouterController" class="btn btn-outline-secondary btn-sm px-3 border-opacity-25 d-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Filter Router -->
<div class="card glass-card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="text-secondary small mb-1">Pilih Router</label>
                <select id="router-select" class="form-select form-select-sm bg-dark text-white border-secondary border-opacity-25">
                    <option value="">-- Semua Router (tanpa realtime) --</option>
                    <?php foreach($data['routers'] as $r): ?>
                        <option value="<?php echo $r->id; ?>" <?php echo ($data['router_id'] == $r->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r->name); ?> (<?php echo htmlspecialchars($r->host_ip); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button id="btn-load-router" class="btn btn-info btn-sm px-4 fw-medium">
                    <i class="bi bi-search me-1"></i> Muat Status
                </button>
            </div>
            <?php if ($data['router_id']): ?>
            <div class="col-md-4 text-end">
                <?php if ($data['syncError']): ?>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 p-2">
                        <i class="bi bi-x-circle me-1"></i> <?php echo htmlspecialchars($data['syncError']); ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 p-2">
                        <i class="bi bi-check-circle me-1"></i> Terhubung — <?php echo count($data['mikrotikStatus']); ?> PPPoE ditemukan
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($data['syncError']): ?>
<div class="alert border border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger rounded-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Gagal terhubung ke router:</strong> <?php echo htmlspecialchars($data['syncError']); ?>
    <br><small class="text-secondary">Pastikan router aktif, IP/Port/Kredensial benar, dan API MikroTik diaktifkan di router.</small>
</div>
<?php endif; ?>

<!-- Statistik Ringkas -->
<?php if ($data['router_id'] && !$data['syncError']): ?>
<?php
    $totalSecrets  = count($data['mikrotikStatus']);
    $totalOnline   = 0;
    $totalDisabled = 0;
    foreach ($data['mikrotikStatus'] as $s) {
        if ($s['online'])   $totalOnline++;
        if ($s['disabled']) $totalDisabled++;
    }
    $totalOffline = $totalSecrets - $totalOnline;
?>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-white"><?php echo $totalSecrets; ?></div>
                <div class="text-secondary small">Total PPPoE Secret</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-success"><?php echo $totalOnline; ?></div>
                <div class="text-secondary small">Online</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-secondary"><?php echo $totalOffline; ?></div>
                <div class="text-secondary small">Offline</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-danger"><?php echo $totalDisabled; ?></div>
                <div class="text-secondary small">Disabled (Terisolasi)</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Pelanggan -->
<div class="card glass-card border-0 shadow-sm">
    <div class="card-header bg-transparent border-secondary border-opacity-25 d-flex justify-content-between align-items-center py-3">
        <span class="text-white fw-medium">Daftar Pelanggan & Status PPPoE</span>
        <div class="d-flex gap-2">
            <input type="text" id="search-customer" class="form-control form-control-sm bg-dark text-white border-secondary border-opacity-25" placeholder="Cari pelanggan..." style="width:200px;">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle" id="sync-table">
                <thead class="border-secondary border-opacity-25 text-secondary small text-uppercase" style="letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3 border-0">Pelanggan</th>
                        <th class="py-3 border-0">PPPoE Username</th>
                        <th class="py-3 border-0">Profile</th>
                        <th class="py-3 border-0">Router</th>
                        <th class="py-3 border-0 text-center">Status DB</th>
                        <th class="py-3 border-0 text-center">Status MikroTik</th>
                        <th class="py-3 border-0">Uptime / IP</th>
                        <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['customers'])): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">
                            <i class="bi bi-people fs-1 opacity-25 d-block mb-3"></i>
                            <h5 class="text-white small fw-bold mb-1">Belum Ada Pelanggan Terdaftar dengan PPPoE</h5>
                            <p class="text-secondary small mb-3">Halaman ini digunakan untuk memantau status realtime pelanggan yang sudah terdaftar di database billing.</p>
                            <?php if ($data['router_id']): ?>
                                <a href="<?php echo URLROOT; ?>/AdminCustomerController/importMikrotik?router_id=<?php echo $data['router_id']; ?>" class="btn btn-info btn-sm px-3 fw-medium">
                                    <i class="bi bi-cloud-download me-1"></i> Tarik / Import Data PPPoE dari MikroTik Sekarang
                                </a>
                            <?php else: ?>
                                <a href="<?php echo URLROOT; ?>/AdminCustomerController/importMikrotik" class="btn btn-info btn-sm px-3 fw-medium">
                                    <i class="bi bi-cloud-download me-1"></i> Tarik / Import Data PPPoE dari MikroTik
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['customers'] as $c): ?>
                        <?php
                            $username   = $c->pppoe_username ?? null;
                            $mtStatus   = ($username && isset($data['mikrotikStatus'][$username])) ? $data['mikrotikStatus'][$username] : null;
                            $routerId   = $c->mikrotik_router_id ?? null;
                        ?>
                        <tr class="customer-row" data-name="<?php echo strtolower($c->name); ?>">
                            <td class="ps-4">
                                <div class="text-white fw-medium"><?php echo htmlspecialchars($c->name); ?></div>
                                <div class="text-secondary small"><?php echo htmlspecialchars($c->customer_id); ?></div>
                            </td>
                            <td>
                                <?php if ($username): ?>
                                    <span class="font-monospace text-info bg-info bg-opacity-10 px-2 py-1 rounded border border-info border-opacity-25 small">
                                        <?php echo htmlspecialchars($username); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-secondary small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-secondary small"><?php echo htmlspecialchars($c->pppoe_profile ?? $c->mikrotik_profile ?? '-'); ?></td>
                            <td class="text-secondary small"><?php echo htmlspecialchars($c->router_name ?? '-'); ?><br><span class="text-muted"><?php echo htmlspecialchars($c->router_ip ?? ''); ?></span></td>
                            <td class="text-center">
                                <?php
                                    $dbStatus = $c->status ?? 'active';
                                    $dbBadge  = match($dbStatus) {
                                        'active'   => ['success', 'Aktif'],
                                        'isolated' => ['warning', 'Terisolasi'],
                                        'inactive' => ['secondary', 'Nonaktif'],
                                        default    => ['secondary', $dbStatus],
                                    };
                                ?>
                                <span class="badge bg-<?php echo $dbBadge[0]; ?> bg-opacity-10 text-<?php echo $dbBadge[0]; ?> border border-<?php echo $dbBadge[0]; ?> border-opacity-25 rounded-pill px-2">
                                    <?php echo $dbBadge[1]; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if (!$data['router_id']): ?>
                                    <span class="text-secondary small">—</span>
                                <?php elseif (!$username): ?>
                                    <span class="text-secondary small">No PPPoE</span>
                                <?php elseif ($mtStatus === null): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2">Tidak Ada</span>
                                <?php elseif ($mtStatus['disabled']): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2">Disabled</span>
                                <?php elseif ($mtStatus['online']): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">
                                        <i class="bi bi-circle-fill me-1" style="font-size:6px;vertical-align:middle;"></i>Online
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2">Offline</span>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <?php if ($mtStatus && $mtStatus['online']): ?>
                                    <div class="text-success"><?php echo htmlspecialchars($mtStatus['uptime']); ?></div>
                                    <div class="text-secondary"><?php echo htmlspecialchars($mtStatus['address']); ?></div>
                                <?php else: ?>
                                    <span class="text-secondary">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <?php if ($username && $routerId): ?>
                                <div class="d-flex gap-1 justify-content-end">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-success border-opacity-25 text-success btn-toggle-pppoe"
                                            data-action="enable"
                                            data-username="<?php echo htmlspecialchars($username); ?>"
                                            data-router-id="<?php echo $routerId; ?>"
                                            title="Enable PPPoE">
                                        <i class="bi bi-play-fill"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger border-opacity-25 text-danger btn-toggle-pppoe"
                                            data-action="disable"
                                            data-username="<?php echo htmlspecialchars($username); ?>"
                                            data-router-id="<?php echo $routerId; ?>"
                                            title="Disable PPPoE">
                                        <i class="bi bi-stop-fill"></i>
                                    </button>
                                    <a href="<?php echo URLROOT; ?>/AdminCustomerController/show/<?php echo $c->id; ?>"
                                       class="btn btn-sm btn-outline-secondary border-opacity-25 text-secondary"
                                       title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                                <?php else: ?>
                                    <span class="text-secondary small">—</span>
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

<!-- Toast Notifikasi -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="sync-toast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="sync-toast-body">Berhasil</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
// Router select redirect
document.getElementById('btn-load-router').addEventListener('click', function() {
    var val = document.getElementById('router-select').value;
    var url = val
        ? '<?php echo URLROOT; ?>/AdminRouterController/sync/' + val
        : '<?php echo URLROOT; ?>/AdminRouterController/sync';
    window.location.href = url;
});

// Search filter
document.getElementById('search-customer').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.customer-row').forEach(function(row) {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
});

// Enable/Disable PPPoE toggle
document.querySelectorAll('.btn-toggle-pppoe').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var action   = this.dataset.action;
        var username = this.dataset.username;
        var routerId = this.dataset.routerId;
        var label    = action === 'enable' ? 'mengaktifkan' : 'menonaktifkan';

        if (!confirm('Yakin ingin ' + label + ' akun PPPoE "' + username + '"?')) return;

        this.disabled = true;
        var self = this;

        var formData = new FormData();
        formData.append('action',    action);
        formData.append('username',  username);
        formData.append('router_id', routerId);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('<?php echo URLROOT; ?>/AdminRouterController/togglePppoe', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(function(data) {
            self.disabled = false;
            showToast(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                setTimeout(function() { location.reload(); }, 1200);
            }
        })
        .catch(function(e) {
            self.disabled = false;
            showToast('Error: ' + e.message, 'danger');
        });
    });
});

function showToast(message, type) {
    var toast    = document.getElementById('sync-toast');
    var toastBody = document.getElementById('sync-toast-body');
    toast.className   = 'toast align-items-center border-0 text-white bg-' + (type === 'success' ? 'success' : 'danger') + ' bg-opacity-90';
    toastBody.textContent = message;
    new bootstrap.Toast(toast, {delay: 3000}).show();
}
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
