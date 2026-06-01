<?php
require_once 'app/config/config.php';
require_once 'app/libraries/RouterosAPI.php';
require_once 'app/libraries/Database.php';
require_once 'app/models/MikrotikRouterModel.php';
require_once 'app/libraries/MikrotikService.php';

$mikrotikService = new MikrotikService();
// We need to connect to the router
$routerModel = new MikrotikRouterModel();
$routers = $routerModel->getAll();
if (empty($routers)) {
    die("No router found\n");
}
$router = $routers[0];

if ($mikrotikService->connect($router->id)) {
    $activeSessions = $mikrotikService->getAllActiveSessions();
    foreach ($activeSessions as $name => $s) {
        if (stripos($name, 'KosMardi') !== false) {
            echo "Found targeted user active session!\n";
            print_r($s);
        }
    }
    
    echo "--- Now checking secrets ---\n";
    $secrets = $mikrotikService->getAllPppoeSecrets();
    foreach ($secrets as $s) {
        $name = $s['name'] ?? '';
        if (stripos($name, 'KosMardi') !== false) {
            echo "Found targeted user secret!\n";
            print_r($s);
        }
    }
} else {
    echo "Connection failed: " . $mikrotikService->getLastError() . "\n";
}
