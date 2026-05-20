@echo off
:: Batch file untuk memproses antrean pesan WhatsApp
:: Daftarkan batch ini di Windows Task Scheduler untuk dijalankan setiap 5 menit.
cd /d "c:\xampp\htdocs\billingv1"
c:\xampp\php\php.exe cron\process_queue.php
