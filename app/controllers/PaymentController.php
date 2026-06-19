<?php
require_once APPROOT . '/vendor/autoload.php';

class PaymentController extends Controller {
    private $paymentModel;
    private $invoiceModel;
    private $customerModel;

    public function __construct() {
        $this->paymentModel  = $this->model('PaymentModel');
        $this->invoiceModel  = $this->model('InvoiceModel');
        $this->customerModel = $this->model('CustomerModel');

        // Konfigurasi Midtrans langsung dari .env — tidak perlu tabel payment_gateways
        \Midtrans\Config::$serverKey    = MIDTRANS_SERVER_KEY;
        \Midtrans\Config::$isProduction = MIDTRANS_IS_PRODUCTION;
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;
    }

    // Dipanggil saat pelanggan menekan tombol bayar
    public function snap($invoiceId) {
        // Wajib login — bisa customer atau admin
        $isCustomerLoggedIn = isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'] === true;
        $isAdminLoggedIn    = isset($_SESSION['admin_logged_in'])    && $_SESSION['admin_logged_in'] === true;
        
        if (!$isCustomerLoggedIn && !$isAdminLoggedIn) {
            header('Location: ' . URLROOT . '/CustomerAuthController/login');
            exit;
        }

        $invoice = $this->invoiceModel->getById($invoiceId);
        if (!$invoice || $invoice->status == 'paid') {
            die('Invoice tidak valid atau sudah dibayar.');
        }
        
        // Pastikan pelanggan hanya bisa bayar invoice miliknya sendiri
        if ($isCustomerLoggedIn && $invoice->customer_id != $_SESSION['customer_id']) {
            die('Akses ditolak: Invoice bukan milik Anda.');
        }

        $customer = $this->customerModel->getById($invoice->customer_id);

        // Buat Order ID unik
        $orderId = 'INV-' . $invoice->id . '-' . time();

        $params = array(
            'transaction_details' => array(
                'order_id' => $orderId,
                'gross_amount' => (int)$invoice->total_amount,
            ),
            'customer_details' => array(
                'first_name' => $customer->name,
                'email' => $customer->email ?: $customer->username,
                'phone' => $customer->whatsapp,
            ),
        );

        try {
            // Buat Snap Payment Page URL via Midtrans
            $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;

            // Simpan percobaan pembayaran ke database
            $paymentData = [
                'invoice_id'         => $invoice->id,
                'reference_id'       => $orderId,
                'amount'             => $invoice->total_amount,
                'payment_gateway_id' => null, // Konfigurasi via .env
                'status'             => 'pending',
                'payment_url'        => $paymentUrl,
            ];

            $this->paymentModel->create($paymentData);

            // Redirect ke halaman pembayaran Midtrans
            header('Location: ' . $paymentUrl);
            exit;
            
        } catch (\Exception $e) {
            error_log('Error Midtrans: ' . $e->getMessage());
            die('Gagal memproses pembayaran melalui Midtrans. Detail Error: ' . $e->getMessage());
        }
    }

    // Webhook URL untuk menerima notifikasi dari Midtrans
    public function webhook() {
        $notif = new \Midtrans\Notification();

        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $order_id = $notif->order_id;
        $fraud = $notif->fraud_status;

        $payment = $this->paymentModel->getByReferenceId($order_id);
        
        if (!$payment) {
            http_response_code(404);
            die('Order tidak ditemukan');
        }

        $status = 'pending';
        $invoiceStatus = 'unpaid';

        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $status = 'pending';
                } else {
                    $status = 'success';
                    $invoiceStatus = 'paid';
                }
            }
        } else if ($transaction == 'settlement') {
            $status = 'success';
            $invoiceStatus = 'paid';
        } else if ($transaction == 'pending') {
            $status = 'pending';
        } else if ($transaction == 'deny') {
            $status = 'failed';
        } else if ($transaction == 'expire') {
            $status = 'expired';
            $invoiceStatus = 'expired';
        } else if ($transaction == 'cancel') {
            $status = 'failed';
            $invoiceStatus = 'cancelled';
        }

        // Update database
        $this->paymentModel->updateWebhookStatus($order_id, $status, $type, json_encode($notif));
        
        if ($invoiceStatus != 'unpaid') {
            $this->invoiceModel->updateStatus($payment->invoice_id, $invoiceStatus);
            
            // Jika sukses: enable mikrotik + kirim notifikasi WA + Rekam Cashflow
            if ($status == 'success') {
                // Rekam transaksi & cashflow
                require_once APPROOT . '/app/models/TransactionModel.php';
                $transactionModel = new TransactionModel();
                $transactionModel->recordPaymentSuccess($payment->id, $payment->invoice_id, $payment->amount);
                
                $this->autoEnableMikrotik($payment->invoice_id);
                $this->sendPaymentSuccessNotification($payment->invoice_id);
            }
        }

        http_response_code(200);
        echo "OK";
    }

    private function autoEnableMikrotik($invoiceId) {
        $invoice = $this->invoiceModel->getById($invoiceId);
        if ($invoice) {
            $customer = $this->customerModel->getById($invoice->customer_id);
            if ($customer) {
                // Fetch PPPoE Data
                require_once APPROOT . '/app/models/PppoeSecretModel.php';
                $pppoeModel = new PppoeSecretModel();
                $pppoe = $pppoeModel->getByCustomerId($customer->id);
                
                if ($pppoe && $customer->status === 'isolated') {
                    // Enable PPPoE di MikroTik
                    $mikrotikService = new MikrotikService();
                    if ($mikrotikService->connect($customer->mikrotik_router_id)) {
                        $mikrotikService->enablePppoeSecret($pppoe->username);
                        $mikrotikService->disconnect();
                    }
                    
                    // Update status pppoe_secrets di database → 'enabled'
                    $pppoeModel->updateByCustomerId([
                        'customer_id'       => $customer->id,
                        'mikrotik_router_id'=> $pppoe->mikrotik_router_id,
                        'username'          => $pppoe->username,
                        'password'          => $pppoe->password,
                        'profile'           => $pppoe->profile,
                        'service'           => $pppoe->service,
                        'status'            => 'enabled',
                    ]);
                }
                
                // Update status customer di database → 'active'
                if ($customer->status === 'isolated') {
                    $this->customerModel->updateStatus($customer->id, 'active');
                }
            }
        }
    }

    private function sendPaymentSuccessNotification($invoiceId) {
        $invoice = $this->invoiceModel->getById($invoiceId);
        if (!$invoice) return;

        $customer = $this->customerModel->getById($invoice->customer_id);
        if (!$customer || empty($customer->whatsapp)) return;

        WhatsappService::sendPaymentSuccess(
            $customer->id,
            $customer->whatsapp,
            $customer->name,
            $invoice->invoice_number,
            $invoice->total_amount,
            $invoice->billing_month
        );
    }
}
