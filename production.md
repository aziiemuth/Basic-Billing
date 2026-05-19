# 🚀 Panduan Alur Produksi (Deployment 100% Siap Operasional)
**Sistem Billing RT/RW Net & Manajemen ISP Terintegrasi MikroTik**

Dokumen ini merupakan panduan lengkap langkah-demi-langkah (*step-by-step*) untuk memigrasikan aplikasi dari lingkungan pengembangan (*development/localhost*) ke server produksi (*production*) menggunakan **XAMPP Windows (24 jam online)** yang terhubung dengan **IP Public, Domain (zienet.web.id), Subfolder (billingv1), dan SSL**.

---

## 📋 Daftar Isi
1. [Langkah 1: Konfigurasi Variabel Lingkungan (.env)](#1-konfigurasi-variabel-lingkungan-env)
2. [Langkah 2: Migrasi & Optimasi Database](#2-migrasi--optimasi-database)
3. [Langkah 3: Integrasi Midtrans Payment Gateway (Mode Live)](#3-integrasi-midtrans-payment-gateway-mode-live)
4. [Langkah 4: Aktivasi WhatsApp Gateway Fonnte](#4-aktivasi-whatsapp-gateway-fonnte)
5. [Langkah 5: Konfigurasi API MikroTik untuk Real Production](#5-konfigurasi-api-mikrotik-untuk-real-production)
6. [Langkah 6: Set Up Otomatisasi (Cron Job di Windows Server)](#6-set-up-otomatisasi-cron-job-di-windows-server)
7. [Langkah 7: Pengamanan Server XAMPP (Security Hardening)](#7-pengamanan-server-xampp-security-hardening)

---

## 1. Konfigurasi Variabel Lingkungan (.env)
File `.env` di direktori utama (`c:\xampp\htdocs\billingv1\.env`) harus diubah total dari konfigurasi testing ke konfigurasi jaringan produksi Anda.

### ✍️ Parameter yang Wajib Diubah di `.env`:
```ini
# ==========================================
# 🌐 Konfigurasi Aplikasi Utama
# ==========================================
# Ganti dengan domain produksi Anda yang sudah terpasang SSL dan menunjuk ke subfolder billingv1
URLROOT=https://zienet.web.id/billingv1
SITENAME="RT/RW Net Indonesia"

# ==========================================
# 🗄️ Database Produksi
# ==========================================
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=billing_db_prod
DB_USERNAME=billing_db_user
# Buat password database yang sangat kuat (kombinasi huruf besar/kecil, angka, simbol)
DB_PASSWORD=P@ssw0rdDbProd2026_Secure!

# ==========================================
# 💳 Midtrans Payment Gateway (LIVE MODE)
# ==========================================
# Ambil dari Dashboard Midtrans -> Settings -> Access Keys -> Production
MIDTRANS_SERVER_KEY=Mid-server-LIVE_SERVER_KEY_ANDA_DISINI
MIDTRANS_CLIENT_KEY=Mid-client-LIVE_CLIENT_KEY_ANDA_DISINI
MIDTRANS_IS_PRODUCTION=true

# ==========================================
# 💬 WhatsApp Gateway (Fonnte API)
# ==========================================
WA_GATEWAY=fonnte
# Ambil token dari dashboard Fonnte setelah mendaftarkan nomor WhatsApp Anda
WA_TOKEN=TOKEN_LIVE_FONNTE_ANDA
WA_ENABLED=true

# ==========================================
# 🔌 MikroTik RouterOS API (Jaringan Utama)
# ==========================================
# Gunakan IP Public Statis, IP Local VPN, atau IP LAN jika server berada di jaringan yang sama
MIKROTIK_HOST=192.168.1.1
# Buat user admin khusus di MikroTik dengan hak akses group API
MIKROTIK_USERNAME=api_billing
MIKROTIK_PASSWORD=password_api_mikrotik_kuat
# Port default API MikroTik: 8728 (atau 8729 jika memakai API SSL)
MIKROTIK_PORT=8728
MIKROTIK_INTERFACE=ether1
# Isi dengan profile default PPPoE yang ada di MikroTik Anda
MIKROTIK_PROFILE=Paket-Home
MIKROTIK_TIMEOUT=3
MIKROTIK_ENABLED=true

# ==========================================
# 🔑 Kunci Keamanan Otomatisasi (Cron Key)
# ==========================================
# Ganti dengan string acak panjang untuk mengamankan trigger otomatisasi Anda
CRON_SECRET=KunciAcakSangatKuatDanPanjangDibuatTahun2026!
```

---

## 2. Migrasi & Optimasi Database
Pindahkan data dari database pengujian lokal Anda ke database produksi yang bersih.

### 🛠️ Langkah Migrasi:
1.  **Ekspor Skema:** Buka `http://localhost/phpmyadmin`, pilih database testing, masuk ke tab **Export**, lalu unduh file `.sql`.
2.  **Buat User DB Baru (Wajib untuk Keamanan):**
    *   Masuk ke phpMyAdmin -> Tab **User Accounts** -> **Add user account**.
    *   Berikan nama pengguna yang unik (misal: `billing_db_user`) dan password yang kuat.
    *   Hanya berikan hak akses (*privileges*) `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `CREATE`, `DROP`, `INDEX`, `ALTER` pada database `billing_db_prod`. **Jangan berikan akses Global Administrator / ALL PRIVILEGES!**
3.  **Impor Skema:** Masuk ke database produksi baru (`billing_db_prod`), pilih tab **Import**, pilih file `.sql` Anda, dan tekan **Import**.
4.  **Bersihkan Data Testing:** Hapus seluruh data dummy testing dengan masuk ke menu *Pengaturan Sistem -> Utilitas Pengujian -> Hapus Seluruh Data Dummy* agar database Anda benar-benar bersih 100% dan siap diisi oleh pelanggan riil pertama.

---

## 3. Integrasi Midtrans Payment Gateway (Mode Live)
Untuk menerima pembayaran otomatis dari pelanggan secara langsung via e-Wallet (Gopay, ShopeePay), Transfer Bank (Virtual Account), atau Gerai Ritel (Alfamart/Indomaret).

### 🛠️ Langkah Integrasi:
1.  Login ke **[Dashboard Midtrans](https://dashboard.midtrans.com/)**.
2.  Pastikan status di pojok kiri atas telah beralih dari **Sandbox** ke **Production** (Live).
3.  Pergi ke menu **Settings -> Access Keys** untuk menyalin **Server Key** dan **Client Key** produksi Anda, lalu tempelkan ke file `.env` Anda.
4.  **Konfigurasi URL Callback / Webhook (Sangat Penting!):**
    *   Pada Dashboard Midtrans, masuk ke menu **Settings -> Configuration**.
    *   Pada kolom **Payment Notification URL**, masukkan URL webhook sistem Anda secara presisi (mengacu pada subfolder `/billingv1`, controller `PaymentController` dan method `webhook`):
        👉 **`https://zienet.web.id/billingv1/PaymentController/webhook`**
    *   Klik **Update** di bagian bawah halaman dashboard Midtrans.
    *   *Catatan Penting:* URL di atas harus menggunakan **HTTPS** yang valid (SSL aktif di domain `zienet.web.id`). Midtrans mewajibkan SSL aktif untuk mengirim notifikasi pembayaran demi keamanan pertukaran data finansial.

---

## 4. Aktivasi WhatsApp Gateway Fonnte
Digunakan untuk mengirim invoice tagihan otomatis setiap bulan, bukti lunas pembayaran secara real-time, reminder jatuh tempo, serta notifikasi isolir.

### 🛠️ Langkah Aktivasi:
1.  Daftarkan akun di **[Fonnte](https://fonnte.com/)**.
2.  Hubungkan (*pairing*) nomor WhatsApp bisnis Anda di dashboard Fonnte dengan memindai kode QR.
3.  Salin token API unik yang dihasilkan Fonnte dan masukkan ke variabel `WA_TOKEN` di `.env`, serta ubah `WA_ENABLED` menjadi `true`.
4.  Pastikan nomor WhatsApp Anda dalam keadaan aktif, memiliki sinyal stabil, dan memiliki paket kuota yang cukup agar pengiriman pesan massal tidak terganggu.

---

## 5. Konfigurasi API MikroTik untuk Real Production
Aplikasi memerlukan akses langsung ke MikroTik Anda untuk membuat PPPoE Secret pelanggan baru, memonitor trafik, serta mematikan/mengisolir pelanggan menunggak secara otomatis.

### 🛠️ Langkah Konfigurasi:
1.  **Buka Layanan API MikroTik:**
    *   Buka aplikasi Winbox, hubungkan ke MikroTik Anda.
    *   Pergi ke **IP -> Services**.
    *   Pastikan layanan **api** (port `8728`) atau **api-ssl** (port `8729`) dalam keadaan **Enabled** (aktif).
2.  **Buat User Akses Khusus:**
    *   Pergi ke **System -> Users**.
    *   Buat user baru (misal: `api_billing`).
    *   Buat group baru dengan akses terbatas pada layanan `read`, `write`, dan `api` saja. Hubungkan user `api_billing` ke group tersebut demi memperketat keamanan akses internal Router.
3.  **Tentukan IP Akses:**
    *   Jika Server XAMPP Anda dan MikroTik berada di bawah satu jaringan lokal yang sama, Anda bisa langsung mengisi `MIKROTIK_HOST` dengan IP LAN MikroTik (misal: `192.168.1.1`).
    *   Jika lokasinya berbeda, hubungkan Server XAMPP dan MikroTik Anda menggunakan VPN terenkripsi (seperti L2TP/IPsec atau WireGuard). Masukkan IP tunnel VPN MikroTik Anda ke variabel `MIKROTIK_HOST`.

---

## 6. Set Up Otomatisasi (Cron Job di Windows Server)
Karena sistem berjalan pada server **XAMPP Windows**, kita tidak dapat memakai `crontab` bawaan Linux. Kita akan menggunakan **Windows Task Scheduler** bawaan Windows Server yang bekerja memicu otomatisasi billing Anda 24 jam penuh.

### 🛠️ Langkah Pembuatan:
1.  **Buat File Pemicu (Batch Script):**
    *   Buat file baru di server Anda dengan nama `run_billing_cron.bat` (Anda bisa menyimpannya di direktori `C:\xampp\htdocs\run_billing_cron.bat`).
    *   Isi file tersebut dengan baris perintah pemicu curl berikut:
        ```bat
        @echo off
        :: Mengirim request aman ke sistem untuk memproses generate tagihan otomatis, cek reminder WA, isolir pelanggan, dan backup database
        curl -sL "http://localhost/billingv1/cron/cron_trigger.php?key=KunciAcakSangatKuatDanPanjangDibuatTahun2026!"
        exit
        ```
        *(Sesuaikan isi parameter `key` dengan `CRON_SECRET` yang Anda buat di `.env`)*
2.  **Konfigurasi Windows Task Scheduler:**
    *   Buka **Task Scheduler** di OS Windows Server Anda (cari lewat Start Menu).
    *   Klik **Create Basic Task** di panel kanan.
    *   **Name:** `Sistem Billing RT/RW Net Cronjob`
    *   **Trigger:** Pilih **Daily** (Harian). Atur waktu eksekusi pada jam sibuk yang sepi, misalnya pukul **01:00 AM** dini hari.
    *   **Action:** Pilih **Start a Program**.
    *   **Program/script:** Klik *Browse* dan arahkan ke file `run_billing_cron.bat` yang Anda buat pada langkah 1.
    *   Klik **Finish**.
3.  **Optimasi Eksekusi 24 Jam:**
    *   Klik dua kali pada tugas yang baru saja Anda buat untuk membuka pengaturannya.
    *   Pada tab **General**, pilih opsi **Run whether user is logged on or not** dan centang **Run with highest privileges** agar skrip tetap berjalan meskipun Windows sedang terkunci atau admin log out.
    *   Pada tab **Settings**, centang **If the running task does not end when requested, force it to stop**.

---

## 7. Pengamanan Server XAMPP (Security Hardening)
Sangat krusial untuk mengamankan server XAMPP Anda karena kini website Anda dapat diakses secara publik lewat internet dengan domain.

### 🛠️ Langkah Pengamanan Wajib:
1.  **Nonaktifkan Tampilan Error PHP (display_errors):**
    *   Buka file konfigurasi PHP XAMPP Anda (`C:\xampp\php\php.ini`).
    *   Cari baris `display_errors = On` dan ubah nilainya menjadi **`display_errors = Off`**.
    *   Cari baris `log_errors = On` (pastikan bernilai On) agar error tetap dicatat ke file log internal server demi kebutuhan audit, bukan ditampilkan di hadapan browser pengunjung.
2.  **Amankan File Rahasia (.env & PHP files):**
    *   Buka file konfigurasi server Apache (`C:\xampp\apache\conf\httpd.conf` atau file `.htaccess` di direktori root aplikasi `/billingv1/`).
    *   Pastikan file konfigurasi `.htaccess` berisi pengaman index direktori untuk memblokir siapapun yang mencoba mengintip file sensitif Anda lewat browser:
        ```apache
        # Nonaktifkan browsing direktori (Index Browsing)
        Options -Indexes

        # Proteksi file .env agar tidak bisa diakses secara publik
        <Files .env>
            Order allow,deny
            Deny from all
        </Files>
        ```
3.  **Paksa Penggunaan HTTPS (SSL Redirection):**
    *   Masukkan aturan penulisan ulang Apache berikut ke dalam file `.htaccess` di folder aplikasi Anda agar setiap pengguna yang masuk lewat HTTP biasa langsung dialihkan secara aman ke HTTPS:
        ```apache
        RewriteEngine On
        RewriteCond %{HTTPS} off
        RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
        ```

---

## 🏁 Verifikasi Akhir
Setelah seluruh langkah di atas diselesaikan:
1.  Akses web dashboard admin Anda dari browser luar atau HP lewat domain: `https://zienet.web.id/billingv1`.
2.  Pastikan lambang **"Sistem Online"** menyala hijau terang.
3.  Buka menu **Laporan & Arus Kas** untuk memastikan tidak ada data lama pengujian yang tertinggal.
4.  Coba daftarkan 1 pelanggan riil pertama Anda, dan periksa apakah PPPoE Secret di MikroTik Anda terbuat secara instan, serta pesan WhatsApp selamat datang dari Fonnte masuk ke HP pelanggan Anda secara otomatis.

**Selamat! Sistem Billing RT/RW Net Anda kini resmi mengudara (LIVE) dan siap melayani transaksi pelanggan secara autopilot!** 🚀💸
