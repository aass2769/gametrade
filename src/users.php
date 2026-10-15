<?php

declare(strict_types=1);

const DUMMY_PASSWORD_HASH = '$2y$12$.5ndwfRuN0rO0GDoGimpjOg1Xcaf8htFY1V/kiPRJBaT8D2jEnAte';

function findUserByUsername(string $username): ?array
{
    $stmt = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function validateRegistration(array $input): array
{
    $username = trim((string) ($input['username'] ?? ''));
    $password = (string) ($input['password'] ?? '');
    $confirm = (string) ($input['password_confirm'] ?? '');
    $errors = [];

    if (!preg_match('/^[A-Za-z0-9_]{4,20}$/', $username)) {
        $errors['username'] = '아이디는 4자 이상 20자 이하의 영문, 숫자, 밑줄만 사용할 수 있습니다.';
    }
    $length = mb_strlen($password);
    if ($length < 8 || $length > 64 || strlen($password) > 72) {
        $errors['password'] = '비밀번호는 8자 이상 64자 이하로 입력해 주세요.';
    }
    if ($password !== $confirm) {
        $errors['password_confirm'] = '비밀번호 확인이 일치하지 않습니다.';
    }

    return [
        'errors' => $errors,
        'username' => $username,
        'password' => $password,
    ];
}

function createUser(string $username, string $password): array
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($hash === false) {
        return ['ok' => false, 'error' => 'unknown'];
    }

    $stmt = db()->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)');
    try {
        $stmt->execute([
            'username' => $username,
            'password_hash' => $hash,
        ]);
    } catch (PDOException $e) {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $driverCode = (int) ($e->errorInfo[1] ?? 0);
        if ($sqlState === '23000' && $driverCode === 1062) {
            return ['ok' => false, 'error' => 'duplicate'];
        }
        throw $e;
    }

    return ['ok' => true, 'id' => (int) db()->lastInsertId()];
}

function attemptLogin(string $username, string $password): bool
{
    $user = findUserByUsername($username);
    $hash = $user['password_hash'] ?? DUMMY_PASSWORD_HASH;
    $valid = password_verify($password, $hash);
    if ($user === null || !$valid) {
        return false;
    }
    loginUser($user);
    return true;
}
