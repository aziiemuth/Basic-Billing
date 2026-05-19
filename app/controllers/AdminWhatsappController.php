<?php
class AdminWhatsappController extends Controller {
    private $customerModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->customerModel = $this->model('CustomerModel');
    }

    public function broadcast() {
        $data = [
            'title' => 'Broadcast WhatsApp',
            'customers' => $this->customerModel->getAll()
        ];
        $this->view('admin/whatsapp/broadcast', $data);
    }

    public function sendBroadcast() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/AdminWhatsappController/broadcast');
            exit;
        }

        $target = $_POST['target'] ?? 'active';
        $message = trim($_POST['message'] ?? '');

        if ($message === '') {
            $_SESSION['toast_error'] = 'Isi pesan broadcast tidak boleh kosong';
            header('Location: ' . URLROOT . '/AdminWhatsappController/broadcast');
            exit;
        }

        $sent = 0;
        $failed = 0;
        foreach ($this->customerModel->getAll() as $customer) {
            if ($target !== 'all' && $customer->status !== $target) {
                continue;
            }
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
