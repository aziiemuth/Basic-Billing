<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Kelola User Login</h4>
        <p class="text-secondary small mb-0">Kelola akun yang dapat masuk ke panel admin.</p>
    </div>
    <a href="<?php echo URLROOT; ?>/AdminUserController/create" class="btn btn-primary btn-sm px-3 fw-medium">
        <i class="bi bi-person-plus me-1"></i> Tambah User
    </a>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3 border-0">Nama</th>
                        <th class="py-3 border-0">Username</th>
                        <th class="py-3 border-0">Role</th>
                        <th class="py-3 border-0">Login Terakhir</th>
                        <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['users'] as $user): ?>
                        <tr>
                            <td class="ps-4 text-white fw-medium"><?php echo htmlspecialchars($user->name); ?></td>
                            <td class="font-monospace text-info"><?php echo htmlspecialchars($user->username); ?></td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25"><?php echo htmlspecialchars($user->role); ?></span></td>
                            <td class="text-secondary"><?php echo $user->last_login ? date('d M Y H:i', strtotime($user->last_login)) : '-'; ?></td>
                            <td class="pe-4 text-end">
                                <a href="<?php echo URLROOT; ?>/AdminUserController/edit/<?php echo $user->id; ?>" class="btn btn-sm btn-outline-warning border-opacity-25"><i class="bi bi-pencil"></i></a>
                                <form action="<?php echo URLROOT; ?>/AdminUserController/delete/<?php echo $user->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus user ini?');">
                                    <?php echo SecurityHelper::csrfField(); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-opacity-25" <?php echo (int)$user->id === (int)($_SESSION['user_id'] ?? 0) ? 'disabled' : ''; ?>><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
