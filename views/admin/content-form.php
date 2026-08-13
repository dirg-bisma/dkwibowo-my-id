<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); $editing = isset($content['id']); ?>
<section class="admin-page container admin-form-page">
<div class="admin-breadcrumb"><a href="/admin">Dashboard</a><span>/</span><span><?= $editing ? 'Edit project' : 'New project' ?></span></div>
<div class="admin-topbar"><div><p class="admin-kicker">Content workspace</p><h1><?= $e($title) ?></h1><p class="admin-lede">Lengkapi metadata dan isi dalam dua bahasa sebelum dipublikasikan.</p></div><?php if ($editing): ?><span class="status status-<?= $e($content['status']) ?>"><?= $e(ucfirst($content['status'])) ?></span><?php endif; ?></div>
<form class="admin-form" method="post" action="<?= $editing ? '/admin/content/' . (int) $content['id'] . '/edit' : '/admin/content/create' ?>">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <section class="admin-card"><div class="admin-card-heading"><div><p class="admin-kicker">01 · Identity</p><h2>Project details</h2></div></div><div class="form-grid">
    <label>Slug <span class="field-hint">URL identifier</span><input class="admin-input" name="slug" value="<?= $e((string) $content['slug']) ?>" placeholder="my-project" required></label>
    <label>Project type <select class="admin-input" name="project_type">
            <option value="creative_work"<?= $content['project_type'] === 'creative_work' ? ' selected' : '' ?>>Creative work</option>
            <option value="software_application"<?= $content['project_type'] === 'software_application' ? ' selected' : '' ?>>Software application</option>
        </select></label><label>Title ID <input class="admin-input" name="title_id" value="<?= $e((string) $content['title_id']) ?>" required></label><label>Title EN <input class="admin-input" name="title_en" value="<?= $e((string) $content['title_en']) ?>" required></label><label>Cover path <span class="field-hint">Upload path, e.g. /media/cover/...</span><input class="admin-input" name="cover_image" value="<?= $e((string) $content['cover_image']) ?>"></label><label class="form-span">Tags <span class="field-hint">Pisahkan dengan koma</span><input class="admin-input" name="tags" value="<?= $e((string) $content['tags']) ?>" placeholder="php, sqlite"></label></div></section>
    <section class="admin-card"><div class="admin-card-heading"><div><p class="admin-kicker">02 · Content</p><h2>Markdown body</h2></div><span class="field-hint">ID + English required to publish</span></div><div class="form-grid form-grid-content"><label>Bahasa Indonesia<textarea class="admin-input markdown-input" name="content_id" rows="16" placeholder="# Judul project..."><?= $e((string) $content['content_id']) ?></textarea></label><label>English<textarea class="admin-input markdown-input" name="content_en" rows="16" placeholder="# Project title..."><?= $e((string) $content['content_en']) ?></textarea></label></div></section>
    <div class="form-actions"><a class="button button-secondary" href="/admin">Cancel</a><button class="button button-primary" type="submit">Save draft</button></div>
</form>
<?php if ($editing): ?>
    <section class="admin-card lifecycle-card"><div class="admin-card-heading"><div><p class="admin-kicker">03 · Lifecycle</p><h2>Publishing actions</h2></div></div><div class="lifecycle-actions">
    <?php foreach (['publish' => 'Publish', 'archive' => 'Archive', 'restore-draft' => 'Restore as draft', 'restore-published' => 'Restore as published'] as $action => $label): ?>
        <form method="post" action="/admin/content/<?= (int) $content['id'] ?>/<?= $action ?>" style="display:inline">
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <button class="button button-secondary" type="submit"><?= $e($label) ?></button>
        </form>
    <?php endforeach; ?></div></section>
<?php endif; ?>
</section>
