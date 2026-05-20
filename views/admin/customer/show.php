<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo URLROOT; ?>/AdminCustomerController" class="text-decoration-none text-secondary small mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Pelanggan</a>
        <h4 class="fw-bold text-white mb-0">Detail Pelanggan</h4>
    </div>
    <div>
        <a href="<?php echo URLROOT; ?>/AdminCustomerController/edit/<?php echo $data['customer']->id; ?>" class="btn btn-warning btn-sm px-3 fw-medium d-flex align-items-center gap-2">
            <i class="bi bi-pencil"></i> Edit Data
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Profil Singkat -->
    <div class="col-12 col-xl-4">
        <div class="card glass-card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <div class="mb-4 mx-auto bg-dark border border-secondary border-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; overflow: hidden;">
                    <?php if ($data['customer']->photo_profile): ?>
                        <img src="<?php echo URLROOT; ?>/uploads/customers/profile/<?php echo $data['customer']->photo_profile; ?>" alt="Profile" class="w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <i class="bi bi-person text-secondary" style="font-size: 4rem;"></i>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($data['customer']->name); ?></h5>
                <p class="text-primary fw-medium mb-3"><?php echo $data['customer']->customer_id; ?></p>
                
                <div class="d-flex justify-content-center mb-4">
                    <?php if ($data['customer']->status == 'active'): ?>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 border border-success border-opacity-25 rounded-pill"><i class="bi bi-check-circle me-1"></i> Aktif</span>
                    <?php elseif ($data['customer']->status == 'inactive'): ?>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 border border-secondary border-opacity-25 rounded-pill"><i class="bi bi-dash-circle me-1"></i> Nonaktif</span>
                    <?php elseif ($data['customer']->status == 'isolated'): ?>
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 border border-danger border-opacity-25 rounded-pill"><i class="bi bi-exclamation-triangle me-1"></i> Terisolir</span>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <a href="https://wa.me/<?php echo preg_replace('/^0/', '62', $data['customer']->whatsapp); ?>" target="_blank" class="btn btn-outline-success btn-sm border-opacity-25 px-3">
                        <i class="bi bi-whatsapp"></i> Chat
                    </a>
                    <?php if ($data['customer']->email): ?>
                    <a href="mailto:<?php echo $data['customer']->email; ?>" class="btn btn-outline-info btn-sm border-opacity-25 px-3">
                        <i class="bi bi-envelope"></i> Email
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top border-secondary border-opacity-25 p-4">
                <div class="mb-3">
                    <small class="text-secondary d-block mb-1">WhatsApp</small>
                    <span class="text-white"><?php echo htmlspecialchars($data['customer']->whatsapp); ?></span>
                </div>
                <div class="mb-3">
                    <small class="text-secondary d-block mb-1">Email</small>
                    <span class="text-white"><?php echo htmlspecialchars($data['customer']->email) ?: '-'; ?></span>
                </div>
                <div>
                    <small class="text-secondary d-block mb-1">Alamat Lengkap</small>
                    <span class="text-white"><?php echo nl2br(htmlspecialchars($data['customer']->address)); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Layanan & KTP -->
    <div class="col-12 col-xl-8">
        <div class="card glass-card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                <h6 class="fw-bold text-white mb-0"><i class="bi bi-router me-2 text-warning"></i> Detail Layanan Internet</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-sm-6">
                        <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                            <small class="text-secondary d-block mb-1">Paket Berlangganan</small>
                            <span class="text-white fw-medium d-block mb-1"><?php echo $data['package_name']; ?></span>
                            <?php if ($data['customer']->custom_price): ?>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 small">Harga Khusus: Rp <?php echo number_format($data['customer']->custom_price, 0, ',', '.'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                            <small class="text-secondary d-block mb-1">Router Server</small>
                            <span class="text-white fw-medium"><?php echo $data['router_name']; ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                            <small class="text-secondary d-block mb-1">PPPoE Username</small>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-white fw-medium font-monospace"><?php echo htmlspecialchars($data['pppoe'] ? $data['pppoe']->username : '-'); ?></span>
                                <?php if(isset($data['pppoe_status'])): ?>
                                    <?php if($data['pppoe_status']['status'] == 'online'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.65rem;" title="Uptime: <?php echo $data['pppoe_status']['uptime']; ?>"><i class="bi bi-circle-fill me-1" style="font-size: 0.4rem; vertical-align: middle;"></i>Online</span>
                                    <?php elseif($data['pppoe_status']['status'] == 'error'): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" style="font-size: 0.65rem;"><i class="bi bi-exclamation-triangle me-1"></i>Router Error</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25" style="font-size: 0.65rem;"><i class="bi bi-circle-fill me-1" style="font-size: 0.4rem; vertical-align: middle;"></i>Offline</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                            <small class="text-secondary d-block mb-1">PPPoE Password</small>
                            <span class="text-white fw-medium font-monospace"><?php echo htmlspecialchars($data['pppoe'] ? $data['pppoe']->password : '-'); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                            <small class="text-secondary d-block mb-1">Tanggal Instalasi</small>
                            <span class="text-white fw-medium"><?php echo date('d F Y', strtotime($data['customer']->installation_date)); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                            <small class="text-secondary d-block mb-1">Tanggal Jatuh Tempo</small>
                            <span class="text-warning fw-medium">Tanggal <?php echo $data['customer']->due_date; ?> Setiap Bulan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0 shadow-sm">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                <h6 class="fw-bold text-white mb-0"><i class="bi bi-card-heading me-2 text-primary"></i> Dokumen KTP</h6>
            </div>
            <div class="card-body p-4">
                <?php if ($data['customer']->photo_ktp): ?>
                    <img src="<?php echo URLROOT; ?>/uploads/customers/ktp/<?php echo $data['customer']->photo_ktp; ?>" alt="KTP" class="img-fluid rounded border border-secondary border-opacity-25" style="max-height: 400px; width: 100%; object-fit: contain; background: #000;">
                <?php else: ?>
                    <div class="text-center py-5 bg-dark bg-opacity-50 rounded border border-secondary border-opacity-25">
                        <i class="bi bi-image text-secondary opacity-50 d-block mb-3" style="font-size: 3rem;"></i>
                        <span class="text-secondary small">Dokumen KTP belum diunggah</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
