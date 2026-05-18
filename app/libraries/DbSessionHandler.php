<?php
class DbSessionHandler implements SessionHandlerInterface {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function open(string $path, string $name): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string|false {
        $this->db->query("SELECT payload FROM sessions WHERE id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();

        if ($row) {
            return $row->payload;
        }
        return ''; // Retun empty string, false will fail in some PHP 8+ strict modes
    }

    public function write(string $id, string $data): bool {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $time = time();

        $this->db->query("REPLACE INTO sessions (id, user_id, customer_id, ip_address, user_agent, payload, last_activity) 
                          VALUES (:id, :user_id, :customer_id, :ip_address, :user_agent, :payload, :last_activity)");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':customer_id', $customer_id);
        $this->db->bind(':ip_address', $ip_address);
        $this->db->bind(':user_agent', $user_agent);
        $this->db->bind(':payload', $data);
        $this->db->bind(':last_activity', $time);

        return $this->db->execute();
    }

    public function destroy(string $id): bool {
        $this->db->query("DELETE FROM sessions WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function gc(int $max_lifetime): int|false {
        $oldest = time() - $max_lifetime;
        $this->db->query("DELETE FROM sessions WHERE last_activity < :oldest");
        $this->db->bind(':oldest', $oldest);
        
        if ($this->db->execute()) {
            return true; // Wait, gc return int|false in php8? Returning true is casted to 1
        }
        return false;
    }
}
