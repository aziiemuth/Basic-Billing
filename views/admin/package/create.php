<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo URLROOT; ?>/AdminPackageController" class="text-decoration-none text-secondary small mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Paket</a>
        <h4 class="fw-bold text-white mb-0">Tambah Paket Internet</h4>
    </div>
</div>

<form action="<?php echo URLROOT; ?>/AdminPackageController/store" method="POST">
    <?php echo SecurityHelper::csrfField(); ?>
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card glass-card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                    <h6 class="fw-bold text-white mb-0"><i class="bi bi-box-seam me-2 text-primary"></i> Detail Paket</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label text-secondary small fw-medium">Nama Paket</label>
                            <input type="text" name="name" class="form-control bg-dark border-secondary border-opacity-25 text-white" required placeholder="Contoh: Paket Keluarga 20 Mbps">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Kecepatan Download (Mbps)</label>
                            <div class="input-group">
                                <input type="number" min="1" name="speed_download" class="form-control bg-dark border-secondary border-opacity-25 text-white border-end-0" required>
                                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary border-start-0">Mbps</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Kecepatan Upload (Mbps)</label>
                            <div class="input-group">
                                <input type="number" min="1" name="speed_upload" class="form-control bg-dark border-secondary border-opacity-25 text-white border-end-0" required>
                                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary border-start-0">Mbps</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Harga Paket (Per Bulan)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary border-end-0">Rp</span>
                                <input type="number" min="0" step="1000" name="price" class="form-control bg-dark border-secondary border-opacity-25 text-white border-start-0" required placeholder="250000">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Nama Profile MikroTik</label>
                            <input type="text" name="mikrotik_profile" class="form-control bg-dark border-secondary border-opacity-25 text-white font-monospace" required placeholder="profile_20m">
                            <div class="form-text text-secondary opacity-75 small">Pastikan nama persis sama dengan di router MikroTik.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-secondary small fw-medium">Deskripsi <span class="text-muted">(Opsional)</span></label>
                            <textarea name="description" rows="3" class="form-control bg-dark border-secondary border-opacity-25 text-white" placeholder="Fasilitas tambahan, dll"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card glass-card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                    <h6 class="fw-bold text-white mb-0"><i class="bi bi-gear me-2 text-warning"></i> Pengaturan Khusus</h6>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="auto_isolate" name="auto_isolate" value="1" checked>
                        <label class="form-check-label text-white ms-2" for="auto_isolate">Isolir Otomatis</label>
                        <div class="form-text text-secondary opacity-75 small mt-1">Sistem akan mematikan koneksi pelanggan paket ini jika telat bayar tagihan.</div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label text-white ms-2" for="is_active">Paket Aktif</label>
                        <div class="form-text text-secondary opacity-75 small mt-1">Hanya paket aktif yang bisa dipilih saat tambah pelanggan.</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow">
                <i class="bi bi-save me-2"></i> Simpan Paket
            </button>
        </div>
    </div>
</form>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
