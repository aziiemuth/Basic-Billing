<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Manajemen Pelanggan</h4>
        <p class="text-secondary small mb-0">Kelola data pelanggan, status, dan layanan internet.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo URLROOT; ?>/AdminCustomerController/importMikrotik" class="btn btn-outline-info btn-sm px-3 fw-medium d-flex align-items-center gap-2 border-opacity-25">
            <i class="bi bi-cloud-download"></i> Import dari MikroTik
        </a>
        <a href="<?php echo URLROOT; ?>/AdminCustomerController/create" class="btn btn-primary btn-sm px-3 fw-medium d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> Tambah Pelanggan
        </a>
    </div>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="border-secondary border-opacity-25 text-secondary small text-uppercase" style="letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3 border-0">ID</th>
                        <th class="py-3 border-0">Pelanggan</th>
                        <th class="py-3 border-0">Kontak</th>
                        <th class="py-3 border-0">Status</th>
                        <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['customers'])): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-secondary">
                            <i class="bi bi-people fs-1 opacity-25 d-block mb-3"></i>
                            Belum ada data pelanggan.<br>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['customers'] as $customer): ?>
                        <tr>
                            <td class="ps-4 text-white fw-medium"><?php echo $customer->customer_id; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden;">
                                        <?php if ($customer->photo_profile): ?>
                                            <img src="<?php echo URLROOT; ?>/uploads/customers/profile/<?php echo $customer->photo_profile; ?>" alt="Profile" class="w-100 h-100 object-fit-cover">
                                        <?php else: ?>
                                            <i class="bi bi-person text-white fs-5"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-white fw-medium mb-1"><?php echo $customer->name; ?></div>
                                        <div class="text-secondary small"><i class="bi bi-geo-alt"></i> <?php echo substr($customer->address, 0, 30) . '...'; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-white small mb-1"><i class="bi bi-whatsapp text-success"></i> <?php echo $customer->whatsapp; ?></div>
                                <?php if ($customer->username): ?>
                                <div class="text-secondary small"><i class="bi bi-person"></i> <?php echo $customer->username; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($customer->status == 'active'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 border border-success border-opacity-25 rounded-pill">Aktif</span>
                                <?php elseif ($customer->status == 'inactive'): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 border border-secondary border-opacity-25 rounded-pill">Nonaktif</span>
                                <?php elseif ($customer->status == 'isolated'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill">Terisolir</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?php echo URLROOT; ?>/AdminCustomerController/show/<?php echo $customer->id; ?>" class="btn btn-sm btn-outline-info border-opacity-25 text-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?php echo URLROOT; ?>/AdminCustomerController/edit/<?php echo $customer->id; ?>" class="btn btn-sm btn-outline-warning border-opacity-25 text-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?php echo URLROOT; ?>/AdminCustomerController/delete/<?php echo $customer->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pelanggan ini? Semua data terkait juga akan terhapus.');">
    <?php echo SecurityHelper::csrfField(); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-opacity-25 text-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
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
