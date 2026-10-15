<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (currentUser() !== null) {
    redirect('/index.php');
}

$error = null;
$username = '';

if (isPost()) {
    verifyCsrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!attemptLogin($username, $password)) {
        $error = '아이디 또는 비밀번호가 올바르지 않습니다.';
    } else {
        redirect('/index.php');
    }
}

startPage('로그인');
?>
<h1>로그인</h1>
<form method="post" action="/login.php" class="stack-form">
    <?= csrfField() ?>
    <?php if ($error !== null): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>
    <label>
        아이디
        <input type="text" name="username" required maxlength="20" value="<?= e($username) ?>">
    </label>
    <label>
        비밀번호
        <input type="password" name="password" required maxlength="64">
    </label>
    <button type="submit">로그인</button>
</form>
<?php endPage(); ?>
