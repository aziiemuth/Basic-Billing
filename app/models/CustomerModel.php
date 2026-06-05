<?php
class CustomerModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Login customer
    public function login($customer_id_or_username, $password) {
        $this->db->query('SELECT * FROM customers WHERE customer_id = :identifier OR username = :identifier');
        $this->db->bind(':identifier', $customer_id_or_username);

        $row = $this->db->single();

        if ($row) {
            $hashed_password = $row->password;
            // Assuming password uses password_hash
            if (password_verify($password, $hashed_password)) {
                return $row;
            }
        }
        return false;
    }

    public function getAll() {
        $this->db->query('SELECT * FROM customers ORDER BY created_at DESC');
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query('SELECT * FROM customers WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getByCustomerIdString($customer_id) {
        $this->db->query('SELECT * FROM customers WHERE customer_id = :customer_id');
        $this->db->bind(':customer_id', $customer_id);
        return $this->db->single();
    }

    public function generateCustomerId() {
        $this->db->query('SELECT customer_id FROM customers ORDER BY id DESC LIMIT 1');
        $row = $this->db->single();
        if (!$row) {
            return 'CUST-0001';
        }
        $lastId = $row->customer_id;
        $num = (int) substr($lastId, 5);
        $num++;
        return 'CUST-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function create($data) {
        $this->db->query('INSERT INTO customers (customer_id, name, whatsapp, email, username, password, address, latitude, longitude, package_id, custom_price, mikrotik_router_id, installation_date, due_date, status, photo_profile, photo_ktp) VALUES (:customer_id, :name, :whatsapp, :email, :username, :password, :address, :latitude, :longitude, :package_id, :custom_price, :mikrotik_router_id, :installation_date, :due_date, :status, :photo_profile, :photo_ktp)');

        $this->db->bind(':customer_id', $data['customer_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':whatsapp', $data['whatsapp']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':latitude', $data['latitude']);
        $this->db->bind(':longitude', $data['longitude']);
        $this->db->bind(':package_id', $data['package_id']);
        $this->db->bind(':custom_price', $data['custom_price']);
        $this->db->bind(':mikrotik_router_id', $data['mikrotik_router_id']);
        $this->db->bind(':installation_date', $data['installation_date']);
        $this->db->bind(':due_date', $data['due_date']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':photo_profile', $data['photo_profile']);
        $this->db->bind(':photo_ktp', $data['photo_ktp']);

        return $this->db->execute();
    }

    public function update($data) {
        $this->db->query('UPDATE customers SET name = :name, whatsapp = :whatsapp, email = :email, username = :username, address = :address, latitude = :latitude, longitude = :longitude, package_id = :package_id, custom_price = :custom_price, mikrotik_router_id = :mikrotik_router_id, installation_date = :installation_date, due_date = :due_date, status = :status WHERE id = :id');

        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':whatsapp', $data['whatsapp']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':latitude', $data['latitude']);
        $this->db->bind(':longitude', $data['longitude']);
        $this->db->bind(':package_id', $data['package_id']);
        $this->db->bind(':custom_price', $data['custom_price']);
        $this->db->bind(':mikrotik_router_id', $data['mikrotik_router_id']);
        $this->db->bind(':installation_date', $data['installation_date']);
        $this->db->bind(':due_date', $data['due_date']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
    }

    public function updatePhotos($id, $photo_profile, $photo_ktp) {
        $query = "UPDATE customers SET ";
        $updates = [];
        if ($photo_profile !== null) $updates[] = "photo_profile = :photo_profile";
        if ($photo_ktp !== null) $updates[] = "photo_ktp = :photo_ktp";
        
        if (empty($updates)) return true;
        
        $query .= implode(', ', $updates) . " WHERE id = :id";
        $this->db->query($query);
        $this->db->bind(':id', $id);
        if ($photo_profile !== null) $this->db->bind(':photo_profile', $photo_profile);
        if ($photo_ktp !== null) $this->db->bind(':photo_ktp', $photo_ktp);
        
        return $this->db->execute();
    }

    public function updatePassword($id, $password) {
        $this->db->query('UPDATE customers SET password = :password WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':password', $password);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM customers WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getCustomersForBilling($package_id = null, $router_id = null) {
        $sql = "SELECT * FROM customers WHERE status = 'active'";
        if ($package_id && $package_id !== 'all') {
            $sql .= " AND package_id = :package_id";
        }
        if ($router_id && $router_id !== 'all') {
            $sql .= " AND mikrotik_router_id = :router_id";
        }
        $sql .= " ORDER BY id ASC";
        
        $this->db->query($sql);
        
        if ($package_id && $package_id !== 'all') {
            $this->db->bind(':package_id', $package_id);
        }
        if ($router_id && $router_id !== 'all') {
            $this->db->bind(':router_id', $router_id);
        }
        
        return $this->db->resultSet();
    }

    /**
     * Update customer status (active/inactive/isolated)
     */
    public function updateStatus($id, $status) {
        $this->db->query('UPDATE customers SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Bulk update due date based on package_id
     */
    public function bulkUpdateDueDateByPackage($package_id, $due_date) {
        $this->db->query('UPDATE customers SET due_date = :due_date WHERE package_id = :package_id');
        $this->db->bind(':due_date', $due_date);
        $this->db->bind(':package_id', $package_id);
        return $this->db->execute();
    }

    /**
     * Get customers with overdue invoices (unpaid & past due date).
     * Returns customers that are active or isolated, not inactive.
     */
    public function getOverdueCustomers() {
        $sql = "SELECT c.*, 
                       ps.username AS pppoe_username,
                       ps.id AS pppoe_secret_id,
                       ps.status AS pppoe_status,
                       ps.mikrotik_router_id AS pppoe_router_id,
                       i.id AS invoice_id,
                       i.invoice_number,
                       i.due_date AS invoice_due_date,
                       i.total_amount,
                       pk.name AS package_name,
                       pk.auto_isolate
                FROM customers c
                JOIN invoices i ON i.customer_id = c.id
                LEFT JOIN pppoe_secrets ps ON ps.customer_id = c.id
                LEFT JOIN packages pk ON pk.id = c.package_id
                WHERE c.status != 'inactive'
                  AND i.status = 'unpaid'
                  AND i.due_date < CURDATE()
                  AND pk.auto_isolate = 1
                  AND c.due_date > 0
                GROUP BY c.id, ps.username, ps.mikrotik_router_id, pk.name, pk.auto_isolate
                ORDER BY c.id ASC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    /**
     * Get all customers with PPPoE data and router info for sync page.
     */
    public function getCustomersWithPppoe($router_id = null) {
        $sql = "SELECT c.*,
                       ps.id AS pppoe_id,
                       ps.username AS pppoe_username,
                       ps.password AS pppoe_password,
                       ps.profile AS pppoe_profile,
                       ps.status AS pppoe_status,
                       ps.mikrotik_router_id,
                       pk.name AS package_name,
                       pk.mikrotik_profile,
                       r.name AS router_name,
                       r.host_ip AS router_ip
                FROM customers c
                LEFT JOIN pppoe_secrets ps ON ps.customer_id = c.id
                LEFT JOIN packages pk ON pk.id = c.package_id
                LEFT JOIN mikrotik_routers r ON r.id = ps.mikrotik_router_id
                WHERE c.status != 'inactive'";
        if ($router_id) {
            $sql .= ' AND ps.mikrotik_router_id = :router_id';
        }
        $sql .= ' ORDER BY c.name ASC';
        $this->db->query($sql);
        if ($router_id) {
            $this->db->bind(':router_id', $router_id);
        }
        return $this->db->resultSet();
    }
    /**
     * Get isolated customers who still have at least one unpaid invoice.
     * Digunakan untuk broadcast WA agar tidak mengirim ke pelanggan yang sudah bayar.
     */
    public function getIsolatedWithUnpaidInvoices() {
        $sql = "SELECT DISTINCT c.*
                FROM customers c
                INNER JOIN invoices i ON i.customer_id = c.id
                WHERE c.status = 'isolated'
                  AND i.status = 'unpaid'
                ORDER BY c.name ASC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }
}
