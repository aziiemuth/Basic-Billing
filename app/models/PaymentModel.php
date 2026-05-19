<?php
class PaymentModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function create($data) {
        $this->db->query("INSERT INTO payments (invoice_id, reference_id, amount, payment_gateway_id, payment_method, status, payment_url) VALUES (:invoice_id, :reference_id, :amount, :payment_gateway_id, :payment_method, :status, :payment_url)");
        $this->db->bind(':invoice_id', $data['invoice_id']);
        $this->db->bind(':reference_id', $data['reference_id'] ?? null);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':payment_gateway_id', $data['payment_gateway_id'] ?? null);
        $this->db->bind(':payment_method', $data['payment_method'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'pending');
        $this->db->bind(':payment_url', $data['payment_url'] ?? null);
        
        return $this->db->execute();
    }

    public function getByReferenceId($referenceId) {
        $this->db->query("SELECT * FROM payments WHERE reference_id = :reference_id");
        $this->db->bind(':reference_id', $referenceId);
        return $this->db->single();
    }
    
    public function getByInvoiceId($invoiceId) {
        $this->db->query("SELECT * FROM payments WHERE invoice_id = :invoice_id ORDER BY created_at DESC LIMIT 1");
        $this->db->bind(':invoice_id', $invoiceId);
        return $this->db->single();
    }

    public function getHistory($status = 'all') {
        $sql = "SELECT p.*, i.invoice_number, i.billing_month, i.due_date, c.name AS customer_name, c.customer_id AS customer_code, pg.name AS gateway_name
                FROM payments p
                JOIN invoices i ON p.invoice_id = i.id
                JOIN customers c ON i.customer_id = c.id
                LEFT JOIN payment_gateways pg ON p.payment_gateway_id = pg.id";
        if ($status !== 'all') {
            $sql .= " WHERE p.status = :status";
        }
        $sql .= " ORDER BY p.created_at DESC";

        $this->db->query($sql);
        if ($status !== 'all') {
            $this->db->bind(':status', $status);
        }
        return $this->db->resultSet();
    }

    public function updateWebhookStatus($referenceId, $status, $paymentMethod, $webhookResponse) {
        // Satu query dinamis — tambahkan paid_at hanya saat status = 'success'
        $paidAtClause = ($status === 'success') ? ', paid_at = CURRENT_TIMESTAMP' : '';
        
        $sql = "UPDATE payments 
                SET status = :status, 
                    payment_method = :payment_method, 
                    webhook_response = :webhook_response{$paidAtClause}, 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE reference_id = :reference_id";
        
        $this->db->query($sql);
        $this->db->bind(':status', $status);
        $this->db->bind(':payment_method', $paymentMethod);
        $this->db->bind(':webhook_response', $webhookResponse);
        $this->db->bind(':reference_id', $referenceId);
        
        return $this->db->execute();
    }

    public function getFilteredPayments($filters = []) {
        $sql = "SELECT p.*, i.invoice_number, i.billing_month, i.due_date, c.name AS customer_name, c.customer_id AS customer_code, pg.name AS gateway_name
                FROM payments p
                JOIN invoices i ON p.invoice_id = i.id
                JOIN customers c ON i.customer_id = c.id
                LEFT JOIN payment_gateways pg ON p.payment_gateway_id = pg.id
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
            $sql .= " AND p.status = :status";
            $binds[':status'] = $filters['status'];
        }
        if (!empty($filters['payment_method']) && $filters['payment_method'] !== 'all') {
            $sql .= " AND p.payment_method = :payment_method";
            $binds[':payment_method'] = $filters['payment_method'];
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $this->db->query($sql);
        foreach ($binds as $param => $val) {
            $this->db->bind($param, $val);
        }
        return $this->db->resultSet();
    }
}
