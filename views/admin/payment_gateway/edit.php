<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="mb-4">
    <a href="<?php echo URLROOT; ?>/AdminPaymentGatewayController" class="text-secondary text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold text-white mb-0 mt-2">Edit Payment Gateway</h4>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="<?php echo URLROOT; ?>/AdminPaymentGatewayController/update/<?php echo $data['gateway']->id; ?>" method="POST">
            <?php echo SecurityHelper::csrfField(); ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small">Nama Gateway</label>
                    <input type="text" name="name" class="form-control bg-dark text-white border-secondary border-opacity-25" value="<?php echo htmlspecialchars($data['gateway']->name); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small">Mode</label>
                    <select name="mode" class="form-select bg-dark text-white border-secondary border-opacity-25">
                        <option value="sandbox" <?php echo $data['gateway']->mode === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                        <option value="production" <?php echo $data['gateway']->mode === 'production' ? 'selected' : ''; ?>>Production</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small">Server Key</label>
                    <input type="text" name="server_key" class="form-control bg-dark text-white border-secondary border-opacity-25" value="<?php echo htmlspecialchars($data['gateway']->server_key ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small">Client Key</label>
                    <input type="text" name="client_key" class="form-control bg-dark text-white border-secondary border-opacity-25" value="<?php echo htmlspecialchars($data['gateway']->client_key ?? ''); ?>">
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $data['gateway']->is_active ? 'checked' : ''; ?>>
                        <label class="form-check-label text-secondary" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
