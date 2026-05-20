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

            $pppoe_username = isset($_POST['pppoe_username']) ? trim($_POST['pppoe_username']) : '';
            $mikrotik_router_id = trim($_POST['mikrotik_router_id']);

            // 1. Cek jika username PPPoE kosong
            if (empty($pppoe_username)) {
                $_SESSION['toast_error'] = 'Username PPPoE tidak boleh kosong!';
                header('Location: ' . URLROOT . '/AdminCustomerController/create');
                exit;
            }

            // 2. Cek duplikasi PPPoE Username di Database Lokal
            $pppoeModel = $this->model('PppoeSecretModel');
            if ($pppoeModel->getByUsername($pppoe_username)) {
                $_SESSION['toast_error'] = 'Gagal! Username PPPoE sudah terdaftar di database billing.';
                header('Location: ' . URLROOT . '/AdminCustomerController/create');
                exit;
            }

            // 3. Cek duplikasi PPPoE Username di Router MikroTik
            $mikrotikService = new MikrotikService();
            if ($mikrotikService->connect($mikrotik_router_id)) {
                $rawSecrets = $mikrotikService->getAllPppoeSecrets();
                $existsOnMikrotik = false;
                foreach ($rawSecrets as $s) {
                    if (isset($s['name']) && $s['name'] === $pppoe_username) {
                        $existsOnMikrotik = true;
                        break;
                    }
                }
                $mikrotikService->disconnect();

                if ($existsOnMikrotik) {
                    $_SESSION['toast_error'] = 'Gagal! Username PPPoE "' . htmlspecialchars($pppoe_username) . '" sudah digunakan di Router MikroTik Anda. Silakan gunakan nama lain.';
                    header('Location: ' . URLROOT . '/AdminCustomerController/create');
                    exit;
                }
            }

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
                'due_date' => (!empty($_POST['due_date']) || $_POST['due_date'] === '0') ? intval($_POST['due_date']) : 0,
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
                    $package = $this->model('PackageModel')->getById($data['package_id']);
                    $profileName = $package ? $package->mikrotik_profile : 'default';

                    $this->model('PppoeSecretModel')->create([
                        'customer_id' => $newCustomer->id,
                        'mikrotik_router_id' => $data['mikrotik_router_id'],
                        'username' => trim($_POST['pppoe_username']),
                        'password' => trim($_POST['pppoe_password']),
                        'profile' => $profileName,
                        'service' => 'pppoe',
                        'status' => 'enabled'
                    ]);

                    // Add PPPoE Secret to Mikrotik
                    $mikrotikService = new MikrotikService();
                    if ($mikrotikService->connect($data['mikrotik_router_id'])) {
                        $mikrotikService->addPppoeSecret(trim($_POST['pppoe_username']), trim($_POST['pppoe_password']), $profileName);
                        $mikrotikService->disconnect();
                    }

                    // --- GENERATE FIRST INVOICE ---
                    $amount = $data['custom_price'] ? $data['custom_price'] : ($package ? $package->price : 0);
                    if ($package && $amount > 0) {
                        $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                        $billing_month = date('Y-m');
                        $dueDay = !empty($data['due_date']) ? intval($data['due_date']) : 20;
                        $due_date = date('Y-m-') . str_pad($dueDay, 2, '0', STR_PAD_LEFT);
                        
                        $invoiceData = [
                            'invoice_number' => $invoice_number,
                            'customer_id' => $newCustomer->id,
                            'package_id' => $package->id,
                            'billing_month' => $billing_month,
                            'amount' => $amount,
                            'discount' => 0,
                            'total_amount' => $amount,
                            'issue_date' => date('Y-m-d'),
                            'due_date' => $due_date,
                            'status' => 'unpaid'
                        ];

                        $invoiceModel = $this->model('InvoiceModel');
                        $invoice_id = $invoiceModel->createInvoice($invoiceData);

                        if ($invoice_id) {
                            $itemData = [
                                'invoice_id' => $invoice_id,
                                'description' => 'Tagihan Internet Paket ' . $package->name . ' - Periode ' . date('F Y'),
                                'quantity' => 1,
                                'unit_price' => $amount,
                                'total_price' => $amount
                            ];
                            $invoiceModel->createInvoiceItem($itemData);
                        }
                    }

                    // --- SEND WHATSAPP NOTIFICATION ---
                    if (!empty($data['whatsapp'])) {
                        // 1. Kirim notifikasi aktivasi internet baru
                        require_once APPROOT . '/app/libraries/WhatsappService.php';
                        WhatsappService::sendActivated($newCustomer->id, $data['whatsapp'], $data['name'], $package ? $package->name : 'Internet');

                        // 2. Kirim notifikasi tagihan pertama
                        if (isset($invoice_number) && isset($amount) && isset($billing_month) && isset($due_date)) {
                            WhatsappService::sendNewInvoice($newCustomer->id, $data['whatsapp'], $data['name'], $invoice_number, $amount, $billing_month, $due_date);
                        }
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

            $pppoe_username = isset($_POST['pppoe_username']) ? trim($_POST['pppoe_username']) : '';
            $mikrotik_router_id = trim($_POST['mikrotik_router_id']);

            // 1. Cek jika username PPPoE kosong
            if (empty($pppoe_username)) {
                $_SESSION['toast_error'] = 'Username PPPoE tidak boleh kosong!';
                header('Location: ' . URLROOT . '/AdminCustomerController/edit/' . $id);
                exit;
            }

            // 2. Cek duplikasi PPPoE Username di Database Lokal (Kecuali user ini sendiri)
            $pppoeModel = $this->model('PppoeSecretModel');
            if ($pppoeModel->getByUsernameExcludingCustomer($pppoe_username, $id)) {
                $_SESSION['toast_error'] = 'Gagal! Username PPPoE sudah digunakan oleh pelanggan lain di database billing.';
                header('Location: ' . URLROOT . '/AdminCustomerController/edit/' . $id);
                exit;
            }

            // 3. Cek duplikasi PPPoE Username di Router MikroTik (Kecuali jika username tidak berubah)
            $existingPppoe = $pppoeModel->getByCustomerId($id);
            $hasUsernameChanged = (!$existingPppoe || $existingPppoe->username !== $pppoe_username);
            
            if ($hasUsernameChanged) {
                $mikrotikService = new MikrotikService();
                if ($mikrotikService->connect($mikrotik_router_id)) {
                    $rawSecrets = $mikrotikService->getAllPppoeSecrets();
                    $existsOnMikrotik = false;
                    foreach ($rawSecrets as $s) {
                        if (isset($s['name']) && $s['name'] === $pppoe_username) {
                            $existsOnMikrotik = true;
                            break;
                        }
                    }
                    $mikrotikService->disconnect();

                    if ($existsOnMikrotik) {
                        $_SESSION['toast_error'] = 'Gagal! Username PPPoE "' . htmlspecialchars($pppoe_username) . '" sudah digunakan oleh pengguna lain di Router MikroTik Anda. Silakan gunakan nama lain.';
                        header('Location: ' . URLROOT . '/AdminCustomerController/edit/' . $id);
                        exit;
                    }
                }
            }

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
                'due_date' => (!empty($_POST['due_date']) || $_POST['due_date'] === '0') ? intval($_POST['due_date']) : 0,
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

    public function importMikrotik() {
        $routerModel = $this->model('MikrotikRouterModel');
        $routers = $routerModel->getAll();
        
        $router_id = isset($_GET['router_id']) ? $_GET['router_id'] : null;
        if (!$router_id && count($routers) > 0) {
            $router_id = $routers[0]->id;
        }

        $secrets = [];
        $error = null;

        if ($router_id) {
            $mikrotikService = new MikrotikService();
            if ($mikrotikService->connect($router_id)) {
                $rawSecrets = $mikrotikService->getAllPppoeSecrets();
                
                // Get existing usernames to avoid duplication
                $db = new Database();
                $db->query("SELECT username FROM pppoe_secrets");
                $existing = $db->resultSet();
                $existingUsernames = [];
                foreach ($existing as $e) {
                    $existingUsernames[] = $e->username;
                }
                
                foreach ($rawSecrets as $s) {
                    if (isset($s['name']) && !in_array($s['name'], $existingUsernames)) {
                        $secrets[] = $s;
                    }
                }
            } else {
                $error = "Gagal terhubung ke MikroTik Router. Pastikan status router aktif.";
            }
        }

        $data = [
            'title' => 'Import Pelanggan dari MikroTik',
            'routers' => $routers,
            'router_id' => $router_id,
            'secrets' => $secrets,
            'error' => $error,
            'packages' => $this->model('PackageModel')->getAll()
        ];
        
        $this->view('admin/customer/import_mikrotik', $data);
    }

    public function storeImportMikrotik() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $router_id = isset($_POST['router_id']) ? $_POST['router_id'] : null;
            $selected_secrets = isset($_POST['secrets']) ? $_POST['secrets'] : [];
            $package_id = isset($_POST['package_id']) ? $_POST['package_id'] : '';
            
            if (empty($selected_secrets) || empty($router_id)) {
                die("Pilih minimal satu pelanggan yang ingin di-import.");
            }
            if (empty($package_id)) {
                $_SESSION['toast_error'] = "Gagal Import: Anda harus menentukan Paket Internet Default / Fallback. Jika belum ada, silakan Sinkronisasi Paket terlebih dahulu.";
                header('Location: ' . URLROOT . '/AdminCustomerController/importMikrotik?router_id=' . $router_id);
                exit;
            }
            
            $mikrotikService = new MikrotikService();
            if (!$mikrotikService->connect($router_id)) {
                die("Gagal terhubung ke router.");
            }
            
            $rawSecrets = $mikrotikService->getAllPppoeSecrets();
            $secretMap = [];
            foreach ($rawSecrets as $s) {
                if (isset($s['name'])) {
                    $secretMap[$s['name']] = $s;
                }
            }
            
            // Fetch packages for auto-matching
            $packageModel = $this->model('PackageModel');
            $allPackages = $packageModel->getAll();
            $packagesByProfile = [];
            foreach ($allPackages as $pkg) {
                $packagesByProfile[$pkg->mikrotik_profile] = $pkg->id;
            }
            
            $success = 0;
            foreach ($selected_secrets as $username) {
                if (isset($secretMap[$username])) {
                    $s = $secretMap[$username];
                    
                    $cid = $this->customerModel->generateCustomerId();
                    
                    // Auto match package
                    $matched_package_id = $package_id; // Default fallback
                    if (isset($s['profile']) && isset($packagesByProfile[$s['profile']])) {
                        $matched_package_id = $packagesByProfile[$s['profile']];
                    }
                    
                    // Create customer
                    $customerData = [
                        'customer_id' => $cid,
                        'name' => $username,
                        'whatsapp' => '',
                        'email' => null,
                        'username' => $username,
                        'password' => password_hash('123456', PASSWORD_DEFAULT),
                        'address' => 'Imported from MikroTik',
                        'latitude' => null,
                        'longitude' => null,
                        'package_id' => $matched_package_id,
                        'custom_price' => null,
                        'mikrotik_router_id' => $router_id,
                        'installation_date' => date('Y-m-d'),
                        'due_date' => 20, // default billing due date
                        'status' => (isset($s['disabled']) && $s['disabled'] == 'true') ? 'isolated' : 'active',
                        'photo_profile' => null,
                        'photo_ktp' => null
                    ];
                    
                    if ($this->customerModel->create($customerData)) {
                        $newCustomer = $this->customerModel->getByCustomerIdString($cid);
                        if ($newCustomer) {
                            $this->model('PppoeSecretModel')->create([
                                'customer_id' => $newCustomer->id,
                                'mikrotik_router_id' => $router_id,
                                'username' => $username,
                                'password' => isset($s['password']) ? $s['password'] : '',
                                'profile' => isset($s['profile']) ? $s['profile'] : 'default',
                                'service' => isset($s['service']) ? $s['service'] : 'pppoe',
                                'status' => (isset($s['disabled']) && $s['disabled'] == 'true') ? 'disabled' : 'enabled'
                            ]);
                            $success++;
                        }
                    }
                }
            }
            
            $_SESSION['toast_success'] = "Berhasil mengimport $success pelanggan dari MikroTik.";
            header('Location: ' . URLROOT . '/AdminCustomerController');
            exit;
        }
    }
}
