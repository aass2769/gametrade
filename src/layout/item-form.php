<?php

declare(strict_types=1);

/** @var array $values */
/** @var array $errors */
/** @var array $servers */
/** @var string $submitLabel */
/** @var int|null $itemId */
?>
<form method="post" class="stack-form" action="">
    <?= csrfField() ?>
    <?php if ($itemId !== null): ?>
        <input type="hidden" name="id" value="<?= (int) $itemId ?>">
    <?php endif; ?>
    <label>
        게임 / 서버
        <?= fieldError($errors, 'server_id') ?>
        <select name="server_id" required>
            <option value="">선택하세요</option>
            <?php
            $currentGame = null;
            foreach ($servers as $server):
                if ($currentGame !== $server['game_name']):
                    if ($currentGame !== null) {
                        echo '</optgroup>';
                    }
                    $currentGame = $server['game_name'];
                    echo '<optgroup label="' . e((string) $server['game_name']) . '">';
                endif;
                $selected = (string) $server['id'] === (string) $values['server_id'] ? ' selected' : '';
                $label = $server['game_name'] . ' - ' . $server['name'];
                echo '<option value="' . (int) $server['id'] . '"' . $selected . '>' . e($label) . '</option>';
            endforeach;
            if ($currentGame !== null) {
                echo '</optgroup>';
            }
            ?>
        </select>
    </label>
    <label>
        아이템명
        <?= fieldError($errors, 'item_name') ?>
        <input type="text" name="item_name" maxlength="100" required value="<?= e((string) $values['item_name']) ?>">
    </label>
    <label>
        판매 가격 (원)
        <?= fieldError($errors, 'price') ?>
        <input type="text" name="price" inputmode="numeric" required value="<?= e((string) $values['price']) ?>">
    </label>
    <label>
        판매 수량
        <?= fieldError($errors, 'quantity') ?>
        <input type="text" name="quantity" inputmode="numeric" required value="<?= e((string) $values['quantity']) ?>">
    </label>
    <label>
        아이템 설명 (선택)
        <?= fieldError($errors, 'description') ?>
        <textarea name="description" maxlength="2000" rows="5"><?= e((string) $values['description']) ?></textarea>
    </label>
    <button type="submit"><?= e($submitLabel) ?></button>
</form>
