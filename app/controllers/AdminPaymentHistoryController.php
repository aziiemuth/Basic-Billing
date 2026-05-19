<?php
class AdminPaymentHistoryController extends Controller {
    public function __construct() {
        AuthAdminMiddleware::check();
    }

    public function index() {
        $status = $_GET['status'] ?? 'all';
        $invoiceStatus = $_GET['invoice_status'] ?? 'all';

        $data = [
            'title' => 'Histori Pembayaran',
            'status' => $status,
            'invoice_status' => $invoiceStatus,
            'payments' => $this->model('PaymentModel')->getHistory($status),
            'invoices' => $this->model('InvoiceModel')->getAllWithDetails($invoiceStatus)
        ];

        $this->view('admin/payment/history', $data);
    }
}
