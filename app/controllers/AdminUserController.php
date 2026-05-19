<?php
class AdminUserController extends Controller {
    private $userModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->userModel = $this->model('User');
    }

    public function index() {
        $this->view('admin/user/index', [
            'title' => 'Kelola User Login',
            'users' => $this->userModel->getAll()
        ]);
    }

    public function create() {
        $this->view('admin/user/create', ['title' => 'Tambah User Login']);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/AdminUserController/create');
            exit;
        }

        $username = trim($_POST['username']);
        if ($this->userModel->usernameExists($username)) {
            $_SESSION['toast_error'] = 'Username sudah digunakan';
            header('Location: ' . URLROOT . '/AdminUserController/create');
            exit;
        }

        $this->userModel->create([
            'name' => trim($_POST['name']),
            'username' => $username,
            'password' => trim($_POST['password']),
            'role' => 'admin'
        ]);

        $_SESSION['toast_success'] = 'User login berhasil ditambahkan';
        header('Location: ' . URLROOT . '/AdminUserController');
        exit;
    }

    public function edit($id) {
        $user = $this->userModel->getById($id);
        if (!$user) {
            header('Location: ' . URLROOT . '/AdminUserController');
            exit;
        }

        $this->view('admin/user/edit', [
            'title' => 'Edit User Login',
            'user' => $user
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/AdminUserController/edit/' . $id);
            exit;
        }

        $username = trim($_POST['username']);
        if ($this->userModel->usernameExists($username, $id)) {
            $_SESSION['toast_error'] = 'Username sudah digunakan';
            header('Location: ' . URLROOT . '/AdminUserController/edit/' . $id);
            exit;
        }

        $this->userModel->update([
            'id' => $id,
            'name' => trim($_POST['name']),
            'username' => $username,
            'password' => trim($_POST['password'] ?? ''),
            'role' => 'admin'
        ]);

        $_SESSION['toast_success'] = 'User login berhasil diperbarui';
        header('Location: ' . URLROOT . '/AdminUserController');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ((int)$id === (int)($_SESSION['user_id'] ?? 0)) {
                $_SESSION['toast_error'] = 'User yang sedang login tidak bisa dihapus';
            } else {
                $this->userModel->delete($id);
                $_SESSION['toast_success'] = 'User login berhasil dihapus';
            }
        }
        header('Location: ' . URLROOT . '/AdminUserController');
        exit;
    }
}
