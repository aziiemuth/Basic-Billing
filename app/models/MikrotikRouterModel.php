<?php
class MikrotikRouterModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAll() {
        $this->db->query('SELECT * FROM mikrotik_routers ORDER BY name ASC');
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query('SELECT * FROM mikrotik_routers WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create($data) {
        $this->db->query('INSERT INTO mikrotik_routers (name, host_ip, api_username, api_password, api_port, pppoe_interface, description, is_active) VALUES (:name, :host_ip, :api_username, :api_password, :api_port, :pppoe_interface, :description, :is_active)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':host_ip', $data['host_ip']);
        $this->db->bind(':api_username', $data['api_username']);
        $this->db->bind(':api_password', $data['api_password']);
        $this->db->bind(':api_port', $data['api_port']);
        $this->db->bind(':pppoe_interface', $data['pppoe_interface']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':is_active', $data['is_active']);
        
        return $this->db->execute();
    }

    public function update($data) {
        if (!empty($data['api_password'])) {
            $this->db->query('UPDATE mikrotik_routers SET name = :name, host_ip = :host_ip, api_username = :api_username, api_password = :api_password, api_port = :api_port, pppoe_interface = :pppoe_interface, description = :description, is_active = :is_active WHERE id = :id');
            $this->db->bind(':api_password', $data['api_password']);
        } else {
            $this->db->query('UPDATE mikrotik_routers SET name = :name, host_ip = :host_ip, api_username = :api_username, api_port = :api_port, pppoe_interface = :pppoe_interface, description = :description, is_active = :is_active WHERE id = :id');
        }

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':host_ip', $data['host_ip']);
        $this->db->bind(':api_username', $data['api_username']);
        $this->db->bind(':api_port', $data['api_port']);
        $this->db->bind(':pppoe_interface', $data['pppoe_interface']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':is_active', $data['is_active']);
        
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM mikrotik_routers WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
