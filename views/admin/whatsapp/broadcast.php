<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-secondary small mb-0">Kirim pesan custom ke pelanggan berdasarkan status. Token dikonfigurasi via file <code>.env</code>.</p>
    </div>
    <?php if (!defined('WA_ENABLED') || !WA_ENABLED): ?>
        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
            <i class="bi bi-exclamation-triangle me-1"></i> WA Gateway Belum Aktif (WA_ENABLED=false di .env)
        </span>
    <?php else: ?>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
            <i class="bi bi-wifi me-1"></i> WA Gateway Aktif
        </span>
    <?php endif; ?>
</div>

<?php
$stats = $data['stats'] ?? ['active' => 0, 'isolated' => 0, 'inactive' => 0, 'all' => 0];
?>

<div class="row g-4">
    <!-- Form Broadcast -->
    <div class="col-12 col-xl-5">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                <h6 class="mb-0 text-white fw-bold"><i class="bi bi-send-fill me-2 text-success"></i>Kirim Pesan</h6>
            </div>
            <div class="card-body p-4">
                <form action="<?php echo URLROOT; ?>/AdminWhatsappController/sendBroadcast" method="POST"
                      onsubmit="return confirm('Kirim broadcast ke target yang dipilih?\n\nAksi ini tidak dapat dibatalkan.');">
                    <?php echo SecurityHelper::csrfField(); ?>

                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-medium">Target Penerima</label>
                        <select id="target-select" name="target" class="form-select bg-dark text-white border-secondary border-opacity-25" onchange="updateTargetInfo()">
                            <option value="active">Pelanggan Aktif</option>
                            <option value="isolated">Pelanggan Terisolir (Belum Bayar)</option>
                            <option value="inactive">Pelanggan Nonaktif</option>
                            <option value="all">Semua Pelanggan</option>
                        </select>
                        <div id="target-info" class="form-text text-info mt-2 small">
                            <i class="bi bi-info-circle me-1"></i>
                            <span id="target-desc">Mengirim ke <?php echo $stats['active']; ?> pelanggan aktif.</span>
                        </div>
                    </div>

                    <!-- Stat Badges -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                            <i class="bi bi-circle-fill me-1" style="font-size:7px;"></i> Aktif: <?php echo $stats['active']; ?>
                        </span>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                            <i class="bi bi-circle-fill me-1" style="font-size:7px;"></i> Terisolir (nunggak): <?php echo $stats['isolated']; ?>
                        </span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                            <i class="bi bi-circle-fill me-1" style="font-size:7px;"></i> Nonaktif: <?php echo $stats['inactive']; ?>
                        </span>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                            <i class="bi bi-circle-fill me-1" style="font-size:7px;"></i> Total: <?php echo $stats['all']; ?>
                        </span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-medium">Isi Pesan</label>
                        <textarea name="message" rows="9" class="form-control bg-dark text-white border-secondary border-opacity-25" required>Halo {nama},

Kami informasikan bahwa ada pemberitahuan layanan internet untuk ID pelanggan {id_pelanggan}.

Terima kasih.</textarea>
                        <div class="form-text text-secondary mt-1">
                            Placeholder: <code class="text-info">{nama}</code>, <code class="text-info">{id_pelanggan}</code>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-medium d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-whatsapp"></i> Kirim Broadcast
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar Pelanggan -->
    <div class="col-12 col-xl-7">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-white fw-bold">Daftar Semua Pelanggan</h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><?php echo count($data['customers']); ?> pelanggan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="text-secondary small text-uppercase sticky-top" style="background: #1a1a2e; top: 0;">
                            <tr>
                                <th class="ps-4 py-3 border-0">Pelanggan</th>
                                <th class="py-3 border-0">WhatsApp</th>
                                <th class="pe-4 py-3 border-0 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['customers'] as $customer): ?>
                                <tr>
                                    <td class="ps-4 text-white">
                                        <?php echo htmlspecialchars($customer->name); ?>
                                        <div class="text-secondary" style="font-size: 0.72rem;"><?php echo htmlspecialchars($customer->customer_id); ?></div>
                                    </td>
                                    <td class="text-info small font-monospace"><?php echo htmlspecialchars($customer->whatsapp ?: '-'); ?></td>
                                    <td class="pe-4 text-center">
                                        <?php
                                        $badgeClass = match($customer->status) {
                                            'active'   => 'bg-success bg-opacity-10 text-success border-success',
                                            'isolated' => 'bg-danger bg-opacity-10 text-danger border-danger',
                                            'inactive' => 'bg-secondary bg-opacity-10 text-secondary border-secondary',
                                            default    => 'bg-secondary bg-opacity-10 text-secondary border-secondary',
                                        };
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> border border-opacity-25">
                                            <?php echo htmlspecialchars($customer->status); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const targetStats = {
    active:   { count: <?php echo $stats['active']; ?>,   desc: 'Mengirim ke <strong><?php echo $stats['active']; ?> pelanggan aktif</strong>.' },
    isolated: { count: <?php echo $stats['isolated']; ?>, desc: 'Mengirim ke <strong><?php echo $stats['isolated']; ?> pelanggan terisolir</strong> yang <strong class="text-warning">masih punya tagihan belum dibayar</strong>. Pelanggan yang sudah bayar tidak akan menerima pesan ini.' },
    inactive: { count: <?php echo $stats['inactive']; ?>, desc: 'Mengirim ke <strong><?php echo $stats['inactive']; ?> pelanggan nonaktif</strong>.' },
    all:      { count: <?php echo $stats['all']; ?>,      desc: 'Mengirim ke <strong>semua <?php echo $stats['all']; ?> pelanggan</strong> tanpa pengecualian.' },
};

function updateTargetInfo() {
    const target = document.getElementById('target-select').value;
    const info   = targetStats[target];
    document.getElementById('target-desc').innerHTML = info.desc;
}

// Inisialisasi deskripsi saat halaman dimuat
updateTargetInfo();
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
