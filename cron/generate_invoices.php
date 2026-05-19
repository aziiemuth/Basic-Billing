<?php
/**
 * CRON JOB: Generate Tagihan Otomatis Bulanan
 * ==============================================
 * Jalankan script ini dengan cron job pada tanggal 1 atau tanggal tertentu setiap bulan:
 *
 *   0 0 1 * * php /path/to/billing/cron/generate_invoices.php >> /path/to/billing/cron/logs/generate_invoices.log 2>&1
 */

// ---- Bootstrap ----
define('APPROOT', dirname(__DIR__));
require_once APPROOT . '/app/config/config.php';
require_once APPROOT . '/app/libraries/Database.php';
require_once APPROOT . '/app/libraries/WhatsappService.php';
require_once APPROOT . '/app/models/CustomerModel.php';
require_once APPROOT . '/app/models/InvoiceModel.php';
require_once APPROOT . '/app/models/PackageModel.php';

// Ensure log directory exists
$logDir = APPROOT . '/cron/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/generate_invoices_' . date('Y-m') . '.log';

function cronLog($message, $logFile) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

cronLog('===== Generate Invoices Cron Dimulai =====', $logFile);

$customerModel = new CustomerModel();
$invoiceModel = new InvoiceModel();
$packageModel = new PackageModel();

// Ambil bulan berjalan
$billing_month = date('Y-m');
cronLog("Bulan Tagihan: {$billing_month}", $logFile);

// Ambil semua pelanggan aktif
$db = new Database();
$db->query("SELECT * FROM customers WHERE status = 'active'");
$customers = $db->resultSet();

cronLog('Ditemukan ' . count($customers) . ' pelanggan aktif.', $logFile);

$success_count = 0;
$skipped_count = 0;
$failed_count = 0;

foreach ($customers as $customer) {
    // Check if invoice already exists for this customer and month
    if ($invoiceModel->checkInvoiceExists($customer->id, $billing_month)) {
        cronLog("  SKIP ['{$customer->name}'] - Tagihan periode {$billing_month} sudah ada.", $logFile);
        $skipped_count++;
        continue;
    }

    $package = $packageModel->getById($customer->package_id);
    if (!$package) {
        cronLog("  FAIL ['{$customer->name}'] - Paket ID {$customer->package_id} tidak ditemukan.", $logFile);
        $failed_count++;
        continue;
    }

    $amount = $customer->custom_price ? $customer->custom_price : $package->price;
    $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    
    // Hitung due date
    $year = date('Y');
    $month = date('m');
    $due_date = $year . '-' . $month . '-' . str_pad($customer->due_date, 2, '0', STR_PAD_LEFT);
    
    $issue_date = date('Y-m-d');

    $invoiceData = [
        'invoice_number' => $invoice_number,
        'customer_id' => $customer->id,
        'package_id' => $package->id,
        'billing_month' => $billing_month,
        'amount' => $amount,
        'discount' => 0,
        'total_amount' => $amount,
        'issue_date' => $issue_date,
        'due_date' => $due_date,
        'status' => 'unpaid'
    ];

    $invoice_id = $invoiceModel->createInvoice($invoiceData);

    if ($invoice_id) {
        $itemData = [
            'invoice_id' => $invoice_id,
            'description' => 'Tagihan Internet Paket ' . $package->name . ' - Periode ' . date('F Y', strtotime($billing_month . '-01')),
            'quantity' => 1,
            'unit_price' => $amount,
            'total_price' => $amount
        ];
        $invoiceModel->createInvoiceItem($itemData);
        
        // Kirim Notifikasi WA & Rekam Log
        if (!empty($customer->whatsapp)) {
            WhatsappService::sendNewInvoice($customer->id, $customer->whatsapp, $customer->name, $invoice_number, $amount, $billing_month, $due_date);
        }
        
        cronLog("  OK   ['{$customer->name}'] - Tagihan {$invoice_number} berhasil dibuat (Rp " . number_format($amount, 0, ',', '.') . "). Notifikasi dikirim.", $logFile);
        $success_count++;
    } else {
        cronLog("  FAIL ['{$customer->name}'] - Gagal menyimpan tagihan ke database.", $logFile);
        $failed_count++;
    }
}

cronLog('===== Ringkasan =====', $logFile);
cronLog("Berhasil dibuat : {$success_count}", $logFile);
cronLog("Dilewati (ada)  : {$skipped_count}", $logFile);
cronLog("Gagal           : {$failed_count}", $logFile);
cronLog('===== Cron Selesai =====', $logFile);
