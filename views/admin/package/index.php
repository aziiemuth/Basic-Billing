<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <p class="text-secondary small mb-0">Kelola daftar paket, harga, bandwidth, dan profil MikroTik.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap justify-content-end">
        <form action="<?php echo URLROOT; ?>/AdminPackageController/syncMikrotik" method="POST" class="m-0">
            <?php echo SecurityHelper::csrfField(); ?>
            <button type="submit" class="btn btn-outline-info btn-sm px-3 fw-medium d-flex align-items-center gap-2 border-opacity-25" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Menarik...'; this.disabled=true; this.form.submit();">
                <i class="bi bi-cloud-arrow-down"></i> <span class="d-none d-sm-inline">Sinkronisasi dari MikroTik</span><span class="d-inline d-sm-none">Sinkronisasi</span>
            </button>
        </form>
        <a href="<?php echo URLROOT; ?>/AdminPackageController/create" class="btn btn-primary btn-sm px-3 fw-medium d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> <span>Tambah Paket</span>
        </a>
    </div>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="border-secondary border-opacity-25 text-secondary small text-uppercase" style="letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3 border-0">Nama Paket</th>
                        <th class="py-3 border-0">Bandwidth</th>
                        <th class="py-3 border-0">Harga</th>
                        <th class="py-3 border-0">Profil MikroTik</th>
                        <th class="py-3 border-0 text-center">Auto Isolir</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['packages'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-box-seam fs-1 opacity-25 d-block mb-3"></i>
                            Belum ada data paket internet.<br>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['packages'] as $package): ?>
                        <tr>
                            <td class="ps-4 text-white fw-medium">
                                <?php echo $package->name; ?>
                                <?php if ($package->description): ?>
                                    <div class="text-secondary small mt-1" style="font-weight: normal;"><?php echo htmlspecialchars($package->description); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1" style="width: fit-content;">
                                        <i class="bi bi-arrow-down"></i> <?php echo $package->speed_download; ?> Mbps
                                    </span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1" style="width: fit-content;">
                                        <i class="bi bi-arrow-up"></i> <?php echo $package->speed_upload; ?> Mbps
                                    </span>
                                </div>
                            </td>
                            <td class="text-white fw-medium">Rp <?php echo number_format($package->price, 0, ',', '.'); ?></td>
                            <td><span class="font-monospace text-secondary bg-dark px-2 py-1 rounded border border-secondary border-opacity-25"><?php echo $package->mikrotik_profile; ?></span></td>
                            <td class="text-center">
                                <?php if ($package->auto_isolate): ?>
                                    <i class="bi bi-check-circle-fill text-success" title="Ya"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger" title="Tidak"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($package->is_active): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 border border-success border-opacity-25 rounded-pill">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 border border-secondary border-opacity-25 rounded-pill">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?php echo URLROOT; ?>/AdminPackageController/edit/<?php echo $package->id; ?>" class="btn btn-sm btn-outline-warning border-opacity-25 text-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?php echo URLROOT; ?>/AdminPackageController/delete/<?php echo $package->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus paket ini?');">
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
