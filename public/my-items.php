<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

requireLogin();

$user = currentUser();
$items = listItemsByUser((int) $user['id']);

startPage('내 판매 상품');
?>
<h1>내 판매 상품</h1>
<?php if ($items === []): ?>
    <p>등록한 상품이 없습니다.</p>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>게임</th>
                <th>서버</th>
                <th>아이템명</th>
                <th>가격</th>
                <th>수량</th>
                <th>상태</th>
                <th>등록일</th>
                <th>동작</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e((string) $item['game_name']) ?></td>
                    <td><?= e((string) $item['server_name']) ?></td>
                    <td><a href="/item.php?id=<?= (int) $item['id'] ?>"><?= e((string) $item['item_name']) ?></a></td>
                    <td><?= e(formatPrice((int) $item['price'])) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= e(statusLabel((string) $item['status'])) ?></td>
                    <td><?= e(formatDate((string) $item['created_at'])) ?></td>
                    <td>
                        <?php if ($item['status'] === 'ON_SALE'): ?>
                            <a href="/item-edit.php?id=<?= (int) $item['id'] ?>">수정</a>
                            <a href="/item-delete.php?id=<?= (int) $item['id'] ?>">삭제</a>
                        <?php else: ?>
                            <a href="/item.php?id=<?= (int) $item['id'] ?>">상세</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php endPage(); ?>
