<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Histori Tagihan & Aktivitas</h4>
        <p class="text-secondary small mb-0">Kelola riwayat invoice, pembayaran online, pemutusan isolir, serta pengiriman notifikasi WhatsApp secara terintegrasi.</p>
    </div>
</div>

<!-- PANEL FILTER UTAMA -->
<div class="card glass-card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="<?php echo URLROOT; ?>/AdminPaymentHistoryController/index" class="row g-3 align-items-end">
            <!-- Filter Per Bulan -->
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-semibold">Per Bulan (Billing Month)</label>
                <input type="month" name="billing_month" class="form-control bg-dark bg-opacity-50 text-white border-secondary border-opacity-25" 
                       value="<?php echo htmlspecialchars($data['filters']['billing_month']); ?>">
            </div>
            
            <!-- Filter Per Pelanggan -->
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-semibold">Per Pelanggan</label>
                <select name="customer_id" class="form-select bg-dark bg-opacity-50 text-white border-secondary border-opacity-25">
                    <option value="all">-- Semua Pelanggan --</option>
                    <?php foreach($data['customers'] as $c): ?>
                        <option value="<?php echo $c->id; ?>" <?php echo $data['filters']['customer_id'] == $c->id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c->customer_id . ' - ' . $c->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Filter Per Status -->
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-semibold">Per Status</label>
                <select name="status" class="form-select bg-dark bg-opacity-50 text-white border-secondary border-opacity-25">
                    <option value="all" <?php echo $data['filters']['status'] == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="paid" <?php echo $data['filters']['status'] == 'paid' ? 'selected' : ''; ?>>Paid / Lunas / Success</option>
                    <option value="unpaid" <?php echo $data['filters']['status'] == 'unpaid' ? 'selected' : ''; ?>>Unpaid / Pending</option>
                    <option value="failed" <?php echo $data['filters']['status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                    <option value="expired" <?php echo $data['filters']['status'] == 'expired' ? 'selected' : ''; ?>>Expired</option>
                </select>
            </div>
            
            <!-- Filter Per Metode Pembayaran -->
            <div class="col-md-2">
                <label class="form-label text-secondary small fw-semibold">Per Metode</label>
                <select name="payment_method" class="form-select bg-dark bg-opacity-50 text-white border-secondary border-opacity-25">
                    <option value="all" <?php echo $data['filters']['payment_method'] == 'all' ? 'selected' : ''; ?>>Semua Metode</option>
                    <option value="qris" <?php echo $data['filters']['payment_method'] == 'qris' ? 'selected' : ''; ?>>QRIS</option>
                    <option value="bank_transfer" <?php echo $data['filters']['payment_method'] == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    <option value="virtual_account" <?php echo $data['filters']['payment_method'] == 'virtual_account' ? 'selected' : ''; ?>>Virtual Account</option>
                    <option value="ewallet" <?php echo $data['filters']['payment_method'] == 'ewallet' ? 'selected' : ''; ?>>E-Wallet</option>
                    <option value="manual" <?php echo $data['filters']['payment_method'] == 'manual' ? 'selected' : ''; ?>>Manual (Tunai/Kasir)</option>
                </select>
            </div>

            <!-- Tombol Submit & Reset -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="<?php echo URLROOT; ?>/AdminPaymentHistoryController/index" class="btn btn-outline-secondary border-opacity-25" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- NAV TABS -->
<ul class="nav nav-tabs border-secondary border-opacity-25 mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#payments" type="button"><i class="bi bi-credit-card me-2"></i>Pembayaran Online</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#invoices" type="button"><i class="bi bi-file-earmark-text me-2"></i>Riwayat Invoice</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#customerLogs" type="button"><i class="bi bi-person-badge-fill me-2"></i>Riwayat Status & Isolir</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#whatsappLogs" type="button"><i class="bi bi-whatsapp me-2"></i>Riwayat Broadcast WA</button>
    </li>
</ul>

<!-- TAB CONTENT -->
<div class="tab-content">
    
    <!-- TAB 1: PEMBAYARAN ONLINE -->
    <div class="tab-pane fade show active" id="payments">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="text-secondary small text-uppercase border-secondary border-opacity-25">
                            <tr>
                                <th class="ps-4 py-3 border-0">Waktu Percobaan</th>
                                <th class="py-3 border-0">Invoice</th>
                                <th class="py-3 border-0">Pelanggan</th>
                                <th class="py-3 border-0">Nominal</th>
                                <th class="py-3 border-0">Metode</th>
                                <th class="py-3 border-0">Gateway</th>
                                <th class="pe-4 py-3 border-0 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['payments'])): ?>
                                <tr><td colspan="7" class="text-center text-secondary py-5"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>Belum ada histori pembayaran online.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['payments'] as $payment): ?>
                                    <tr>
                                        <td class="ps-4 text-secondary"><?php echo date('d M Y H:i', strtotime($payment->created_at)); ?></td>
                                        <td class="font-monospace text-info"><?php echo htmlspecialchars($payment->invoice_number); ?></td>
                                        <td class="text-white fw-semibold"><?php echo htmlspecialchars($payment->customer_name); ?> <span class="text-secondary small">(<?php echo htmlspecialchars($payment->customer_code); ?>)</span></td>
                                        <td class="text-warning fw-semibold">Rp <?php echo number_format($payment->amount, 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                                <?php echo $payment->payment_method ? strtoupper(str_replace('_', ' ', $payment->payment_method)) : 'MANUAL'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($payment->gateway_name ?: 'Kasir (Tunai)'); ?></td>
                                        <td class="pe-4 text-center">
                                            <?php if ($payment->status === 'success' || $payment->status === 'paid'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Success</span>
                                            <?php elseif ($payment->status === 'pending'): ?>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><?php echo ucfirst($payment->status); ?></span>
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
    </div>

    <!-- TAB 2: RIWAYAT INVOICE -->
    <div class="tab-pane fade" id="invoices">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="text-secondary small text-uppercase border-secondary border-opacity-25">
                            <tr>
                                <th class="ps-4 py-3 border-0">Invoice</th>
                                <th class="py-3 border-0">Pelanggan</th>
                                <th class="py-3 border-0">Periode</th>
                                <th class="py-3 border-0">Jatuh Tempo</th>
                                <th class="py-3 border-0">Total Tagihan</th>
                                <th class="py-3 border-0 text-center">Status</th>
                                <th class="pe-4 py-3 border-0 text-end">Cetak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['invoices'])): ?>
                                <tr><td colspan="7" class="text-center text-secondary py-5"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>Belum ada tagihan terbit.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['invoices'] as $invoice): ?>
                                    <tr>
                                        <td class="ps-4 font-monospace text-info"><?php echo htmlspecialchars($invoice->invoice_number); ?></td>
                                        <td class="text-white fw-semibold"><?php echo htmlspecialchars($invoice->customer_name); ?> <span class="text-secondary small">(<?php echo htmlspecialchars($invoice->customer_code); ?>)</span></td>
                                        <td><?php echo date('F Y', strtotime($invoice->billing_month . '-01')); ?></td>
                                        <td class="text-secondary"><?php echo date('d M Y', strtotime($invoice->due_date)); ?></td>
                                        <td class="text-warning fw-semibold">Rp <?php echo number_format($invoice->total_amount, 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <?php if ($invoice->status === 'paid'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Paid (Lunas)</span>
                                            <?php elseif ($invoice->status === 'unpaid'): ?>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Unpaid</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary bg-opacity-25"><?php echo strtoupper($invoice->status); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <a href="<?php echo URLROOT; ?>/AdminInvoiceController/thermal/<?php echo $invoice->id; ?>" target="_blank" class="btn btn-sm btn-outline-info border-opacity-25" title="Cetak Thermal 80mm">
                                                <i class="bi bi-printer me-1"></i> Struk
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: RIWAYAT STATUS & ISOLIR -->
    <div class="tab-pane fade" id="customerLogs">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="text-secondary small text-uppercase border-secondary border-opacity-25">
                            <tr>
                                <th class="ps-4 py-3 border-0">Waktu Aktivitas</th>
                                <th class="py-3 border-0">Pelanggan</th>
                                <th class="py-3 border-0">Tindakan</th>
                                <th class="pe-4 py-3 border-0">Keterangan Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['customerLogs'])): ?>
                                <tr><td colspan="4" class="text-center text-secondary py-5"><i class="bi bi-shield-exclamation fs-1 d-block mb-2 opacity-50"></i>Belum ada log perubahan status pelanggan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['customerLogs'] as $log): ?>
                                    <tr>
                                        <td class="ps-4 text-secondary"><?php echo date('d M Y H:i', strtotime($log->created_at)); ?></td>
                                        <td class="text-white fw-semibold"><?php echo htmlspecialchars($log->customer_name); ?> <span class="text-secondary small">(<?php echo htmlspecialchars($log->customer_code); ?>)</span></td>
                                        <td>
                                            <?php 
                                            $action = strtolower($log->action);
                                            if (strpos($action, 'isolir') !== false || strpos($action, 'isolate') !== false): ?>
                                                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25"><i class="bi bi-lock me-1"></i>ISOLIR (OFF)</span>
                                            <?php elseif (strpos($action, 'aktif') !== false || strpos($action, 'activate') !== false || strpos($action, 'enable') !== false): ?>
                                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25"><i class="bi bi-unlock me-1"></i>AKTIF (ON)</span>
                                            <?php else: ?>
                                                <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25"><?php echo strtoupper($log->action); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-secondary small"><?php echo htmlspecialchars($log->description); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: RIWAYAT BROADCAST WA -->
    <div class="tab-pane fade" id="whatsappLogs">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="text-secondary small text-uppercase border-secondary border-opacity-25">
                            <tr>
                                <th class="ps-4 py-3 border-0">Waktu Kirim</th>
                                <th class="py-3 border-0">Pelanggan</th>
                                <th class="py-3 border-0">No WA</th>
                                <th class="py-3 border-0">Tipe Pesan</th>
                                <th class="py-3 border-0">Isi Notifikasi</th>
                                <th class="pe-4 py-3 border-0 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['whatsappLogs'])): ?>
                                <tr><td colspan="6" class="text-center text-secondary py-5"><i class="bi bi-chat-left-dots fs-1 d-block mb-2 opacity-50"></i>Belum ada histori log pengiriman notifikasi WhatsApp.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['whatsappLogs'] as $wlog): ?>
                                    <tr>
                                        <td class="ps-4 text-secondary small"><?php echo date('d M Y H:i:s', strtotime($wlog->created_at)); ?></td>
                                        <td class="text-white fw-semibold"><?php echo htmlspecialchars($wlog->customer_name); ?></td>
                                        <td class="text-info"><?php echo htmlspecialchars($wlog->phone_number); ?></td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-25 text-white">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $wlog->message_type)); ?>
                                            </span>
                                        </td>
                                        <td class="text-secondary small text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($wlog->message); ?>">
                                            <?php echo htmlspecialchars($wlog->message); ?>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <?php if ($wlog->status === 'sent'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-check2-all me-1"></i>Terkirim</span>
                                            <?php elseif ($wlog->status === 'pending'): ?>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Pending (Queue)</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="bi bi-x-circle me-1"></i>Gagal</span>
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
    </div>

</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
