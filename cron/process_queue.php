<?php
/**
 * CRON JOB: Memproses Antrean Pesan WhatsApp Pending
 * =====================================================
 * Script ini memproses pengiriman antrean WA (pending) secara bertahap
 * untuk menghindari nomor terblokir (anti-spam rate-limiting).
 */

define('APPROOT', dirname(__DIR__));
require_once APPROOT . '/app/config/config.php';
require_once APPROOT . '/app/libraries/Database.php';
require_once APPROOT . '/app/libraries/WhatsappService.php';

// Ensure log directory exists
$logDir = APPROOT . '/cron/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/queue_' . date('Y-m') . '.log';

$processed = WhatsappService::processQueue(10); // Proses 10 pesan sekaligus per pemanggilan

if ($processed > 0) {
    $line = '[' . date('Y-m-d H:i:s') . '] Berhasil memproses ' . $processed . ' antrean pesan WhatsApp.' . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}
