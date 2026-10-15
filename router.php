<?php

declare(strict_types=1);

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($uriPath) || $uriPath === '') {
    $uriPath = '/';
}

$publicRoot = realpath(__DIR__ . '/public');
$candidate = realpath(__DIR__ . '/public' . $uriPath);

if (
    $candidate !== false
    && $publicRoot !== false
    && is_file($candidate)
    && str_starts_with($candidate, $publicRoot . DIRECTORY_SEPARATOR)
) {
    return false;
}

if ($uriPath === '/') {
    require __DIR__ . '/public/index.php';
    return true;
}

http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo '페이지를 찾을 수 없습니다.';
return true;
