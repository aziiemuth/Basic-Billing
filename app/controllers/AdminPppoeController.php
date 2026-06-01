<?php
class AdminPppoeController extends Controller {
    private $routerModel;
    private $customerModel;
    private $pppoeSecretModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->routerModel = $this->model('MikrotikRouterModel');
        $this->customerModel = $this->model('CustomerModel');
        $this->pppoeSecretModel = $this->model('PppoeSecretModel');
    }

    public function index() {
        $routers = $this->routerModel->getAll();
        
        $router_id = isset($_GET['router_id']) ? $_GET['router_id'] : null;
        if (!$router_id && count($routers) > 0) {
            $router_id = $routers[0]->id;
        }

        $secrets = [];
        $error = null;
        $selectedRouter = null;
        $packages = $this->model('PackageModel')->getAll();

        if ($router_id) {
            $selectedRouter = $this->routerModel->getById($router_id);
            require_once APPROOT . '/app/libraries/MikrotikService.php';
            $mikrotikService = new MikrotikService();
            
            if ($mikrotikService->connect($router_id)) {
                $rawSecrets = $mikrotikService->getAllPppoeSecrets();
                
                // Get active connections for online status
                $activeSessions = $mikrotikService->getAllActiveSessions();

                $mikrotikService->disconnect();
                
                // Get all PPPoE secrets from Database to check if they exist
                $db = new Database();
                $db->query("SELECT ps.username, c.id as customer_id, c.name as customer_name FROM pppoe_secrets ps JOIN customers c ON ps.customer_id = c.id WHERE ps.mikrotik_router_id = :router_id");
                $db->bind(':router_id', $router_id);
                $dbSecrets = $db->resultSet();
                
                $dbMap = [];
                foreach ($dbSecrets as $dbs) {
                    $dbMap[$dbs->username] = [
                        'customer_id' => $dbs->customer_id,
                        'customer_name' => $dbs->customer_name
                    ];
                }
                
                // Build case-insensitive active sessions map
                $activeSessionsMap = [];
                foreach ($activeSessions as $name => $sess) {
                    $activeSessionsMap[strtolower(trim($name))] = $sess;
                }

                // Merge Data
                foreach ($rawSecrets as $s) {
                    if (isset($s['name'])) {
                        $username = $s['name'];
                        $usernameKey = strtolower(trim($username));
                        $is_in_db = isset($dbMap[$username]);
                        
                        $is_online = isset($activeSessionsMap[$usernameKey]);
                        
                        $secrets[] = [
                            'username' => $username,
                            'profile' => isset($s['profile']) ? $s['profile'] : 'default',
                            'service' => isset($s['service']) ? $s['service'] : 'pppoe',
                            'disabled' => isset($s['disabled']) && ($s['disabled'] == 'true' || $s['disabled'] === true || $s['disabled'] == 1 || $s['disabled'] === 'yes'),
                            'is_in_db' => $is_in_db,
                            'customer_id' => $is_in_db ? $dbMap[$username]['customer_id'] : null,
                            'customer_name' => $is_in_db ? $dbMap[$username]['customer_name'] : null,
                            'is_online' => $is_online,
                            'uptime' => $is_online && isset($activeSessionsMap[$usernameKey]['uptime']) ? $activeSessionsMap[$usernameKey]['uptime'] : '-',
                            'ip_address' => $is_online && isset($activeSessionsMap[$usernameKey]['address']) ? $activeSessionsMap[$usernameKey]['address'] : '-',
                            'caller_id' => $is_online && isset($activeSessionsMap[$usernameKey]['caller-id']) ? $activeSessionsMap[$usernameKey]['caller-id'] : '-'
                        ];
                    }
                }
            } else {
                $error = "Gagal terhubung ke MikroTik Router: " . $mikrotikService->getLastError();
            }
        }

        $data = [
            'title' => 'Data PPPoE MikroTik',
            'routers' => $routers,
            'router_id' => $router_id,
            'selectedRouter' => $selectedRouter,
            'secrets' => $secrets,
            'error' => $error,
            'packages' => $packages
        ];
        
        $this->view('admin/pppoe/index', $data);
    }

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

        require_once APPROOT . '/app/libraries/MikrotikService.php';
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
            // Update status di DB juga jika pelanggan tersebut ada di DB
            $db = new Database();
            $db->query('UPDATE pppoe_secrets SET status = :status WHERE username = :username AND mikrotik_router_id = :router_id');
            $db->bind(':status', $action === 'enable' ? 'enabled' : 'disabled');
            $db->bind(':username', $username);
            $db->bind(':router_id', $router_id);
            $db->execute();

            // Sync dengan tabel customers
            $db->query('SELECT customer_id FROM pppoe_secrets WHERE username = :username AND mikrotik_router_id = :router_id');
            $db->bind(':username', $username);
            $db->bind(':router_id', $router_id);
            $linkedSecret = $db->single();

            if ($linkedSecret && !empty($linkedSecret->customer_id)) {
                $db->query('UPDATE customers SET status = :status WHERE id = :customer_id');
                $db->bind(':status', $action === 'enable' ? 'active' : 'isolated');
                $db->bind(':customer_id', $linkedSecret->customer_id);
                $db->execute();
            }
            
            echo json_encode(['success' => true, 'message' => 'Berhasil ' . ($action === 'enable' ? 'mengaktifkan' : 'menonaktifkan') . ' akun PPPoE ' . $username]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengubah status PPPoE: ' . $mikrotikService->getLastError()]);
        }
        exit;
    }
}
