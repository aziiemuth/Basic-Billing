-- ============================================================
-- MIGRATION: MikroTik PPPoE Integration
-- Jalankan SQL ini di database billing_db
-- ============================================================

-- 1. Tabel log sinkronisasi MikroTik
CREATE TABLE IF NOT EXISTS `mikrotik_sync_logs` (
    `id`          bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `router_id`   int(11) NOT NULL,
    `action`      varchar(50) NOT NULL COMMENT 'sync, auto_isolate, manual_enable, manual_disable',
    `total`       int(11) DEFAULT 0,
    `success`     int(11) DEFAULT 0,
    `failed`      int(11) DEFAULT 0,
    `details`     longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    `created_at`  timestamp NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `mikrotik_sync_logs_router_id_foreign` (`router_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Data Router MikroTik Default
-- Sesuaikan password sebelum dijalankan!
INSERT INTO `mikrotik_routers` (`name`, `host_ip`, `api_username`, `api_password`, `api_port`, `pppoe_interface`, `description`, `is_active`)
VALUES (
    'Router Utama',
    '10.5.50.1',
    'zizan',
    'password router',  -- GANTI dengan password asli router
    8728,
    'pppoe1',
    'Router MikroTik utama untuk layanan PPPoE pelanggan',
    1
) ON DUPLICATE KEY UPDATE `name` = `name`;

-- 3. Data Paket Internet Contoh (dengan profile MikroTik)
INSERT INTO `packages` (`name`, `speed_download`, `speed_upload`, `price`, `mikrotik_profile`, `description`, `is_active`, `auto_isolate`)
VALUES
    ('Paket Rumah 10 Mbps',  10, 10, 150000, 'pppoe-RUMAH',    'Paket internet rumahan 10 Mbps',   1, 1),
    ('Paket Rumah 20 Mbps',  20, 20, 250000, 'pppoe-RUMAH-20', 'Paket internet rumahan 20 Mbps',   1, 1),
    ('Paket Bisnis 50 Mbps', 50, 50, 500000, 'pppoe-BISNIS',   'Paket internet bisnis 50 Mbps',    1, 1)
ON DUPLICATE KEY UPDATE `name` = `name`;

-- ============================================================
-- VERIFIKASI: Pastikan tabel sudah ada
-- ============================================================
-- SELECT * FROM mikrotik_routers;
-- SELECT * FROM packages;
-- SELECT * FROM pppoe_secrets;
