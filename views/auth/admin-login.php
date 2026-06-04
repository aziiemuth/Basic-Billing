<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/auth_header.php'; ?>

<div class="auth-container p-4">
    <div class="glass-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-gradient text-white rounded-3 mb-3 shadow" style="width: 64px; height: 64px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-shield-lock" viewBox="0 0 16 16">
                  <path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/>
                  <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415z"/>
                </svg>
            </div>
            <h3 class="fw-bold text-white mb-1">Billing App</h3>
            <p class="text-secondary small">Masuk sebagai Admin / Staff</p>
        </div>

        <?php if (!empty($data['error'])): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle-fill me-2" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
            <div><?php echo htmlspecialchars($data['error']); ?></div>
        </div>
        <?php endif; ?>

        <form action="<?php echo URLROOT; ?>/AdminAuthController/login" method="POST">
            <?php echo SecurityHelper::csrfField(); ?>
            <div class="mb-3">
                <label for="username" class="form-label text-secondary small fw-medium">Username</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 bg-transparent text-secondary">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" value="<?php echo htmlspecialchars($data['username']); ?>" placeholder="Masukkan username" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label text-secondary small fw-medium">Password</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 bg-transparent text-secondary">
                        <i class="bi bi-key"></i>
                    </span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Masuk</button>
        </form>

        <div class="text-center mt-4">
            <p class="text-secondary small mb-0">Bukan admin? <a href="<?php echo URLROOT; ?>/CustomerAuthController/login" class="text-primary text-decoration-none fw-medium">Portal Pelanggan</a></p>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/auth_footer.php'; ?>
