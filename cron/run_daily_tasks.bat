@echo off
:: Batch file untuk menjalankan seluruh rutinitas harian billing
:: Daftarkan batch ini di Windows Task Scheduler untuk dijalankan sekali sehari (misal jam 02:00 AM).
cd /d "c:\xampp\htdocs\billingv1"
echo ===== Menjalankan Tugas Harian Billing =====
c:\xampp\php\php.exe cron\auto_isolate.php
c:\xampp\php\php.exe cron\sync_pppoe.php
c:\xampp\php\php.exe cron\generate_invoices.php
c:\xampp\php\php.exe cron\wa_reminders.php
c:\xampp\php\php.exe cron\db_backup.php
echo ===========================================
