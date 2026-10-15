<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

requireLogin();

$id = positiveInt($_POST['id'] ?? $_GET['id'] ?? null);
if ($id === null) {
    abortRequest(404, '상품을 찾을 수 없습니다.');
}

$item = requireOwnedItem($id, '이 상품을 삭제할 수 없습니다.');
if ($item['status'] !== 'ON_SALE') {
    abortRequest(403, '현재 상태에서는 삭제할 수 없습니다.');
}

if (isPost()) {
    verifyCsrf();
    $user = currentUser();
    $deleted = deleteItem($id, (int) $user['id']);
    if (!$deleted) {
        abortRequest(403, '현재 상태에서는 삭제할 수 없습니다.');
    }
    setFlash('상품을 삭제했습니다.');
    redirect('/index.php');
}

startPage('상품 삭제');
?>
<h1>상품 삭제</h1>
<p>아래 상품을 삭제합니다. 삭제한 상품은 되돌릴 수 없습니다.</p>
<dl>
    <dt>게임</dt>
    <dd><?= e((string) $item['game_name']) ?></dd>
    <dt>서버</dt>
    <dd><?= e((string) $item['server_name']) ?></dd>
    <dt>아이템명</dt>
    <dd><?= e((string) $item['item_name']) ?></dd>
    <dt>가격</dt>
    <dd><?= e(formatPrice((int) $item['price'])) ?></dd>
</dl>
<form method="post" action="/item-delete.php" class="actions">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
    <button type="submit">삭제합니다</button>
    <a class="button secondary" href="/item.php?id=<?= (int) $item['id'] ?>">취소</a>
</form>
<?php endPage(); ?>
