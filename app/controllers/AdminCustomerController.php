<?php
class AdminCustomerController extends Controller {
    private $customerModel;

    public function __construct() {
        // Apply Middleware
        AuthAdminMiddleware::check();
        $this->customerModel = $this->model('CustomerModel');
    }

    public function index() {
        $customers = $this->customerModel->getAll();
        
        $data = [
            'title' => 'Manajemen Pelanggan',
            'customers' => $customers
        ];
        
        $this->view('admin/customer/index', $data);
    }

    public function create() {
        $data = [
            'title' => 'Tambah Pelanggan',
            'customer_id' => $this->customerModel->generateCustomerId(),
            'packages' => $this->model('PackageModel')->getAll(),
            'routers' => $this->model('MikrotikRouterModel')->getAll()
        ];
        
        $this->view('admin/customer/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); // Deprecated in PHP 8.1+

            $data = [
                'customer_id' => trim($_POST['customer_id']),
                'name' => trim($_POST['name']),
                'whatsapp' => trim($_POST['whatsapp']),
                'email' => isset($_POST['email']) ? trim($_POST['email']) : null,
                'username' => trim($_POST['username']),
                'password' => password_hash(trim($_POST['password']), PASSWORD_DEFAULT),
                'address' => trim($_POST['address']),
                'latitude' => trim($_POST['latitude']),
                'longitude' => trim($_POST['longitude']),
                'package_id' => trim($_POST['package_id']),
                'custom_price' => !empty($_POST['custom_price']) ? trim($_POST['custom_price']) : null,
                'mikrotik_router_id' => trim($_POST['mikrotik_router_id']),
                'installation_date' => trim($_POST['installation_date']),
                'due_date' => trim($_POST['due_date']),
                'status' => trim($_POST['status']),
                'photo_profile' => null,
                'photo_ktp' => null
            ];

            // Handle file uploads
            $uploadDir = APPROOT . '/public/uploads/customers/';
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (isset($_FILES['photo_profile']) && $_FILES['photo_profile']['error'] === UPLOAD_ERR_OK) {
                $fileMime = mime_content_type($_FILES['photo_profile']['tmp_name']);
                if (!in_array($fileMime, $allowedTypes)) {
                    die('Error: Tipe file foto profil tidak diizinkan. Hanya JPG, PNG, GIF, WEBP.');
                }
                $ext = pathinfo($_FILES['photo_profile']['name'], PATHINFO_EXTENSION);
                $filename = 'profile_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo_profile']['tmp_name'], $uploadDir . 'profile/' . $filename)) {
                    $data['photo_profile'] = $filename;
                }
            }
            
            if (isset($_FILES['photo_ktp']) && $_FILES['photo_ktp']['error'] === UPLOAD_ERR_OK) {
                $fileMime = mime_content_type($_FILES['photo_ktp']['tmp_name']);
                $allowedKtp = array_merge($allowedTypes, ['application/pdf']);
                if (!in_array($fileMime, $allowedKtp)) {
                    die('Error: Tipe file KTP tidak diizinkan. Hanya JPG, PNG, GIF, WEBP, PDF.');
                }
                $ext = pathinfo($_FILES['photo_ktp']['name'], PATHINFO_EXTENSION);
                $filename = 'ktp_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo_ktp']['tmp_name'], $uploadDir . 'ktp/' . $filename)) {
                    $data['photo_ktp'] = $filename;
                }
            }

            if ($this->customerModel->create($data)) {
                $newCustomer = $this->customerModel->getByCustomerIdString($data['customer_id']);
                if ($newCustomer) {
                    $this->model('PppoeSecretModel')->create([
                        'customer_id' => $newCustomer->id,
                        'mikrotik_router_id' => $data['mikrotik_router_id'],
                        'username' => trim($_POST['pppoe_username']),
                        'password' => trim($_POST['pppoe_password']),
                        'profile' => 'default',
                        'service' => 'pppoe',
                        'status' => 'enabled'
                    ]);

                    // Add PPPoE Secret to Mikrotik
                    $mikrotikService = new MikrotikService();
                    if ($mikrotikService->connect($data['mikrotik_router_id'])) {
                        $package = $this->model('PackageModel')->getById($data['package_id']);
                        $profile = $package ? $package->mikrotik_profile : 'default';
                        $mikrotikService->addPppoeSecret(trim($_POST['pppoe_username']), trim($_POST['pppoe_password']), $profile);
                        $mikrotikService->disconnect();
                    }
                }
                // Redirect on success
                $_SESSION['toast_success'] = 'Pelanggan berhasil ditambahkan';
                header('Location: ' . URLROOT . '/AdminCustomerController');
                exit;
            } else {
                die('Something went wrong.');
            }
        } else {
            header('Location: ' . URLROOT . '/AdminCustomerController/create');
            exit;
        }
    }

    public function edit($id) {
        $customer = $this->customerModel->getById($id);
        
        if (!$customer) {
            header('Location: ' . URLROOT . '/AdminCustomerController');
            exit;
        }

        $data = [
            'title' => 'Edit Pelanggan',
            'customer' => $customer,
            'pppoe' => $this->model('PppoeSecretModel')->getByCustomerId($id),
            'packages' => $this->model('PackageModel')->getAll(),
            'routers' => $this->model('MikrotikRouterModel')->getAll()
        ];
        
        $this->view('admin/customer/edit', $data);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); // Deprecated in PHP 8.1+

            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'whatsapp' => trim($_POST['whatsapp']),
                'email' => isset($_POST['email']) ? trim($_POST['email']) : null,
                'username' => trim($_POST['username']),
                'address' => trim($_POST['address']),
                'latitude' => trim($_POST['latitude']),
                'longitude' => trim($_POST['longitude']),
                'package_id' => trim($_POST['package_id']),
                'custom_price' => !empty($_POST['custom_price']) ? trim($_POST['custom_price']) : null,
                'mikrotik_router_id' => trim($_POST['mikrotik_router_id']),
                'installation_date' => trim($_POST['installation_date']),
                'due_date' => trim($_POST['due_date']),
                'status' => trim($_POST['status'])
            ];

            // Only update password if provided
            if (!empty($_POST['password'])) {
                $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
                $this->customerModel->updatePassword($id, $password);
            }

            // Handle file uploads
            $uploadDir = APPROOT . '/public/uploads/customers/';
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $photo_profile = null;
            $photo_ktp = null;
            
            if (isset($_FILES['photo_profile']) && $_FILES['photo_profile']['error'] === UPLOAD_ERR_OK) {
                $fileMime = mime_content_type($_FILES['photo_profile']['tmp_name']);
                if (!in_array($fileMime, $allowedTypes)) {
                    die('Error: Tipe file foto profil tidak diizinkan. Hanya JPG, PNG, GIF, WEBP.');
                }
                $ext = pathinfo($_FILES['photo_profile']['name'], PATHINFO_EXTENSION);
                $filename = 'profile_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo_profile']['tmp_name'], $uploadDir . 'profile/' . $filename)) {
                    $photo_profile = $filename;
                }
            }
            
            if (isset($_FILES['photo_ktp']) && $_FILES['photo_ktp']['error'] === UPLOAD_ERR_OK) {
                $fileMime = mime_content_type($_FILES['photo_ktp']['tmp_name']);
                $allowedKtp = array_merge($allowedTypes, ['application/pdf']);
                if (!in_array($fileMime, $allowedKtp)) {
                    die('Error: Tipe file KTP tidak diizinkan. Hanya JPG, PNG, GIF, WEBP, PDF.');
                }
                $ext = pathinfo($_FILES['photo_ktp']['name'], PATHINFO_EXTENSION);
                $filename = 'ktp_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo_ktp']['tmp_name'], $uploadDir . 'ktp/' . $filename)) {
                    $photo_ktp = $filename;
                }
            }

            if ($photo_profile || $photo_ktp) {
                $this->customerModel->updatePhotos($id, $photo_profile, $photo_ktp);
            }

            if ($this->customerModel->update($data)) {
                $pppoeData = [
                    'customer_id' => $id,
                    'mikrotik_router_id' => $data['mikrotik_router_id'],
                    'username' => trim($_POST['pppoe_username']),
                    'password' => trim($_POST['pppoe_password']),
                    'profile' => 'default',
                    'service' => 'pppoe',
                    'status' => 'enabled'
                ];
                $pppoeSecretModel = $this->model('PppoeSecretModel');
                $existing = $pppoeSecretModel->getByCustomerId($id);
                if ($existing) {
                    $pppoeData['id'] = $existing->id;
                    $pppoeSecretModel->update($pppoeData);
                    
                    // Update PPPoE Secret on Mikrotik
                    $mikrotikService = new MikrotikService();
                    if ($mikrotikService->connect($data['mikrotik_router_id'])) {
                        $package = $this->model('PackageModel')->getById($data['package_id']);
                        $profile = $package ? $package->mikrotik_profile : 'default';
                        $mikrotikService->updatePppoeSecret(trim($_POST['pppoe_username']), trim($_POST['pppoe_password']), $profile);
                        $mikrotikService->disconnect();
                    }
                } else {
                    $pppoeSecretModel->create($pppoeData);
                    
                    // Add PPPoE Secret to Mikrotik
                    $mikrotikService = new MikrotikService();
                    if ($mikrotikService->connect($data['mikrotik_router_id'])) {
                        $package = $this->model('PackageModel')->getById($data['package_id']);
                        $profile = $package ? $package->mikrotik_profile : 'default';
                        $mikrotikService->addPppoeSecret(trim($_POST['pppoe_username']), trim($_POST['pppoe_password']), $profile);
                        $mikrotikService->disconnect();
                    }
                }

                $_SESSION['toast_success'] = 'Data pelanggan berhasil diperbarui';
                header('Location: ' . URLROOT . '/AdminCustomerController');
                exit;
            } else {
                die('Something went wrong.');
            }
        } else {
            header('Location: ' . URLROOT . '/AdminCustomerController/edit/' . $id);
            exit;
        }
    }

    public function show($id) {
        $customer = $this->customerModel->getById($id);
        
        if (!$customer) {
            header('Location: ' . URLROOT . '/AdminCustomerController');
            exit;
        }

        $pppoe = $this->model('PppoeSecretModel')->getByCustomerId($id);
        $package = $this->model('PackageModel')->getById($customer->package_id);
        $router = $this->model('MikrotikRouterModel')->getById($customer->mikrotik_router_id);

        $pppoe_status = ['status' => 'offline'];
        if ($pppoe && $router) {
            $mikrotikService = new MikrotikService();
            if ($mikrotikService->connect($router->id)) {
                $pppoe_status = $mikrotikService->getPppoeStatus($pppoe->username);
                $mikrotikService->disconnect();
            } else {
                $pppoe_status = ['status' => 'error'];
            }
        }

        $data = [
            'title' => 'Detail Pelanggan',
            'customer' => $customer,
            'pppoe' => $pppoe,
            'package_name' => $package ? $package->name : 'Unknown',
            'router_name' => $router ? $router->name : 'Unknown',
            'pppoe_status' => $pppoe_status
        ];
        
        $this->view('admin/customer/show', $data);
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $customer = $this->customerModel->getById($id);
            if ($customer) {
                // Delete files if they exist
                $uploadDir = APPROOT . '/public/uploads/customers/';
                if ($customer->photo_profile && file_exists($uploadDir . 'profile/' . $customer->photo_profile)) {
                    unlink($uploadDir . 'profile/' . $customer->photo_profile);
                }
                if ($customer->photo_ktp && file_exists($uploadDir . 'ktp/' . $customer->photo_ktp)) {
                    unlink($uploadDir . 'ktp/' . $customer->photo_ktp);
                }

                // Fetch PPPoE before deleting the customer (to avoid cascading delete losing the data)
                $pppoe = $this->model('PppoeSecretModel')->getByCustomerId($id);
                
                if ($this->customerModel->delete($id)) {
                    // Remove PPPoE Secret from Mikrotik
                    if ($pppoe) {
                        $mikrotikService = new MikrotikService();
                        if ($mikrotikService->connect($customer->mikrotik_router_id)) {
                            $mikrotikService->removePppoeSecret($pppoe->username);
                            $mikrotikService->disconnect();
                        }
                    }

                    $_SESSION['toast_success'] = 'Pelanggan berhasil dihapus';
                    header('Location: ' . URLROOT . '/AdminCustomerController');
                    exit;
                } else {
                    die('Something went wrong.');
                }
            }
        }
        header('Location: ' . URLROOT . '/AdminCustomerController');
        exit;
    }
}
