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
}
