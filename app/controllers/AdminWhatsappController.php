<?php
class AdminWhatsappController extends Controller {
    private $customerModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->customerModel = $this->model('CustomerModel');
    }

    /**
     * Halaman broadcast WA — tampilkan statistik per kategori target.
     */
    public function broadcast() {
        $allCustomers   = $this->customerModel->getAll();
        $isolatedUnpaid = $this->customerModel->getIsolatedWithUnpaidInvoices();

        // Hitung jumlah per target untuk preview di view
        $stats = [
            'active'   => count(array_filter($allCustomers, fn($c) => $c->status === 'active')),
            'isolated' => count($isolatedUnpaid),   // Hanya yang masih punya tagihan belum bayar
            'inactive' => count(array_filter($allCustomers, fn($c) => $c->status === 'inactive')),
            'all'      => count($allCustomers),
        ];

        $data = [
            'title'     => 'Broadcast WhatsApp',
            'customers' => $allCustomers,
            'stats'     => $stats,
        ];
        $this->view('admin/whatsapp/broadcast', $data);
    }

    /**
     * Proses pengiriman broadcast WA.
     * Target 'isolated' hanya menyasar pelanggan yang terisolir DAN masih punya tagihan belum dibayar.
     */
    public function sendBroadcast() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/AdminWhatsappController/broadcast');
            exit;
        }

        $target  = $_POST['target'] ?? 'active';
        $message = trim($_POST['message'] ?? '');

        if ($message === '') {
            $_SESSION['toast_error'] = 'Isi pesan broadcast tidak boleh kosong.';
            header('Location: ' . URLROOT . '/AdminWhatsappController/broadcast');
            exit;
        }

        // Tentukan daftar penerima berdasarkan target
        if ($target === 'isolated') {
            // Hanya pelanggan terisolir yang MASIH punya tagihan belum dibayar
            // Pelanggan yang sudah bayar tidak akan menerima blast ini
            $customers = $this->customerModel->getIsolatedWithUnpaidInvoices();
        } elseif ($target === 'all') {
            $customers = $this->customerModel->getAll();
        } else {
            // Filter berdasarkan status: active atau inactive
            $all       = $this->customerModel->getAll();
            $customers = array_values(array_filter($all, fn($c) => $c->status === $target));
        }

        $sent   = 0;
        $failed = 0;

        foreach ($customers as $customer) {
            if (empty($customer->whatsapp)) {
                $failed++;
                continue;
            }

            $personalMessage = str_replace(
                ['{nama}', '{id_pelanggan}'],
                [$customer->name, $customer->customer_id],
                $message
            );

            if (WhatsappService::send($customer->id, $customer->whatsapp, $personalMessage, 'custom')) {
                $sent++;
            } else {
                $failed++;
            }
        }

        $_SESSION['toast_success'] = "Broadcast selesai. Terkirim: {$sent}, gagal/pending: {$failed}.";
        header('Location: ' . URLROOT . '/AdminWhatsappController/broadcast');
        exit;
    }
}
