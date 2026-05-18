<?php
class DashboardModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getCustomerStats() {
        $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN status = 'isolated' THEN 1 ELSE 0 END) as isolated
            FROM customers
        ");
        return $this->db->single();
    }

    public function getInvoiceStats() {
        $this->db->query("SELECT COUNT(*) as unpaid_count FROM invoices WHERE status = 'unpaid'");
        return $this->db->single()->unpaid_count;
    }

    public function getRevenueThisMonth() {
        $this->db->query("
            SELECT COALESCE(SUM(amount), 0) as total_revenue 
            FROM payments 
            WHERE status = 'success' 
            AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ");
        return $this->db->single()->total_revenue;
    }

    public function getPaymentChartData() {
        // Get revenue for the last 6 months
        $this->db->query("
            SELECT 
                DATE_FORMAT(created_at, '%b') as month_name,
                MONTH(created_at) as month_num,
                SUM(amount) as total
            FROM payments
            WHERE status = 'success' 
            AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
            ORDER BY YEAR(created_at) ASC, MONTH(created_at) ASC
        ");
        $results = $this->db->resultSet();
        
        $labels = [];
        $data = [];
        
        foreach($results as $row) {
            $labels[] = $row->month_name;
            $data[] = (float) $row->total;
        }

        // If no data, return empty structures
        if (empty($labels)) {
            $labels = [date('M')];
            $data = [0];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    public function getRouters() {
        $this->db->query("SELECT * FROM mikrotik_routers");
        return $this->db->resultSet();
    }
}
