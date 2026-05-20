<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?> - <?php echo $data['invoice']->invoice_number; ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- PWA Settings -->
    <link rel="manifest" href="<?php echo URLROOT; ?>/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ISP Portal">
    <link rel="apple-touch-icon" href="<?php echo URLROOT; ?>/assets/icon-192.png">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .invoice-box {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        @media print {
            body {
                background-color: #ffffff;
            }
            .invoice-box {
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="invoice-box">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
            <div>
                <h2 class="fw-bold text-primary mb-0"><?php echo SITENAME; ?></h2>
                <p class="text-secondary mb-0">Layanan Internet Berkualitas</p>
            </div>
            <div class="text-end">
                <h3 class="fw-bold mb-1">INVOICE</h3>
                <p class="text-secondary mb-0">#<?php echo $data['invoice']->invoice_number; ?></p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-sm-6">
                <h6 class="text-secondary mb-2">Ditagihkan Kepada:</h6>
                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($data['customer']->name); ?></h5>
                <p class="mb-0 text-secondary"><?php echo htmlspecialchars($data['customer']->address); ?></p>
                <p class="mb-0 text-secondary">Telp: <?php echo htmlspecialchars($data['customer']->whatsapp); ?></p>
            </div>
            <div class="col-sm-6 text-sm-end mt-4 mt-sm-0">
                <h6 class="text-secondary mb-2">Detail Tagihan:</h6>
                <p class="mb-1"><strong>Periode:</strong> <?php echo date('F Y', strtotime($data['invoice']->billing_month . '-01')); ?></p>
                <p class="mb-1"><strong>Tanggal Terbit:</strong> <?php echo date('d M Y', strtotime($data['invoice']->issue_date)); ?></p>
                <p class="mb-0"><strong>Jatuh Tempo:</strong> <?php echo date('d M Y', strtotime($data['invoice']->due_date)); ?></p>
            </div>
        </div>

        <table class="table table-bordered mb-5">
            <thead class="table-light">
                <tr>
                    <th>Deskripsi Layanan</th>
                    <th class="text-end">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Paket Internet - <strong><?php echo htmlspecialchars($data['package']->name); ?></strong><br>
                        <small class="text-secondary">Kecepatan: <?php echo htmlspecialchars($data['package']->mikrotik_profile); ?></small>
                    </td>
                    <td class="text-end align-middle">Rp <?php echo number_format($data['invoice']->amount, 0, ',', '.'); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-end fw-bold">Total Tagihan</td>
                    <td class="text-end fw-bold fs-5 text-primary">Rp <?php echo number_format($data['invoice']->total_amount, 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td class="text-end fw-bold text-secondary">Status Pembayaran</td>
                    <td class="text-end fw-bold">
                        <?php if($data['invoice']->status == 'paid'): ?>
                            <span class="text-success">LUNAS</span>
                        <?php elseif($data['invoice']->status == 'unpaid'): ?>
                            <span class="text-danger">BELUM LUNAS</span>
                        <?php else: ?>
                            <span class="text-warning"><?php echo strtoupper($data['invoice']->status); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center no-print mt-5">
            <button onclick="window.print()" class="btn btn-primary px-4 me-2"><i class="bi bi-printer"></i> Cetak / Simpan PDF</button>
            <a href="<?php echo URLROOT; ?>/CustomerDashboardController" class="btn btn-outline-secondary px-4">Kembali</a>
        </div>
    </div>
</div>

</body>
</html>
