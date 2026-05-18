# 🌐 Sistem Billing RT/RW Net & ISP

Sistem Billing Internet Service Provider (ISP) / RT/RW Net berbasis **PHP Native (Arsitektur MVC)**. Aplikasi ini dirancang untuk memudahkan manajemen pelanggan, tagihan otomatis, serta terintegrasi langsung dengan Mikrotik, Midtrans (Payment Gateway), dan Fonnte (WhatsApp Gateway).

---

## ✨ Fitur Utama

### 🧑‍💼 Modul Admin
- **Dashboard Interaktif**: Statistik jumlah pelanggan, tren pendapatan (Chart.js), dan pantauan status koneksi Mikrotik secara *real-time*.
- **Manajemen Pelanggan**: CRUD data pelanggan, penugasan ke router Mikrotik tertentu, pembuatan secret PPPoE otomatis, dan unggah berkas KTP & Foto Profil.
- **Manajemen Paket Internet**: Atur kecepatan dan harga paket yang terhubung langsung dengan profile Mikrotik.
- **Manajemen Router (Multi-Router)**: Mendukung penggunaan lebih dari satu router Mikrotik.
- **Generate Tagihan**: Pembuatan invoice otomatis setiap bulan.
- **Laporan Keuangan**: Laporan arus kas, pendapatan bulanan, dan invoice tertunggak.
- **Sistem Pengaturan (Settings)**: Pengaturan identitas perusahaan, pajak, zona waktu, dan peringatan H-x jatuh tempo.

### 👥 Modul Pelanggan (Customer Portal)
- **Cek Tagihan**: Pelanggan dapat login untuk melihat tagihan aktif dan riwayat pembayaran.
- **Pembayaran Otomatis**: Integrasi Midtrans memungkinkan pelanggan membayar langsung dengan QRIS, Virtual Account, e-Wallet, dll.
- **Notifikasi WhatsApp**: Pengingat tagihan dan struk pembayaran otomatis dikirim ke WhatsApp.

### 🔌 Integrasi & Otomatisasi
- **Mikrotik RouterOS API**: Pembuatan PPPoE Secret otomatis, *enable/disable* (isolir) pelanggan otomatis saat jatuh tempo, sinkronisasi status.
- **Payment Gateway (Midtrans)**: Deteksi pembayaran instan (*webhook*) dan langsung melunasi invoice.
- **WhatsApp Gateway (Fonnte)**: Pengiriman notifikasi tagihan dan pembayaran.
- **Cron Job**: Eksekusi background untuk generate invoice bulanan, notifikasi WA, dan auto-isolir dengan pengamanan berbasis *Secret Key*.

---

## 💻 Stack Teknologi
- **Backend:** PHP Native (7.4 - 8.x) dengan pendekatan MVC (Model-View-Controller)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3 (Glassmorphism), Vanilla JavaScript
- **Framework UI:** Bootstrap 5 (Dark Mode)
- **Library UI:** SweetAlert2 (Toast Notification), Chart.js
- **API Library:** RouterosAPI (Mikrotik)

---

## 🚀 Instalasi & Persiapan

1. **Clone/Upload Repository**  
   Simpan project ini ke dalam direktori root server lokal Anda (misalnya di `htdocs/billing` jika menggunakan XAMPP).

2. **Setup Database**
   - Buat database baru di MySQL (misal: `billing_db`).
   - Import file SQL yang disediakan (jika ada) atau jalankan migrasi tabel yang terlampir pada dokumentasi skema database.

3. **Konfigurasi Environment (`.env`)**
   - Salin file `.env.example` (jika ada) menjadi `.env` di folder root project.
   - Sesuaikan konfigurasi berikut:
     ```env
     # Konfigurasi Dasar
     URLROOT=http://localhost/billing # (Gunakan domain Anda di production)
     SITENAME="Billing App"

     # Konfigurasi Database
     DB_HOST=localhost
     DB_PORT=3306
     DB_DATABASE=billing_db
     DB_USERNAME=root
     DB_PASSWORD=

     # Konfigurasi Midtrans
     MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
     MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
     MIDTRANS_IS_PRODUCTION=false

     # Konfigurasi WhatsApp (Fonnte)
     WA_GATEWAY=fonnte
     WA_TOKEN=token_fonnte_anda
     WA_ENABLED=true

     # Konfigurasi Utama Mikrotik (Jika Single Router)
     MIKROTIK_HOST=10.5.50.1
     MIKROTIK_USERNAME=admin
     MIKROTIK_PASSWORD=admin
     MIKROTIK_PORT=8728
     MIKROTIK_ENABLED=true

     # Keamanan Cron Job
     CRON_SECRET=SecretKeyKuatAnda123!
     ```

4. **Konfigurasi Folder Uploads**
   - Pastikan folder `public/uploads/customers/profile/` dan `public/uploads/customers/ktp/` memiliki hak akses tulis (*writable*) `chmod 777` atau *ownership* yang tepat.

---

## ⚙️ Menjalankan Cron Job
Untuk menjalankan otomatisasi (generate invoice & isolir pelanggan tunggak), tambahkan perintah berikut ke dalam *Crontab* di server Linux Anda:

```bash
# Jalankan pengecekan setiap jam 08:00 pagi
0 8 * * * /usr/bin/php /var/www/html/billing/public/index.php url=CronController/run?key=SecretKeyKuatAnda123!
```
*(Ganti URL dan Path sesuai dengan root instalasi dan `CRON_SECRET` Anda di `.env`).*

---

## 📱 Mobile-Friendly & Local Development
Sistem ini menggunakan algoritma **Dynamic URLROOT**. Saat mengembangkan secara lokal, Anda bisa mengakses URL via IP (contoh: `http://192.168.1.x/billing`) menggunakan smartphone di satu jaringan Wi-Fi yang sama, dan sistem UI akan langsung beradaptasi (responsif) tanpa mem-force-redirect Anda kembali ke `localhost`.

---

## 🛡️ Keamanan
- Menggunakan `password_hash` & `password_verify` (Bcrypt) untuk otentikasi.
- Seluruh form menggunakan validasi input & CSRF Token.
- Pengecekan sesi ketat di *Payment Webhook* & *Midtrans Snap*.
- Filter Ekstensi MIME Ketat untuk upload KTP & Profil demi menghindari serangan injeksi skrip.

---

**Dikembangkan untuk memudahkan operasional jaringan Anda!**
