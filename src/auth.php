<?php

declare(strict_types=1);

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['user_id'],
        'username' => (string) $_SESSION['username'],
    ];
}

function requireLogin(): void
{
    if (currentUser() === null) {
        redirect('/login.php');
    }
}

function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = (string) $user['username'];
    unset($_SESSION['csrf_token']);
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => (bool) $params['secure'],
            'httponly' => (bool) $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}
