<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
<h1>Trash</h1>
<p><a href="/admin">Back to admin</a></p>
<?php if ($items === []): ?>
    <p>Trash is empty.</p>
<?php else: ?>
    <ul>
    <?php foreach ($items as $item): ?>
        <li>
            <?= $e($item['title_id'] ?: $item['slug']) ?>
            (<?= $e($item['original_status']) ?>)
            <form method="post" action="/admin/trash/<?= (int) $item['id'] ?>/restore" style="display:inline">
                <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
                <button type="submit">Restore</button>
            </form>
            <form method="post" action="/admin/trash/<?= (int) $item['id'] ?>/delete" style="display:inline">
                <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
                <button type="submit">Permanent delete</button>
            </form>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
