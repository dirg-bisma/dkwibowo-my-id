<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
<h1>Admin</h1>
<form method="post" action="/admin/logout" style="display:inline">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <button type="submit">Logout</button>
</form>
<p><a href="/admin/content/create">Create project</a> · <a href="/admin/trash">Trash</a></p>
<h2>Content</h2>
<ul>
<?php foreach ($items as $item): ?>
    <li>
        <a href="/admin/content/<?= (int) $item['id'] ?>/edit"><?= $e($item['title_id'] ?: $item['slug']) ?></a>
        — <?= $e($item['status']) ?>
        <?php if ($item['status'] !== 'trashed'): ?>
            <form method="post" action="/admin/content/<?= (int) $item['id'] ?>/trash" style="display:inline">
                <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
                <button type="submit">Move to trash</button>
            </form>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>
<h2>Tags</h2>
<form method="post" action="/admin/tags/create">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <input name="name" required>
    <button type="submit">Create tag</button>
</form>
<ul>
<?php foreach ($tags as $tag): ?>
    <li>
        <form method="post" action="/admin/tags/<?= (int) $tag['id'] ?>/edit" style="display:inline">
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <input name="name" value="<?= $e($tag['name']) ?>" required>
            <button type="submit">Save</button>
        </form>
        <form method="post" action="/admin/tags/<?= (int) $tag['id'] ?>/delete" style="display:inline">
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <button type="submit">Delete</button>
        </form>
    </li>
<?php endforeach; ?>
</ul>
