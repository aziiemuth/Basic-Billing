<?php require_once APPROOT . '/views/layouts/admin_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1">Histori Pembayaran</h4>
        <p class="text-secondary small mb-0">Pantau percobaan pembayaran, status invoice, dan cetak tagihan thermal.</p>
    </div>
</div>

<ul class="nav nav-tabs border-secondary border-opacity-25 mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#payments" type="button">Pembayaran Online</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#invoices" type="button">Invoice</button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="payments">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3 border-0">Waktu</th>
                                <th class="py-3 border-0">Invoice</th>
                                <th class="py-3 border-0">Pelanggan</th>
                                <th class="py-3 border-0">Nominal</th>
                                <th class="py-3 border-0">Gateway</th>
                                <th class="pe-4 py-3 border-0">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['payments'])): ?>
                                <tr><td colspan="6" class="text-center text-secondary py-5">Belum ada histori pembayaran online.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['payments'] as $payment): ?>
                                    <tr>
                                        <td class="ps-4 text-secondary"><?php echo date('d M Y H:i', strtotime($payment->created_at)); ?></td>
                                        <td class="font-monospace text-info"><?php echo htmlspecialchars($payment->invoice_number); ?></td>
                                        <td class="text-white"><?php echo htmlspecialchars($payment->customer_name); ?></td>
                                        <td class="text-warning">Rp <?php echo number_format($payment->amount, 0, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($payment->gateway_name ?: '-'); ?></td>
                                        <td class="pe-4"><span class="badge bg-secondary bg-opacity-25"><?php echo htmlspecialchars($payment->status); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="invoices">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3 border-0">Invoice</th>
                                <th class="py-3 border-0">Pelanggan</th>
                                <th class="py-3 border-0">Periode</th>
                                <th class="py-3 border-0">Total</th>
                                <th class="py-3 border-0">Status</th>
                                <th class="pe-4 py-3 border-0 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['invoices'])): ?>
                                <tr><td colspan="6" class="text-center text-secondary py-5">Belum ada invoice.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['invoices'] as $invoice): ?>
                                    <tr>
                                        <td class="ps-4 font-monospace text-info"><?php echo htmlspecialchars($invoice->invoice_number); ?></td>
                                        <td class="text-white"><?php echo htmlspecialchars($invoice->customer_name); ?></td>
                                        <td><?php echo date('F Y', strtotime($invoice->billing_month . '-01')); ?></td>
                                        <td class="text-warning">Rp <?php echo number_format($invoice->total_amount, 0, ',', '.'); ?></td>
                                        <td><span class="badge bg-secondary bg-opacity-25"><?php echo htmlspecialchars($invoice->status); ?></span></td>
                                        <td class="pe-4 text-end">
                                            <a href="<?php echo URLROOT; ?>/AdminInvoiceController/thermal/<?php echo $invoice->id; ?>" target="_blank" class="btn btn-sm btn-outline-info border-opacity-25" title="Cetak Thermal">
                                                <i class="bi bi-printer"></i>
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
</div>

<?php require_once APPROOT . '/views/layouts/admin_footer.php'; ?>
