<?php
class AdminDashboardController extends Controller {
    public function __construct() {
        // Apply Middleware
        AuthAdminMiddleware::check();
    }

    public function index() {
        $dashboardModel = $this->model('DashboardModel');

        $customerStats = $dashboardModel->getCustomerStats();
        $unpaidInvoices = $dashboardModel->getInvoiceStats();
        $revenueThisMonth = $dashboardModel->getRevenueThisMonth();
        $chartData = $dashboardModel->getPaymentChartData();
        $routers = $dashboardModel->getRouters();

        $data = [
            'title' => 'Admin Dashboard',
            'customerStats' => $customerStats,
            'unpaidInvoices' => $unpaidInvoices,
            'revenueThisMonth' => $revenueThisMonth,
            'chartData' => $chartData,
            'routers' => $routers
        ];
        $this->view('admin/dashboard', $data);
    }
}
