<?php

class AuthAdminMiddleware {
    public static function check() {
        if (!isset($_SESSION['admin_logged_in']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
            header('Location: ' . URLROOT . '/AdminAuthController/login');
            exit;
        }
    }

    public static function redirectIfAuthenticated() {
        if (isset($_SESSION['admin_logged_in']) && in_array($_SESSION['role'], ['admin', 'staff'])) {
            header('Location: ' . URLROOT . '/AdminDashboardController');
            exit;
        }
    }
}
