<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?php echo URLROOT; ?>/AdminCustomerController" class="text-decoration-none text-secondary small mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Pelanggan</a>
        <h4 class="fw-bold text-white mb-0">Tambah Pelanggan Baru</h4>
    </div>
</div>

<form action="<?php echo URLROOT; ?>/AdminCustomerController/store" method="POST" enctype="multipart/form-data">
    <?php echo SecurityHelper::csrfField(); ?>
    <div class="row g-4">
        <!-- Informasi Personal -->
        <div class="col-12 col-xl-8">
            <div class="card glass-card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                    <h6 class="fw-bold text-white mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i> Informasi Personal</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Customer ID</label>
                            <input type="text" name="customer_id" class="form-control bg-dark border-secondary border-opacity-25 text-white" value="<?php echo $data['customer_id']; ?>" readonly required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control bg-dark border-secondary border-opacity-25 text-white" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">No. WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control bg-dark border-secondary border-opacity-25 text-white" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Alamat Email</label>
                            <input type="email" name="email" class="form-control bg-dark border-secondary border-opacity-25 text-white">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Username Portal</label>
                            <input type="text" name="username" class="form-control bg-dark border-secondary border-opacity-25 text-white">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary small fw-medium">Password Portal</label>
                            <div class="input-group">
                                <input type="password" name="password" id="portal_password" class="form-control bg-dark border-secondary border-opacity-25 text-white" required>
                                <button class="btn btn-outline-secondary border-secondary border-opacity-25" type="button" onclick="togglePasswordVisibility('portal_password', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text text-secondary opacity-75 small">Digunakan pelanggan untuk login ke portal.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary small fw-medium">Alamat Lengkap</label>
                            <textarea name="address" rows="3" class="form-control bg-dark border-secondary border-opacity-25 text-white"></textarea>
                        </div>
                        <div class="col-12 mb-0 d-flex justify-content-between align-items-end">
                            <label class="form-label text-secondary small fw-medium mb-0">Koordinat Lokasi</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="getCurrentLocation()">
                                <i class="bi bi-geo-alt"></i> Gunakan Lokasi Saat Ini
                            </button>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label text-secondary small fw-medium">Latitude</label>
                            <input type="text" name="latitude" id="latitude" class="form-control bg-dark border-secondary border-opacity-25 text-white">
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label text-secondary small fw-medium">Longitude</label>
                            <input type="text" name="longitude" id="longitude" class="form-control bg-dark border-secondary border-opacity-25 text-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Layanan & PPPoE -->
            <div class="card glass-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                    <h6 class="fw-bold text-white mb-0"><i class="bi bi-router me-2 text-warning"></i> Detail Layanan & Router</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Paket Internet</label>
                            <select name="package_id" class="form-select bg-dark border-secondary border-opacity-25 text-white" required>
                                <option value="" disabled selected>Pilih Paket</option>
                                <?php foreach($data['packages'] as $pkg): ?>
                                    <option value="<?php echo $pkg->id; ?>"><?php echo $pkg->name; ?> - Rp <?php echo number_format($pkg->price, 0, ',', '.'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Harga Khusus <span class="text-muted">(Opsional)</span></label>
                            <input type="number" name="custom_price" class="form-control bg-dark border-secondary border-opacity-25 text-white" placeholder="Kosongkan jika mengikuti harga paket">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Router</label>
                            <select name="mikrotik_router_id" class="form-select bg-dark border-secondary border-opacity-25 text-white" required>
                                <option value="" disabled selected>Pilih Router</option>
                                <?php foreach($data['routers'] as $router): ?>
                                    <option value="<?php echo $router->id; ?>"><?php echo $router->name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Tanggal Instalasi</label>
                            <input type="date" name="installation_date" class="form-control bg-dark border-secondary border-opacity-25 text-white" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Jatuh Tempo (Tanggal)</label>
                            <input type="number" min="1" max="28" name="due_date" class="form-control bg-dark border-secondary border-opacity-25 text-white" value="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">Status Awal</label>
                            <select name="status" class="form-select bg-dark border-secondary border-opacity-25 text-white" required>
                                <option value="active" selected>Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">PPPoE Username</label>
                            <input type="text" name="pppoe_username" class="form-control bg-dark border-secondary border-opacity-25 text-white" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-medium">PPPoE Password</label>
                            <input type="text" name="pppoe_password" class="form-control bg-dark border-secondary border-opacity-25 text-white" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Berkas -->
        <div class="col-12 col-xl-4">
            <div class="card glass-card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                    <h6 class="fw-bold text-white mb-0"><i class="bi bi-file-earmark-person me-2 text-info"></i> Berkas Pelanggan</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-medium">Foto Profil</label>
                        <input type="file" name="photo_profile" class="form-control bg-dark border-secondary border-opacity-25 text-white" accept="image/*">
                        <div class="form-text text-secondary opacity-75 small mt-2">Format: JPG, PNG. Maksimal 2MB.</div>
                    </div>
                    <div>
                        <label class="form-label text-secondary small fw-medium">Foto KTP</label>
                        <input type="file" name="photo_ktp" class="form-control bg-dark border-secondary border-opacity-25 text-white" accept="image/*">
                        <div class="form-text text-secondary opacity-75 small mt-2">Pastikan foto KTP terlihat jelas dan tidak blur.</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow">
                <i class="bi bi-save me-2"></i> Simpan Pelanggan
            </button>
        </div>
    </div>
</form>

<script>
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        const btn = document.querySelector('button[onclick="getCurrentLocation()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Mendapatkan...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Berhasil';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }, function(error) {
            alert('Error mendapatkan lokasi: ' + error.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    } else {
        alert('Geolocation tidak didukung oleh browser Anda.');
    }
}
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
