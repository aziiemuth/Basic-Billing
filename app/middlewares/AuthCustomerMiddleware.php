<?php

class AuthCustomerMiddleware {
    public static function check() {
        if (!isset($_SESSION['customer_logged_in']) || $_SESSION['role'] !== 'customer') {
            header('Location: ' . URLROOT . '/CustomerAuthController/login');
            exit;
        }
    }

    public static function redirectIfAuthenticated() {
        if (isset($_SESSION['customer_logged_in']) && $_SESSION['role'] === 'customer') {
            header('Location: ' . URLROOT . '/CustomerDashboardController');
            exit;
        }
    }
}
