<?php
class TransactionModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function recordPaymentSuccess($payment_id, $invoice_id, $amount, $payment_method = 'tunai') {
        $date = date('Y-m-d');
        
        // Coba cari payment method dari tabel payments jika tidak ditentukan sebagai tunai
        if ($payment_id) {
            $this->db->query("SELECT payment_method FROM payments WHERE id = :pid");
            $this->db->bind(':pid', $payment_id);
            $payData = $this->db->single();
            if ($payData && $payData->payment_method) {
                $payment_method = $payData->payment_method;
            }
        }
        
        $methodLabel = strtoupper($payment_method);
        $desc = "Pembayaran tagihan (Invoice ID: " . $invoice_id . ") - " . $methodLabel;
        
        // 1. Insert into transactions
        $this->db->query("INSERT INTO transactions (invoice_id, payment_id, type, amount, description, transaction_date) 
                          VALUES (:invoice_id, :payment_id, 'income', :amount, :description, :transaction_date)");
        $this->db->bind(':invoice_id', $invoice_id);
        $this->db->bind(':payment_id', $payment_id);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':description', $desc);
        $this->db->bind(':transaction_date', $date);
        
        if ($this->db->execute()) {
            $transaction_id = $this->db->lastInsertId();
            
            // 2. Get previous balance from cash_flows
            $this->db->query("SELECT balance FROM cash_flows ORDER BY id DESC LIMIT 1");
            $last_balance = $this->db->single();
            $current_balance = $last_balance ? $last_balance->balance : 0;
            
            $new_balance = $current_balance + $amount;
            
            // 3. Insert into cash_flows
            $this->db->query("INSERT INTO cash_flows (transaction_id, flow_type, amount, balance, description) 
                              VALUES (:transaction_id, 'in', :amount, :balance, :description)");
            $this->db->bind(':transaction_id', $transaction_id);
            $this->db->bind(':amount', $amount);
            $this->db->bind(':balance', $new_balance);
            $this->db->bind(':description', $desc);
            
            return $this->db->execute();
        }
        return false;
    }
}
