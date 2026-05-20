<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title']); ?> - <?php echo htmlspecialchars($data['invoice']->invoice_number); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #eaeef2; font-family: "Courier New", Courier, monospace; color: #000; -webkit-print-color-adjust: exact; }
        .receipt { width: 80mm; min-height: 100vh; margin: 0 auto; padding: 15px 12px; background: #fff; font-size: 12px; line-height: 1.4; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .double-line { border-top: 3px double #000; margin: 8px 0; }
        .line { border-top: 1px dashed #000; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .label { flex: 0 0 32mm; }
        .value { flex: 1; text-align: right; word-break: break-word; }
        .logo-placeholder { font-size: 32px; margin-bottom: 4px; }
        .qr-container { margin: 15px 0; text-align: center; }
        .qr-container img { width: 120px; height: 120px; border: 1px solid #ddd; padding: 4px; border-radius: 4px; }
        .no-print { position: fixed; top: 15px; right: 15px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; display: flex; gap: 8px; }
        .no-print button { padding: 10px 16px; font-weight: 600; cursor: pointer; border-radius: 6px; border: none; font-size: 13px; transition: all 0.2s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-print { background-color: #0d6efd; color: white; }
        .btn-print:hover { background-color: #0b5ed7; }
        .btn-back { background-color: #6c757d; color: white; }
        .btn-back:hover { background-color: #5c636a; }
        
        @media print {
            @page { size: 80mm auto; margin: 0; }
            body { background: #fff; margin: 0; padding: 0; }
            .receipt { width: 80mm; margin: 0; padding: 10px; min-height: auto; box-shadow: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Struk</button>
        <button class="btn-back" onclick="window.history.back()">Kembali</button>
    </div>
    
    <div class="receipt">
        <!-- Logo Usaha / Icon -->
        <div class="center logo-placeholder">🌐</div>
        <div class="center bold" style="font-size: 15px; letter-spacing: 0.5px;"><?php echo htmlspecialchars($data['settings']->company_name ?? SITENAME); ?></div>
        <div class="center"><?php echo htmlspecialchars($data['settings']->company_address ?? 'Layanan Internet Berkualitas'); ?></div>
        <div class="center">WA: <?php echo htmlspecialchars($data['settings']->company_whatsapp ?? ''); ?></div>
        <div class="double-line"></div>

        <div class="center bold" style="font-size: 13px; letter-spacing: 1px;">STRUK PEMBAYARAN INTERNET</div>
        <div class="line"></div>

        <!-- Detail Struk -->
        <div class="row"><div class="label">No. Invoice</div><div class="value">#<?php echo htmlspecialchars($data['invoice']->invoice_number); ?></div></div>
        <div class="row"><div class="label">ID Pelanggan</div><div class="value"><?php echo htmlspecialchars($data['invoice']->customer_code); ?></div></div>
        <div class="row"><div class="label">Nama</div><div class="value"><?php echo htmlspecialchars($data['invoice']->customer_name); ?></div></div>
        <div class="row"><div class="label">Layanan</div><div class="value"><?php echo htmlspecialchars($data['invoice']->package_name ?? '-'); ?></div></div>
        <div class="row"><div class="label">Periode</div><div class="value"><?php echo date('F Y', strtotime($data['invoice']->billing_month . '-01')); ?></div></div>
        
        <?php if ($data['invoice']->status == 'paid' && !empty($data['invoice']->paid_at)): ?>
            <div class="row"><div class="label">Tanggal Bayar</div><div class="value"><?php echo date('d/m/Y H:i', strtotime($data['invoice']->paid_at)); ?></div></div>
        <?php else: ?>
            <div class="row"><div class="label">Jatuh Tempo</div><div class="value"><?php echo date('d/m/Y', strtotime($data['invoice']->due_date)); ?></div></div>
        <?php endif; ?>
        
        <div class="line"></div>

        <!-- Rincian Biaya -->
        <div class="bold">Rincian Layanan:</div>
        <?php if (!empty($data['items'])): ?>
            <?php foreach ($data['items'] as $item): ?>
                <div class="row">
                    <div>- <?php echo htmlspecialchars($item->description); ?></div>
                    <div>Rp <?php echo number_format($item->total_price, 0, ',', '.'); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="row">
                <div>- Internet Paket <?php echo htmlspecialchars($data['invoice']->package_name ?? '-'); ?></div>
                <div>Rp <?php echo number_format($data['invoice']->amount, 0, ',', '.'); ?></div>
            </div>
        <?php endif; ?>

        <div class="line"></div>
        <div class="row"><div>Subtotal</div><div>Rp <?php echo number_format($data['invoice']->amount, 0, ',', '.'); ?></div></div>
        <div class="row"><div>Diskon/Potongan</div><div>Rp <?php echo number_format($data['invoice']->discount, 0, ',', '.'); ?></div></div>
        <div class="row bold" style="font-size: 13px;"><div>TOTAL BAYAR</div><div>Rp <?php echo number_format($data['invoice']->total_amount, 0, ',', '.'); ?></div></div>
        <div class="line"></div>
        
        <!-- Status Pembayaran Badge -->
        <div class="center bold" style="font-size: 14px; padding: 4px; border: 1px solid #000; margin: 5px 0;">
            STATUS: <?php echo strtoupper($data['invoice']->status); ?>
        </div>

        <!-- Dynamic QR Code (QR Pembayaran / Verifikasi) -->
        <div class="qr-container">
            <?php 
                // QR content: Payment URL if Unpaid, else verification string
                $qrData = $data['invoice']->status == 'paid' 
                    ? 'VERIFIED-OK:' . $data['invoice']->invoice_number . ':' . $data['invoice']->total_amount 
                    : URLROOT . '/PaymentController/snap/' . $data['invoice']->id;
            ?>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?php echo urlencode($qrData); ?>" alt="QR Code">
            <div style="font-size: 9px; margin-top: 4px; color: #555;">
                <?php echo $data['invoice']->status == 'paid' ? 'Scan untuk verifikasi keaslian struk' : 'Scan QR untuk membayar online'; ?>
            </div>
        </div>

        <div class="line"></div>
        <!-- Footer Custom -->
        <div class="center bold" style="font-size: 11px;"><?php echo htmlspecialchars($data['settings']->invoice_footer ?? 'Terima kasih atas kepercayaan Anda.'); ?></div>
        <div class="center" style="font-size: 10px; margin-top: 4px; color: #444;">Simpan struk ini sebagai bukti pembayaran sah.</div>
    </div>
</body>
</html>
