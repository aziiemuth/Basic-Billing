<?php
class AdminProfileController extends Controller {

    private $settingsModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->settingsModel = $this->model('SettingsModel');
    }

    /**
     * Halaman utama profile admin + status koneksi MikroTik + Pengaturan Sistem
     */
    public function index() {
        // Menghindari test koneksi sinkron pada page load agar tidak timeout. Status akan dicek via AJAX (Test Ulang).
        $mtResult = [
            'success' => false,
            'message' => 'Status belum dicek. Silakan klik "Test Ulang".',
            'host' => defined('MIKROTIK_HOST') ? MIKROTIK_HOST : '',
            'port' => defined('MIKROTIK_PORT') ? MIKROTIK_PORT : 8728
        ];

        // Cek juga apakah ada router di database
        $routerModel = $this->model('MikrotikRouterModel');
        $dbRouters   = $routerModel->getAll();
        
        $settings = $this->settingsModel->getSettings();

        $data = [
            'title'      => 'Profil & Pengaturan Sistem',
            'mtResult'   => $mtResult,
            'dbRouters'  => $dbRouters,
            'settings'   => $settings,
            'adminName'  => $_SESSION['user_name']  ?? 'Administrator',
            'adminRole'  => $_SESSION['role']        ?? 'admin',
        ];

        $this->view('admin/profile/index', $data);
    }
    
    public function updateSettings() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $currentSettings = $this->settingsModel->getSettings();
            $logoName = $currentSettings->company_logo;

            // Handle logo file upload if present
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['company_logo']['tmp_name'];
                $fileName = $_FILES['company_logo']['name'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $fileMime = mime_content_type($fileTmpPath);
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (in_array($fileMime, $allowedMimes) && in_array($ext, $allowedExtensions)) {
                    $uploadDir = dirname(APPROOT) . '/public/uploads/logo/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Delete old logo file if exists and is not default
                    if (!empty($logoName) && file_exists($uploadDir . $logoName)) {
                        @unlink($uploadDir . $logoName);
                    }

                    $logoName = 'logo_' . time() . '.' . $ext;
                    move_uploaded_file($fileTmpPath, $uploadDir . $logoName);
                } else {
                    $_SESSION['flash_message'] = 'Tipe file logo tidak diizinkan. Hanya format JPG, JPEG, PNG, GIF, atau WEBP.';
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: ' . URLROOT . '/AdminProfileController');
                    exit;
                }
            }

            $data = [
                'company_name' => trim($_POST['company_name']),
                'company_logo' => $logoName,
                'company_address' => trim($_POST['company_address']),
                'company_whatsapp' => trim($_POST['company_whatsapp']),
                'company_email' => trim($_POST['company_email']),
                'invoice_footer' => trim($_POST['invoice_footer']),
                'timezone' => trim($_POST['timezone']),
                'currency_format' => trim($_POST['currency_format']),
                'auto_isolate' => isset($_POST['auto_isolate']) ? 1 : 0,
                'wa_reminder_days' => trim($_POST['wa_reminder_days']),
            ];
            
            if ($this->settingsModel->update($data)) {
                $_SESSION['flash_message'] = 'Pengaturan sistem berhasil diperbarui.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = 'Gagal memperbarui pengaturan sistem.';
                $_SESSION['flash_type'] = 'danger';
            }
            header('Location: ' . URLROOT . '/AdminProfileController');
            exit;
        }
    }


    /**
     * AJAX — Re-test koneksi MikroTik dari config
     */
    public function testMikrotik() {
        header('Content-Type: application/json');

        $mikrotikService = new MikrotikService();
        $result          = $mikrotikService->testConnection(null);

        // Tambahkan timestamp
        $result['checked_at'] = date('d M Y, H:i:s');

        echo json_encode($result);
        exit;
    }

    /**
     * AJAX — Real-time resource MikroTik (CPU, RAM, Uptime)
     */
    public function mikrotikResource() {
        header('Content-Type: application/json');
        
        // Disable session blocking to allow concurrent fast polling
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $mikrotikService = new MikrotikService();
        $result = $mikrotikService->getResource(null);
        echo json_encode($result);
        exit;
    }

    /**
     * AJAX — Test koneksi ke router tertentu dari DB
     */
    public function testRouter($id) {
        header('Content-Type: application/json');

        $mikrotikService = new MikrotikService();
        $result          = $mikrotikService->testConnection($id);
        $result['checked_at'] = date('d M Y, H:i:s');

        echo json_encode($result);
        exit;
    }

    /**
     * Mengimpor 5 Data Pelanggan & Tagihan Dummy untuk Pengujian
     */
    public function importDummyData() {
        $db = new Database();
        
        try {
            // Cek paket. Jika kosong, buat dummy package
            $db->query("SELECT id FROM packages LIMIT 1");
            $package = $db->single();
            if (!$package) {
                $db->query("INSERT INTO packages (name, speed_download, speed_upload, price, mikrotik_profile, description, is_active, auto_isolate) 
                            VALUES ('[Dummy] Paket Super 10 Mbps', '10M', '10M', 150000, 'default', 'Paket internet testing dummy', 1, 1)");
                $db->execute();
                $packageId = $db->lastInsertId();
            } else {
                $packageId = $package->id;
            }

            // Cek router
            $db->query("SELECT id FROM mikrotik_routers LIMIT 1");
            $router = $db->single();
            if (!$router) {
                // Buat dummy router jika kosong agar tidak error NOT NULL constraint
                $dummyHost = defined('MIKROTIK_HOST') ? MIKROTIK_HOST : '127.0.0.1';
                $dummyUser = defined('MIKROTIK_USERNAME') ? MIKROTIK_USERNAME : 'admin';
                $dummyPass = defined('MIKROTIK_PASSWORD') ? MIKROTIK_PASSWORD : '';
                $dummyPort = defined('MIKROTIK_PORT') ? MIKROTIK_PORT : 8728;
                
                $db->query("INSERT INTO mikrotik_routers (name, host_ip, api_username, api_password, api_port, pppoe_interface, description, is_active) 
                            VALUES ('[Dummy] Router Utama', :host, :user, :pass, :port, 'ether1', 'Router simulasi dummy', 1)");
                $db->bind(':host', $dummyHost);
                $db->bind(':user', $dummyUser);
                $db->bind(':pass', $dummyPass);
                $db->bind(':port', $dummyPort);
                $db->execute();
                $routerId = $db->lastInsertId();
            } else {
                $routerId = $router->id;
            }

            // List dummy data
            $dummies = [
                ['name' => '[Dummy] Budi Santoso', 'username' => 'dummy_budi', 'email' => 'budi.dummy@example.com', 'whatsapp' => '081234567891', 'address' => 'Jl. Mawar Indah No. 12, Jakarta', 'status' => 'active'],
                ['name' => '[Dummy] Siti Aminah', 'username' => 'dummy_siti', 'email' => 'siti.dummy@example.com', 'whatsapp' => '081234567892', 'address' => 'Jl. Melati Raya No. 45, Jakarta', 'status' => 'active'],
                ['name' => '[Dummy] Eko Prasetyo', 'username' => 'dummy_eko', 'email' => 'eko.dummy@example.com', 'whatsapp' => '081234567893', 'address' => 'Jl. Kenanga Baru No. 8, Jakarta', 'status' => 'active'],
                ['name' => '[Dummy] Diana Putri', 'username' => 'dummy_diana', 'email' => 'diana.dummy@example.com', 'whatsapp' => '081234567894', 'address' => 'Jl. Kamboja No. 19, Jakarta', 'status' => 'isolated'],
                ['name' => '[Dummy] Rian Hidayat', 'username' => 'dummy_rian', 'email' => 'rian.dummy@example.com', 'whatsapp' => '081234567895', 'address' => 'Jl. Dahlia Blok C No. 3, Jakarta', 'status' => 'isolated']
            ];

            $passwordHashed = password_hash('password123', PASSWORD_BCRYPT);
            $importCount = 0;

            foreach ($dummies as $index => $dum) {
                // Generate Customer ID
                $custCode = 'CUST-DUM-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                
                // Cek apakah sudah ada customer dengan username ini
                $db->query("SELECT id FROM customers WHERE username = :username");
                $db->bind(':username', $dum['username']);
                if ($db->single()) {
                    continue; // Skip if already exists
                }

                // Insert Customer
                $db->query("INSERT INTO customers (customer_id, name, whatsapp, email, username, password, address, latitude, longitude, package_id, custom_price, mikrotik_router_id, installation_date, due_date, status) 
                            VALUES (:customer_id, :name, :whatsapp, :email, :username, :password, :address, '-6.200000', '106.816666', :package_id, NULL, :router_id, '2026-05-10', '10', :status)");
                $db->bind(':customer_id', $custCode);
                $db->bind(':name', $dum['name']);
                $db->bind(':whatsapp', $dum['whatsapp']);
                $db->bind(':email', $dum['email']);
                $db->bind(':username', $dum['username']);
                $db->bind(':password', $passwordHashed);
                $db->bind(':address', $dum['address']);
                $db->bind(':package_id', $packageId);
                $db->bind(':router_id', $routerId);
                $db->bind(':status', $dum['status']);
                $db->execute();
                
                $customerId = $db->lastInsertId();
                $importCount++;

                // ---- INVOICE 1: PAID (Lunas Bulan April) ----
                $invNum1 = 'INV/DUMMY/202604/' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                $db->query("INSERT INTO invoices (invoice_number, customer_id, package_id, billing_month, amount, discount, total_amount, issue_date, due_date, status, paid_at) 
                            VALUES (:invoice_number, :customer_id, :package_id, '2026-04', 150000, 0, 150000, '2026-04-01', '2026-04-10', 'paid', '2026-04-08 10:00:00')");
                $db->bind(':invoice_number', $invNum1);
                $db->bind(':customer_id', $customerId);
                $db->bind(':package_id', $packageId);
                $db->execute();
                $invId1 = $db->lastInsertId();

                // Item Invoice 1
                $db->query("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price) 
                            VALUES (:invoice_id, 'Paket Internet - 10Mbps (April 2026)', 1, 150000, 150000)");
                $db->bind(':invoice_id', $invId1);
                $db->execute();

                // Payment 1
                $db->query("INSERT INTO payments (invoice_id, reference_id, amount, payment_gateway_id, payment_method, status, paid_at) 
                            VALUES (:invoice_id, :ref, 150000, NULL, 'manual', 'success', '2026-04-08 10:00:00')");
                $db->bind(':invoice_id', $invId1);
                $db->bind(':ref', 'REF-DUMMY-' . time() . '-' . $index);
                $db->execute();
                $paymentId = $db->lastInsertId();

                // Transaction 1
                $db->query("INSERT INTO transactions (invoice_id, payment_id, type, amount, description, transaction_date) 
                            VALUES (:invoice_id, :payment_id, 'income', 150000, :desc, '2026-04-08')");
                $db->bind(':invoice_id', $invId1);
                $db->bind(':payment_id', $paymentId);
                $db->bind(':desc', "Pembayaran tagihan [Dummy] (Invoice ID: {$invId1})");
                $db->execute();
                $transId = $db->lastInsertId();

                // Cash Flow 1
                $db->query("SELECT balance FROM cash_flows ORDER BY id DESC LIMIT 1");
                $lastBalanceRow = $db->single();
                $prevBalance = $lastBalanceRow ? $lastBalanceRow->balance : 0;
                $newBalance = $prevBalance + 150000;

                $db->query("INSERT INTO cash_flows (transaction_id, flow_type, amount, balance, description) 
                            VALUES (:transaction_id, 'in', 150000, :balance, :desc)");
                $db->bind(':transaction_id', $transId);
                $db->bind(':balance', $newBalance);
                $db->bind(':desc', "Pembayaran tagihan [Dummy] (Invoice ID: {$invId1})");
                $db->execute();

                // ---- INVOICE 2: UNPAID (Belum Lunas Bulan Mei) ----
                $invNum2 = 'INV/DUMMY/202605/' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                $db->query("INSERT INTO invoices (invoice_number, customer_id, package_id, billing_month, amount, discount, total_amount, issue_date, due_date, status) 
                            VALUES (:invoice_number, :customer_id, :package_id, '2026-05', 150000, 0, 150000, '2026-05-01', '2026-05-10', 'unpaid')");
                $db->bind(':invoice_number', $invNum2);
                $db->bind(':customer_id', $customerId);
                $db->bind(':package_id', $packageId);
                $db->execute();
                $invId2 = $db->lastInsertId();

                // Item Invoice 2
                $db->query("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price) 
                            VALUES (:invoice_id, 'Paket Internet - 10Mbps (Mei 2026)', 1, 150000, 150000)");
                $db->bind(':invoice_id', $invId2);
                $db->execute();
            }

            if ($importCount > 0) {
                $_SESSION['flash_message'] = "Berhasil mengimpor {$importCount} data pelanggan & tagihan dummy untuk pengujian.";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Data dummy sudah diimpor sebelumnya.";
                $_SESSION['flash_type'] = "warning";
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Gagal mengimpor data dummy: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }

        header('Location: ' . URLROOT . '/AdminProfileController');
        exit;
    }

    /**
     * Menghapus seluruh data pelanggan & tagihan dummy hasil pengujian
     */
    public function deleteDummyData() {
        $db = new Database();
        
        try {
            // Get all dummy customer IDs
            $db->query("SELECT id FROM customers WHERE name LIKE '[Dummy]%'");
            $customers = $db->resultSet();
            $custIds = array_map(function($c) { return $c->id; }, $customers);

            if (!empty($custIds)) {
                $idsPlaceholder = implode(',', $custIds);
                
                // Get invoices for those customers
                $db->query("SELECT id FROM invoices WHERE customer_id IN ({$idsPlaceholder})");
                $invoices = $db->resultSet();
                $invIds = array_map(function($i) { return $i->id; }, $invoices);

                if (!empty($invIds)) {
                    $invIdsPlaceholder = implode(',', $invIds);

                    // Delete cash flows associated with those invoices
                    $db->query("DELETE FROM cash_flows WHERE description LIKE '%[Dummy]%'");
                    $db->execute();

                    // Delete transactions
                    $db->query("DELETE FROM transactions WHERE invoice_id IN ({$invIdsPlaceholder})");
                    $db->execute();

                    // Delete payments
                    $db->query("DELETE FROM payments WHERE invoice_id IN ({$invIdsPlaceholder})");
                    $db->execute();

                    // Delete invoice items
                    $db->query("DELETE FROM invoice_items WHERE invoice_id IN ({$invIdsPlaceholder})");
                    $db->execute();

                    // Delete invoices
                    $db->query("DELETE FROM invoices WHERE customer_id IN ({$idsPlaceholder})");
                    $db->execute();
                }

                // Delete customers
                $db->query("DELETE FROM customers WHERE id IN ({$idsPlaceholder})");
                $db->execute();
            }

            // Delete dummy packages if any and not used
            $db->query("DELETE FROM packages WHERE name LIKE '[Dummy]%'");
            $db->execute();

            // Delete dummy routers if any and not used
            $db->query("DELETE FROM mikrotik_routers WHERE name LIKE '[Dummy]%'");
            $db->execute();

            $_SESSION['flash_message'] = "Seluruh data pelanggan & tagihan dummy pengujian berhasil dibersihkan dari database.";
            $_SESSION['flash_type'] = "success";
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Gagal membersihkan data dummy: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }

        header('Location: ' . URLROOT . '/AdminProfileController');
        exit;
    }
}
