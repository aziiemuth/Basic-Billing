<?php

class AuthAdminMiddleware {
    public static function check() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
            header('Location: ' . URLROOT . '/AdminAuthController/login');
            exit;
        }
    }

    public static function redirectIfAuthenticated() {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['role'] === 'admin') {
            header('Location: ' . URLROOT . '/AdminDashboardController');
            exit;
        }
    }
}
