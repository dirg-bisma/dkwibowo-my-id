<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
<h1><?= $e($title) ?></h1>
<?php $editing = isset($content['id']); ?>
<form method="post" action="<?= $editing ? '/admin/content/' . (int) $content['id'] . '/edit' : '/admin/content/create' ?>">
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <label>Slug <input name="slug" value="<?= $e((string) $content['slug']) ?>" required></label><br>
    <label>Project type
        <select name="project_type">
            <option value="creative_work"<?= $content['project_type'] === 'creative_work' ? ' selected' : '' ?>>Creative work</option>
            <option value="software_application"<?= $content['project_type'] === 'software_application' ? ' selected' : '' ?>>Software application</option>
        </select>
    </label><br>
    <label>Title ID <input name="title_id" value="<?= $e((string) $content['title_id']) ?>" required></label><br>
    <label>Title EN <input name="title_en" value="<?= $e((string) $content['title_en']) ?>" required></label><br>
    <label>Cover path <input name="cover_image" value="<?= $e((string) $content['cover_image']) ?>"></label><br>
    <label>Tags <input name="tags" value="<?= $e((string) $content['tags']) ?>" placeholder="php, sqlite"></label><br>
    <label>Markdown ID<br><textarea name="content_id" rows="12" cols="80"><?= $e((string) $content['content_id']) ?></textarea></label><br>
    <label>Markdown EN<br><textarea name="content_en" rows="12" cols="80"><?= $e((string) $content['content_en']) ?></textarea></label><br>
    <button type="submit">Save draft</button>
</form>
<?php if ($editing): ?>
    <p>Status: <?= $e((string) $content['status']) ?></p>
    <?php foreach (['publish' => 'Publish', 'archive' => 'Archive', 'restore-draft' => 'Restore as draft', 'restore-published' => 'Restore as published'] as $action => $label): ?>
        <form method="post" action="/admin/content/<?= (int) $content['id'] ?>/<?= $action ?>" style="display:inline">
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <button type="submit"><?= $e($label) ?></button>
        </form>
    <?php endforeach; ?>
<?php endif; ?>
