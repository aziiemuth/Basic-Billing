<?php
class ReportModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getSummary($month = null, $year = null) {
        $sqlPemasukan = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'success'";
        $sqlTunggakan = "SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE status = 'unpaid'";

        if ($month && $year) {
            $sqlPemasukan .= " AND MONTH(updated_at) = :month AND YEAR(updated_at) = :year";
            $sqlTunggakan .= " AND SUBSTRING(billing_month, 6, 2) = :month AND SUBSTRING(billing_month, 1, 4) = :year";
        }

        // Pemasukan
        $this->db->query($sqlPemasukan);
        if ($month && $year) {
            $this->db->bind(':month', $month);
            $this->db->bind(':year', $year);
        }
        $pemasukan = $this->db->single()->total;

        // Tunggakan
        $this->db->query($sqlTunggakan);
        if ($month && $year) {
            $this->db->bind(':month', str_pad($month, 2, '0', STR_PAD_LEFT));
            $this->db->bind(':year', $year);
        }
        $tunggakan = $this->db->single()->total;
        
        // Count pelanggan lunas vs belum
        $sqlLunas = "SELECT COUNT(*) as count FROM invoices WHERE status = 'paid'";
        $sqlBelum = "SELECT COUNT(*) as count FROM invoices WHERE status = 'unpaid'";
        
        if ($month && $year) {
            $sqlLunas .= " AND SUBSTRING(billing_month, 6, 2) = :month AND SUBSTRING(billing_month, 1, 4) = :year";
            $sqlBelum .= " AND SUBSTRING(billing_month, 6, 2) = :month AND SUBSTRING(billing_month, 1, 4) = :year";
        }
        
        $this->db->query($sqlLunas);
        if ($month && $year) {
            $this->db->bind(':month', str_pad($month, 2, '0', STR_PAD_LEFT));
            $this->db->bind(':year', $year);
        }
        $pelangganLunas = $this->db->single()->count;
        
        $this->db->query($sqlBelum);
        if ($month && $year) {
            $this->db->bind(':month', str_pad($month, 2, '0', STR_PAD_LEFT));
            $this->db->bind(':year', $year);
        }
        $pelangganBelum = $this->db->single()->count;

        return [
            'pemasukan' => $pemasukan,
            'tunggakan' => $tunggakan,
            'pelanggan_lunas' => $pelangganLunas,
            'pelanggan_belum' => $pelangganBelum
        ];
    }

    public function getCashflow($month = null, $year = null) {
        $sql = "SELECT p.*, i.invoice_number, i.billing_month, c.name as customer_name, pk.name as package_name 
                FROM payments p
                JOIN invoices i ON p.invoice_id = i.id
                JOIN customers c ON i.customer_id = c.id
                JOIN packages pk ON i.package_id = pk.id
                WHERE p.status = 'success'";
                
        if ($month && $year) {
            $sql .= " AND MONTH(p.updated_at) = :month AND YEAR(p.updated_at) = :year";
        }
        
        $sql .= " ORDER BY p.updated_at DESC";
        
        $this->db->query($sql);
        
        if ($month && $year) {
            $this->db->bind(':month', $month);
            $this->db->bind(':year', $year);
        }
        
        return $this->db->resultSet();
    }

    public function getMonthlyIncomeTrend($year) {
        $sql = "SELECT MONTH(updated_at) as month, SUM(amount) as total 
                FROM payments 
                WHERE status = 'success' AND YEAR(updated_at) = :year 
                GROUP BY MONTH(updated_at) 
                ORDER BY MONTH(updated_at) ASC";
        $this->db->query($sql);
        $this->db->bind(':year', $year);
        return $this->db->resultSet();
    }

    public function getPaymentMethodsSummary($month, $year) {
        $sql = "SELECT COALESCE(payment_method, 'manual') as method, COUNT(*) as count, SUM(amount) as total 
                FROM payments 
                WHERE status = 'success' AND MONTH(updated_at) = :month AND YEAR(updated_at) = :year 
                GROUP BY payment_method";
        $this->db->query($sql);
        $this->db->bind(':month', $month);
        $this->db->bind(':year', $year);
        return $this->db->resultSet();
    }

    public function getCustomerGrowthSummary() {
        $sql = "SELECT status, COUNT(*) as count FROM customers GROUP BY status";
        $this->db->query($sql);
        return $this->db->resultSet();
    }
}
