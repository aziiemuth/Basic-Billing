<?php
class AdminProfileController extends Controller {

    private $settingsModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->settingsModel = $this->model('SettingsModel');
    }

    /**
     * Halaman utama profile admin + status koneksi MikroTik + Pengaturan Sistem
     */
    public function index() {
        // Test koneksi MikroTik secara langsung saat halaman dimuat
        $mikrotikService = new MikrotikService();
        $mtResult        = $mikrotikService->testConnection(null); // dari config.php

        // Cek juga apakah ada router di database
        $routerModel = $this->model('MikrotikRouterModel');
        $dbRouters   = $routerModel->getAll();
        
        $settings = $this->settingsModel->getSettings();

        $data = [
            'title'      => 'Profil & Pengaturan Sistem',
            'mtResult'   => $mtResult,
            'dbRouters'  => $dbRouters,
            'settings'   => $settings,
            'adminName'  => $_SESSION['user_name']  ?? 'Administrator',
            'adminRole'  => $_SESSION['role']        ?? 'admin',
        ];

        $this->view('admin/profile/index', $data);
    }
    
    public function updateSettings() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $currentSettings = $this->settingsModel->getSettings();
            $logoName = $currentSettings->company_logo;

            // Handle logo file upload if present
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['company_logo']['tmp_name'];
                $fileName = $_FILES['company_logo']['name'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $fileMime = mime_content_type($fileTmpPath);
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (in_array($fileMime, $allowedMimes) && in_array($ext, $allowedExtensions)) {
                    $uploadDir = dirname(APPROOT) . '/public/uploads/logo/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Delete old logo file if exists and is not default
                    if (!empty($logoName) && file_exists($uploadDir . $logoName)) {
                        @unlink($uploadDir . $logoName);
                    }

                    $logoName = 'logo_' . time() . '.' . $ext;
                    move_uploaded_file($fileTmpPath, $uploadDir . $logoName);
                } else {
                    $_SESSION['flash_message'] = 'Tipe file logo tidak diizinkan. Hanya format JPG, JPEG, PNG, GIF, atau WEBP.';
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: ' . URLROOT . '/AdminProfileController');
                    exit;
                }
            }

            $data = [
                'company_name' => trim($_POST['company_name']),
                'company_logo' => $logoName,
                'company_address' => trim($_POST['company_address']),
                'company_whatsapp' => trim($_POST['company_whatsapp']),
                'company_email' => trim($_POST['company_email']),
                'invoice_footer' => trim($_POST['invoice_footer']),
                'timezone' => trim($_POST['timezone']),
                'currency_format' => trim($_POST['currency_format']),
                'auto_isolate' => isset($_POST['auto_isolate']) ? 1 : 0,
                'wa_reminder_days' => trim($_POST['wa_reminder_days']),
            ];
            
            if ($this->settingsModel->update($data)) {
                $_SESSION['flash_message'] = 'Pengaturan sistem berhasil diperbarui.';
                $_SESSION['flash_type'] = 'success';
            } else {
                $_SESSION['flash_message'] = 'Gagal memperbarui pengaturan sistem.';
                $_SESSION['flash_type'] = 'danger';
            }
            header('Location: ' . URLROOT . '/AdminProfileController');
            exit;
        }
    }


    /**
     * AJAX — Re-test koneksi MikroTik dari config
     */
    public function testMikrotik() {
        header('Content-Type: application/json');

        $mikrotikService = new MikrotikService();
        $result          = $mikrotikService->testConnection(null);

        // Tambahkan timestamp
        $result['checked_at'] = date('d M Y, H:i:s');

        echo json_encode($result);
        exit;
    }

    /**
     * AJAX — Test koneksi ke router tertentu dari DB
     */
    public function testRouter($id) {
        header('Content-Type: application/json');

        $mikrotikService = new MikrotikService();
        $result          = $mikrotikService->testConnection($id);
        $result['checked_at'] = date('d M Y, H:i:s');

        echo json_encode($result);
        exit;
    }
}
