<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
<section class="section-shell search-page"><div class="container"><div class="section-heading"><p class="eyebrow">Search</p><h1><?= $e($title) ?></h1><p class="muted">Query: <?= $e($query) ?></p></div>
<p><a href="/<?= $e($language === 'id' ? 'en' : 'id') ?>?<?= http_build_query(array_filter(['q' => $query, 'tag' => $tag, 'page' => $page], static fn ($value) => $value !== null && $value !== '')) ?>">Switch language</a></p>
<?php if ($items === []): ?>
    <p>No results found.</p>
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
            <a href="/<?= $e($language) ?>?<?= http_build_query(array_filter(['q' => $query, 'tag' => $tag, 'page' => $number], static fn ($value) => $value !== null && $value !== '')) ?>"><?= $number ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
