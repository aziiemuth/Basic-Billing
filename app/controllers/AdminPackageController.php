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

    public function syncMikrotik() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $router_id = isset($_POST['router_id']) ? $_POST['router_id'] : null;
            
            require_once APPROOT . '/app/libraries/MikrotikService.php';
            $mikrotikService = new MikrotikService();
            
            // Connect to either specific router or default
            $connected = $router_id ? $mikrotikService->connect($router_id) : $mikrotikService->connectDefault();
            
            if (!$connected) {
                $_SESSION['toast_error'] = 'Gagal terhubung ke MikroTik: ' . $mikrotikService->getLastError();
                header('Location: ' . URLROOT . '/AdminPackageController');
                exit;
            }
            
            $profiles = $mikrotikService->getAllPppoeProfiles();
            if (empty($profiles)) {
                $_SESSION['toast_error'] = 'Tidak ada PPP Profile ditemukan di MikroTik.';
                header('Location: ' . URLROOT . '/AdminPackageController');
                exit;
            }
            
            $existingPackages = $this->packageModel->getAll();
            $existingProfiles = array_column($existingPackages, 'mikrotik_profile');
            
            $addedCount = 0;
            foreach ($profiles as $p) {
                if (isset($p['name']) && !in_array($p['name'], $existingProfiles)) {
                    // Extract rate-limit (speed) if available
                    $speed_download = 0;
                    $speed_upload = 0;
                    if (isset($p['rate-limit']) && !empty($p['rate-limit'])) {
                        // format is Usually rx/tx e.g. "5M/10M" (RX from router perspective is Upload, TX is Download)
                        $rateParts = explode('/', $p['rate-limit']);
                        if (count($rateParts) == 2) {
                            $speed_upload = (int) filter_var($rateParts[0], FILTER_SANITIZE_NUMBER_INT);
                            $speed_download = (int) filter_var($rateParts[1], FILTER_SANITIZE_NUMBER_INT);
                        }
                    }

                    $data = [
                        'name' => 'Paket ' . $p['name'],
                        'speed_download' => $speed_download,
                        'speed_upload' => $speed_upload,
                        'price' => 0, // Default price
                        'mikrotik_profile' => $p['name'],
                        'description' => 'Di-import otomatis dari MikroTik',
                        'is_active' => 1,
                        'auto_isolate' => 1
                    ];
                    
                    if ($this->packageModel->create($data)) {
                        $addedCount++;
                    }
                }
            }
            
            if ($addedCount > 0) {
                $_SESSION['toast_success'] = "Berhasil mensinkronisasi dan menambahkan $addedCount paket baru dari MikroTik. Jangan lupa untuk mengatur harganya!";
            } else {
                $_SESSION['toast_success'] = 'Sinkronisasi berhasil. Semua profile MikroTik sudah terdaftar sebagai paket.';
            }
            
            header('Location: ' . URLROOT . '/AdminPackageController');
        } else {
            header('Location: ' . URLROOT . '/AdminPackageController');
        }
    }
}
