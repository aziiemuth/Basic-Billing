<?php
require_once 'app/config/config.php';
require_once 'app/libraries/RouterosAPI.php';
require_once 'app/libraries/Database.php';
require_once 'app/models/MikrotikRouterModel.php';
require_once 'app/libraries/MikrotikService.php';

$mikrotikService = new MikrotikService();
$routerModel = new MikrotikRouterModel();
$routers = $routerModel->getAll();
if (empty($routers)) {
    die("No router found\n");
}
$router = $routers[0];

if ($mikrotikService->connect($router->id)) {
    $activeSessions = $mikrotikService->getAllActiveSessions();
    $secrets = $mikrotikService->getAllPppoeSecrets();
    
    $target = 'KosMardi';
    
    echo "--- Active Sessions ---\n";
    foreach ($activeSessions as $name => $s) {
        if (stripos($name, $target) !== false) {
            echo "Session Name: '{$name}' (Length: " . strlen($name) . ")\n";
        }
    }
    
    echo "--- Secrets ---\n";
    foreach ($secrets as $s) {
        $name = $s['name'] ?? '';
        if (stripos($name, $target) !== false) {
            echo "Secret Name: '{$name}' (Length: " . strlen($name) . ")\n";
            $is_online = isset($activeSessions[$name]);
            echo "Is Online (exact match): " . ($is_online ? "TRUE" : "FALSE") . "\n";
            $is_online_trim = isset($activeSessions[trim($name)]);
            echo "Is Online (trimmed): " . ($is_online_trim ? "TRUE" : "FALSE") . "\n";
        }
    }
} else {
    echo "Connection failed: " . $mikrotikService->getLastError() . "\n";
}
