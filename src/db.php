<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $db = app_config()['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'],
        (int) $db['port'],
        $db['name'],
        $db['charset']
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if (class_exists(\Pdo\Mysql::class)) {
        $options[\Pdo\Mysql::ATTR_FOUND_ROWS] = true;
    } else {
        $options[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
    }

    try {
        $pdo = new PDO($dsn, (string) $db['user'], (string) $db['pass'], $options);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo '잠시 후 다시 시도해 주세요.';
        exit;
    }

    return $pdo;
}
