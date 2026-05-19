<?php
class AdminInvoiceController extends Controller {
    private $invoiceModel;
    private $customerModel;
    private $packageModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->invoiceModel = $this->model('InvoiceModel');
        $this->customerModel = $this->model('CustomerModel');
        $this->packageModel = $this->model('PackageModel');
    }

    public function generate() {
        $packages = $this->packageModel->getAll();
        $routers = $this->model('MikrotikRouterModel')->getAll();
        $data = [
            'title' => 'Generate Tagihan Massal',
            'packages' => $packages,
            'routers' => $routers
        ];
        $this->view('admin/invoice/generate', $data);
    }

    public function manual() {
        // Hanya ambil pelanggan yang aktif untuk mempermudah list
        $customers = $this->customerModel->getCustomersForBilling('all');
        $packages = $this->packageModel->getAll();
        
        // Buat map paket untuk mempermudah di view
        $packageMap = [];
        foreach($packages as $pkg) {
            $packageMap[$pkg->id] = $pkg;
        }
        
        $data = [
            'title' => 'Tagihan Manual (Direct WA)',
            'customers' => $customers,
            'packageMap' => $packageMap
        ];
        $this->view('admin/invoice/manual', $data);
    }

    public function thermal($invoiceId) {
        $invoice = $this->invoiceModel->getByIdWithDetails($invoiceId);
        if (!$invoice) {
            header('Location: ' . URLROOT . '/AdminPaymentHistoryController');
            exit;
        }

        $data = [
            'title' => 'Cetak Tagihan Thermal',
            'invoice' => $invoice,
            'items' => $this->invoiceModel->getItems($invoiceId),
            'settings' => $this->model('SettingsModel')->getSettings()
        ];

        $this->view('admin/invoice/thermal', $data);
    }

    public function apiGetTargetCustomers() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
            $input = json_decode(file_get_contents('php://input'), true);
            $billing_month = isset($input['billing_month']) ? $input['billing_month'] : '';
            $package_id = isset($input['package_id']) ? $input['package_id'] : 'all';
            $router_id = isset($input['router_id']) ? $input['router_id'] : 'all';

            if (empty($billing_month)) {
                echo json_encode(['status' => 'error', 'message' => 'Bulan tagihan harus dipilih']);
                exit;
            }

            $customers = $this->customerModel->getCustomersForBilling($package_id, $router_id);
            $targets = [];

            foreach ($customers as $customer) {
                // Cek apakah invoice bulan ini sudah ada
                if (!$this->invoiceModel->checkInvoiceExists($customer->id, $billing_month)) {
                    $targets[] = $customer->id;
                }
            }

            echo json_encode([
                'status' => 'success',
                'total_targets' => count($targets),
                'targets' => $targets
            ]);
            exit;
        }
    }

    public function apiGenerateBatch() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
            $input = json_decode(file_get_contents('php://input'), true);
            $billing_month = isset($input['billing_month']) ? $input['billing_month'] : '';
            $customer_ids = isset($input['customer_ids']) ? $input['customer_ids'] : [];

            if (empty($billing_month) || empty($customer_ids)) {
                echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
                exit;
            }

            $success_count = 0;
            $failed_count = 0;

            foreach ($customer_ids as $customer_id) {
                $customer = $this->customerModel->getById($customer_id);
                if (!$customer || $customer->status !== 'active') {
                    $failed_count++;
                    continue;
                }

                // Double check to prevent duplicates
                if ($this->invoiceModel->checkInvoiceExists($customer->id, $billing_month)) {
                    $failed_count++;
                    continue;
                }

                $package = $this->packageModel->getById($customer->package_id);
                if (!$package) {
                    $failed_count++;
                    continue;
                }

                $amount = $customer->custom_price ? $customer->custom_price : $package->price;
                $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                
                // Hitung due date
                $year = substr($billing_month, 0, 4);
                $month = substr($billing_month, 5, 2);
                $due_date = $year . '-' . $month . '-' . str_pad($customer->due_date, 2, '0', STR_PAD_LEFT);
                
                // Jika due date kurang dari hari ini (tanggal penagihan telat di bulan yang sama), 
                // ini sekadar logic tanggal penagihan. Kita gunakan format standar saja.
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

                $invoice_id = $this->invoiceModel->createInvoice($invoiceData);

                if ($invoice_id) {
                    $itemData = [
                        'invoice_id' => $invoice_id,
                        'description' => 'Tagihan Internet Paket ' . $package->name . ' - Periode ' . date('F Y', strtotime($billing_month . '-01')),
                        'quantity' => 1,
                        'unit_price' => $amount,
                        'total_price' => $amount
                    ];
                    $this->invoiceModel->createInvoiceItem($itemData);
                    
                    // Kirim Notifikasi WA & Rekam Log
                    if (!empty($customer->whatsapp)) {
                        WhatsappService::sendNewInvoice($customer->id, $customer->whatsapp, $customer->name, $invoice_number, $amount, $billing_month, $due_date);
                    }
                    
                    $success_count++;
                } else {
                    $failed_count++;
                }
            }

            echo json_encode([
                'status' => 'success',
                'processed' => count($customer_ids),
                'success' => $success_count,
                'failed' => $failed_count
            ]);
            exit;
        }
    }

    // =========================================================================
    // Tandai Invoice sebagai LUNAS (Manual oleh Admin) + Enable PPPoE
    // =========================================================================
    public function markAsPaid($invoiceId) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit;
        }

        $invoice = $this->invoiceModel->getById($invoiceId);
        if (!$invoice) {
            echo json_encode(['success' => false, 'message' => 'Invoice tidak ditemukan.']);
            exit;
        }

        if ($invoice->status === 'paid') {
            echo json_encode(['success' => false, 'message' => 'Invoice sudah berstatus lunas.']);
            exit;
        }

        // Update status invoice
        $this->invoiceModel->updateStatus($invoiceId, 'paid');
        
        // Simpan transaksi cashflow (Payment ID null karena manual)
        require_once APPROOT . '/app/models/TransactionModel.php';
        $transactionModel = new TransactionModel();
        $transactionModel->recordPaymentSuccess(null, $invoiceId, $invoice->total_amount);

        // Enable PPPoE di MikroTik & ubah status customer ke active
        $customer = $this->customerModel->getById($invoice->customer_id);
        $mtResult = ['connected' => false, 'message' => ''];

        if ($customer) {
            $this->customerModel->updateStatus($customer->id, 'active');
            
            // Kirim notifikasi WA pelunasan manual
            if (!empty($customer->whatsapp)) {
                WhatsappService::sendPaymentSuccess($customer->id, $customer->whatsapp, $customer->name, $invoice->invoice_number, $invoice->total_amount, $invoice->billing_month);
            }

            $pppoeModel = $this->model('PppoeSecretModel');
            $pppoe = $pppoeModel->getByCustomerId($customer->id);

            if ($pppoe) {
                $mikrotikService = new MikrotikService();
                if ($mikrotikService->connect($customer->mikrotik_router_id)) {
                    $ok = $mikrotikService->enablePppoeSecret($pppoe->username);
                    $mikrotikService->disconnect();
                    $mtResult = ['connected' => true, 'enabled' => $ok, 'username' => $pppoe->username];

                    if ($ok) {
                        // Update status PPPoE di DB
                        $db = new Database();
                        $db->query('UPDATE pppoe_secrets SET status = :s WHERE id = :id');
                        $db->bind(':s', 'enabled');
                        $db->bind(':id', $pppoe->id);
                        $db->execute();
                    }
                } else {
                    $mtResult = ['connected' => false, 'message' => $mikrotikService->getLastError()];
                }
            }
        }

        echo json_encode([
            'success'   => true,
            'message'   => 'Invoice ditandai lunas.',
            'mikrotik'  => $mtResult,
        ]);
        exit;
    }

    // =========================================================================
    // Isolasi Manual: Disable PPPoE pelanggan tertentu
    // =========================================================================
    public function isolateCustomer($invoiceId) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit;
        }

        $invoice = $this->invoiceModel->getById($invoiceId);
        if (!$invoice) {
            echo json_encode(['success' => false, 'message' => 'Invoice tidak ditemukan.']);
            exit;
        }

        $customer = $this->customerModel->getById($invoice->customer_id);
        if (!$customer) {
            echo json_encode(['success' => false, 'message' => 'Pelanggan tidak ditemukan.']);
            exit;
        }

        $pppoeModel = $this->model('PppoeSecretModel');
        $pppoe      = $pppoeModel->getByCustomerId($customer->id);

        if (!$pppoe) {
            echo json_encode(['success' => false, 'message' => 'Data PPPoE pelanggan tidak ditemukan.']);
            exit;
        }

        $mikrotikService = new MikrotikService();
        if (!$mikrotikService->connect($customer->mikrotik_router_id)) {
            echo json_encode(['success' => false, 'message' => 'Gagal terhubung ke router: ' . $mikrotikService->getLastError()]);
            exit;
        }

        $ok = $mikrotikService->disablePppoeSecret($pppoe->username);
        $mikrotikService->disconnect();

        if ($ok) {
            $this->customerModel->updateStatus($customer->id, 'isolated');
            $db = new Database();
            $db->query('UPDATE pppoe_secrets SET status = :s WHERE id = :id');
            $db->bind(':s', 'disabled');
            $db->bind(':id', $pppoe->id);
            $db->execute();
            
            // Kirim notifikasi WA terisolir
            if (!empty($customer->whatsapp)) {
                WhatsappService::sendIsolated($customer->id, $customer->whatsapp, $customer->name);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Akun PPPoE ' . $pppoe->username . ' berhasil dinonaktifkan.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menonaktifkan PPPoE: ' . $mikrotikService->getLastError(),
            ]);
        }
        exit;
    }
}
