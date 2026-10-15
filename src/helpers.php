<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    if (!preg_match('#^/[A-Za-z0-9_./?=&%-]*$#', $path)) {
        $path = '/index.php';
    }
    header('Location: ' . $path);
    exit;
}

function setFlash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function getFlash(): ?string
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $message = (string) $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $message;
}

function statusLabel(string $status): string
{
    return match ($status) {
        'ON_SALE' => '판매 중',
        'IN_PROGRESS' => '거래 진행 중',
        'COMPLETED' => '거래 완료',
        default => '알 수 없음',
    };
}

function formatPrice(int $price): string
{
    return number_format($price) . '원';
}

function formatDate(string $value): string
{
    $time = strtotime($value);
    if ($time === false) {
        return '';
    }
    return date('Y-m-d H:i', $time);
}

function nl2brE(?string $value): string
{
    return nl2br(e($value), false);
}

function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function requirePost(): void
{
    if (!isPost()) {
        http_response_code(405);
        header('Allow: POST');
        echo '허용되지 않는 요청입니다.';
        exit;
    }
}

function positiveInt(mixed $value): ?int
{
    $id = filter_var($value, FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        return null;
    }
    return $id;
}

function fieldError(array $errors, string $key): string
{
    if (!isset($errors[$key])) {
        return '';
    }
    return '<p class="error">' . e((string) $errors[$key]) . '</p>';
}

function startPage(string $title): void
{
    $pageTitle = $title;
    require __DIR__ . '/layout/header.php';
}

function endPage(): void
{
    require __DIR__ . '/layout/footer.php';
}

function abortRequest(int $status, string $message): void
{
    http_response_code($status);
    $title = match ($status) {
        404 => '찾을 수 없음',
        403 => '권한 없음',
        default => '요청 오류',
    };
    startPage($title);
    echo '<p>' . e($message) . '</p>';
    echo '<p><a href="/index.php">목록으로</a></p>';
    endPage();
    exit;
}
