<?php
class CustomerAuthController extends Controller {
    private $customerModel;

    public function __construct() {
        $this->customerModel = $this->model('CustomerModel');
    }

    public function login() {
        AuthCustomerMiddleware::redirectIfAuthenticated();

        // Check for POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            SecurityHelper::validateCsrf();
            
            // Process form
            $identifier = trim($_POST['identifier']); 
            
            // Cek batasan limit brute force
            $limitCheck = SecurityHelper::checkLoginRateLimit($identifier);
            if (!$limitCheck['allowed']) {
                $this->view('auth/customer-login', ['identifier' => $identifier, 'error' => $limitCheck['message']]);
                return;
            }

            $password = trim($_POST['password']);

            $loggedInCustomer = $this->customerModel->login($identifier, $password);

            if ($loggedInCustomer) {
                // Bersihkan record kegagalan
                SecurityHelper::clearLoginAttempts($identifier);
                
                // Create session
                $this->createCustomerSession($loggedInCustomer);
            } else {
                SecurityHelper::recordFailedLogin($identifier);
                // Return to login with error
                $data = [
                    'identifier' => $identifier,
                    'error' => 'ID Pelanggan atau Password salah'
                ];
                $this->view('auth/customer-login', $data);
            }
        } else {
            // Init data
            $data = [
                'identifier' => '',
                'error' => ''
            ];
            // Load view
            $this->view('auth/customer-login', $data);
        }
    }

    public function createCustomerSession($customer) {
        $_SESSION['customer_logged_in'] = true;
        $_SESSION['customer_id'] = $customer->id;
        $_SESSION['customer_code'] = $customer->customer_id;
        $_SESSION['customer_name'] = $customer->name;
        $_SESSION['role'] = 'customer';
        
        // Catat log aktivitas login
        $db = new Database();
        $db->query("INSERT INTO customer_logs (customer_id, action, description) VALUES (:customer_id, 'login', 'Berhasil login ke Customer Portal')");
        $db->bind(':customer_id', $customer->id);
        $db->execute();
        
        header('Location: ' . URLROOT . '/CustomerDashboardController');
    }

    public function logout() {
        if (isset($_SESSION['customer_id'])) {
            $db = new Database();
            $db->query("INSERT INTO customer_logs (customer_id, action, description) VALUES (:customer_id, 'logout', 'Keluar dari Customer Portal')");
            $db->bind(':customer_id', $_SESSION['customer_id']);
            $db->execute();
        }

        unset($_SESSION['customer_logged_in']);
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_code']);
        unset($_SESSION['customer_name']);
        unset($_SESSION['role']);
        session_destroy();
        header('Location: ' . URLROOT . '/CustomerAuthController/login');
    }
}
