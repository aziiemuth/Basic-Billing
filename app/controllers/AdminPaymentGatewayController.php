<?php
class AdminPaymentGatewayController extends Controller {
    private $gatewayModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->gatewayModel = $this->model('PaymentGatewayModel');
    }

    public function index() {
        $data = [
            'title' => 'Kelola Payment Gateway',
            'gateways' => $this->gatewayModel->getAll()
        ];
        $this->view('admin/payment_gateway/index', $data);
    }

    public function create() {
        $this->view('admin/payment_gateway/create', ['title' => 'Tambah Payment Gateway']);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/AdminPaymentGatewayController/create');
            exit;
        }

        $this->gatewayModel->create([
            'name' => trim($_POST['name']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'server_key' => trim($_POST['server_key']),
            'client_key' => trim($_POST['client_key']),
            'mode' => $_POST['mode'] === 'production' ? 'production' : 'sandbox',
        ]);

        $_SESSION['toast_success'] = 'Payment gateway berhasil ditambahkan';
        header('Location: ' . URLROOT . '/AdminPaymentGatewayController');
        exit;
    }

    public function edit($id) {
        $gateway = $this->gatewayModel->getById($id);
        if (!$gateway) {
            header('Location: ' . URLROOT . '/AdminPaymentGatewayController');
            exit;
        }

        $this->view('admin/payment_gateway/edit', [
            'title' => 'Edit Payment Gateway',
            'gateway' => $gateway
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/AdminPaymentGatewayController/edit/' . $id);
            exit;
        }

        $this->gatewayModel->update([
            'id' => $id,
            'name' => trim($_POST['name']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'server_key' => trim($_POST['server_key']),
            'client_key' => trim($_POST['client_key']),
            'mode' => $_POST['mode'] === 'production' ? 'production' : 'sandbox',
        ]);

        $_SESSION['toast_success'] = 'Payment gateway berhasil diperbarui';
        header('Location: ' . URLROOT . '/AdminPaymentGatewayController');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->gatewayModel->delete($id);
            $_SESSION['toast_success'] = 'Payment gateway berhasil dihapus';
        }
        header('Location: ' . URLROOT . '/AdminPaymentGatewayController');
        exit;
    }
}
