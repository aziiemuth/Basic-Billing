<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-0">Generate Tagihan Massal</h4>
        <p class="text-secondary mb-0">Buat tagihan untuk banyak pelanggan sekaligus</p>
    </div>
</div>

<div class="row g-4">
    <!-- Form Panel -->
    <div class="col-12 col-xl-5">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-4">
                <form id="generateForm">
                    <div class="mb-4">
                        <label class="form-label text-secondary small">Pilih Bulan Tagihan <span class="text-danger">*</span></label>
                        <input type="month" class="form-control bg-dark text-white border-secondary border-opacity-25" id="billingMonth" name="billing_month" value="<?php echo date('Y-m'); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small">Filter Paket Internet</label>
                        <select class="form-select bg-dark text-white border-secondary border-opacity-25" id="packageFilter" name="package_id">
                            <option value="all">Semua Pelanggan Aktif</option>
                            <?php foreach ($data['packages'] as $package) : ?>
                                <option value="<?php echo $package->id; ?>"><?php echo $package->name; ?> (Rp <?php echo number_format($package->price, 0, ',', '.'); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-secondary opacity-75">Hanya pelanggan berstatus Aktif yang akan dibuatkan tagihan.</div>
                    </div>

                    <div class="alert alert-info bg-info bg-opacity-10 text-info border border-info border-opacity-25 p-3 rounded" role="alert">
                        <i class="bi bi-info-circle me-2"></i> Sistem secara otomatis akan mencegah duplikasi tagihan. Pelanggan yang sudah dibuatkan tagihannya untuk bulan tersebut akan dilewati.
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-medium" id="btnGenerate">
                        <i class="bi bi-lightning-charge me-2"></i> Mulai Generate Massal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Progress Panel -->
    <div class="col-12 col-xl-7">
        <div class="card glass-card border-0 shadow-sm h-100" id="progressPanel" style="display: none;">
            <div class="card-header bg-transparent border-secondary border-opacity-25 p-4">
                <h6 class="fw-bold text-white mb-0"><i class="bi bi-activity text-warning me-2"></i> Status Eksekusi</h6>
            </div>
            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                <h4 class="text-white mb-2" id="progressText">0 / 0</h4>
                <p class="text-secondary mb-4" id="progressStatus">Menyiapkan data pelanggan...</p>

                <div class="progress bg-dark border border-secondary border-opacity-25 mb-4" style="height: 20px; border-radius: 10px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>

                <div class="d-flex justify-content-center gap-4">
                    <div class="text-center">
                        <h3 class="text-success fw-bold mb-0" id="countSuccess">0</h3>
                        <small class="text-secondary">Berhasil</small>
                    </div>
                    <div class="text-center">
                        <h3 class="text-danger fw-bold mb-0" id="countFailed">0</h3>
                        <small class="text-secondary">Dilewati / Gagal</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card glass-card border-0 shadow-sm h-100" id="idlePanel">
            <div class="card-body p-4 d-flex align-items-center justify-content-center flex-column text-center">
                <i class="bi bi-receipt text-secondary opacity-50 mb-3" style="font-size: 4rem;"></i>
                <h5 class="text-white fw-bold mb-2">Siap Mengeksekusi</h5>
                <p class="text-secondary mb-0">Atur kriteria penagihan di sebelah kiri lalu klik tombol Generate untuk memulai proses otomatis.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('generateForm');
    const btnGenerate = document.getElementById('btnGenerate');
    const idlePanel = document.getElementById('idlePanel');
    const progressPanel = document.getElementById('progressPanel');
    
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressStatus = document.getElementById('progressStatus');
    const countSuccessEl = document.getElementById('countSuccess');
    const countFailedEl = document.getElementById('countFailed');

    let isProcessing = false;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (isProcessing) return;

        const billingMonth = document.getElementById('billingMonth').value;
        const packageId = document.getElementById('packageFilter').value;

        if (!billingMonth) {
            alert('Silakan pilih bulan tagihan');
            return;
        }

        if (!confirm('Anda yakin ingin men-generate tagihan massal untuk bulan ' + billingMonth + '?')) {
            return;
        }

        // Start UI Transition
        isProcessing = true;
        btnGenerate.disabled = true;
        btnGenerate.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
        
        idlePanel.style.display = 'none';
        progressPanel.style.display = 'flex';
        
        // Reset counters
        countSuccessEl.innerText = '0';
        countFailedEl.innerText = '0';
        progressBar.style.width = '0%';
        progressBar.innerText = '0%';
        progressText.innerText = 'Mengumpulkan data...';
        progressStatus.innerText = 'Mencari pelanggan yang memenuhi syarat...';

        try {
            // Step 1: Fetch target customers
            const reqTargets = await fetch('<?php echo URLROOT; ?>/AdminInvoiceController/apiGetTargetCustomers', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ billing_month: billingMonth, package_id: packageId })
            });
            const resTargets = await reqTargets.json();

            if (resTargets.status !== 'success') {
                throw new Error(resTargets.message || 'Gagal mengambil data pelanggan');
            }

            const targets = resTargets.targets;
            const total = targets.length;

            if (total === 0) {
                progressText.innerText = 'Selesai';
                progressStatus.innerText = 'Tidak ada pelanggan yang perlu ditagih (mungkin semua tagihan bulan ini sudah dibuat).';
                progressBar.style.width = '100%';
                progressBar.innerText = '100%';
                progressBar.classList.remove('progress-bar-animated');
                finishProcess();
                return;
            }

            progressStatus.innerText = 'Mulai memproses ' + total + ' pelanggan...';

            // Step 2: Batch Processing
            // Diubah menjadi 1 agar pengiriman notifikasi WA dilakukan satu per satu dan aman dari resiko banned SPAM
            const batchSize = 1; 
            let processed = 0;
            let success = 0;
            let failed = 0;

            for (let i = 0; i < total; i += batchSize) {
                const batchIds = targets.slice(i, i + batchSize);
                
                const reqBatch = await fetch('<?php echo URLROOT; ?>/AdminInvoiceController/apiGenerateBatch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ billing_month: billingMonth, customer_ids: batchIds })
                });
                
                const resBatch = await reqBatch.json();
                
                if (resBatch.status === 'success') {
                    success += resBatch.success;
                    failed += resBatch.failed;
                } else {
                    failed += batchIds.length;
                }

                processed += batchIds.length;

                // Update UI
                const percentage = Math.round((processed / total) * 100);
                progressBar.style.width = percentage + '%';
                progressBar.innerText = percentage + '%';
                progressText.innerText = processed + ' / ' + total;
                countSuccessEl.innerText = success;
                countFailedEl.innerText = failed;

                // Memberi jeda aman selama 10 detik (10000 ms) tiap kali invoice + WA terkirim
                // untuk menghindari sistem anti-spam WhatsApp
                if (processed < total) {
                    progressStatus.innerText = 'Menjaga jeda aman pengiriman WA (Anti-Banned 10 Detik)...';
                    await new Promise(resolve => setTimeout(resolve, 10000));
                    progressStatus.innerText = 'Memproses penagihan pelanggan berikutnya...';
                }
            }

            progressStatus.innerText = 'Pembuatan tagihan massal selesai!';
            progressBar.classList.remove('progress-bar-animated');
            finishProcess();

        } catch (error) {
            console.error(error);
            progressStatus.innerText = 'Terjadi kesalahan sistem: ' + error.message;
            progressStatus.classList.add('text-danger');
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-danger');
            finishProcess();
        }
    });

    function finishProcess() {
        isProcessing = false;
        btnGenerate.disabled = false;
        btnGenerate.innerHTML = '<i class="bi bi-lightning-charge me-2"></i> Mulai Generate Massal';
    }
});
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
