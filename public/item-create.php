<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

requireLogin();

$errors = [];
$values = [
    'server_id' => '',
    'item_name' => '',
    'price' => '',
    'quantity' => '',
    'description' => '',
];

if (isPost()) {
    verifyCsrf();
    $values = postedItemValues();
    $validated = validateItemInput($_POST);
    $errors = $validated['errors'];
    if ($errors === []) {
        $user = currentUser();
        $newId = createItem((int) $user['id'], $validated['data']);
        setFlash('상품을 등록했습니다.');
        redirect('/item.php?id=' . $newId);
    }
}

$servers = listServers();
$submitLabel = '등록';
$itemId = null;
startPage('아이템 등록');
?>
<h1>아이템 등록</h1>
<?php require __DIR__ . '/../src/layout/item-form.php'; ?>
<?php endPage(); ?>
