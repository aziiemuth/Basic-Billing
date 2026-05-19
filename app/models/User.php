<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Find user by username
    public function findUserByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);

        $row = $this->db->single();

        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Login user
    public function login($username, $password) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);

        $row = $this->db->single();

        if ($row) {
            $hashed_password = $row->password;
            if (password_verify($password, $hashed_password)) {
                return $row;
            }
        }
        return false;
    }

    public function getAll() {
        $this->db->query('SELECT id, name, username, role, last_login, created_at, updated_at FROM users ORDER BY id ASC');
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query('SELECT id, name, username, role, last_login, created_at, updated_at FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function usernameExists($username, $excludeId = null) {
        $sql = 'SELECT id FROM users WHERE username = :username';
        if ($excludeId) {
            $sql .= ' AND id != :id';
        }
        $this->db->query($sql);
        $this->db->bind(':username', $username);
        if ($excludeId) {
            $this->db->bind(':id', $excludeId);
        }
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function create($data) {
        $this->db->query('INSERT INTO users (name, username, password, role) VALUES (:name, :username, :password, :role)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':role', $data['role']);
        return $this->db->execute();
    }

    public function update($data) {
        if (!empty($data['password'])) {
            $this->db->query('UPDATE users SET name = :name, username = :username, password = :password, role = :role WHERE id = :id');
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        } else {
            $this->db->query('UPDATE users SET name = :name, username = :username, role = :role WHERE id = :id');
        }

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':role', $data['role']);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function touchLastLogin($id) {
        $this->db->query('UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
