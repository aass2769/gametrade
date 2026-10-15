<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

requireLogin();

$id = positiveInt($_POST['id'] ?? $_GET['id'] ?? null);
if ($id === null) {
    abortRequest(404, '상품을 찾을 수 없습니다.');
}

$item = requireOwnedItem($id, '이 상품을 수정할 수 없습니다.');
if ($item['status'] !== 'ON_SALE') {
    abortRequest(403, '현재 상태에서는 수정할 수 없습니다.');
}

$errors = [];
$values = [
    'server_id' => (string) $item['server_id'],
    'item_name' => (string) $item['item_name'],
    'price' => (string) $item['price'],
    'quantity' => (string) $item['quantity'],
    'description' => (string) ($item['description'] ?? ''),
];

if (isPost()) {
    verifyCsrf();
    $values = postedItemValues();
    $validated = validateItemInput($_POST);
    $errors = $validated['errors'];
    if ($errors === []) {
        $user = currentUser();
        $updated = updateItem($id, (int) $user['id'], $validated['data']);
        if (!$updated) {
            abortRequest(403, '현재 상태에서는 수정할 수 없습니다.');
        }
        setFlash('상품을 수정했습니다.');
        redirect('/item.php?id=' . $id);
    }
}

$servers = listServers();
$submitLabel = '저장';
$itemId = $id;
startPage('아이템 수정');
?>
<h1>아이템 수정</h1>
<?php require __DIR__ . '/../src/layout/item-form.php'; ?>
<?php endPage(); ?>
