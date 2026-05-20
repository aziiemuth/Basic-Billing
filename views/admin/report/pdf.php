<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - <?php echo $data['year'] . '-' . $data['month']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; margin: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 25px; }
        .company-details h2 { margin: 0 0 5px 0; font-size: 20px; font-weight: bold; color: #111; }
        .company-details p { margin: 0; color: #666; font-size: 11px; }
        .report-title { text-align: right; }
        .report-title h1 { margin: 0 0 5px 0; font-size: 18px; color: #0d6efd; font-weight: bold; }
        .report-title p { margin: 0; color: #555; }
        
        .summary-box { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .summary-card { border: 1px solid #ddd; border-radius: 6px; padding: 12px; text-align: center; background: #fafafa; }
        .summary-card h4 { margin: 0 0 5px 0; font-size: 11px; text-transform: uppercase; color: #666; letter-spacing: 0.5px; }
        .summary-card p { margin: 0; font-size: 16px; font-weight: bold; color: #111; }
        .summary-card p.income { color: #198754; }
        .summary-card p.debt { color: #dc3545; }

        .section-title { font-size: 13px; font-weight: bold; border-left: 3px solid #0d6efd; padding-left: 8px; margin-bottom: 12px; color: #111; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        th { background-color: #f1f3f5; font-weight: bold; font-size: 11px; text-transform: uppercase; color: #444; }
        tr:nth-child(even) { background-color: #fcfcfc; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; font-size: 11px; }
        
        .footer { text-align: center; margin-top: 50px; font-size: 10px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="company-details">
            <h2><?php echo htmlspecialchars($data['settings']->company_name ?? SITENAME); ?></h2>
            <p><?php echo htmlspecialchars($data['settings']->company_address ?? 'Penyedia Layanan Internet'); ?></p>
            <p>WhatsApp: <?php echo htmlspecialchars($data['settings']->company_whatsapp ?? '-'); ?></p>
        </div>
        <div class="report-title">
            <h1>LAPORAN KEUANGAN</h1>
            <p>Periode: <?php 
                $months = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                echo $months[$data['month']] . ' ' . $data['year']; 
            ?></p>
        </div>
    </div>

    <!-- Kotak Ringkasan Keuangan -->
    <div class="summary-box">
        <div class="summary-card">
            <h4>Total Pemasukan</h4>
            <p class="income">Rp <?php echo number_format($data['summary']['pemasukan'], 0, ',', '.'); ?></p>
        </div>
        <div class="summary-card">
            <h4>Total Tunggakan</h4>
            <p class="debt">Rp <?php echo number_format($data['summary']['tunggakan'], 0, ',', '.'); ?></p>
        </div>
        <div class="summary-card">
            <h4>Pelanggan Lunas</h4>
            <p><?php echo $data['summary']['pelanggan_lunas']; ?> Orang</p>
        </div>
        <div class="summary-card">
            <h4>Belum Bayar</h4>
            <p><?php echo $data['summary']['pelanggan_belum']; ?> Orang</p>
        </div>
    </div>

    <!-- Detail Arus Kas -->
    <div class="section-title">Riwayat Transaksi Masuk (Cashflow)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 18%;">No Invoice</th>
                <th style="width: 15%;">Periode Tagihan</th>
                <th>Nama Pelanggan</th>
                <th style="width: 15%;">Metode</th>
                <th style="width: 18%;" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['cashflow'])): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #888; padding: 20px 0;">Belum ada data transaksi kas masuk untuk periode ini.</td>
                </tr>
            <?php else: ?>
                <?php foreach($data['cashflow'] as $row): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($row->updated_at)); ?></td>
                        <td class="font-mono"><?php echo htmlspecialchars($row->invoice_number); ?></td>
                        <td><?php echo date('M Y', strtotime($row->billing_month . '-01')); ?></td>
                        <td><strong><?php echo htmlspecialchars($row->customer_name); ?></strong></td>
                        <td><?php echo $row->payment_method ? strtoupper(str_replace('_', ' ', $row->payment_method)) : 'MANUAL'; ?></td>
                        <td class="text-right"><strong>Rp <?php echo number_format($row->amount, 0, ',', '.'); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh Sistem Billing Internet <?php echo htmlspecialchars($data['settings']->company_name ?? SITENAME); ?> pada <?php echo date('d/m/Y H:i:s'); ?>.</p>
        <p>Simpan dokumen cetak ini sebagai arsip keuangan resmi.</p>
    </div>
</body>
</html>
