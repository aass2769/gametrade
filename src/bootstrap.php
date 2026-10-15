<?php

declare(strict_types=1);

function app_config(): array
{
    static $config = null;
    if (is_array($config)) {
        return $config;
    }

    $path = dirname(__DIR__) . '/config/config.php';
    if (!is_file($path)) {
        error_log('config missing: ' . $path);
        http_response_code(500);
        echo '설정을 확인할 수 없습니다.';
        exit;
    }

    $loaded = require $path;
    if (!is_array($loaded) || !isset($loaded['db']) || !is_array($loaded['db'])) {
        error_log('config invalid: ' . $path);
        http_response_code(500);
        echo '설정을 확인할 수 없습니다.';
        exit;
    }

    $config = $loaded;
    return $config;
}

$logDir = dirname(__DIR__) . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/app.log');
date_default_timezone_set('Asia/Seoul');

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => false,
]);
session_start();

set_exception_handler(static function (Throwable $e): void {
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '잠시 후 다시 시도해 주세요.';
});

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/users.php';
require_once __DIR__ . '/items.php';
