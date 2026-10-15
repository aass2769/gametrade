<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (currentUser() !== null) {
    redirect('/index.php');
}

$errors = [];
$username = '';

if (isPost()) {
    verifyCsrf();
    $validated = validateRegistration($_POST);
    $errors = $validated['errors'];
    $username = $validated['username'];
    if ($errors === []) {
        $created = createUser($validated['username'], $validated['password']);
        if (!$created['ok'] && $created['error'] === 'duplicate') {
            $errors['username'] = '이미 사용 중인 아이디입니다.';
        } elseif (!$created['ok']) {
            abortRequest(500, '잠시 후 다시 시도해 주세요.');
        } else {
            loginUser([
                'id' => $created['id'],
                'username' => $validated['username'],
            ]);
            setFlash('가입했습니다.');
            redirect('/index.php');
        }
    }
}

startPage('회원가입');
?>
<h1>회원가입</h1>
<form method="post" action="/register.php" class="stack-form">
    <?= csrfField() ?>
    <label>
        아이디
        <?= fieldError($errors, 'username') ?>
        <input type="text" name="username" required maxlength="20" value="<?= e($username) ?>">
    </label>
    <label>
        비밀번호
        <?= fieldError($errors, 'password') ?>
        <input type="password" name="password" required maxlength="64">
    </label>
    <label>
        비밀번호 확인
        <?= fieldError($errors, 'password_confirm') ?>
        <input type="password" name="password_confirm" required maxlength="64">
    </label>
    <button type="submit">가입</button>
</form>
<?php endPage(); ?>
