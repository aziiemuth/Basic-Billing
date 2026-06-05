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

        // Fetch live MikroTik Status and active PPPoE count
        require_once APPROOT . '/app/libraries/MikrotikService.php';
        $mikrotikService = new MikrotikService();
        
        foreach ($routers as &$router) {
            $router->is_online = false;
            $router->active_pppoe_count = 0;
            
            if ($router->is_active) {
                if ($mikrotikService->connect($router->id)) {
                    $router->is_online = true;
                    $activeSessions = $mikrotikService->getAllActiveSessions();
                    $router->active_pppoe_count = is_array($activeSessions) ? count($activeSessions) : 0;
                    $mikrotikService->disconnect();
                }
            }
        }

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

    public function ajaxData() {
        $dashboardModel = $this->model('DashboardModel');

        $customerStats = $dashboardModel->getCustomerStats();
        $unpaidInvoices = $dashboardModel->getInvoiceStats();
        $revenueThisMonth = $dashboardModel->getRevenueThisMonth();
        $routers = $dashboardModel->getRouters();

        require_once APPROOT . '/app/libraries/MikrotikService.php';
        $mikrotikService = new MikrotikService();
        
        $routersData = [];
        foreach ($routers as $router) {
            $routerInfo = [
                'name' => $router->name,
                'host_ip' => $router->host_ip,
                'is_active' => $router->is_active,
                'is_online' => false,
                'active_pppoe_count' => 0
            ];
            
            if ($router->is_active) {
                if ($mikrotikService->connect($router->id)) {
                    $routerInfo['is_online'] = true;
                    $activeSessions = $mikrotikService->getAllActiveSessions();
                    $routerInfo['active_pppoe_count'] = is_array($activeSessions) ? count($activeSessions) : 0;
                    $mikrotikService->disconnect();
                }
            }
            $routersData[] = $routerInfo;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'customerStats' => $customerStats,
            'unpaidInvoices' => $unpaidInvoices,
            'revenueThisMonth' => number_format($revenueThisMonth, 0, ',', '.'),
            'routers' => $routersData
        ]);
        exit;
    }
}
