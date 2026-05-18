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
}
