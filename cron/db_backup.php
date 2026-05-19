<?php
/**
 * CRON JOB: Backup Database Otomatis
 * ====================================================
 * Jalankan script ini dengan cron job mingguan atau harian:
 *
 *   0 2 * * * php /path/to/billing/cron/db_backup.php >> /path/to/billing/cron/logs/backup.log 2>&1
 */

// ---- Bootstrap ----
define('APPROOT', dirname(__DIR__));
require_once APPROOT . '/app/config/config.php';

// Ensure log directory exists
$logDir = APPROOT . '/cron/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/backup_' . date('Y-m') . '.log';

function cronLog($message, $logFile) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

cronLog('===== Database Backup Cron Dimulai =====', $logFile);

// Set backup directory
$backupDir = APPROOT . '/public/uploads/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Generate backup filename
$backupFile = $backupDir . '/backup_db_' . DB_DATABASE . '_' . date('Ymd_His') . '.sql';

// Detect mysqldump path in Windows/XAMPP or default environment
$mysqldump = 'mysqldump';
$xamppDump = 'c:/xampp/mysql/bin/mysqldump.exe';

if (file_exists($xamppDump)) {
    $mysqldump = '"' . $xamppDump . '"';
}

$dbPassArg = !empty(DB_PASSWORD) ? '-p' . DB_PASSWORD : '';
$command = "{$mysqldump} -h " . DB_HOST . " -u " . DB_USERNAME . " {$dbPassArg} " . DB_DATABASE . " > \"{$backupFile}\"";

cronLog("Mengeksekusi perintah backup database...", $logFile);

$output = [];
$returnVar = -1;
exec($command, $output, $returnVar);

if ($returnVar === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
    cronLog("✅ BERHASIL! Database '" . DB_DATABASE . "' berhasil dibackup.", $logFile);
    cronLog("Lokasi file: " . str_replace('/', DIRECTORY_SEPARATOR, $backupFile), $logFile);
    cronLog("Ukuran file: " . number_format(filesize($backupFile) / 1024, 2) . " KB", $logFile);
} else {
    cronLog("❌ GAGAL! Perintah backup keluar dengan kode: {$returnVar}.", $logFile);
    cronLog("Mencoba backup manual menggunakan query PHP backup fallback...", $logFile);
    
    // Fallback: Backup manual dengan PHP jika mysqldump gagal
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE . ";charset=utf8", DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $tables = [];
        $result = $pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $sql = "-- Billing App Database Backup Fallback\n";
        $sql .= "-- Waktu: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            $result = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $row = $result->fetch(PDO::FETCH_NUM);
            $sql .= "\n\n" . $row[1] . ";\n\n";
            
            $result = $pdo->query("SELECT * FROM `{$table}`");
            $numFields = $result->columnCount();
            
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $sql .= "INSERT INTO `{$table}` VALUES(";
                for ($j=0; $j<$numFields; $j++) {
                    if (isset($row[$j])) {
                        $sql .= $pdo->quote($row[$j]);
                    } else {
                        $sql .= "NULL";
                    }
                    if ($j < ($numFields-1)) {
                        $sql .= ",";
                    }
                }
                $sql .= ");\n";
            }
            $sql .= "\n\n\n";
        }
        
        file_put_contents($backupFile, $sql);
        cronLog("✅ BERHASIL (Fallback)! Database '" . DB_DATABASE . "' berhasil dibackup secara manual via PHP PDO.", $logFile);
        cronLog("Lokasi file: " . str_replace('/', DIRECTORY_SEPARATOR, $backupFile), $logFile);
        cronLog("Ukuran file: " . number_format(filesize($backupFile) / 1024, 2) . " KB", $logFile);
    } catch (Exception $e) {
        cronLog("❌ GAGAL TOTAL! Error fallback: " . $e->getMessage(), $logFile);
    }
}

// ---- Bersihkan backup yang lebih lama dari 30 hari ----
cronLog("Membersihkan file backup lama (lebih dari 30 hari)...", $logFile);
$files = glob($backupDir . '/*.sql');
$now = time();
$deletedCount = 0;

foreach ($files as $file) {
    if (is_file($file)) {
        if ($now - filemtime($file) >= 30 * 24 * 60 * 60) { // 30 hari
            unlink($file);
            $deletedCount++;
        }
    }
}

cronLog("Pembersihan selesai. Mengapus {$deletedCount} file backup usang.", $logFile);
cronLog('===== Cron Selesai =====', $logFile);
