<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-0">Tagihan Manual (Direct WA)</h4>
        <p class="text-secondary mb-0">Kirim pesan tagihan/pengingat langsung ke WhatsApp pelanggan tanpa sistem API Fonnte.</p>
    </div>
</div>

<div class="card glass-card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control bg-dark text-white border-secondary border-opacity-25" placeholder="Cari nama pelanggan atau nomor WA...">
            </div>
            <div class="col-md-6 text-md-end text-secondary small">
                <i class="bi bi-info-circle me-1"></i> Klik tombol <strong>Kirim Tagihan WA</strong> untuk membuka WhatsApp Web / Aplikasi langsung beserta pesannya.
            </div>
        </div>
    </div>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="border-secondary border-opacity-25 text-secondary small text-uppercase" style="letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3 border-0">Pelanggan</th>
                        <th class="py-3 border-0">Nomor WhatsApp</th>
                        <th class="py-3 border-0">Paket Internet</th>
                        <th class="py-3 border-0">Total Tagihan</th>
                        <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="customerTableBody">
                    <?php if (empty($data['customers'])): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-secondary">
                            <i class="bi bi-people fs-1 opacity-25 d-block mb-3"></i>
                            Tidak ada pelanggan aktif ditemukan.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['customers'] as $customer): ?>
                        <?php 
                            if ($customer->status !== 'active') continue; // Pastikan hanya pelanggan aktif
                            $pkg = isset($data['packageMap'][$customer->package_id]) ? $data['packageMap'][$customer->package_id] : null;
                            $pkgName = $pkg ? $pkg->name : 'Tidak Ada Paket';
                            $amount = $customer->custom_price ? $customer->custom_price : ($pkg ? $pkg->price : 0);
                        ?>
                        <tr class="customer-row">
                            <td class="ps-4">
                                <div class="fw-medium text-white customer-name"><?php echo htmlspecialchars($customer->name); ?></div>
                                <div class="text-secondary small">ID: <?php echo htmlspecialchars($customer->customer_id); ?></div>
                            </td>
                            <td>
                                <?php if ($customer->whatsapp): ?>
                                    <span class="text-info customer-wa"><i class="bi bi-whatsapp me-1"></i><?php echo htmlspecialchars($customer->whatsapp); ?></span>
                                <?php else: ?>
                                    <span class="text-secondary small customer-wa">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-25 border border-secondary border-opacity-25 px-2 py-1"><?php echo htmlspecialchars($pkgName); ?></span>
                            </td>
                            <td class="text-warning fw-medium">
                                Rp <?php echo number_format($amount, 0, ',', '.'); ?>
                            </td>
                            <td class="pe-4 text-end">
                                <?php if ($customer->whatsapp): ?>
                                    <button class="btn btn-sm btn-success fw-medium d-inline-flex align-items-center gap-1 shadow-sm" onclick="sendDirectWA('<?php echo htmlspecialchars($customer->whatsapp); ?>', '<?php echo htmlspecialchars(addslashes($customer->name)); ?>', '<?php echo htmlspecialchars($customer->customer_id); ?>', '<?php echo htmlspecialchars(addslashes($pkgName)); ?>', '<?php echo number_format($amount, 0, ',', '.'); ?>')">
                                        <i class="bi bi-whatsapp"></i> Kirim Tagihan WA
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary fw-medium d-inline-flex align-items-center gap-1 shadow-sm" disabled>
                                        <i class="bi bi-whatsapp"></i> WA Kosong
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Search Filter
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('.customer-row');
    
    rows.forEach(function(row) {
        let name = row.querySelector('.customer-name').textContent.toLowerCase();
        let wa = row.querySelector('.customer-wa').textContent.toLowerCase();
        
        if (name.includes(filter) || wa.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Send Direct WA with Struk Template
function sendDirectWA(phone, name, customerId, pkg, price) {
    if (!phone || phone === '-') {
        alert('Nomor WhatsApp pelanggan tidak tersedia!');
        return;
    }
    
    // Format nomor (ganti 08 jadi 628, atau +62)
    phone = phone.replace(/[^0-9]/g, ''); // Hapus karakter non-angka
    if (phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    }

    var currentDate = new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
    var currentMonth = new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });

    var text = `=========================\n       * ZIENET WIFI *      \n=========================\n     *STRUK TAGIHAN BULANAN*\n-------------------------\nTanggal   : ${currentDate}\nPelanggan : *${name}*\nID        : ${customerId}\nBulan     : ${currentMonth}\n-------------------------\n*Rincian Layanan:*\n- Paket WiFi   : ${pkg}\n- Tarif Bulanan: Rp ${price}\n-------------------------\n*TOTAL HARUS DIBAYAR:*\n👉 *Rp ${price}*\n=========================\n\nMohon lakukan pembayaran tepat waktu agar koneksi internet Anda tetap aktif dan lancar. Jika sudah membayar, silakan abaikan struk tagihan ini.\n\nTerima kasih banyak atas kerjasamanya! 🙏`;
    
    var url = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
    window.open(url, '_blank');
}
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
