<?php
class InvoiceModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getInvoicesByCustomerId($customerId) {
        $this->db->query("SELECT * FROM invoices WHERE customer_id = :customer_id ORDER BY created_at DESC");
        $this->db->bind(':customer_id', $customerId);
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM invoices WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getByIdWithDetails($id) {
        $this->db->query("SELECT i.*, c.name AS customer_name, c.customer_id AS customer_code, c.whatsapp, c.address, pk.name AS package_name
                          FROM invoices i
                          JOIN customers c ON i.customer_id = c.id
                          LEFT JOIN packages pk ON i.package_id = pk.id
                          WHERE i.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getItems($invoiceId) {
        $this->db->query("SELECT * FROM invoice_items WHERE invoice_id = :invoice_id ORDER BY id ASC");
        $this->db->bind(':invoice_id', $invoiceId);
        return $this->db->resultSet();
    }

    public function getAllWithDetails($status = 'all') {
        $sql = "SELECT i.*, c.name AS customer_name, c.customer_id AS customer_code, pk.name AS package_name
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                LEFT JOIN packages pk ON i.package_id = pk.id";
        if ($status !== 'all') {
            $sql .= " WHERE i.status = :status";
        }
        $sql .= " ORDER BY i.created_at DESC";

        $this->db->query($sql);
        if ($status !== 'all') {
            $this->db->bind(':status', $status);
        }
        return $this->db->resultSet();
    }

    public function getByInvoiceNumber($invoiceNumber) {
        $this->db->query("SELECT * FROM invoices WHERE invoice_number = :invoice_number");
        $this->db->bind(':invoice_number', $invoiceNumber);
        return $this->db->single();
    }

    public function updateStatus($id, $status) {
        // Satu query dinamis — tambahkan paid_at hanya saat status = 'paid'
        $paidAtClause = ($status === 'paid') ? ', paid_at = CURRENT_TIMESTAMP' : '';
        
        $sql = "UPDATE invoices 
                SET status = :status{$paidAtClause}, updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        
        $this->db->query($sql);
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function createInvoice($data) {
        $this->db->query("INSERT INTO invoices (invoice_number, customer_id, package_id, billing_month, amount, discount, total_amount, issue_date, due_date, status) VALUES (:invoice_number, :customer_id, :package_id, :billing_month, :amount, :discount, :total_amount, :issue_date, :due_date, :status)");
        $this->db->bind(':invoice_number', $data['invoice_number']);
        $this->db->bind(':customer_id', $data['customer_id']);
        $this->db->bind(':package_id', $data['package_id']);
        $this->db->bind(':billing_month', $data['billing_month']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':discount', isset($data['discount']) ? $data['discount'] : 0);
        $this->db->bind(':total_amount', $data['total_amount']);
        $this->db->bind(':issue_date', $data['issue_date']);
        $this->db->bind(':due_date', $data['due_date']);
        $this->db->bind(':status', isset($data['status']) ? $data['status'] : 'unpaid');
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function createInvoiceItem($data) {
        $this->db->query("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price) VALUES (:invoice_id, :description, :quantity, :unit_price, :total_price)");
        $this->db->bind(':invoice_id', $data['invoice_id']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':quantity', isset($data['quantity']) ? $data['quantity'] : 1);
        $this->db->bind(':unit_price', $data['unit_price']);
        $this->db->bind(':total_price', $data['total_price']);
        
        return $this->db->execute();
    }

    public function checkInvoiceExists($customer_id, $billing_month) {
        $this->db->query("SELECT id FROM invoices WHERE customer_id = :customer_id AND billing_month = :billing_month LIMIT 1");
        $this->db->bind(':customer_id', $customer_id);
        $this->db->bind(':billing_month', $billing_month);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function getFilteredInvoices($filters = []) {
        $sql = "SELECT i.*, c.name AS customer_name, c.customer_id AS customer_code, pk.name AS package_name
                FROM invoices i
                JOIN customers c ON i.customer_id = c.id
                LEFT JOIN packages pk ON i.package_id = pk.id
                WHERE 1=1";
        
        $binds = [];
        
        if (!empty($filters['billing_month'])) {
            $sql .= " AND i.billing_month = :billing_month";
            $binds[':billing_month'] = $filters['billing_month'];
        }
        if (!empty($filters['customer_id']) && $filters['customer_id'] !== 'all') {
            $sql .= " AND i.customer_id = :customer_id";
            $binds[':customer_id'] = $filters['customer_id'];
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND i.status = :status";
            $binds[':status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        $this->db->query($sql);
        foreach ($binds as $param => $val) {
            $this->db->bind($param, $val);
        }
        return $this->db->resultSet();
    }

    /**
     * Ambil semua pelanggan aktif & isolated beserta info invoice unpaid terbarunya.
     * Jika tidak ada invoice unpaid, invoice_id / id bernilai null (belum digenerate).
     */
    public function getUnpaidInvoicesForManual() {
        $sql = "SELECT c.id AS customer_id_db,
                       c.name AS customer_name,
                       c.customer_id AS customer_code,
                       c.whatsapp,
                       c.status AS customer_status,
                       c.custom_price,
                       c.due_date AS customer_due_day,
                       pk.name AS package_name,
                       pk.id AS package_id,
                       pk.price AS package_price,
                       i.id AS id,
                       i.invoice_number,
                       i.total_amount,
                       i.due_date,
                       i.billing_month,
                       i.status AS invoice_status
                FROM customers c
                LEFT JOIN packages pk ON pk.id = c.package_id
                LEFT JOIN (
                    SELECT i1.*
                    FROM invoices i1
                    WHERE i1.status = 'unpaid'
                      AND i1.id = (
                          SELECT MAX(i2.id)
                          FROM invoices i2
                          WHERE i2.customer_id = i1.customer_id
                            AND i2.status = 'unpaid'
                      )
                ) i ON i.customer_id = c.id
                WHERE c.status IN ('active', 'isolated')
                ORDER BY (i.id IS NULL) ASC, i.due_date ASC, c.name ASC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }
}
