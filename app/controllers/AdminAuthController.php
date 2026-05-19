<?php
class AdminAuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function login() {
        AuthAdminMiddleware::redirectIfAuthenticated();

        // Check for POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            SecurityHelper::validateCsrf();
            
            // Process form
            $username = trim($_POST['username']);
            
            // Cek batasan limit brute force
            $limitCheck = SecurityHelper::checkLoginRateLimit($username);
            if (!$limitCheck['allowed']) {
                $this->view('auth/admin-login', ['username' => $username, 'error' => $limitCheck['message']]);
                return;
            }
            
            $password = trim($_POST['password']);

            $loggedInUser = $this->userModel->login($username, $password);

            if ($loggedInUser) {
                // Bersihkan record kegagalan
                SecurityHelper::clearLoginAttempts($username);
                $this->userModel->touchLastLogin($loggedInUser->id);
                // Create session
                $this->createUserSession($loggedInUser);
            } else {
                SecurityHelper::recordFailedLogin($username);
                // Return to login with error
                $data = [
                    'username' => $username,
                    'error' => 'Password atau username salah'
               ];
                $this->view('auth/admin-login', $data);
            }
        } else {
            // Init data
            $data = [
                'username' => '',
                'error' => ''
            ];
            // Load view
            $this->view('auth/admin-login', $data);
        }
    }

    public function createUserSession($user) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_username'] = $user->username;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['role'] = $user->role;
        
        header('Location: ' . URLROOT . '/AdminDashboardController');
    }

    public function logout() {
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['user_id']);
        unset($_SESSION['user_username']);
        unset($_SESSION['user_name']);
        unset($_SESSION['role']);
        session_destroy();
        header('Location: ' . URLROOT . '/AdminAuthController/login');
    }
}
