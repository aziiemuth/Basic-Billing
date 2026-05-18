<?php
class PppoeSecretModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function create($data) {
        $this->db->query('INSERT INTO pppoe_secrets (customer_id, mikrotik_router_id, username, password, profile, service, status) VALUES (:customer_id, :mikrotik_router_id, :username, :password, :profile, :service, :status)');
        
        $this->db->bind(':customer_id', $data['customer_id']);
        $this->db->bind(':mikrotik_router_id', $data['mikrotik_router_id']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':profile', $data['profile']);
        $this->db->bind(':service', $data['service'] ?? 'pppoe');
        $this->db->bind(':status', $data['status'] ?? 'enabled');

        return $this->db->execute();
    }

    public function update($data) {
        $this->db->query('UPDATE pppoe_secrets SET mikrotik_router_id = :mikrotik_router_id, username = :username, password = :password, profile = :profile, service = :service, status = :status WHERE id = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':mikrotik_router_id', $data['mikrotik_router_id']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':profile', $data['profile']);
        $this->db->bind(':service', $data['service'] ?? 'pppoe');
        $this->db->bind(':status', $data['status'] ?? 'enabled');

        return $this->db->execute();
    }

    public function updateByCustomerId($data) {
        $this->db->query('UPDATE pppoe_secrets SET mikrotik_router_id = :mikrotik_router_id, username = :username, password = :password, profile = :profile, service = :service, status = :status WHERE customer_id = :customer_id');
        
        $this->db->bind(':customer_id', $data['customer_id']);
        $this->db->bind(':mikrotik_router_id', $data['mikrotik_router_id']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':profile', $data['profile']);
        $this->db->bind(':service', $data['service'] ?? 'pppoe');
        $this->db->bind(':status', $data['status'] ?? 'enabled');

        return $this->db->execute();
    }

    public function getByCustomerId($customer_id) {
        $this->db->query('SELECT * FROM pppoe_secrets WHERE customer_id = :customer_id LIMIT 1');
        $this->db->bind(':customer_id', $customer_id);
        return $this->db->single();
    }

    public function getByUsername($username) {
        $this->db->query('SELECT * FROM pppoe_secrets WHERE username = :username LIMIT 1');
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    public function getByUsernameExcludingCustomer($username, $customer_id) {
        $this->db->query('SELECT * FROM pppoe_secrets WHERE username = :username AND customer_id != :customer_id LIMIT 1');
        $this->db->bind(':username', $username);
        $this->db->bind(':customer_id', $customer_id);
        return $this->db->single();
    }

    public function deleteByCustomerId($customer_id) {
        $this->db->query('DELETE FROM pppoe_secrets WHERE customer_id = :customer_id');
        $this->db->bind(':customer_id', $customer_id);
        return $this->db->execute();
    }
}
