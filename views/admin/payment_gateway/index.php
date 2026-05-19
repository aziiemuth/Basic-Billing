<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Kelola Payment Gateway</h4>
        <p class="text-secondary small mb-0">Atur kredensial pembayaran online yang digunakan sistem.</p>
    </div>
    <a href="<?php echo URLROOT; ?>/AdminPaymentGatewayController/create" class="btn btn-primary btn-sm px-3 fw-medium">
        <i class="bi bi-plus-lg me-1"></i> Tambah Gateway
    </a>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3 border-0">Gateway</th>
                        <th class="py-3 border-0">Mode</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="py-3 border-0">Server Key</th>
                        <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['gateways'])): ?>
                        <tr><td colspan="5" class="text-center text-secondary py-5">Belum ada payment gateway.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['gateways'] as $gateway): ?>
                            <tr>
                                <td class="ps-4 text-white fw-medium"><?php echo htmlspecialchars($gateway->name); ?></td>
                                <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25"><?php echo htmlspecialchars($gateway->mode); ?></span></td>
                                <td class="text-center">
                                    <?php if ($gateway->is_active): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-monospace text-secondary"><?php echo $gateway->server_key ? htmlspecialchars(substr($gateway->server_key, 0, 10)) . '...' : '-'; ?></td>
                                <td class="pe-4 text-end">
                                    <a href="<?php echo URLROOT; ?>/AdminPaymentGatewayController/edit/<?php echo $gateway->id; ?>" class="btn btn-sm btn-outline-warning border-opacity-25" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form action="<?php echo URLROOT; ?>/AdminPaymentGatewayController/delete/<?php echo $gateway->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus payment gateway ini?');">
                                        <?php echo SecurityHelper::csrfField(); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-opacity-25" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
