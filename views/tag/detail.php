<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
<section class="section-shell tag-page"><div class="container"><div class="section-heading"><p class="eyebrow">Tag archive</p><h1><?= $e($tag['name']) ?></h1></div>
<p><a href="/<?= $e($language === 'id' ? 'en' : 'id') ?>/tag/<?= $e($tag['slug']) ?>?page=<?= $page ?>">Switch language</a></p>
<?php if ($items === []): ?>
    <p>No published projects found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($items as $item): ?>
            <li><a href="/<?= $e($language) ?>/content/<?= $e($item['slug']) ?>"><?= $e($language === 'id' ? $item['title_id'] : $item['title_en']) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?></div></section>
<?php if ($totalPages > 1): ?>
    <nav aria-label="Pagination">
        <?php for ($number = 1; $number <= $totalPages; $number++): ?>
            <a href="/<?= $e($language) ?>/tag/<?= $e($tag['slug']) ?>?page=<?= $number ?>"><?= $number ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
