<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$games = listGames();
$qRaw = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$gameId = null;
if (isset($_GET['game_id']) && $_GET['game_id'] !== '') {
    $parsedGameId = filter_var($_GET['game_id'], FILTER_VALIDATE_INT);
    if ($parsedGameId !== false && $parsedGameId > 0) {
        $gameId = $parsedGameId;
    }
}

$searchError = null;
$q = null;
if (mb_strlen($qRaw) > 100) {
    $searchError = '검색어는 100자 이하로 입력해 주세요.';
} else {
    $stripped = str_replace(['%', '_'], '', $qRaw);
    if ($stripped !== '') {
        $q = $stripped;
    }
}

$items = [];
if ($searchError === null) {
    $items = searchItems([
        'game_id' => $gameId,
        'q' => $q,
    ]);
}

$filtered = $gameId !== null || $q !== null;
startPage('아이템 목록');
?>
<h1>아이템 목록</h1>
<form class="filters" method="get" action="/index.php">
    <label>
        게임
        <select name="game_id">
            <option value="">전체</option>
            <?php foreach ($games as $game): ?>
                <option value="<?= (int) $game['id'] ?>"<?= $gameId === (int) $game['id'] ? ' selected' : '' ?>>
                    <?= e((string) $game['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        아이템명
        <input type="text" name="q" maxlength="100" value="<?= e($qRaw) ?>">
    </label>
    <button type="submit">검색</button>
</form>
<?php if ($searchError !== null): ?>
    <p class="error"><?= e($searchError) ?></p>
<?php elseif ($items === []): ?>
    <p><?= $filtered ? '검색 결과가 없습니다.' : '등록된 아이템이 없습니다.' ?></p>
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
                    <td><span class="badge badge-<?= e((string) $item['status']) ?>"><?= e(statusLabel((string) $item['status'])) ?></span></td>
                    <td><?= e(formatDate((string) $item['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php endPage(); ?>
