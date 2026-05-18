<?php
class PackageModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAll() {
        $this->db->query('SELECT * FROM packages ORDER BY price ASC');
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query('SELECT * FROM packages WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create($data) {
        $this->db->query('INSERT INTO packages (name, speed_download, speed_upload, price, mikrotik_profile, description, is_active, auto_isolate) VALUES (:name, :speed_download, :speed_upload, :price, :mikrotik_profile, :description, :is_active, :auto_isolate)');

        $this->db->bind(':name', $data['name']);
        $this->db->bind(':speed_download', $data['speed_download']);
        $this->db->bind(':speed_upload', $data['speed_upload']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':mikrotik_profile', $data['mikrotik_profile']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':is_active', $data['is_active']);
        $this->db->bind(':auto_isolate', $data['auto_isolate']);

        return $this->db->execute();
    }

    public function update($data) {
        $this->db->query('UPDATE packages SET name = :name, speed_download = :speed_download, speed_upload = :speed_upload, price = :price, mikrotik_profile = :mikrotik_profile, description = :description, is_active = :is_active, auto_isolate = :auto_isolate WHERE id = :id');

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':speed_download', $data['speed_download']);
        $this->db->bind(':speed_upload', $data['speed_upload']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':mikrotik_profile', $data['mikrotik_profile']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':is_active', $data['is_active']);
        $this->db->bind(':auto_isolate', $data['auto_isolate']);

        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM packages WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
