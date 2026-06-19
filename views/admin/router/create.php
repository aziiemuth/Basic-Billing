<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="mb-4">
    <a href="<?php echo URLROOT; ?>/AdminRouterController" class="text-secondary text-decoration-none small d-inline-flex align-items-center gap-2 mb-2">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Router
    </a>
    <p class="text-secondary small mb-0">Tambahkan koneksi router MikroTik baru untuk dihubungkan ke sistem.</p>
</div>

<div class="row">
    <div class="col-12 col-xl-8">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="<?php echo URLROOT; ?>/AdminRouterController/store" method="POST">
    <?php echo SecurityHelper::csrfField(); ?>
                    <!-- Fake inputs to prevent browser autofill -->
                    <input type="text" name="prevent_autofill_username" style="display:none;" />
                    <input type="password" name="prevent_autofill_password" style="display:none;" />
                    
                    <h6 class="text-white fw-bold mb-3 border-bottom border-secondary border-opacity-25 pb-2">Informasi Umum</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small mb-1">Nama Router <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-dark border-secondary border-opacity-25 text-white" required placeholder="Misal: Router Pusat" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small mb-1">Host IP <span class="text-danger">*</span></label>
                            <input type="text" name="host_ip" class="form-control bg-dark border-secondary border-opacity-25 text-white" required placeholder="Misal: 192.168.1.1" autocomplete="off">
                        </div>
                    </div>

                    <h6 class="text-white fw-bold mb-3 border-bottom border-secondary border-opacity-25 pb-2">Kredensial API</h6>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary small mb-1">API Username <span class="text-danger">*</span></label>
                            <input type="text" name="api_username" class="form-control bg-dark border-secondary border-opacity-25 text-white" required autocomplete="off">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary small mb-1">API Password <span class="text-danger">*</span></label>
                            <input type="password" name="api_password" class="form-control bg-dark border-secondary border-opacity-25 text-white" required autocomplete="new-password">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-secondary small mb-1">API Port</label>
                            <input type="number" name="api_port" class="form-control bg-dark border-secondary border-opacity-25 text-white" value="8728" autocomplete="off">
                            <div class="form-text text-secondary small">Default port API MikroTik adalah 8728.</div>
                        </div>
                    </div>

                    <h6 class="text-white fw-bold mb-3 border-bottom border-secondary border-opacity-25 pb-2">Konfigurasi Tambahan</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small mb-1">PPPoE Interface</label>
                            <input type="text" name="pppoe_interface" class="form-control bg-dark border-secondary border-opacity-25 text-white" placeholder="Kosongkan jika default">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small mb-1">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label text-white" for="is_active">Aktif</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-secondary small mb-1">Deskripsi</label>
                            <textarea name="description" rows="3" class="form-control bg-dark border-secondary border-opacity-25 text-white"></textarea>
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-25 my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo URLROOT; ?>/AdminRouterController" class="btn btn-dark border-secondary border-opacity-25 px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-medium">Simpan Router</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
