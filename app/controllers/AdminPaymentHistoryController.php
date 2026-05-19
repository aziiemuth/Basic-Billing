<?php
class AdminPaymentHistoryController extends Controller {
    private $paymentModel;
    private $invoiceModel;
    private $customerModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->paymentModel = $this->model('PaymentModel');
        $this->invoiceModel = $this->model('InvoiceModel');
        $this->customerModel = $this->model('CustomerModel');
    }

    public function index() {
        // Collect filters from $_GET
        $billing_month   = $_GET['billing_month'] ?? '';
        $customer_id     = $_GET['customer_id'] ?? 'all';
        $status          = $_GET['status'] ?? 'all';
        $payment_method  = $_GET['payment_method'] ?? 'all';

        $filters = [
            'billing_month' => $billing_month,
            'customer_id'   => $customer_id,
            'status'        => $status,
            'payment_method'=> $payment_method
        ];

        // Fetch advanced filtered lists
        $payments = $this->paymentModel->getFilteredPayments($filters);
        $invoices = $this->invoiceModel->getFilteredInvoices($filters);
        $customers = $this->customerModel->getAll();

        // Direct DB queries for Logs (Status, Isolir, WhatsApp Logs)
        $db = new Database();

        // 1. WhatsApp Logs
        $waSql = "SELECT wl.*, c.name AS customer_name, c.customer_id AS customer_code 
                  FROM whatsapp_logs wl 
                  JOIN customers c ON wl.customer_id = c.id 
                  WHERE 1=1";
        $waBinds = [];
        if ($customer_id !== 'all') {
            $waSql .= " AND wl.customer_id = :customer_id";
            $waBinds[':customer_id'] = $customer_id;
        }
        $waSql .= " ORDER BY wl.created_at DESC";
        $db->query($waSql);
        foreach ($waBinds as $param => $val) {
            $db->bind($param, $val);
        }
        $whatsappLogs = $db->resultSet();

        // 2. Customer Logs (including Status changes & Isolir history)
        $clSql = "SELECT cl.*, c.name AS customer_name, c.customer_id AS customer_code 
                  FROM customer_logs cl 
                  JOIN customers c ON cl.customer_id = c.id 
                  WHERE 1=1";
        $clBinds = [];
        if ($customer_id !== 'all') {
            $clSql .= " AND cl.customer_id = :customer_id";
            $clBinds[':customer_id'] = $customer_id;
        }
        $clSql .= " ORDER BY cl.created_at DESC";
        $db->query($clSql);
        foreach ($clBinds as $param => $val) {
            $db->bind($param, $val);
        }
        $customerLogs = $db->resultSet();

        $data = [
            'title'          => 'Histori Tagihan & Aktivitas',
            'filters'        => $filters,
            'payments'       => $payments,
            'invoices'       => $invoices,
            'customers'      => $customers,
            'whatsappLogs'   => $whatsappLogs,
            'customerLogs'   => $customerLogs
        ];

        $this->view('admin/payment/history', $data);
    }
}
