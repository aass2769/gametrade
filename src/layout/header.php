<?php

declare(strict_types=1);

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · GameTrade</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <a class="logo" href="/index.php">GameTrade</a>
    <nav>
        <?php if ($user !== null): ?>
            <a href="/item-create.php">아이템 등록</a>
            <a href="/my-items.php">내 판매 상품</a>
            <span class="username"><?= e($user['username']) ?></span>
            <form method="post" action="/logout.php">
                <?= csrfField() ?>
                <button type="submit">로그아웃</button>
            </form>
        <?php else: ?>
            <a href="/login.php">로그인</a>
            <a href="/register.php">회원가입</a>
        <?php endif; ?>
    </nav>
</header>
<main>
<?php $flash = getFlash(); ?>
<?php if ($flash !== null): ?>
    <p class="flash"><?= e($flash) ?></p>
<?php endif; ?>
