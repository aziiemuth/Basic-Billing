<?php
// Load Config
require_once __DIR__ . '/config/config.php';

// Load Libraries
require_once __DIR__ . '/libraries/Core.php';
require_once __DIR__ . '/libraries/Controller.php';
require_once __DIR__ . '/libraries/Database.php';
require_once __DIR__ . '/libraries/MikrotikService.php';
require_once __DIR__ . '/libraries/WhatsappService.php';
require_once __DIR__ . '/libraries/SecurityHelper.php';

// Load Middlewares
require_once __DIR__ . '/middlewares/AuthAdminMiddleware.php';
require_once __DIR__ . '/middlewares/AuthCustomerMiddleware.php';

// Load Custom Session Handler
require_once __DIR__ . '/libraries/DbSessionHandler.php';

// Set Session Handler to Database
$sessionHandler = new DbSessionHandler();
session_set_save_handler($sessionHandler, true);

// Start Session
session_start();
