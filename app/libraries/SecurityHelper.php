<?php
class SecurityHelper {
    
    /**
     * ======================================
     * 1. CSRF PROTECTION
     * ======================================
     */
    
    // Generate CSRF Token
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Buat input hidden untuk form
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    // Validasi token saat POST
    public static function validateCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                die('CSRF token validation failed. Deteksi kemungkinan serangan (Cross-Site Request Forgery).');
            }
        }
    }

    /**
     * ======================================
     * 2. XSS PROTECTION (Sanitasi)
     * ======================================
     */
     
    // Sanitasi semua input secara rekursif
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
        } else {
            $data = htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }

    /**
     * ======================================
     * 3. LOGIN RATE LIMITER (Brute Force Protection)
     * ======================================
     */
     
    // Cek apakah IP/User terkena limit
    public static function checkLoginRateLimit($identifier, $maxAttempts = 5, $lockoutTime = 300) { // 300 sec = 5 menit
        $ip = $_SERVER['REMOTE_ADDR'];
        $sessionKey = 'login_attempts_' . md5($ip . $identifier);
        $lockKey = 'login_lockout_' . md5($ip . $identifier);

        // Jika sedang di-lock
        if (isset($_SESSION[$lockKey])) {
            $remaining = $_SESSION[$lockKey] - time();
            if ($remaining > 0) {
                return [
                    'allowed' => false,
                    'message' => 'Terlalu banyak percobaan gagal. Silakan coba lagi dalam ' . ceil($remaining / 60) . ' menit.'
                ];
            } else {
                // Hapus lock jika waktu sudah habis
                unset($_SESSION[$lockKey]);
                unset($_SESSION[$sessionKey]);
            }
        }
        
        return ['allowed' => true];
    }

    // Catat kegagalan login
    public static function recordFailedLogin($identifier, $maxAttempts = 5, $lockoutTime = 300) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $sessionKey = 'login_attempts_' . md5($ip . $identifier);
        $lockKey = 'login_lockout_' . md5($ip . $identifier);

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = 1;
        } else {
            $_SESSION[$sessionKey]++;
        }

        // Jika melebihi batas, kunci (lock)
        if ($_SESSION[$sessionKey] >= $maxAttempts) {
            $_SESSION[$lockKey] = time() + $lockoutTime;
        }
    }

    // Hapus histori kegagalan (jika berhasil login)
    public static function clearLoginAttempts($identifier) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $sessionKey = 'login_attempts_' . md5($ip . $identifier);
        $lockKey = 'login_lockout_' . md5($ip . $identifier);
        
        unset($_SESSION[$sessionKey]);
        unset($_SESSION[$lockKey]);
    }
}
