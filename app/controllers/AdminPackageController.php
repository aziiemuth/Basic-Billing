<?php
class AdminPackageController extends Controller {
    private $packageModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->packageModel = $this->model('PackageModel');
    }

    public function index() {
        $packages = $this->packageModel->getAll();
        
        $data = [
            'title' => 'Manajemen Paket Internet',
            'packages' => $packages
        ];
        
        $this->view('admin/package/index', $data);
    }

    public function create() {
        $data = [
            'title' => 'Tambah Paket Internet'
        ];
        
        $this->view('admin/package/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'speed_download' => trim($_POST['speed_download']),
                'speed_upload' => trim($_POST['speed_upload']),
                'price' => trim($_POST['price']),
                'mikrotik_profile' => trim($_POST['mikrotik_profile']),
                'description' => trim($_POST['description']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'auto_isolate' => isset($_POST['auto_isolate']) ? 1 : 0
            ];

            if ($this->packageModel->create($data)) {
                $_SESSION['toast_success'] = 'Paket internet berhasil ditambahkan';
                header('Location: ' . URLROOT . '/AdminPackageController');
            } else {
                die('Terjadi kesalahan saat menyimpan paket.');
            }
        } else {
            header('Location: ' . URLROOT . '/AdminPackageController');
        }
    }

    public function edit($id) {
        $package = $this->packageModel->getById($id);
        
        if (!$package) {
            header('Location: ' . URLROOT . '/AdminPackageController');
            return;
        }

        $data = [
            'title' => 'Edit Paket Internet',
            'package' => $package
        ];
        
        $this->view('admin/package/edit', $data);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'speed_download' => trim($_POST['speed_download']),
                'speed_upload' => trim($_POST['speed_upload']),
                'price' => trim($_POST['price']),
                'mikrotik_profile' => trim($_POST['mikrotik_profile']),
                'description' => trim($_POST['description']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'auto_isolate' => isset($_POST['auto_isolate']) ? 1 : 0
            ];

            if ($this->packageModel->update($data)) {
                $_SESSION['toast_success'] = 'Paket internet berhasil diperbarui';
                header('Location: ' . URLROOT . '/AdminPackageController');
            } else {
                die('Terjadi kesalahan saat mengupdate paket.');
            }
        } else {
            header('Location: ' . URLROOT . '/AdminPackageController');
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                if ($this->packageModel->delete($id)) {
                    $_SESSION['toast_success'] = 'Paket internet berhasil dihapus';
                    header('Location: ' . URLROOT . '/AdminPackageController');
                } else {
                    die('Gagal menghapus paket.');
                }
            } catch (PDOException $e) {
                // Biasanya error foreign key jika paket sudah dipakai pelanggan
                $_SESSION['toast_error'] = 'Gagal menghapus paket. Paket ini mungkin sedang digunakan oleh pelanggan.';
                header('Location: ' . URLROOT . '/AdminPackageController');
                exit;
            }
        } else {
            header('Location: ' . URLROOT . '/AdminPackageController');
        }
    }
}
