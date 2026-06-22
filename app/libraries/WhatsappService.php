<?php
/**
 * WhatsApp Notification Service
 * Menggunakan Fonnte sebagai gateway WA.
 * 
 * Cara pakai:
 * 1. Daftar di https://fonnte.com
 * 2. Dapatkan token device dari dashboard Fonnte
 * 3. Isi WA_TOKEN di file .env
 * 4. Ubah WA_ENABLED menjadi true di file .env
 */
class WhatsappService {

    /**
     * Kirim pesan WhatsApp ke satu nomor.
     * 
     * @param int $customerId ID pelanggan dari database
     * @param string $phone Nomor HP tujuan (format: 08xxx atau 628xxx)
     * @param string $message Isi pesan
     * @param string $messageType Tipe pesan (new_invoice, reminder, dll)
     * @return bool true jika berhasil, false jika gagal atau WA_ENABLED = false
     */
    public static function send($customerId, $phone, $message, $messageType = 'custom') {
        // Normalisasi nomor: hilangkan tanda +, spasi, strip
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // Ubah awalan 0 menjadi 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // Jika WA belum diaktifkan (token belum diisi), record as pending/failed or just skip API call
        if (!defined('WA_ENABLED') || !WA_ENABLED) {
            self::logToDb($customerId, $phone, $messageType, $message, 'pending');
            return false;
        }

        $gateway = defined('WA_GATEWAY') ? WA_GATEWAY : 'fonnte';
        $success = false;

        switch ($gateway) {
            case 'fonnte':
                $success = self::sendViaFonnte($phone, $message);
                break;
            case 'w4':
                $success = self::sendViaW4($phone, $message);
                break;
        }
        
        $status = $success ? 'sent' : 'failed';
        self::logToDb($customerId, $phone, $messageType, $message, $status);
        
        return $success;
    }
    
    private static function logToDb($customerId, $phone, $messageType, $message, $status) {
        $db = new Database();
        $query = "INSERT INTO whatsapp_logs (customer_id, phone_number, message_type, message, status, sent_at) 
                  VALUES (:customer_id, :phone, :type, :message, :status, :sent_at)";
        $db->query($query);
        $db->bind(':customer_id', $customerId);
        $db->bind(':phone', $phone);
        $db->bind(':type', $messageType);
        $db->bind(':message', $message);
        $db->bind(':status', $status);
        $db->bind(':sent_at', $status == 'sent' ? date('Y-m-d H:i:s') : null);
        $db->execute();
    }

    /**
     * Driver untuk Fonnte API
     */
    private static function sendViaFonnte($phone, $message) {
        $token = WA_TOKEN;
        $url   = 'https://api.fonnte.com/send';

        $data = [
            'target'  => $phone,
            'message' => $message,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $token,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            return isset($result['status']) && $result['status'] === true;
        }
        return false;
    }

    /**
     * Driver untuk W4 API Gateway
     */
    private static function sendViaW4($phone, $message) {
        $token = WA_TOKEN;
        $url = defined('WA_API_URL') ? WA_API_URL : 'https://api.w4gateway.com/send';

        $data = [
            'phone'   => $phone,
            'message' => $message,
            'token'   => $token
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            return (isset($result['status']) && ($result['status'] === true || $result['status'] === 'success')) 
                || (isset($result['success']) && $result['success'] == true);
        }
        return false;
    }

    /**
     * Memasukkan pesan ke antrian (queue)
     */
    public static function queue($customerId, $phone, $message, $messageType = 'custom') {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        self::logToDb($customerId, $phone, $messageType, $message, 'pending');
        return true;
    }

    /**
     * Memproses antrian pesan pending secara bertahap (rate-limiting)
     */
    public static function processQueue($limit = 10) {
        $db = new Database();
        $db->query("SELECT * FROM whatsapp_logs WHERE status = 'pending' ORDER BY id ASC LIMIT :limit");
        $db->bind(':limit', $limit);
        $queue = $db->resultSet();
        
        if (empty($queue)) return 0;
        
        $processed = 0;
        foreach ($queue as $item) {
            $success = false;
            $gateway = defined('WA_GATEWAY') ? WA_GATEWAY : 'fonnte';
            
            if (defined('WA_ENABLED') && WA_ENABLED) {
                switch ($gateway) {
                    case 'fonnte':
                        $success = self::sendViaFonnte($item->phone_number, $item->message);
                        break;
                    case 'w4':
                        $success = self::sendViaW4($item->phone_number, $item->message);
                        break;
                }
            }
            
            $db->query("UPDATE whatsapp_logs SET status = :status, sent_at = :sent_at, updated_at = NOW() WHERE id = :id");
            $db->bind(':id', $item->id);
            $db->bind(':status', $success ? 'sent' : 'failed');
            $db->bind(':sent_at', $success ? date('Y-m-d H:i:s') : null);
            $db->execute();
            
            $processed++;
            usleep(1500000); // 1.5s delay
        }
        
        return $processed;
    }

    /**
     * Mengantrekan ulang semua pesan yang gagal dikirim
     */
    public static function retryFailed() {
        $db = new Database();
        $db->query("UPDATE whatsapp_logs SET status = 'pending', sent_at = NULL WHERE status = 'failed'");
        return $db->execute();
    }

    // =========================================================
    //  TEMPLATE PESAN
    // =========================================================

    /**
     * Kirim notifikasi pembayaran lunas
     */
    public static function sendPaymentSuccess($customerId, $phone, $customerName, $invoiceNumber, $amount, $billingMonth, $paymentMethod = 'Sistem') {
        $bulan = self::indonesianMonth(date('n', strtotime($billingMonth . '-01')));
        $tahun = date('Y', strtotime($billingMonth . '-01'));
        $nominal = 'Rp ' . number_format($amount, 0, ',', '.');

        $message = "*Pembayaran Berhasil*\n\n"
                 . "Halo *{$customerName}*,\n"
                 . "Kami telah menerima pembayaran tagihan internet Anda via *{$paymentMethod}*.\n\n"
                 . "Detail Pembayaran:\n"
                 . "No. Invoice : {$invoiceNumber}\n"
                 . "Periode     : {$bulan} {$tahun}\n"
                 . "Nominal     : {$nominal}\n\n"
                 . "Internet Anda sudah aktif kembali.\n"
                 . "Terima kasih telah membayar tepat waktu!\n\n"
                 . "_" . SITENAME . "_";

        return self::send($customerId, $phone, $message, 'payment_success');
    }

    /**
     * Kirim notifikasi tagihan baru
     */
    public static function sendNewInvoice($customerId, $phone, $customerName, $invoiceNumber, $amount, $billingMonth, $dueDate) {
        $bulan = self::indonesianMonth(date('n', strtotime($billingMonth . '-01')));
        $tahun = date('Y', strtotime($billingMonth . '-01'));
        $nominal = 'Rp ' . number_format($amount, 0, ',', '.');
        $jatuhTempo = date('d F Y', strtotime($dueDate));

        $message = "*Tagihan Internet Baru*\n\n"
                 . "Halo *{$customerName}*,\n"
                 . "Tagihan internet Anda untuk bulan *{$bulan} {$tahun}* telah terbit.\n\n"
                 . "Nominal     : {$nominal}\n"
                 . "Jatuh Tempo : {$jatuhTempo}\n"
                 . "No. Invoice : {$invoiceNumber}\n\n"
                 . "Silakan lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari pemutusan layanan.\n\n"
                 . "_" . SITENAME . "_";

        return self::send($customerId, $phone, $message, 'new_invoice');
    }

    /**
     * Kirim notifikasi pengingat jatuh tempo (H-3)
     */
    public static function sendPaymentReminder($customerId, $phone, $customerName, $amount, $dueDate) {
        $nominal = 'Rp ' . number_format($amount, 0, ',', '.');
        $jatuhTempo = date('d F Y', strtotime($dueDate));

        $message = "*Pengingat Tagihan*\n\n"
                 . "Halo *{$customerName}*,\n"
                 . "Tagihan internet Anda sebesar *{$nominal}* akan jatuh tempo pada *{$jatuhTempo}*.\n\n"
                 . "Segera lakukan pembayaran agar internet Anda tetap aktif.\n\n"
                 . "_" . SITENAME . "_";

        return self::send($customerId, $phone, $message, 'reminder');
    }

    /**
     * Kirim notifikasi akun diisolir
     */
    public static function sendIsolated($customerId, $phone, $customerName) {
        $message = "*Layanan Internet Dinonaktifkan*\n\n"
                 . "Halo *{$customerName}*,\n"
                 . "Layanan internet Anda telah kami nonaktifkan sementara karena terdapat tagihan yang belum dibayar.\n\n"
                 . "Segera lakukan pembayaran untuk mengaktifkan kembali layanan Anda.\n\n"
                 . "Jika ada pertanyaan, silakan hubungi kami.\n\n"
                 . "_" . SITENAME . "_";

        return self::send($customerId, $phone, $message, 'isolation');
    }

    /**
     * Kirim notifikasi aktivasi internet baru
     */
    public static function sendActivated($customerId, $phone, $customerName, $packageName) {
        $message = "*Layanan Internet Aktif!*\n\n"
                 . "Halo *{$customerName}*,\n"
                 . "Pemasangan baru internet Anda telah sukses dilakukan.\n"
                 . "Paket Layanan: *{$packageName}*\n\n"
                 . "Selamat menikmati layanan internet tanpa batas dari kami!\n\n"
                 . "Jika ada kendala koneksi, silakan hubungi Customer Support kami.\n\n"
                 . "_" . SITENAME . "_";

        return self::send($customerId, $phone, $message, 'activation');
    }

    /**
     * Helper: nama bulan dalam Bahasa Indonesia
     */
    private static function indonesianMonth($monthNumber) {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April',   5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',    8 => 'Agustus',   9 => 'September',
            10 => 'Oktober',11 => 'November', 12 => 'Desember'
        ];
        return $months[$monthNumber] ?? '';
    }
}
