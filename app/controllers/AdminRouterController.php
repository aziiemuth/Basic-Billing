<?php
class AdminRouterController extends Controller {
    private $routerModel;

    public function __construct() {
        // Apply Middleware
        AuthAdminMiddleware::check();
        $this->routerModel = $this->model('MikrotikRouterModel');
    }

    public function index() {
        $routers = $this->routerModel->getAll();
        
        $data = [
            'title'   => 'Manajemen Router Server',
            'routers' => $routers,
        ];
        
        $this->view('admin/router/index', $data);
    }

    public function create() {
        $data = [
            'title' => 'Tambah Router Baru',
        ];
        
        $this->view('admin/router/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'name'            => trim($_POST['name']),
                'host_ip'         => trim($_POST['host_ip']),
                'api_username'    => trim($_POST['api_username']),
                'api_password'    => trim($_POST['api_password']),
                'api_port'        => !empty($_POST['api_port'])       ? (int)trim($_POST['api_port'])       : 8728,
                'pppoe_interface' => !empty($_POST['pppoe_interface']) ? trim($_POST['pppoe_interface'])     : null,
                'description'     => !empty($_POST['description'])    ? trim($_POST['description'])         : null,
                'is_active'       => isset($_POST['is_active'])       ? 1 : 0,
            ];

            if ($this->routerModel->create($data)) {
                $_SESSION['toast_success'] = 'Router berhasil ditambahkan';
                header('Location: ' . URLROOT . '/AdminRouterController');
                exit;
            } else {
                die('Terjadi kesalahan saat menambahkan data router.');
            }
        } else {
            header('Location: ' . URLROOT . '/AdminRouterController/create');
            exit;
        }
    }

    public function edit($id) {
        $router = $this->routerModel->getById($id);
        
        if (!$router) {
            header('Location: ' . URLROOT . '/AdminRouterController');
            exit;
        }

        $data = [
            'title'  => 'Edit Router Server',
            'router' => $router,
        ];
        
        $this->view('admin/router/edit', $data);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id'              => $id,
                'name'            => trim($_POST['name']),
                'host_ip'         => trim($_POST['host_ip']),
                'api_username'    => trim($_POST['api_username']),
                'api_password'    => !empty($_POST['api_password']) ? trim($_POST['api_password']) : null,
                'api_port'        => !empty($_POST['api_port'])       ? (int)trim($_POST['api_port'])   : 8728,
                'pppoe_interface' => !empty($_POST['pppoe_interface']) ? trim($_POST['pppoe_interface']) : null,
                'description'     => !empty($_POST['description'])    ? trim($_POST['description'])     : null,
                'is_active'       => isset($_POST['is_active'])       ? 1 : 0,
            ];

            if ($this->routerModel->update($data)) {
                $_SESSION['toast_success'] = 'Router berhasil diperbarui';
                header('Location: ' . URLROOT . '/AdminRouterController');
                exit;
            } else {
                die('Terjadi kesalahan saat memperbarui data router.');
            }
        } else {
            header('Location: ' . URLROOT . '/AdminRouterController/edit/' . $id);
            exit;
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->routerModel->delete($id)) {
                $_SESSION['toast_success'] = 'Router berhasil dihapus';
                header('Location: ' . URLROOT . '/AdminRouterController');
                exit;
            } else {
                die('Terjadi kesalahan saat menghapus data router.');
            }
        }
        header('Location: ' . URLROOT . '/AdminRouterController');
        exit;
    }

    // =========================================================================
    // AJAX: Test Koneksi ke Router (dari DB atau Config)
    // =========================================================================
    public function testConnection($id = null) {
        header('Content-Type: application/json');

        $mikrotikService = new MikrotikService();
        $result          = $mikrotikService->testConnection($id ?: null);

        echo json_encode($result);
        exit;
    }



    // =========================================================================
    // Halaman Sinkronisasi Status PPPoE
    // =========================================================================
    public function sync($id = null) {
        $routers = $this->routerModel->getAll();

        // Load customers with PPPoE data
        $customerModel = $this->model('CustomerModel');
        $customers     = $customerModel->getCustomersWithPppoe($id ?: null);

        // If a specific router is selected, fetch live status from MikroTik
        $mikrotikStatus = [];
        $syncError      = null;
        $selectedRouter = null;

        if ($id) {
            $selectedRouter  = $this->routerModel->getById($id);
            $mikrotikService = new MikrotikService();
            if ($mikrotikService->connect($id)) {
                $mikrotikStatus = $mikrotikService->syncAllStatus();
                $mikrotikService->disconnect();
            } else {
                $syncError = $mikrotikService->getLastError();
            }
        }

        $data = [
            'title'          => 'Sinkronisasi PPPoE',
            'routers'        => $routers,
            'customers'      => $customers,
            'mikrotikStatus' => $mikrotikStatus,
            'syncError'      => $syncError,
            'selectedRouter' => $selectedRouter,
            'router_id'      => $id,
        ];

        $this->view('admin/mikrotik/sync', $data);
    }

    // =========================================================================
    // AJAX: Enable/Disable PPPoE satu pelanggan dari halaman sync
    // =========================================================================
    public function togglePppoe() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit;
        }

        $action    = $_POST['action']    ?? ''; // 'enable' or 'disable'
        $username  = $_POST['username']  ?? '';
        $router_id = $_POST['router_id'] ?? '';

        if (empty($action) || empty($username) || empty($router_id)) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
            exit;
        }

        $mikrotikService = new MikrotikService();
        if (!$mikrotikService->connect($router_id)) {
            echo json_encode(['success' => false, 'message' => 'Gagal terhubung ke router: ' . $mikrotikService->getLastError()]);
            exit;
        }

        if ($action === 'enable') {
            $ok = $mikrotikService->enablePppoeSecret($username);
        } else {
            $ok = $mikrotikService->disablePppoeSecret($username);
        }
        $mikrotikService->disconnect();

        if ($ok) {
            // Update status di DB juga
            $pppoeModel = $this->model('PppoeSecretModel');
            // We need to find by username — add a helper method
            $this->updatePppoeStatusByUsername($username, $action === 'enable' ? 'enabled' : 'disabled');
            echo json_encode(['success' => true, 'message' => 'Berhasil ' . ($action === 'enable' ? 'mengaktifkan' : 'menonaktifkan') . ' akun PPPoE ' . $username]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengubah status PPPoE: ' . $mikrotikService->getLastError()]);
        }
        exit;
    }

    private function updatePppoeStatusByUsername($username, $status) {
        $db = new Database();
        $db->query('UPDATE pppoe_secrets SET status = :status WHERE username = :username');
        $db->bind(':status', $status);
        $db->bind(':username', $username);
        $db->execute();
    }

    // =========================================================================
    // Isolasi semua pelanggan yang menunggak
    // =========================================================================
    public function isolateOverdue() {
        header('Content-Type: application/json');

        $customerModel = $this->model('CustomerModel');
        $overdue       = $customerModel->getOverdueCustomers();

        $results = [
            'total'    => count($overdue),
            'success'  => 0,
            'failed'   => 0,
            'skipped'  => 0,
            'details'  => [],
        ];

        // Group by router to avoid reconnecting for each customer
        $byRouter = [];
        foreach ($overdue as $c) {
            $rid = $c->pppoe_router_id ?? null;
            if (!$rid || empty($c->pppoe_username)) {
                $results['skipped']++;
                $results['details'][] = ['name' => $c->name, 'status' => 'skipped', 'reason' => 'Tidak ada data PPPoE'];
                continue;
            }
            $byRouter[$rid][] = $c;
        }

        foreach ($byRouter as $router_id => $customers) {
            $mikrotikService = new MikrotikService();
            $connected       = $mikrotikService->connect($router_id);

            foreach ($customers as $c) {
                if (!$connected) {
                    $results['failed']++;
                    $results['details'][] = ['name' => $c->name, 'status' => 'failed', 'reason' => 'Gagal koneksi ke router'];
                    continue;
                }

                $ok = $mikrotikService->disablePppoeSecret($c->pppoe_username);

                if ($ok) {
                    // Update DB status
                    $customerModel->updateStatus($c->id, 'isolated');
                    $this->updatePppoeStatusByUsername($c->pppoe_username, 'disabled');
                    $results['success']++;
                    $results['details'][] = ['name' => $c->name, 'pppoe' => $c->pppoe_username, 'status' => 'isolated'];
                } else {
                    $results['failed']++;
                    $results['details'][] = ['name' => $c->name, 'pppoe' => $c->pppoe_username, 'status' => 'failed', 'reason' => $mikrotikService->getLastError()];
                }
            }

            if ($connected) {
                $mikrotikService->disconnect();
            }
        }

        echo json_encode($results);
        exit;
    }
}
