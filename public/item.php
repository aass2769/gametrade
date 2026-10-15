<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$id = positiveInt($_GET['id'] ?? null);
if ($id === null) {
    abortRequest(404, '상품을 찾을 수 없습니다.');
}

$item = findItem($id);
if ($item === null) {
    abortRequest(404, '상품을 찾을 수 없습니다.');
}

$user = currentUser();
$isOwner = $user !== null && (int) $item['user_id'] === $user['id'];
$canEdit = $isOwner && $item['status'] === 'ON_SALE';
$nextStatuses = $isOwner ? allowedNextStatuses((string) $item['status']) : [];

startPage((string) $item['item_name']);
?>
<h1><?= e((string) $item['item_name']) ?></h1>
<dl>
    <dt>게임</dt>
    <dd><?= e((string) $item['game_name']) ?></dd>
    <dt>서버</dt>
    <dd><?= e((string) $item['server_name']) ?></dd>
    <dt>가격</dt>
    <dd><?= e(formatPrice((int) $item['price'])) ?></dd>
    <dt>수량</dt>
    <dd><?= (int) $item['quantity'] ?></dd>
    <dt>상태</dt>
    <dd><?= e(statusLabel((string) $item['status'])) ?></dd>
    <dt>판매자</dt>
    <dd><?= e((string) $item['username']) ?></dd>
    <dt>설명</dt>
    <dd>
        <?php if ($item['description'] === null || $item['description'] === ''): ?>
            설명이 없습니다.
        <?php else: ?>
            <?= nl2brE((string) $item['description']) ?>
        <?php endif; ?>
    </dd>
    <dt>등록일</dt>
    <dd><?= e(formatDate((string) $item['created_at'])) ?></dd>
    <dt>수정일</dt>
    <dd><?= e(formatDate((string) $item['updated_at'])) ?></dd>
</dl>

<?php if ($canEdit): ?>
    <p class="actions">
        <a class="button" href="/item-edit.php?id=<?= (int) $item['id'] ?>">수정</a>
        <a class="button secondary" href="/item-delete.php?id=<?= (int) $item['id'] ?>">삭제</a>
    </p>
<?php endif; ?>

<?php if ($nextStatuses !== []): ?>
    <form method="post" action="/item-status.php" class="stack-form">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
        <label>
            거래 상태 변경
            <select name="status">
                <?php foreach ($nextStatuses as $next): ?>
                    <option value="<?= e($next) ?>"><?= e(statusLabel($next)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php if ($item['status'] === 'IN_PROGRESS'): ?>
            <p>판매 중으로 바꾸면 진행 표시를 취소합니다.</p>
        <?php endif; ?>
        <button type="submit">상태 변경</button>
    </form>
<?php endif; ?>
<?php endPage(); ?>
