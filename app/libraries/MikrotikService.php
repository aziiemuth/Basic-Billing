<?php
require_once APPROOT . '/app/libraries/RouterosAPI.php';
require_once APPROOT . '/app/models/MikrotikRouterModel.php';

class MikrotikService {
    private $api;
    private $router;
    private $lastError = '';

    public function __construct() {
        $this->api = new RouterosAPI();
    }

    // =========================================================================
    // KONEKSI — dari Database (multi-router) atau Config (router utama)
    // =========================================================================

    /**
     * Hubungkan ke router berdasarkan router_id di database.
     * Jika data DB kosong/tidak aktif, FALLBACK ke konstanta MIKROTIK_* di config.php.
     */
    public function connect($router_id) {
        $routerModel  = new MikrotikRouterModel();
        $this->router = $routerModel->getById($router_id);

        // Jika router ditemukan di DB dan aktif, gunakan data DB
        if ($this->router && $this->router->is_active) {
            $host     = $this->router->host_ip;
            $username = $this->router->api_username;
            $password = $this->router->api_password;
            $port     = $this->router->api_port;
        } else {
            // Fallback ke config.php jika DB tidak ada / tidak aktif
            if (!defined('MIKROTIK_ENABLED') || !MIKROTIK_ENABLED) {
                $this->lastError = 'Router tidak ditemukan di database dan integrasi MikroTik dinonaktifkan di config.';
                return false;
            }
            $host     = MIKROTIK_HOST;
            $username = MIKROTIK_USERNAME;
            $password = MIKROTIK_PASSWORD;
            $port     = MIKROTIK_PORT;
        }

        if (empty($host)) {
            $this->lastError = 'Host IP MikroTik tidak ditentukan.';
            return false;
        }

        $this->api->port     = !empty($port) ? (int)$port : 8728;
        $this->api->timeout  = defined('MIKROTIK_TIMEOUT') ? MIKROTIK_TIMEOUT : 3;
        $this->api->attempts = 1;
        $this->api->delay    = 0;

        if ($this->api->connect($host, $username, $password)) {
            return true;
        }

        $this->lastError = 'Koneksi ke router gagal (' . $host . ':' . $this->api->port . '). Periksa IP, port, username, dan password di file .env atau menu Router.';
        return false;
    }

    /**
     * Hubungkan langsung ke router utama menggunakan konstanta dari config.php.
     * Gunakan ini jika tidak ada router_id (misal: cron job, test awal).
     */
    public function connectFromConfig() {
        if (!defined('MIKROTIK_ENABLED') || !MIKROTIK_ENABLED) {
            $this->lastError = 'Integrasi MikroTik dinonaktifkan (MIKROTIK_ENABLED = false) di file .env.';
            return false;
        }

        if (empty(MIKROTIK_HOST)) {
            $this->lastError = 'Host IP MikroTik utama (MIKROTIK_HOST) tidak ditentukan di file .env.';
            return false;
        }

        $this->api->port     = !empty(MIKROTIK_PORT) ? (int)MIKROTIK_PORT : 8728;
        $this->api->timeout  = defined('MIKROTIK_TIMEOUT') ? MIKROTIK_TIMEOUT : 3;
        $this->api->attempts = 1;
        $this->api->delay    = 0;

        if ($this->api->connect(MIKROTIK_HOST, MIKROTIK_USERNAME, MIKROTIK_PASSWORD)) {
            return true;
        }

        $this->lastError = 'Gagal terhubung ke router utama (' . MIKROTIK_HOST . ':' . $this->api->port . '). Periksa konfigurasi MIKROTIK_* di file .env.';
        return false;
    }

    /**
     * Alias connectFromConfig() — digunakan saat hanya ada 1 router utama.
     */
    public function connectDefault() {
        return $this->connectFromConfig();
    }

    /**
     * Test koneksi ke router.
     * Jika router_id diberikan → coba dari DB dulu, fallback ke config.
     * Jika router_id null → langsung pakai config.php.
     */
    public function testConnection($router_id = null) {
        // Tentukan kredensial
        if ($router_id) {
            $routerModel = new MikrotikRouterModel();
            $router      = $routerModel->getById($router_id);

            if ($router && $router->is_active) {
                $host     = $router->host_ip;
                $username = $router->api_username;
                $password = $router->api_password;
                $port     = $router->api_port;
                $source   = 'database';
            } else {
                // Fallback ke config
                $host     = MIKROTIK_HOST;
                $username = MIKROTIK_USERNAME;
                $password = MIKROTIK_PASSWORD;
                $port     = MIKROTIK_PORT;
                $source   = 'config (fallback)';
            }
        } else {
            // Langsung dari config
            $host     = MIKROTIK_HOST;
            $username = MIKROTIK_USERNAME;
            $password = MIKROTIK_PASSWORD;
            $port     = MIKROTIK_PORT;
            $source   = 'config';
        }

        if (empty($host)) {
            return [
                'success' => false,
                'message' => 'Konfigurasi IP MikroTik kosong/belum diatur.',
                'source'  => $source,
            ];
        }

        $api           = new RouterosAPI();
        $api->port     = !empty($port) ? (int)$port : 8728;
        $api->timeout  = defined('MIKROTIK_TIMEOUT') ? MIKROTIK_TIMEOUT : 3;
        $api->attempts = 1;
        $api->delay    = 0;

        if ($api->connect($host, $username, $password)) {
            $identity = $api->comm('/system/identity/print');
            $name     = (!empty($identity) && isset($identity[0]['name'])) ? $identity[0]['name'] : 'Unknown';

            $resource = $api->comm('/system/resource/print');
            $uptime   = (!empty($resource) && isset($resource[0]['uptime'])) ? $resource[0]['uptime'] : '-';
            $version  = (!empty($resource) && isset($resource[0]['version'])) ? $resource[0]['version'] : '-';

            // Fetch Profiles & Interfaces from RouterOS
            $profilesData = $api->comm('/ppp/profile/print');
            $profiles = [];
            if (is_array($profilesData)) {
                foreach ($profilesData as $p) {
                    if (isset($p['name'])) {
                        $profiles[] = $p['name'];
                    }
                }
            }

            $interfacesData = $api->comm('/interface/print');
            $interfaces = [];
            if (is_array($interfacesData)) {
                foreach ($interfacesData as $i) {
                    if (isset($i['name'])) {
                        $interfaces[] = $i['name'];
                    }
                }
            }

            $api->disconnect();

            return [
                'success'    => true,
                'message'    => 'Koneksi berhasil! (Sumber: ' . $source . ')',
                'identity'   => $name,
                'uptime'     => $uptime,
                'version'    => $version,
                'host'       => $host,
                'port'       => $port,
                'source'     => $source,
                'profiles'   => $profiles,
                'interfaces' => $interfaces
            ];
        }

        return [
            'success' => false,
            'message' => 'Gagal terhubung ke ' . $host . ':' . $port . '. Periksa konfigurasi MIKROTIK_* di file .env.',
            'source'  => $source,
        ];
    }

    public function disconnect() {
        $this->api->disconnect();
    }

    public function getLastError() {
        return $this->lastError;
    }

    // =========================================================================
    // PPPoE SECRET MANAGEMENT
    // =========================================================================

    /**
     * Add PPPoE Secret
     */
    public function addPppoeSecret($username, $password, $profile, $service = 'pppoe') {
        try {
            // Check if secret already exists first
            $existing = $this->api->comm('/ppp/secret/print', ['?name' => $username]);
            if (!empty($existing)) {
                // Already exists, update instead
                return $this->updatePppoeSecret($username, $password, $profile);
            }

            $response = $this->api->comm('/ppp/secret/add', [
                'name'     => $username,
                'password' => $password,
                'profile'  => $profile,
                'service'  => $service,
            ]);
            return !isset($response[0]['!trap']);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Update PPPoE Secret
     */
    public function updatePppoeSecret($username, $password, $profile) {
        try {
            $secret = $this->api->comm('/ppp/secret/print', ['?name' => $username]);

            if (!empty($secret) && isset($secret[0]['.id'])) {
                $params = [
                    '.id'     => $secret[0]['.id'],
                    'profile' => $profile,
                ];
                if (!empty($password)) {
                    $params['password'] = $password;
                }

                $response = $this->api->comm('/ppp/secret/set', $params);
                return !isset($response[0]['!trap']);
            }
            return false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Remove PPPoE Secret
     */
    public function removePppoeSecret($username) {
        try {
            $secret = $this->api->comm('/ppp/secret/print', ['?name' => $username]);

            if (!empty($secret) && isset($secret[0]['.id'])) {
                // Kick active connection first
                $this->kickActiveConnection($username);

                $response = $this->api->comm('/ppp/secret/remove', ['.id' => $secret[0]['.id']]);
                return !isset($response[0]['!trap']);
            }
            return false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Disable PPPoE Secret (Isolate)
     */
    public function disablePppoeSecret($username) {
        try {
            $secret = $this->api->comm('/ppp/secret/print', ['?name' => $username]);

            if (!empty($secret) && isset($secret[0]['.id'])) {
                $response = $this->api->comm('/ppp/secret/set', [
                    '.id'      => $secret[0]['.id'],
                    'disabled' => 'yes',
                ]);

                // Kick active connection to apply immediately
                $this->kickActiveConnection($username);

                return !isset($response[0]['!trap']);
            }
            return false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Enable PPPoE Secret
     */
    public function enablePppoeSecret($username) {
        try {
            $secret = $this->api->comm('/ppp/secret/print', ['?name' => $username]);

            if (!empty($secret) && isset($secret[0]['.id'])) {
                $response = $this->api->comm('/ppp/secret/set', [
                    '.id'      => $secret[0]['.id'],
                    'disabled' => 'no',
                ]);
                return !isset($response[0]['!trap']);
            }
            return false;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Get PPPoE Secret detail
     */
    public function getPppoeSecret($username) {
        try {
            $secret = $this->api->comm('/ppp/secret/print', ['?name' => $username]);
            return !empty($secret) ? $secret[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get all PPPoE Secrets from MikroTik
     */
    public function getAllPppoeSecrets() {
        try {
            return $this->api->comm('/ppp/secret/print');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get all PPPoE Profiles from MikroTik
     */
    public function getAllPppoeProfiles() {
        try {
            return $this->api->comm('/ppp/profile/print');
        } catch (Exception $e) {
            return [];
        }
    }

    // =========================================================================
    // STATUS & ACTIVE SESSIONS
    // =========================================================================

    /**
     * Check if PPPoE is Online
     */
    public function getPppoeStatus($username) {
        try {
            $active = $this->api->comm('/ppp/active/print', ['?name' => $username]);

            if (!empty($active)) {
                return [
                    'status'  => 'online',
                    'uptime'  => isset($active[0]['uptime'])  ? $active[0]['uptime']  : '-',
                    'address' => isset($active[0]['address']) ? $active[0]['address'] : '-',
                    'service' => isset($active[0]['service']) ? $active[0]['service'] : '-',
                    'caller'  => isset($active[0]['caller-id']) ? $active[0]['caller-id'] : '-',
                ];
            }
            return ['status' => 'offline'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get all active PPPoE sessions from MikroTik
     */
    public function getAllActiveSessions() {
        try {
            $sessions = $this->api->comm('/ppp/active/print');
            // Index by username for easy lookup
            $indexed = [];
            foreach ($sessions as $s) {
                if (isset($s['name'])) {
                    $indexed[$s['name']] = $s;
                }
            }
            return $indexed;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Sync status of all customers connected to this router.
     * Returns array: [ username => 'online'|'offline' ]
     */
    public function syncAllStatus() {
        $activeSessions = $this->getAllActiveSessions();
        $secrets        = $this->getAllPppoeSecrets();

        // Build case-insensitive active sessions map
        $activeSessionsMap = [];
        foreach ($activeSessions as $name => $sess) {
            $activeSessionsMap[strtolower(trim($name))] = $sess;
        }

        $result = [];
        foreach ($secrets as $secret) {
            if (!isset($secret['name'])) continue;
            $uname          = $secret['name'];
            $unameKey       = strtolower(trim($uname));
            $isDisabled     = isset($secret['disabled']) && $secret['disabled'] === 'true';
            $isOnline       = isset($activeSessionsMap[$unameKey]);

            $result[$uname] = [
                'disabled' => $isDisabled,
                'online'   => $isOnline,
                'profile'  => isset($secret['profile']) ? $secret['profile'] : '-',
                'uptime'   => $isOnline && isset($activeSessionsMap[$unameKey]['uptime']) ? $activeSessionsMap[$unameKey]['uptime'] : '-',
                'address'  => $isOnline && isset($activeSessionsMap[$unameKey]['address']) ? $activeSessionsMap[$unameKey]['address'] : '-',
            ];
        }
        return $result;
    }

    // =========================================================================
    // PROFILE MANAGEMENT
    // =========================================================================

    /**
     * Get list of all PPPoE profiles
     */
    public function getProfileList() {
        try {
            return $this->api->comm('/ppp/profile/print');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Check if a PPPoE profile exists
     */
    public function profileExists($profileName) {
        try {
            $profiles = $this->api->comm('/ppp/profile/print', ['?name' => $profileName]);
            return !empty($profiles);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create a new PPPoE profile with rate limit.
     * $rateLimit format: "10M/10M" (upload/download)
     */
    public function createProfile($name, $rateLimit = '') {
        try {
            if ($this->profileExists($name)) {
                return true; // Already exists
            }

            $params = ['name' => $name, 'service' => 'pppoe'];
            if (!empty($rateLimit)) {
                $params['rate-limit'] = $rateLimit;
            }

            $response = $this->api->comm('/ppp/profile/add', $params);
            return !isset($response[0]['!trap']);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Ensure profile exists for a package. Creates if not found.
     * $speedDownload and $speedUpload in Mbps
     */
    public function ensureProfileExists($profileName, $speedDownload = 0, $speedUpload = 0) {
        if ($this->profileExists($profileName)) {
            return true;
        }
        $rateLimit = '';
        if ($speedUpload > 0 && $speedDownload > 0) {
            $rateLimit = $speedUpload . 'M/' . $speedDownload . 'M';
        }
        return $this->createProfile($profileName, $rateLimit);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Get list of all physical and virtual interfaces
     */
    public function getInterfaceList() {
        try {
            return $this->api->comm('/interface/print');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Kick active PPPoE connection
     */
    private function kickActiveConnection($username) {
        try {
            $active = $this->api->comm('/ppp/active/print', ['?name' => $username]);
            if (!empty($active) && isset($active[0]['.id'])) {
                $this->api->comm('/ppp/active/remove', ['.id' => $active[0]['.id']]);
            }
        } catch (Exception $e) {
            // Silent fail — connection may have already dropped
        }
    }
}
