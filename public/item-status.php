<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

requirePost();
requireLogin();
verifyCsrf();

$id = positiveInt($_POST['id'] ?? null);
if ($id === null) {
    abortRequest(404, '상품을 찾을 수 없습니다.');
}

$item = requireOwnedItem($id, '이 상품의 상태를 변경할 수 없습니다.');
$next = (string) ($_POST['status'] ?? '');
if (!in_array($next, allowedNextStatuses((string) $item['status']), true)) {
    http_response_code(400);
    startPage('상태 변경');
    echo '<p class="error">허용되지 않는 상태 변경입니다.</p>';
    echo '<p><a href="/item.php?id=' . (int) $id . '">상품으로 돌아가기</a></p>';
    endPage();
    exit;
}

$user = currentUser();
$changed = changeItemStatus($id, (int) $user['id'], $next);
setFlash($changed ? '거래 상태를 변경했습니다.' : '상태가 이미 변경되었습니다.');
redirect('/item.php?id=' . $id);
