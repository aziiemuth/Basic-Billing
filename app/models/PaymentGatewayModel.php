<?php
class PaymentGatewayModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAll() {
        $this->db->query("SELECT * FROM payment_gateways ORDER BY id ASC");
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM payment_gateways WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getActive($name = null) {
        $sql = "SELECT * FROM payment_gateways WHERE is_active = 1";
        if ($name) {
            $sql .= " AND name = :name";
        }
        $sql .= " ORDER BY id ASC LIMIT 1";

        $this->db->query($sql);
        if ($name) {
            $this->db->bind(':name', $name);
        }
        return $this->db->single();
    }

    public function create($data) {
        $this->db->query("INSERT INTO payment_gateways (name, is_active, server_key, client_key, mode) VALUES (:name, :is_active, :server_key, :client_key, :mode)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':is_active', $data['is_active']);
        $this->db->bind(':server_key', $data['server_key']);
        $this->db->bind(':client_key', $data['client_key']);
        $this->db->bind(':mode', $data['mode']);
        return $this->db->execute();
    }

    public function update($data) {
        $this->db->query("UPDATE payment_gateways SET name = :name, is_active = :is_active, server_key = :server_key, client_key = :client_key, mode = :mode WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':is_active', $data['is_active']);
        $this->db->bind(':server_key', $data['server_key']);
        $this->db->bind(':client_key', $data['client_key']);
        $this->db->bind(':mode', $data['mode']);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM payment_gateways WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
