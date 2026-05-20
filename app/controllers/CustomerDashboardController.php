<?php
class CustomerDashboardController extends Controller {
    private $invoiceModel;
    private $customerModel;
    private $packageModel;
    private $pppoeSecretModel;

    public function __construct() {
        // Apply Middleware
        AuthCustomerMiddleware::check();
        $this->invoiceModel = $this->model('InvoiceModel');
        $this->customerModel = $this->model('CustomerModel');
        $this->packageModel = $this->model('PackageModel');
        $this->pppoeSecretModel = $this->model('PppoeSecretModel');
    }

    public function index() {
        $customerId = $_SESSION['customer_id'];
        
        $customer = $this->customerModel->getById($customerId);
        $package = $customer ? $this->packageModel->getById($customer->package_id) : null;
        $invoices = $this->invoiceModel->getInvoicesByCustomerId($customerId);
        $pppoe = $this->pppoeSecretModel->getByCustomerId($customerId);
        
        // Cek status online dari MikroTik
        require_once APPROOT . '/app/libraries/MikrotikService.php';
        $mikrotikService = new MikrotikService();
        $online_status = 'offline';
        $uptime = '-';
        $ip_address = '-';
        
        if ($pppoe && $pppoe->mikrotik_router_id) {
            if ($mikrotikService->connect($pppoe->mikrotik_router_id)) {
                $status = $mikrotikService->getPppoeStatus($pppoe->username);
                if (isset($status['status']) && $status['status'] == 'online') {
                    $online_status = 'online';
                    $uptime = $status['uptime'] ?? '-';
                    $ip_address = $status['address'] ?? '-';
                }
                $mikrotikService->disconnect();
            }
        }
        
        $settingsModel = $this->model('SettingsModel');
        $settings = $settingsModel->getSettings();
        
        $data = [
            'title' => 'Customer Portal',
            'customer' => $customer,
            'package' => $package,
            'invoices' => $invoices,
            'pppoe' => $pppoe,
            'online_status' => $online_status,
            'uptime' => $uptime,
            'ip_address' => $ip_address,
            'settings' => $settings
        ];
        $this->view('customer/dashboard', $data);
    }
    
    public function invoice($invoice_id) {
        $customerId = $_SESSION['customer_id'];
        $invoice = $this->invoiceModel->getById($invoice_id);
        
        // Validasi: invoice harus ada dan milik pelanggan yang sedang login
        if (!$invoice || $invoice->customer_id != $customerId) {
            header('Location: ' . URLROOT . '/CustomerDashboardController');
            exit;
        }
        
        $customer = $this->customerModel->getById($invoice->customer_id);
        $package = $this->packageModel->getById($invoice->package_id);
        
        $data = [
            'title' => 'Cetak Invoice',
            'invoice' => $invoice,
            'customer' => $customer,
            'package' => $package
        ];
        
        $this->view('customer/invoice', $data);
    }
}
