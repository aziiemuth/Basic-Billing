<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title']); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f5f5f5; font-family: "Courier New", monospace; color: #000; }
        .receipt { width: 80mm; min-height: 100vh; margin: 0 auto; padding: 10px; background: #fff; font-size: 12px; line-height: 1.35; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: 700; }
        .line { border-top: 1px dashed #000; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .label { flex: 0 0 31mm; }
        .value { flex: 1; text-align: right; word-break: break-word; }
        .no-print { position: fixed; top: 12px; right: 12px; font-family: Arial, sans-serif; }
        .no-print button { padding: 8px 12px; cursor: pointer; }
        @media print {
            @page { size: 80mm auto; margin: 0; }
            body { background: #fff; }
            .receipt { width: 80mm; margin: 0; min-height: auto; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Cetak</button>
    </div>
    <div class="receipt">
        <div class="center bold"><?php echo htmlspecialchars($data['settings']->company_name ?? SITENAME); ?></div>
        <div class="center"><?php echo htmlspecialchars($data['settings']->company_address ?? ''); ?></div>
        <div class="center"><?php echo htmlspecialchars($data['settings']->company_whatsapp ?? ''); ?></div>
        <div class="line"></div>

        <div class="center bold">STRUK TAGIHAN INTERNET</div>
        <div class="line"></div>

        <div class="row"><div class="label">Invoice</div><div class="value"><?php echo htmlspecialchars($data['invoice']->invoice_number); ?></div></div>
        <div class="row"><div class="label">Tanggal</div><div class="value"><?php echo date('d/m/Y', strtotime($data['invoice']->issue_date)); ?></div></div>
        <div class="row"><div class="label">Jatuh Tempo</div><div class="value"><?php echo date('d/m/Y', strtotime($data['invoice']->due_date)); ?></div></div>
        <div class="row"><div class="label">Pelanggan</div><div class="value"><?php echo htmlspecialchars($data['invoice']->customer_name); ?></div></div>
        <div class="row"><div class="label">ID</div><div class="value"><?php echo htmlspecialchars($data['invoice']->customer_code); ?></div></div>
        <div class="row"><div class="label">Periode</div><div class="value"><?php echo date('F Y', strtotime($data['invoice']->billing_month . '-01')); ?></div></div>
        <div class="line"></div>

        <?php if (!empty($data['items'])): ?>
            <?php foreach ($data['items'] as $item): ?>
                <div class="bold"><?php echo htmlspecialchars($item->description); ?></div>
                <div class="row"><div><?php echo (int)$item->quantity; ?> x Rp <?php echo number_format($item->unit_price, 0, ',', '.'); ?></div><div>Rp <?php echo number_format($item->total_price, 0, ',', '.'); ?></div></div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bold">Paket <?php echo htmlspecialchars($data['invoice']->package_name ?? '-'); ?></div>
            <div class="row"><div>1 x Rp <?php echo number_format($data['invoice']->amount, 0, ',', '.'); ?></div><div>Rp <?php echo number_format($data['invoice']->amount, 0, ',', '.'); ?></div></div>
        <?php endif; ?>

        <div class="line"></div>
        <div class="row"><div>Subtotal</div><div>Rp <?php echo number_format($data['invoice']->amount, 0, ',', '.'); ?></div></div>
        <div class="row"><div>Diskon</div><div>Rp <?php echo number_format($data['invoice']->discount, 0, ',', '.'); ?></div></div>
        <div class="row bold"><div>Total</div><div>Rp <?php echo number_format($data['invoice']->total_amount, 0, ',', '.'); ?></div></div>
        <div class="row"><div>Status</div><div><?php echo strtoupper($data['invoice']->status); ?></div></div>
        <div class="line"></div>
        <div class="center"><?php echo htmlspecialchars($data['settings']->invoice_footer ?? 'Terima kasih.'); ?></div>
    </div>
</body>
</html>
