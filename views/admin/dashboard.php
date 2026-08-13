<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
<section class="admin-page container">
<div class="admin-topbar">
    <div><p class="admin-kicker">Content workspace</p><h1>Admin dashboard</h1><p class="admin-lede">Kelola project, status publikasi, dan tag dari satu tempat.</p></div>
    <form method="post" action="/admin/logout">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <button class="button button-secondary" type="submit">Logout</button>
    </form>
</div>
<div class="admin-actions"><a class="button button-primary" href="/admin/content/create">＋ Create project</a><a class="button button-secondary" href="/admin/trash">View trash</a></div>
<div class="admin-grid admin-grid-main">
<section class="admin-card admin-card-wide"><div class="admin-card-heading"><div><p class="admin-kicker">Workspace</p><h2>Content</h2></div><span class="admin-count"><?= count($items) ?> items</span></div>
<?php if ($items === []): ?><div class="admin-empty"><p>Belum ada content.</p><a class="text-link" href="/admin/content/create">Create your first project →</a></div><?php else: ?>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Project</th><th>Type</th><th>Status</th><th>Updated</th><th></th></tr></thead><tbody>
<?php foreach ($items as $item): ?>
    <tr>
        <td><a class="admin-project-title" href="/admin/content/<?= (int) $item['id'] ?>/edit"><?= $e($item['title_id'] ?: $item['slug']) ?></a><span class="admin-slug">/<?= $e($item['slug']) ?></span></td>
        <td><?= $item['project_type'] === 'software_application' ? 'Software' : 'Creative' ?></td>
        <td><span class="status status-<?= $e($item['status']) ?>"><?= $e(ucfirst($item['status'])) ?></span></td>
        <td><?= $e(substr((string) $item['updated_at'], 0, 10)) ?></td>
        <td class="admin-row-actions"><a class="text-link" href="/admin/content/<?= (int) $item['id'] ?>/edit">Edit</a>
        <?php if ($item['status'] !== 'trashed'): ?>
            <form method="post" action="/admin/content/<?= (int) $item['id'] ?>/trash">
                <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
                <button class="button-link danger-link" type="submit">Trash</button>
            </form>
        <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<section class="admin-card"><div class="admin-card-heading"><div><p class="admin-kicker">Taxonomy</p><h2>Tags</h2></div><span class="admin-count"><?= count($tags) ?> tags</span></div>
<form class="inline-form" method="post" action="/admin/tags/create">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <input class="admin-input" name="name" placeholder="e.g. PHP" required><button class="button button-primary" type="submit">Add tag</button>
</form>
<div class="tag-list">
<?php foreach ($tags as $tag): ?>
    <div class="tag-row"><form class="inline-form" method="post" action="/admin/tags/<?= (int) $tag['id'] ?>/edit">
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <input class="admin-input" name="name" value="<?= $e($tag['name']) ?>" required><button class="button button-secondary" type="submit">Save</button>
        </form>
        <form method="post" action="/admin/tags/<?= (int) $tag['id'] ?>/delete">
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <button class="button-link danger-link" type="submit">Delete</button></form></div>
<?php endforeach; ?>
</div></section></div></section>
