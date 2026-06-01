<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold text-white mb-1">
            <i class="bi bi-receipt-cutoff me-2 text-warning"></i>Tagihan Manual
        </h4>
        <p class="text-secondary small mb-0">
            Kelola tagihan pelanggan aktif &amp; terisolir — kirim WA, terima tunai, atau proses via payment gateway.
        </p>
    </div>
    <?php if (!defined('WA_ENABLED') || !WA_ENABLED): ?>
        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
            <i class="bi bi-exclamation-triangle me-1"></i>WA Gateway Belum Aktif &mdash; Kirim WA akan buka WhatsApp Web
        </span>
    <?php else: ?>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
            <i class="bi bi-wifi me-1"></i>WA Gateway Aktif (Fonnte API)
        </span>
    <?php endif; ?>
</div>

<!-- Search & Info Bar -->
<div class="card glass-card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row align-items-center g-3">
            <div class="col-md-5">
                <input type="text" id="searchInput"
                       class="form-control bg-dark text-white border-secondary border-opacity-25"
                       placeholder="Cari nama, ID pelanggan, atau nomor WA...">
            </div>
            <div class="col-md-7 text-md-end">
                <span class="text-secondary small">
                    <i class="bi bi-info-circle me-1"></i>
                    Menampilkan pelanggan <strong class="text-white">aktif &amp; terisolir</strong>.
                    Total pelanggan: <strong class="text-white"><?php echo count($data['invoices']); ?></strong>.
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Tagihan -->
<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="text-secondary small text-uppercase border-secondary border-opacity-25" style="letter-spacing:0.5px;">
                    <tr>
                        <th class="ps-4 py-3 border-0">Pelanggan</th>
                        <th class="py-3 border-0">WhatsApp</th>
                        <th class="py-3 border-0">No. Invoice</th>
                        <th class="py-3 border-0">Tagihan</th>
                        <th class="py-3 border-0">Jatuh Tempo</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="invoiceTableBody">
                    <?php if (empty($data['invoices'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-people fs-1 opacity-25 d-block mb-3 text-secondary"></i>
                            Belum ada data pelanggan terdaftar.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($data['invoices'] as $inv): ?>
                        <?php
                            $hasInvoice = !empty($inv->id);
                            $isIsolated = $inv->customer_status === 'isolated';
                            $hasWa      = !empty($inv->whatsapp);
                            
                            // Hitung nominal tagihan (dari invoice jika ada, fallback ke harga paket/custom)
                            $billAmount = $hasInvoice ? $inv->total_amount : ($inv->custom_price ? $inv->custom_price : $inv->package_price);
                            $amountFormatted = number_format($billAmount, 0, ',', '.');
                            
                            // Hitung jatuh tempo
                            if ($hasInvoice) {
                                $dueDateVal = $inv->due_date;
                                $isOverdue  = strtotime($dueDateVal) < strtotime('today');
                                $dueDateFormatted = date('d M Y', strtotime($dueDateVal));
                            } else {
                                $dueDay     = !empty($inv->customer_due_day) ? intval($inv->customer_due_day) : 20;
                                $dueDateVal = date('Y-m-') . str_pad($dueDay, 2, '0', STR_PAD_LEFT);
                                $isOverdue  = strtotime($dueDateVal) < strtotime('today');
                                $dueDateFormatted = date('d M Y', strtotime($dueDateVal)) . ' (Estimasi)';
                            }
                        ?>
                        <tr class="invoice-row" id="row-cust-<?php echo $inv->customer_id_db; ?>" data-invoice-id="<?php echo $inv->id ?? ''; ?>">
                            <!-- Pelanggan -->
                            <td class="ps-4">
                                <div class="fw-medium text-white inv-name"><?php echo htmlspecialchars($inv->customer_name); ?></div>
                                <div class="text-secondary small inv-code"><?php echo htmlspecialchars($inv->customer_code); ?></div>
                            </td>

                            <!-- WhatsApp -->
                            <td class="inv-wa small font-monospace">
                                <?php if ($hasWa): ?>
                                    <span class="text-info"><?php echo htmlspecialchars($inv->whatsapp); ?></span>
                                <?php else: ?>
                                    <span class="text-secondary">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- No. Invoice & Paket -->
                            <td class="col-invoice-number">
                                <?php if ($hasInvoice): ?>
                                    <div class="text-white small font-monospace"><?php echo htmlspecialchars($inv->invoice_number); ?></div>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 small" style="font-size: 0.7rem;">
                                        Belum Dibuat
                                    </span>
                                <?php endif; ?>
                                <div class="text-secondary small"><?php echo htmlspecialchars($inv->package_name ?? '-'); ?></div>
                            </td>

                            <!-- Tagihan -->
                            <td class="text-warning fw-semibold">Rp <?php echo $amountFormatted; ?></td>

                            <!-- Jatuh Tempo -->
                            <td class="col-due-date">
                                <div class="<?php echo $isOverdue ? 'text-danger fw-medium' : 'text-white'; ?>">
                                    <?php echo $dueDateFormatted; ?>
                                </div>
                                <?php if ($isOverdue): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 mt-1" style="font-size:0.65rem;">
                                        Jatuh Tempo
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Status Pelanggan -->
                            <td class="text-center">
                                <?php if ($isIsolated): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                        <i class="bi bi-shield-x me-1"></i>Terisolir
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                        <i class="bi bi-circle-fill me-1" style="font-size:7px;"></i>Aktif
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi -->
                            <td class="pe-4 col-actions">
                                <div class="d-flex justify-content-end flex-wrap gap-2">
                                    <?php if ($hasInvoice): ?>
                                        <!-- Jika sudah ada invoice -->
                                        <!-- 1. Kirim WA Tagihan -->
                                        <?php if ($hasWa): ?>
                                        <button class="btn btn-sm btn-outline-success border-opacity-25 d-flex align-items-center gap-1"
                                                id="btn-wa-<?php echo $inv->id; ?>"
                                                title="<?php echo (defined('WA_ENABLED') && WA_ENABLED) ? 'Kirim via Fonnte API' : 'Buka WhatsApp Web'; ?>"
                                                onclick="sendManualWA(
                                                    <?php echo $inv->id; ?>,
                                                    '<?php echo htmlspecialchars(addslashes($inv->whatsapp), ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars(addslashes($inv->customer_name), ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars($inv->customer_code); ?>',
                                                    '<?php echo htmlspecialchars(addslashes($inv->package_name ?? ''), ENT_QUOTES); ?>',
                                                    '<?php echo $amountFormatted; ?>',
                                                    '<?php echo htmlspecialchars($inv->invoice_number); ?>',
                                                    '<?php echo htmlspecialchars($inv->billing_month); ?>',
                                                    '<?php echo htmlspecialchars($inv->due_date); ?>'
                                                )">
                                            <i class="bi bi-whatsapp"></i>
                                            <span class="d-none d-xl-inline">Kirim WA</span>
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary border-opacity-25" disabled title="Nomor WA tidak tersedia">
                                            <i class="bi bi-whatsapp"></i>
                                        </button>
                                        <?php endif; ?>

                                        <!-- 2. Tandai Lunas (Tunai) -->
                                        <button class="btn btn-sm btn-outline-warning border-opacity-25 d-flex align-items-center gap-1"
                                                id="btn-cash-<?php echo $inv->id; ?>"
                                                title="Tandai Lunas — Pembayaran Tunai"
                                                onclick="confirmMarkPaid(
                                                    <?php echo $inv->id; ?>,
                                                    '<?php echo htmlspecialchars(addslashes($inv->customer_name), ENT_QUOTES); ?>',
                                                    'Rp <?php echo $amountFormatted; ?>'
                                                )">
                                            <i class="bi bi-cash-coin"></i>
                                            <span class="d-none d-xl-inline">Tunai</span>
                                        </button>


                                    <?php else: ?>
                                        <!-- Jika belum ada invoice -->
                                        <button class="btn btn-sm btn-warning d-flex align-items-center gap-1 px-3"
                                                onclick="generateInvoice(<?php echo $inv->customer_id_db; ?>, this)">
                                            <i class="bi bi-plus-circle"></i>
                                            <span>Buat Tagihan</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination Footer -->
        <div class="card-footer bg-transparent border-secondary border-opacity-25 d-flex justify-content-between align-items-center py-3 flex-wrap gap-2" id="pagination-footer" style="display: none;">
            <div class="text-secondary small" id="pagination-info">
                Menampilkan 0 - 0 dari 0 data
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0" id="pagination-controls">
                    <!-- populated via JS -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- ====== Modal: Konfirmasi Tandai Lunas Tunai ====== -->
<div class="modal fade" id="modalMarkPaid" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="background:#1a1a2e; border:1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title text-white fw-bold">
                    <i class="bi bi-cash-coin text-warning me-2"></i>Konfirmasi Pembayaran Tunai
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary mb-3">
                    Tandai invoice pelanggan berikut sebagai <strong class="text-success">LUNAS</strong>?
                </p>
                <div class="bg-dark bg-opacity-50 rounded p-3 border border-secondary border-opacity-25">
                    <div class="text-white fw-semibold" id="modal-customer-name">-</div>
                    <div class="text-warning mt-1" id="modal-invoice-amount">-</div>
                </div>
                <div class="alert alert-info bg-info bg-opacity-10 text-info border border-info border-opacity-25 p-3 rounded mt-3 mb-0 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Sistem akan otomatis: menandai invoice <strong>lunas</strong>, mengaktifkan
                    <strong>koneksi internet</strong> (PPPoE MikroTik), mengubah status pelanggan ke
                    <strong>Aktif</strong>, dan mengirim <strong>notifikasi WhatsApp</strong> pembayaran berhasil.
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning fw-medium" id="btn-confirm-paid">
                    <i class="bi bi-check-circle me-1"></i>Ya, Tandai Lunas (Tunai)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ====== Toast Notifikasi ====== -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
    <div id="liveToast" class="toast border-0 shadow-lg" role="alert" aria-live="assertive" data-bs-delay="5000">
        <div class="toast-header border-0" id="toast-header" style="background:rgba(30,30,50,0.95);">
            <i class="me-2" id="toast-icon"></i>
            <strong class="me-auto text-white" id="toast-title">Notifikasi</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body text-secondary small" id="toast-body" style="background:rgba(20,20,40,0.95);">-</div>
    </div>
</div>

<script>
const WA_ENABLED  = <?php echo (defined('WA_ENABLED') && WA_ENABLED) ? 'true' : 'false'; ?>;
const URLROOT     = '<?php echo URLROOT; ?>';
const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const SITE_NAME   = '<?php echo addslashes(defined('SITENAME') ? SITENAME : 'Billing App'); ?>';

// ---- Pagination & Search Filter ----
const itemsPerPage = 15;
let currentPage = 1;
let rows = Array.from(document.querySelectorAll('.invoice-row'));

const paginationFooter = document.getElementById('pagination-footer');
const paginationInfo = document.getElementById('pagination-info');
const paginationControls = document.getElementById('pagination-controls');
const searchInput = document.getElementById('searchInput');

function renderPagination() {
    if (rows.length === 0) {
        if (paginationFooter) paginationFooter.style.display = 'none';
        return;
    }
    
    // Filter rows based on search input
    const searchVal = searchInput ? searchInput.value.toLowerCase() : '';
    const filteredRows = rows.filter(row => {
        // Skip if row is removed
        if (!row.parentNode) return false;
        const name = row.querySelector('.inv-name')?.textContent.toLowerCase() ?? '';
        const code = row.querySelector('.inv-code')?.textContent.toLowerCase() ?? '';
        const wa   = row.querySelector('.inv-wa')?.textContent.toLowerCase()   ?? '';
        return name.includes(searchVal) || code.includes(searchVal) || wa.includes(searchVal);
    });
    
    const totalItems = filteredRows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
    
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }
    if (currentPage < 1) {
        currentPage = 1;
    }
    
    // Hide all rows
    rows.forEach(row => row.style.display = 'none');
    
    // Show rows for current page
    const start = (currentPage - 1) * itemsPerPage;
    const end = Math.min(start + itemsPerPage, totalItems);
    
    for (let i = start; i < end; i++) {
        filteredRows[i].style.display = '';
    }
    
    // Update pagination info
    if (totalItems === 0) {
        if (paginationInfo) paginationInfo.textContent = 'Menampilkan 0 data';
        if (paginationFooter) paginationFooter.style.display = 'none';
    } else {
        if (paginationInfo) paginationInfo.textContent = `Menampilkan ${start + 1} - ${end} dari ${totalItems} data`;
        if (paginationFooter) paginationFooter.style.display = 'flex';
    }
    
    // Render buttons
    if (paginationControls) {
        paginationControls.innerHTML = '';
        
        // Prev button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link bg-dark border-secondary border-opacity-25 text-white" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
        prevLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
            }
        });
        paginationControls.appendChild(prevLi);
        
        // Page numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const pageLi = document.createElement('li');
            pageLi.className = `page-item ${currentPage === i ? 'active' : ''}`;
            
            const activeLinkClass = currentPage === i ? 'bg-primary border-primary text-white' : 'bg-dark border-secondary border-opacity-25 text-white';
            
            pageLi.innerHTML = `<a class="page-link ${activeLinkClass}" href="#">${i}</a>`;
            pageLi.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = i;
                renderPagination();
            });
            paginationControls.appendChild(pageLi);
        }
        
        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link bg-dark border-secondary border-opacity-25 text-white" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
        nextLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
            }
        });
        paginationControls.appendChild(nextLi);
    }
}

if (searchInput) {
    searchInput.addEventListener('keyup', function () {
        currentPage = 1;
        renderPagination();
    });
}

// Initial render
document.addEventListener('DOMContentLoaded', function () {
    renderPagination();
});

// ---- Toast Helper ----
function showToast(type, title, message) {
    const toastEl = document.getElementById('liveToast');
    document.getElementById('toast-title').textContent = title;
    document.getElementById('toast-body').textContent  = message;

    const iconMap = {
        success: 'bi bi-check-circle-fill text-success',
        warning: 'bi bi-exclamation-triangle-fill text-warning',
        error:   'bi bi-x-circle-fill text-danger',
    };
    document.getElementById('toast-icon').className = iconMap[type] || iconMap.error;

    bootstrap.Toast.getOrCreateInstance(toastEl).show();
}

// ====================================================================
// Generate Tagihan Tunggal (On the fly)
// ====================================================================
function generateInvoice(customerId, btn) {
    const origHtml = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

    fetch(URLROOT + '/AdminInvoiceController/generateSingleInvoice/' + customerId, {
        method:  'POST',
        headers: { 'X-CSRF-Token': CSRF_TOKEN, 'Content-Type': 'application/json' },
    })
    .then(r => r.json())
    .then(function (data) {
        if (data.success) {
            showToast('success', 'Berhasil', data.message);
            
            // Perbarui baris tabel secara dinamis
            const row = document.getElementById('row-cust-' + customerId);
            row.setAttribute('data-invoice-id', data.invoice.id);
            
            // 1. Update Kolom No Invoice
            const colInv = row.querySelector('.col-invoice-number');
            colInv.innerHTML = `
                <div class="text-white small font-monospace">${data.invoice.invoice_number}</div>
                <div class="text-secondary small">${data.invoice.package_name}</div>
            `;
            
            // 2. Update Jatuh Tempo
            const colDue = row.querySelector('.col-due-date');
            const d = new Date(data.invoice.due_date);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const formattedDate = d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
            colDue.innerHTML = `<div class="text-white">${formattedDate}</div>`;
            
            // 3. Update Kolom Aksi
            const colActions = row.querySelector('.col-actions');
            const phone = row.querySelector('.inv-wa').innerText.trim();
            const name = row.querySelector('.inv-name').innerText.trim();
            const code = row.querySelector('.inv-code').innerText.trim();
            
            let btnWa = '';
            if (phone !== '-') {
                btnWa = `
                    <button class="btn btn-sm btn-outline-success border-opacity-25 d-flex align-items-center gap-1"
                            id="btn-wa-${data.invoice.id}"
                            title="${WA_ENABLED ? 'Kirim via Fonnte API' : 'Buka WhatsApp Web'}"
                            onclick="sendManualWA(
                                ${data.invoice.id},
                                '${phone.replace(/'/g, "\\'")}',
                                '${name.replace(/'/g, "\\'")}',
                                '${code}',
                                '${data.invoice.package_name.replace(/'/g, "\\'")}',
                                '${data.invoice.amount_formatted}',
                                '${data.invoice.invoice_number}',
                                '${data.invoice.billing_month}',
                                '${data.invoice.due_date}'
                            )">
                        <i class="bi bi-whatsapp"></i>
                        <span class="d-none d-xl-inline">Kirim WA</span>
                    </button>
                `;
            } else {
                btnWa = `
                    <button class="btn btn-sm btn-outline-secondary border-opacity-25" disabled title="Nomor WA tidak tersedia">
                        <i class="bi bi-whatsapp"></i>
                    </button>
                `;
            }
            
            colActions.innerHTML = `
                <div class="d-flex justify-content-end flex-wrap gap-2">
                    ${btnWa}
                    <button class="btn btn-sm btn-outline-warning border-opacity-25 d-flex align-items-center gap-1"
                            id="btn-cash-${data.invoice.id}"
                            title="Tandai Lunas — Pembayaran Tunai"
                            onclick="confirmMarkPaid(
                                ${data.invoice.id},
                                '${name.replace(/'/g, "\\'")}',
                                'Rp ${data.invoice.amount_formatted}'
                            )">
                        <i class="bi bi-cash-coin"></i>
                        <span class="d-none d-xl-inline">Tunai</span>
                    </button>

                </div>
            `;
        } else {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            showToast('error', 'Gagal', data.message);
        }
    })
    .catch(function (e) {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        showToast('error', 'Error', 'Terjadi kesalahan: ' + e.message);
    });
}

// ====================================================================
// Kirim WA Tagihan
// ====================================================================
function sendManualWA(invoiceId, phone, name, customerId, pkg, amount, invoiceNumber, billingMonth, dueDate) {
    const btn = document.getElementById('btn-wa-' + invoiceId);

    if (WA_ENABLED) {
        // --- Kirim via Fonnte API ---
        const origHtml = btn.innerHTML;
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        fetch(URLROOT + '/AdminInvoiceController/sendManualWA/' + invoiceId, {
            method:  'POST',
            headers: { 'X-CSRF-Token': CSRF_TOKEN, 'Content-Type': 'application/json' },
        })
        .then(r => r.json())
        .then(function (data) {
            btn.disabled  = false;
            btn.innerHTML = origHtml;
            showToast(data.success ? 'success' : 'error',
                      data.success ? 'WA Terkirim'     : 'Gagal Kirim WA',
                      data.message);
        })
        .catch(function (e) {
            btn.disabled  = false;
            btn.innerHTML = origHtml;
            showToast('error', 'Error', 'Terjadi kesalahan: ' + e.message);
        });

    } else {
        // --- Fallback: buka WhatsApp Web ---
        let p = phone.replace(/[^0-9]/g, '');
        if (p.startsWith('0')) p = '62' + p.substring(1);

        const tgl        = new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
        const bulan      = new Date(billingMonth + '-01').toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
        const jatuhTempo = new Date(dueDate).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

        const text = `=========================\n   * ${SITE_NAME} *\n=========================\n   *STRUK TAGIHAN BULANAN*\n-------------------------\nTanggal    : ${tgl}\nPelanggan  : *${name}*\nID         : ${customerId}\nBulan      : ${bulan}\nNo. Invoice: ${invoiceNumber}\n-------------------------\n*Rincian Layanan:*\n- Paket WiFi    : ${pkg}\n- Tarif Bulanan : Rp ${amount}\n-------------------------\n*TOTAL HARUS DIBAYAR:*\n*Rp ${amount}*\nJatuh Tempo : *${jatuhTempo}*\n=========================\n\nMohon lakukan pembayaran sebelum tanggal jatuh tempo agar koneksi internet Anda tetap aktif.\n\nTerima kasih\n_${SITE_NAME}_`;

        window.open('https://wa.me/' + p + '?text=' + encodeURIComponent(text), '_blank');
        showToast('warning', 'WA Gateway Belum Aktif',
                  'Pesan dibuka di WhatsApp Web. Isi WA_TOKEN and WA_ENABLED=true di .env untuk kirim otomatis.');
    }
}

// ====================================================================
// Tandai Lunas (Pembayaran Tunai)
// ====================================================================
let pendingPaidInvoiceId = null;

function confirmMarkPaid(invoiceId, customerName, amount) {
    pendingPaidInvoiceId = invoiceId;
    document.getElementById('modal-customer-name').textContent  = customerName;
    document.getElementById('modal-invoice-amount').textContent = amount;
    new bootstrap.Modal(document.getElementById('modalMarkPaid')).show();
}

document.getElementById('btn-confirm-paid').addEventListener('click', function () {
    if (!pendingPaidInvoiceId) return;

    const btn       = this;
    const invoiceId = pendingPaidInvoiceId;

    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Memproses...';

    fetch(URLROOT + '/AdminInvoiceController/markAsPaid/' + invoiceId, {
        method:  'POST',
        headers: { 'X-CSRF-Token': CSRF_TOKEN, 'Content-Type': 'application/json' },
    })
    .then(r => r.json())
    .then(function (data) {
        btn.disabled  = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Ya, Tandai Lunas (Tunai)';

        bootstrap.Modal.getInstance(document.getElementById('modalMarkPaid')).hide();
        pendingPaidInvoiceId = null;

        if (data.success) {
            // Cari row berdasarkan data-invoice-id
            const rowEl = document.querySelector(`.invoice-row[data-invoice-id="${invoiceId}"]`);
            if (rowEl) {
                rowEl.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                rowEl.style.opacity    = '0';
                rowEl.style.transform  = 'translateX(30px)';
                setTimeout(() => {
                    rowEl.remove();
                    renderPagination();
                }, 500);
            }

            const mt    = data.mikrotik;
            const mtMsg = mt?.connected
                ? (mt.enabled ? ' Internet pelanggan aktif kembali ✅' : ' Gagal enable PPPoE: ' + (mt.message ?? ''))
                : ' (Router tidak terhubung — aktifkan PPPoE manual)';

            showToast('success', 'Pembayaran Tunai Berhasil', 'Invoice ditandai lunas.' + mtMsg);
        } else {
            showToast('error', 'Gagal', data.message);
        }
    })
    .catch(function (e) {
        btn.disabled  = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Ya, Tandai Lunas (Tunai)';
        pendingPaidInvoiceId = null;
        showToast('error', 'Error', 'Terjadi kesalahan: ' + e.message);
    });
});
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>