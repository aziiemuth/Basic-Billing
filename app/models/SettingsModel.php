<?php
class SettingsModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getSettings() {
        $this->db->query("SELECT * FROM settings WHERE id = 1");
        return $this->db->single();
    }

    public function update($data) {
        $query = "UPDATE settings SET 
            company_name = :company_name, 
            company_logo = :company_logo,
            company_address = :company_address, 
            company_whatsapp = :company_whatsapp, 
            company_email = :company_email, 
            invoice_footer = :invoice_footer, 
            timezone = :timezone, 
            currency_format = :currency_format, 
            auto_isolate = :auto_isolate, 
            wa_reminder_days = :wa_reminder_days 
            WHERE id = 1";

        $this->db->query($query);
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':company_logo', $data['company_logo']);
        $this->db->bind(':company_address', $data['company_address']);
        $this->db->bind(':company_whatsapp', $data['company_whatsapp']);
        $this->db->bind(':company_email', $data['company_email']);
        $this->db->bind(':invoice_footer', $data['invoice_footer']);
        $this->db->bind(':timezone', $data['timezone']);
        $this->db->bind(':currency_format', $data['currency_format']);
        $this->db->bind(':auto_isolate', $data['auto_isolate']);
        $this->db->bind(':wa_reminder_days', $data['wa_reminder_days']);

        return $this->db->execute();
    }
}
