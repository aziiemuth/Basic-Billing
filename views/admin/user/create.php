<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="mb-4">
    <a href="<?php echo URLROOT; ?>/AdminUserController" class="text-secondary text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold text-white mb-0 mt-2">Tambah User Login</h4>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="<?php echo URLROOT; ?>/AdminUserController/store" method="POST">
            <?php echo SecurityHelper::csrfField(); ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-secondary small">Nama</label>
                    <input type="text" name="name" class="form-control bg-dark text-white border-secondary border-opacity-25" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small">Username</label>
                    <input type="text" name="username" class="form-control bg-dark text-white border-secondary border-opacity-25" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small">Password</label>
                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary border-opacity-25" minlength="6" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-secondary small">Role</label>
                    <select name="role" class="form-select bg-dark text-white border-secondary border-opacity-25">
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4 mt-4"><i class="bi bi-save me-1"></i> Simpan</button>
        </form>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
