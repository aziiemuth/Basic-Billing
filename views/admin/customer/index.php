<?php /** @var array $data */ ?>
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
    <div class="card-header bg-transparent border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="fw-bold text-white mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Daftar Pelanggan</h6>
        <div style="width: 250px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary"><i class="bi bi-search"></i></span>
                <input type="text" id="customerSearchInput" class="form-control bg-dark text-white border-secondary border-opacity-25" placeholder="Cari nama pelanggan...">
            </div>
        </div>
    </div>
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
                            <td class="customer-status-col" data-router-id="<?php echo $customer->mikrotik_router_id; ?>">
                                <?php if ($customer->status == 'active'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 border border-success border-opacity-25 rounded-pill customer-status-badge">Aktif</span>
                                <?php elseif ($customer->status == 'inactive'): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 border border-secondary border-opacity-25 rounded-pill customer-status-badge">Nonaktif</span>
                                <?php elseif ($customer->status == 'isolated'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill customer-status-badge">Terisolir</span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsPerPage = 15;
    let currentPage = 1;
    
    const rows = Array.from(document.querySelectorAll('tbody tr')).filter(row => {
        return !(row.cells.length === 1 && row.cells[0].colSpan === 5);
    });
    
    const paginationFooter = document.getElementById('pagination-footer');
    const paginationInfo = document.getElementById('pagination-info');
    const paginationControls = document.getElementById('pagination-controls');
    const searchInput = document.getElementById('customerSearchInput');
    
    function renderPagination() {
        if (rows.length === 0) {
            if (paginationFooter) paginationFooter.style.display = 'none';
            return;
        }
        
        // Filter rows based on search input
        const searchVal = searchInput ? searchInput.value.toLowerCase() : '';
        const filteredRows = rows.filter(row => {
            const nameCell = row.cells[1];
            if (nameCell) {
                const nameEl = nameCell.querySelector('.text-white.fw-medium.mb-1');
                const usernameEl = row.cells[2].querySelector('.text-secondary.small');
                
                const nameText = nameEl ? nameEl.textContent : '';
                const usernameText = usernameEl ? usernameEl.textContent : '';
                
                return nameText.toLowerCase().includes(searchVal) || usernameText.toLowerCase().includes(searchVal);
            }
            return false;
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

    // Initial render
    renderPagination();

    // Check router status for all customers
    const statusCols = document.querySelectorAll('.customer-status-col[data-router-id]');
    const uniqueRouterIds = new Set();
    statusCols.forEach(col => {
        const rId = col.getAttribute('data-router-id');
        if (rId && rId.trim() !== '') {
            uniqueRouterIds.add(rId);
        }
    });

    uniqueRouterIds.forEach(routerId => {
        fetch(APP_URLROOT + '/AdminRouterController/testConnection/' + routerId)
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    // Router is dead (offline)
                    const colsToUpdate = document.querySelectorAll(`.customer-status-col[data-router-id="${routerId}"]`);
                    colsToUpdate.forEach(col => {
                        if (!col.querySelector('.router-mati-badge')) {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill ms-1 mt-1 d-inline-block router-mati-badge';
                            badge.textContent = 'Router Mati';
                            badge.title = 'Koneksi ke router ini terputus';
                            col.appendChild(badge);
                        }
                    });
                }
            })
            .catch(() => {
                const colsToUpdate = document.querySelectorAll(`.customer-status-col[data-router-id="${routerId}"]`);
                colsToUpdate.forEach(col => {
                    if (!col.querySelector('.router-mati-badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-danger bg-opacity-10 text-danger px-2 py-1 border border-danger border-opacity-25 rounded-pill ms-1 mt-1 d-inline-block router-mati-badge';
                        badge.textContent = 'Router Mati';
                        badge.title = 'Koneksi ke router ini terputus';
                        col.appendChild(badge);
                    }
                });
            });
    });
});
</script>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
