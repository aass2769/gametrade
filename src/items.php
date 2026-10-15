<?php

declare(strict_types=1);

function listGames(): array
{
    $stmt = db()->query('SELECT id, name FROM games ORDER BY id');
    return $stmt->fetchAll();
}

function listServers(): array
{
    $stmt = db()->query(
        'SELECT s.id, s.name, g.id AS game_id, g.name AS game_name
         FROM game_servers AS s
         INNER JOIN games AS g ON g.id = s.game_id
         ORDER BY g.id, s.id'
    );
    return $stmt->fetchAll();
}

function findServer(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT s.id, s.game_id, s.name, g.name AS game_name
         FROM game_servers AS s
         INNER JOIN games AS g ON g.id = s.game_id
         WHERE s.id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function searchItems(array $filters): array
{
    $sql = 'SELECT i.id, i.item_name, i.price, i.quantity, i.status, i.created_at,
                   g.id AS game_id, g.name AS game_name, s.name AS server_name
            FROM items AS i
            INNER JOIN game_servers AS s ON s.id = i.server_id
            INNER JOIN games AS g ON g.id = s.game_id
            WHERE 1 = 1';
    $params = [];

    if (isset($filters['game_id']) && $filters['game_id'] !== null) {
        $sql .= ' AND g.id = :game_id';
        $params['game_id'] = (int) $filters['game_id'];
    }
    if (isset($filters['q']) && is_string($filters['q']) && $filters['q'] !== '') {
        $sql .= ' AND i.item_name LIKE :item_name';
        $params['item_name'] = '%' . $filters['q'] . '%';
    }

    $sql .= ' ORDER BY i.created_at DESC, i.id DESC';
    $stmt = db()->prepare($sql);
    foreach ($params as $key => $value) {
        if ($key === 'game_id') {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function findItem(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT i.id, i.user_id, i.item_name, i.price, i.quantity, i.description, i.status,
                i.created_at, i.updated_at, u.username, g.id AS game_id, g.name AS game_name,
                s.id AS server_id, s.name AS server_name
         FROM items AS i
         INNER JOIN users AS u ON u.id = i.user_id
         INNER JOIN game_servers AS s ON s.id = i.server_id
         INNER JOIN games AS g ON g.id = s.game_id
         WHERE i.id = :id'
    );
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function listItemsByUser(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT i.id, i.item_name, i.price, i.quantity, i.status, i.created_at,
                g.name AS game_name, s.name AS server_name
         FROM items AS i
         INNER JOIN game_servers AS s ON s.id = i.server_id
         INNER JOIN games AS g ON g.id = s.game_id
         WHERE i.user_id = :user_id
         ORDER BY i.created_at DESC, i.id DESC'
    );
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function postedItemValues(): array
{
    return [
        'server_id' => trim((string) ($_POST['server_id'] ?? '')),
        'item_name' => trim((string) ($_POST['item_name'] ?? '')),
        'price' => trim((string) ($_POST['price'] ?? '')),
        'quantity' => trim((string) ($_POST['quantity'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
    ];
}

function validateItemInput(array $input): array
{
    $errors = [];
    $serverId = filter_var($input['server_id'] ?? '', FILTER_VALIDATE_INT);
    $itemName = trim((string) ($input['item_name'] ?? ''));
    $price = filter_var($input['price'] ?? '', FILTER_VALIDATE_INT);
    $quantity = filter_var($input['quantity'] ?? '', FILTER_VALIDATE_INT);
    $description = trim((string) ($input['description'] ?? ''));

    if ($serverId === false || $serverId < 1 || findServer($serverId) === null) {
        $errors['server_id'] = '서버를 선택하세요.';
    }
    if ($itemName === '' || mb_strlen($itemName) > 100) {
        $errors['item_name'] = '아이템명은 1자 이상 100자 이하로 입력해 주세요.';
    }
    if ($price === false || $price < 1 || $price > 100000000) {
        $errors['price'] = '가격은 1원 이상 100,000,000원 이하의 정수로 입력해 주세요.';
    }
    if ($quantity === false || $quantity < 1 || $quantity > 9999) {
        $errors['quantity'] = '수량은 1개 이상 9,999개 이하의 정수로 입력해 주세요.';
    }
    if (mb_strlen($description) > 2000) {
        $errors['description'] = '설명은 2,000자 이하로 입력해 주세요.';
    }

    return [
        'errors' => $errors,
        'data' => [
            'server_id' => $serverId === false ? 0 : $serverId,
            'item_name' => $itemName,
            'price' => $price === false ? 0 : $price,
            'quantity' => $quantity === false ? 0 : $quantity,
            'description' => $description === '' ? null : $description,
        ],
    ];
}

function createItem(int $userId, array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO items (user_id, server_id, item_name, price, quantity, description, status)
         VALUES (:user_id, :server_id, :item_name, :price, :quantity, :description, \'ON_SALE\')'
    );
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':server_id', (int) $data['server_id'], PDO::PARAM_INT);
    $stmt->bindValue(':item_name', (string) $data['item_name']);
    $stmt->bindValue(':price', (int) $data['price'], PDO::PARAM_INT);
    $stmt->bindValue(':quantity', (int) $data['quantity'], PDO::PARAM_INT);
    if ($data['description'] === null) {
        $stmt->bindValue(':description', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':description', (string) $data['description']);
    }
    $stmt->execute();
    return (int) db()->lastInsertId();
}

function updateItem(int $id, int $userId, array $data): bool
{
    $stmt = db()->prepare(
        'UPDATE items
         SET server_id = :server_id,
             item_name = :item_name,
             price = :price,
             quantity = :quantity,
             description = :description
         WHERE id = :id AND user_id = :user_id AND status = \'ON_SALE\''
    );
    $stmt->bindValue(':server_id', (int) $data['server_id'], PDO::PARAM_INT);
    $stmt->bindValue(':item_name', (string) $data['item_name']);
    $stmt->bindValue(':price', (int) $data['price'], PDO::PARAM_INT);
    $stmt->bindValue(':quantity', (int) $data['quantity'], PDO::PARAM_INT);
    if ($data['description'] === null) {
        $stmt->bindValue(':description', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':description', (string) $data['description']);
    }
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->rowCount() === 1;
}

function deleteItem(int $id, int $userId): bool
{
    $stmt = db()->prepare(
        'DELETE FROM items WHERE id = :id AND user_id = :user_id AND status = \'ON_SALE\''
    );
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->rowCount() === 1;
}

function allowedNextStatuses(string $current): array
{
    return match ($current) {
        'ON_SALE' => ['IN_PROGRESS'],
        'IN_PROGRESS' => ['COMPLETED', 'ON_SALE'],
        'COMPLETED' => [],
        default => [],
    };
}

function changeItemStatus(int $id, int $userId, string $next): bool
{
    $item = findItem($id);
    if ($item === null || (int) $item['user_id'] !== $userId) {
        return false;
    }
    if (!in_array($next, allowedNextStatuses((string) $item['status']), true)) {
        return false;
    }

    $stmt = db()->prepare(
        'UPDATE items
         SET status = :next_status
         WHERE id = :id AND user_id = :user_id AND status = :current_status'
    );
    $stmt->bindValue(':next_status', $next);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':current_status', (string) $item['status']);
    $stmt->execute();
    return $stmt->rowCount() === 1;
}

function requireOwnedItem(int $id, string $forbiddenMessage): array
{
    $item = findItem($id);
    if ($item === null) {
        abortRequest(404, '상품을 찾을 수 없습니다.');
    }
    $user = currentUser();
    if ($user === null || (int) $item['user_id'] !== $user['id']) {
        abortRequest(403, $forbiddenMessage);
    }
    return $item;
}
