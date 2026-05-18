<?php require_once APPROOT . '/views/layouts/auth_header.php'; ?>

<div class="auth-container p-4">
    <div class="glass-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-info bg-gradient text-white rounded-3 mb-3 shadow" style="width: 64px; height: 64px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person-badge" viewBox="0 0 16 16">
                  <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                  <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 10.5 0h-6zM3 2.5A1.5 1.5 0 0 1 4.5 1h6A1.5 1.5 0 0 1 12 2.5V14a1.5 1.5 0 0 1-1.5 1.5h-7A1.5 1.5 0 0 1 3 14V2.5zM8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.29 10 8 10z"/>
                </svg>
            </div>
            <h3 class="fw-bold text-white mb-1">Portal Pelanggan</h3>
            <p class="text-secondary small">Cek tagihan & status internet Anda</p>
        </div>

        <?php if (!empty($data['error'])): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle-fill me-2" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
            <div><?php echo htmlspecialchars($data['error']); ?></div>
        </div>
        <?php endif; ?>

        <form action="<?php echo URLROOT; ?>/CustomerAuthController/login" method="POST" data-ajax="true">
            <?php echo SecurityHelper::csrfField(); ?>
            <div class="mb-3">
                <label for="identifier" class="form-label text-secondary small fw-medium">ID Pelanggan / Username</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 bg-transparent text-secondary">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" id="identifier" name="identifier" value="<?php echo htmlspecialchars($data['identifier']); ?>" placeholder="CUST-0001" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label text-secondary small fw-medium">Password</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 bg-transparent text-secondary">
                        <i class="bi bi-key"></i>
                    </span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-info w-100 py-2 fw-semibold text-white">Masuk Portal</button>
        </form>

        <div class="text-center mt-4">
            <p class="text-secondary small mb-0">Staff Billing? <a href="<?php echo URLROOT; ?>/AdminAuthController/login" class="text-info text-decoration-none fw-medium">Login Admin</a></p>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/auth_footer.php'; ?>
