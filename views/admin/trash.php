<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
<section class="admin-page container"><div class="admin-breadcrumb"><a href="/admin">Dashboard</a><span>/</span><span>Trash</span></div><div class="admin-topbar"><div><p class="admin-kicker">Content workspace</p><h1>Trash</h1><p class="admin-lede">Restore content atau hapus permanen dengan hati-hati.</p></div></div>
<?php if ($items === []): ?>
    <section class="admin-card admin-empty"><p>Trash is empty.</p><a class="text-link" href="/admin">Back to dashboard →</a></section>
<?php else: ?>
    <section class="admin-card"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Project</th><th>Previous status</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($items as $item): ?>
        <tr><td><span class="admin-project-title"><?= $e($item['title_id'] ?: $item['slug']) ?></span><span class="admin-slug">/<?= $e($item['slug']) ?></span></td><td><?= $e(ucfirst($item['original_status'])) ?></td><td class="admin-row-actions"><form method="post" action="/admin/trash/<?= (int) $item['id'] ?>/restore">
                <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
                <button class="button button-secondary" type="submit">Restore</button>
            </form>
            <form method="post" action="/admin/trash/<?= (int) $item['id'] ?>/delete">
                <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
                <button class="button-link danger-link" type="submit">Delete permanently</button></form></td></tr>
    <?php endforeach; ?>
    </tbody></table></div></section>
<?php endif; ?>
</section>
