<?php
class CronController extends Controller {

    public function __construct() {
        // Keamanan: Hanya izinkan dari Command Line Interface (CLI) atau dengan token rahasia
        $isCli = (php_sapi_name() === 'cli');
        $secret = isset($_GET['key']) ? $_GET['key'] : '';
        
        // Anda dapat menambahkan CRON_SECRET=... di file .env untuk lapisan pengamanan
        $envSecret = defined('CRON_SECRET') ? CRON_SECRET : 'SuperSecretCronKey2026';
        
        if (!$isCli && $secret !== $envSecret) {
            http_response_code(403);
            die("Akses ditolak: Invalid Cron Key.");
        }
    }

    /**
     * Entry point untuk eksekusi semua task cronjob
     * Dapat dipanggil via:
     * 1. CLI: php public/index.php url=CronController/run
     * 2. URL: http://localhost/billing/CronController/run?key=SuperSecretCronKey2026
     */
    public function run() {
        header('Content-Type: text/plain');
        echo "============================================\n";
        echo " MULAI EKSEKUSI CRONJOB - " . date('Y-m-d H:i:s') . "\n";
        echo "============================================\n\n";
        
        $this->generateInvoices();
        $this->sendReminders();
        $this->isolateOverdue();
        $this->backupDatabase();
        
        echo "\n============================================\n";
        echo " SELESAI EKSEKUSI CRONJOB - " . date('Y-m-d H:i:s') . "\n";
        echo "============================================\n";
    }

    /**
     * 1. Generate Tagihan Otomatis
     */
    private function generateInvoices() {
        echo "[CRON] Task 1: Generate Tagihan Otomatis...\n";
        $customerModel = $this->model('CustomerModel');
        $invoiceModel = $this->model('InvoiceModel');
        $packageModel = $this->model('PackageModel');
        
        $billing_month = date('Y-m');
        $customers = $customerModel->getCustomersForBilling('all');
        
        $count = 0;
        foreach ($customers as $customer) {
            // Abaikan jika tidak aktif
            if ($customer->status !== 'active') continue;
            
            // Periksa jika tagihan bulan ini belum dibuat
            if (!$invoiceModel->checkInvoiceExists($customer->id, $billing_month)) {
                $package = $packageModel->getById($customer->package_id);
                if (!$package) continue;
                
                $amount = $customer->custom_price ? $customer->custom_price : $package->price;
                $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                $due_date = date('Y-m-') . str_pad($customer->due_date, 2, '0', STR_PAD_LEFT);
                $issue_date = date('Y-m-d');
                
                // Pastikan due date tidak berada di bulan lalu jika penagihan lambat
                if (strtotime($due_date) < strtotime($issue_date)) {
                    $due_date = date('Y-m-d', strtotime('+7 days', strtotime($issue_date)));
                }
                
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
                    
                    // Notifikasi WA
                    if (!empty($customer->whatsapp)) {
                        require_once APPROOT . '/app/libraries/WhatsappService.php';
                        WhatsappService::sendNewInvoice($customer->id, $customer->whatsapp, $customer->name, $invoice_number, $amount, $billing_month, $due_date);
                        
                        // Beri jeda 10 detik agar tidak SPAM/Banned oleh Fonnte
                        sleep(10);
                    }
                    $count++;
                }
            }
        }
        echo "       >> Berhasil membuat $count tagihan baru.\n";
    }

    /**
     * 2. Kirim Reminder WA (H-3 Jatuh Tempo)
     */
    private function sendReminders() {
        echo "[CRON] Task 2: Reminder WhatsApp (H-3)...\n";
        $db = new Database();
        
        $target_date = date('Y-m-d', strtotime('+3 days'));
        
        $query = "SELECT i.*, c.name, c.whatsapp 
                  FROM invoices i 
                  JOIN customers c ON i.customer_id = c.id 
                  WHERE i.status = 'unpaid' 
                    AND i.due_date = :target_date 
                    AND c.whatsapp IS NOT NULL 
                    AND c.whatsapp != ''";
                    
        $db->query($query);
        $db->bind(':target_date', $target_date);
        $invoices = $db->resultSet();
        
        $count = 0;
        foreach ($invoices as $inv) {
            require_once APPROOT . '/app/libraries/WhatsappService.php';
            WhatsappService::sendPaymentReminder($inv->customer_id, $inv->whatsapp, $inv->name, $inv->total_amount, $inv->due_date);
            $count++;
            
            // Beri jeda 10 detik agar tidak SPAM/Banned oleh Fonnte
            sleep(10);
        }
        echo "       >> Mengirim $count reminder jatuh tempo.\n";
    }

    /**
     * 3. Isolasi Otomatis Pelanggan Menunggak
     */
    private function isolateOverdue() {
        echo "[CRON] Task 3: Isolasi Pelanggan Menunggak...\n";
        
        // Cek pengaturan sistem: Apakah isolasi otomatis diizinkan?
        $settingsModel = $this->model('SettingsModel');
        $settings = $settingsModel->getSettings();
        
        // Jika pengaturan tidak ada atau auto_isolate = 0, lewati
        if (!$settings || (isset($settings->auto_isolate) && $settings->auto_isolate == 0)) {
            echo "       >> Isolasi otomatis sedang dinonaktifkan di Pengaturan Sistem. Dilewati.\n";
            return;
        }
        
        $customerModel = $this->model('CustomerModel');
        $overdue = $customerModel->getOverdueCustomers();
        
        // Kelompokkan berdasarkan router agar efisien
        $byRouter = [];
        foreach ($overdue as $c) {
            $rid = $c->pppoe_router_id ?? null;
            if (!$rid || empty($c->pppoe_username)) continue;
            $byRouter[$rid][] = $c;
        }
        
        $count = 0;
        foreach ($byRouter as $router_id => $customers) {
            $mikrotikService = new MikrotikService();
            if ($mikrotikService->connect($router_id)) {
                foreach ($customers as $c) {
                    if ($mikrotikService->disablePppoeSecret($c->pppoe_username)) {
                        $customerModel->updateStatus($c->id, 'isolated');
                        
                        // Update status pppoe DB
                        $db = new Database();
                        $db->query('UPDATE pppoe_secrets SET status = :status WHERE username = :username');
                        $db->bind(':status', 'disabled');
                        $db->bind(':username', $c->pppoe_username);
                        $db->execute();
                        
                        // Notifikasi WA isolir
                        if (!empty($c->whatsapp)) {
                            require_once APPROOT . '/app/libraries/WhatsappService.php';
                            WhatsappService::sendIsolated($c->id, $c->whatsapp, $c->name);
                        }
                        $count++;
                    }
                }
                $mikrotikService->disconnect();
            }
        }
        echo "       >> Berhasil mengisolasi $count pelanggan menunggak.\n";
    }

    /**
     * 4. Auto Backup Database
     */
    private function backupDatabase() {
        echo "[CRON] Task 4: Backup Database Otomatis...\n";
        
        $backupDir = APPROOT . '/../backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $dbHost = defined('DB_HOST')     ? DB_HOST     : 'localhost';
        $dbUser = defined('DB_USERNAME') ? DB_USERNAME : 'root';
        $dbPass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
        $dbName = defined('DB_DATABASE') ? DB_DATABASE : 'billing_db';
        
        $filename = $backupDir . '/billing_backup_' . date('Y-m-d_His') . '.sql';
        
        // Peringatan: Script dump ini membutuhkan mysqldump dalam environment PATH server.
        $passStr = empty($dbPass) ? '' : "-p\"{$dbPass}\"";
        $cmd = "mysqldump -h {$dbHost} -u {$dbUser} {$passStr} {$dbName} > \"{$filename}\"";
        
        exec($cmd, $output, $returnVar);
        
        if ($returnVar === 0) {
            echo "       >> Berhasil mencadangkan database: " . basename($filename) . "\n";
            
            // Hapus backup lama (lebih dari 30 hari)
            $files = glob($backupDir . '/*.sql');
            $deletedCount = 0;
            foreach ($files as $file) {
                if (is_file($file) && time() - filemtime($file) >= 30 * 24 * 60 * 60) {
                    unlink($file);
                    $deletedCount++;
                }
            }
            if ($deletedCount > 0) {
                echo "       >> Menghapus $deletedCount file backup kadaluarsa.\n";
            }
        } else {
            echo "       >> Gagal mencadangkan database. Pastikan perintah 'mysqldump' tersedia di server Anda.\n";
        }
    }
}
