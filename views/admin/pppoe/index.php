<?php /** @var array $data */ ?>
<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-secondary small mb-0">Pusat kendali PPPoE. Pantau status real-time dan tarik data (import) pelanggan langsung dari router.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap justify-content-end">
        <a href="<?php echo URLROOT; ?>/AdminCustomerController/create" class="btn btn-primary btn-sm px-3 fw-medium d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Tambah PPPoE & Pelanggan</span><span class="d-inline d-sm-none">Tambah</span>
        </a>
    </div>
</div>

<!-- Filter Router -->
<div class="card glass-card border-0 shadow-sm mb-4 filter-card">
    <div class="card-body p-3">
        <form method="GET" action="<?php echo URLROOT; ?>/AdminPppoeController" class="row g-3 align-items-end">
            <div class="col-12 col-sm-6 col-md-3">
                <label class="text-secondary small mb-1">Pilih Router MikroTik</label>
                <select name="router_id" class="form-select form-select-sm bg-dark text-white border-secondary border-opacity-25" onchange="this.form.submit()">
                    <option value="">-- Pilih Router --</option>
                    <?php foreach($data['routers'] as $r): ?>
                        <option value="<?php echo $r->id; ?>" <?php echo ($data['router_id'] == $r->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r->name); ?> (<?php echo htmlspecialchars($r->host_ip); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="text-secondary small mb-1">Status PPPoE</label>
                <select id="pppoeStatusFilter" class="form-select form-select-sm bg-dark text-white border-secondary border-opacity-25">
                    <option value="">Semua Status</option>
                    <option value="online">Hanya Online</option>
                    <option value="offline">Hanya Offline</option>
                    <option value="disabled">Hanya Disabled</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <label class="text-secondary small mb-1">Cari PPPoE Username</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" id="pppoeSearchInput" class="form-control bg-dark text-white border-secondary border-opacity-25" placeholder="Ketik nama atau username...">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <button type="submit" class="btn btn-info btn-sm fw-medium w-100">
                    <i class="bi bi-arrow-repeat me-1"></i> Refresh
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($data['error']): ?>
<div class="alert border border-danger border-opacity-25 bg-danger bg-opacity-10 text-danger rounded-3">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Gagal terhubung ke router:</strong> <?php echo htmlspecialchars($data['error']); ?>
</div>
<?php endif; ?>

<?php if ($data['router_id'] && !$data['error']): ?>
<?php
    $totalSecrets = count($data['secrets']);
    $totalInDb = 0;
    $totalOnline = 0;
    $totalDisabled = 0;
    foreach ($data['secrets'] as $s) {
        if ($s['is_in_db']) $totalInDb++;
        if ($s['is_online']) $totalOnline++;
        if ($s['disabled']) $totalDisabled++;
    }
    $totalNotInDb = $totalSecrets - $totalInDb;
?>
<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-white"><?php echo $totalSecrets; ?></div>
                <div class="text-secondary small">Total Akun di MikroTik</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-warning"><?php echo $totalNotInDb; ?></div>
                <div class="text-secondary small">Belum Masuk Billing</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-success"><?php echo $totalOnline; ?></div>
                <div class="text-secondary small">Status Online</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-danger"><?php echo $totalDisabled; ?></div>
                <div class="text-secondary small">Akun Disabled</div>
            </div>
        </div>
    </div>
</div>

<form action="<?php echo URLROOT; ?>/AdminCustomerController/storeImportMikrotik" method="POST" id="importForm">
    <?php echo SecurityHelper::csrfField(); ?>
    <input type="hidden" name="router_id" value="<?php echo htmlspecialchars($data['router_id']); ?>">
    
    <?php if ($totalNotInDb > 0): ?>
    <div class="card glass-card border-0 shadow-sm mb-4 border border-info border-opacity-25">
        <div class="card-body p-3 bg-info bg-opacity-10 rounded">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h6 class="text-info fw-bold mb-1"><i class="bi bi-info-circle me-2"></i>Ada <?php echo $totalNotInDb; ?> akun yang belum masuk ke sistem Billing!</h6>
                    <p class="text-info text-opacity-75 small mb-0">Centang akun pada tabel di bawah, lalu klik tombol Import.</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="submit" id="btnImport" class="btn btn-info btn-sm fw-medium px-4">
                        <i class="bi bi-cloud-download me-1"></i> Import Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card glass-card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="border-secondary border-opacity-25 text-secondary small text-uppercase" style="letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3 border-0" style="width: 40px;">
                                <input class="form-check-input" type="checkbox" id="checkAll">
                            </th>
                            <th class="py-3 border-0">PPPoE Username</th>
                            <th class="py-3 border-0">Profile (Paket)</th>
                            <th class="py-3 border-0 text-center">Status DB</th>
                            <th class="py-3 border-0 text-center">Live Status</th>
                            <th class="py-3 border-0">IP / Uptime</th>
                            <th class="pe-4 py-3 border-0 text-end">Aksi Router</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['secrets'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-hdd-network fs-1 opacity-25 d-block mb-3"></i>
                                Tidak ada data PPPoE di MikroTik ini.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($data['secrets'] as $s): ?>
                            <tr>
                                <td class="ps-4">
                                    <?php if (!$s['is_in_db']): ?>
                                        <input class="form-check-input secret-checkbox" type="checkbox" name="secrets[]" value="<?php echo htmlspecialchars($s['username']); ?>">
                                    <?php else: ?>
                                        <i class="bi bi-check2 text-success opacity-50"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-white fw-bold"><?php echo htmlspecialchars($s['username']); ?></div>
                                    <div class="text-secondary small" style="font-size: 0.7rem;">Service: <?php echo htmlspecialchars($s['service']); ?></div>
                                </td>
                                <td>
                                    <span class="font-monospace text-info bg-info bg-opacity-10 px-2 py-1 rounded border border-info border-opacity-25 small"><?php echo htmlspecialchars($s['profile']); ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($s['is_in_db']): ?>
                                        <a href="<?php echo URLROOT; ?>/AdminCustomerController/edit/<?php echo $s['customer_id']; ?>" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-decoration-none d-inline-flex align-items-center"><i class="bi bi-person-check me-1"></i> Terhubung</a>
                                    <?php else: ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 d-inline-flex align-items-center"><i class="bi bi-person-x me-1"></i> Belum Import</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($s['is_online']): ?>
                                        <span class="badge bg-success text-white px-2 py-1 rounded-pill shadow-sm d-inline-flex align-items-center" style="font-size: 0.7rem;"><i class="bi bi-activity me-1"></i> ONLINE</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-white px-2 py-1 rounded-pill opacity-50 d-inline-flex align-items-center" style="font-size: 0.7rem;">OFFLINE</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($s['is_online']): ?>
                                        <div class="text-white small font-monospace mb-1"><?php echo htmlspecialchars($s['ip_address']); ?></div>
                                        <div class="text-secondary" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($s['uptime']); ?></div>
                                    <?php else: ?>
                                        <div class="text-secondary small opacity-50">-</div>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input pppoe-toggle" type="checkbox" role="switch" 
                                            data-username="<?php echo htmlspecialchars($s['username']); ?>"
                                            data-router="<?php echo $data['router_id']; ?>"
                                            <?php echo !$s['disabled'] ? 'checked' : ''; ?>
                                            title="Enable/Disable di MikroTik">
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
                    <ul class="pagination pagination-sm mb-0 gap-1" id="pagination-controls">
                        <!-- populated via JS -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check All functionality
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.secret-checkbox');
    
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = checkAll.checked;
            });
        });
    }

    // Form submission validation
    const importForm = document.getElementById('importForm');
    const btnImport = document.getElementById('btnImport');
    
    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            const count = document.querySelectorAll('.secret-checkbox:checked').length;
            if (count === 0) {
                e.preventDefault();
                alert('Pilih minimal satu akun PPPoE untuk di-import!');
                return;
            }
            
            // package_id is optional now
            
            if (!confirm(`Anda yakin ingin meng-import ${count} akun PPPoE ini ke database Billing?`)) {
                e.preventDefault();
            } else {
                if (btnImport) {
                    btnImport.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                    btnImport.disabled = true;
                }
            }
        });
    }

    // Toggle Enable/Disable PPPoE
    const toggleSwitches = document.querySelectorAll('.pppoe-toggle');
    toggleSwitches.forEach(sw => {
        sw.addEventListener('change', function() {
            const username = this.getAttribute('data-username');
            const router_id = this.getAttribute('data-router');
            const action = this.checked ? 'enable' : 'disable';
            const switchEl = this;
            
            // disable UI while processing
            switchEl.disabled = true;

            const formData = new FormData();
            formData.append('action', action);
            formData.append('username', username);
            formData.append('router_id', router_id);
            
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                formData.append('csrf_token', csrfMeta.getAttribute('content'));
            }

            fetch(APP_URLROOT + '/AdminPppoeController/togglePppoe', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Create minimal toast notification
                    const toastContainer = document.createElement('div');
                    toastContainer.style.position = 'fixed';
                    toastContainer.style.bottom = '20px';
                    toastContainer.style.right = '20px';
                    toastContainer.style.zIndex = '9999';
                    toastContainer.innerHTML = `
                        <div class="alert alert-success shadow-sm border-0 d-flex align-items-center gap-2 mb-0 py-2">
                            <i class="bi bi-check-circle-fill"></i> ${data.message}
                        </div>
                    `;
                    document.body.appendChild(toastContainer);
                    setTimeout(() => {
                        toastContainer.style.opacity = '0';
                        toastContainer.style.transition = 'opacity 0.5s';
                        setTimeout(() => toastContainer.remove(), 500);
                    }, 3000);
                } else {
                    alert('Gagal: ' + data.message);
                    switchEl.checked = !switchEl.checked; // revert
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi.');
                switchEl.checked = !switchEl.checked; // revert
            })
            .finally(() => {
                switchEl.disabled = false;
            });
        });
    });

    // Pagination & Search logic
    const itemsPerPage = 15;
    let currentPage = 1;
    
    const rows = Array.from(document.querySelectorAll('tbody tr')).filter(row => {
        return !(row.cells.length === 1 && row.cells[0].colSpan === 7);
    });
    
    const paginationFooter = document.getElementById('pagination-footer');
    const paginationInfo = document.getElementById('pagination-info');
    const paginationControls = document.getElementById('pagination-controls');
    const searchInput = document.getElementById('pppoeSearchInput');
    const statusFilter = document.getElementById('pppoeStatusFilter');
    
    function renderPagination() {
        if (rows.length === 0) {
            if (paginationFooter) paginationFooter.style.display = 'none';
            return;
        }
        
        // Filter rows based on search and status inputs
        const searchVal = searchInput ? searchInput.value.toLowerCase() : '';
        const statusVal = statusFilter ? statusFilter.value : '';
        
        const filteredRows = rows.filter(row => {
            // Check search filter
            const usernameCell = row.cells[1];
            let matchesSearch = false;
            if (usernameCell) {
                const usernameText = usernameCell.querySelector('.text-white.fw-bold').textContent || '';
                matchesSearch = usernameText.toLowerCase().includes(searchVal);
            }
            if (!matchesSearch) return false;

            // Check status filter
            if (statusVal) {
                if (statusVal === 'online' || statusVal === 'offline') {
                    const statusCell = row.cells[4];
                    if (statusCell) {
                        const statusText = statusCell.textContent.toLowerCase();
                        if (!statusText.includes(statusVal)) return false;
                    }
                } else if (statusVal === 'disabled') {
                    const actionCell = row.cells[6];
                    if (actionCell) {
                        const toggleInput = actionCell.querySelector('.pppoe-toggle');
                        if (toggleInput && toggleInput.checked) return false;
                    }
                }
            }

            return true;
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
            paginationInfo.textContent = 'Menampilkan 0 data';
            if (paginationFooter) paginationFooter.style.display = 'none';
        } else {
            paginationInfo.textContent = `Menampilkan ${start + 1} - ${end} dari ${totalItems} data`;
            if (paginationFooter) paginationFooter.style.display = 'flex';
        }
        
        // Render buttons
        paginationControls.innerHTML = '';
        
        // Prev button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link rounded bg-dark border-secondary border-opacity-25 text-white" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
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
            
            pageLi.innerHTML = `<a class="page-link rounded ${activeLinkClass}" href="#">${i}</a>`;
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
        nextLi.innerHTML = `<a class="page-link rounded bg-dark border-secondary border-opacity-25 text-white" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
        nextLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
            }
        });
        paginationControls.appendChild(nextLi);
    }

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            currentPage = 1;
            renderPagination();
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            currentPage = 1;
            renderPagination();
        });
    }

    // Initial render
    renderPagination();
});
</script>

<?php endif; ?>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
