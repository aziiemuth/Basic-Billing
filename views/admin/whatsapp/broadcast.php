<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Broadcast WhatsApp</h4>
        <p class="text-secondary small mb-0">Kirim pesan custom ke pelanggan berdasarkan status.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-5">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="<?php echo URLROOT; ?>/AdminWhatsappController/sendBroadcast" method="POST" onsubmit="return confirm('Kirim broadcast ke target yang dipilih?');">
                    <?php echo SecurityHelper::csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Target</label>
                        <select name="target" class="form-select bg-dark text-white border-secondary border-opacity-25">
                            <option value="active">Pelanggan Aktif</option>
                            <option value="isolated">Pelanggan Terisolir</option>
                            <option value="inactive">Pelanggan Nonaktif</option>
                            <option value="all">Semua Pelanggan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Isi Pesan</label>
                        <textarea name="message" rows="9" class="form-control bg-dark text-white border-secondary border-opacity-25" required>Halo {nama},

Kami informasikan bahwa ada pemberitahuan layanan internet untuk ID pelanggan {id_pelanggan}.

Terima kasih.</textarea>
                        <div class="form-text text-secondary">Placeholder: {nama}, {id_pelanggan}</div>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-medium"><i class="bi bi-whatsapp me-1"></i> Kirim Broadcast</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                <h6 class="mb-0 text-white fw-bold">Ringkasan Target</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3 border-0">Pelanggan</th>
                                <th class="py-3 border-0">WhatsApp</th>
                                <th class="pe-4 py-3 border-0">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['customers'] as $customer): ?>
                                <tr>
                                    <td class="ps-4 text-white"><?php echo htmlspecialchars($customer->name); ?></td>
                                    <td class="text-info"><?php echo htmlspecialchars($customer->whatsapp ?: '-'); ?></td>
                                    <td class="pe-4"><span class="badge bg-secondary bg-opacity-25"><?php echo htmlspecialchars($customer->status); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
