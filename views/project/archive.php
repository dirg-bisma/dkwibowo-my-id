<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
<section class="section-shell archive-page">
    <div class="container">
        <div class="section-heading reveal"><p class="eyebrow">Project archive</p><h1><?= $language === 'id' ? 'Semua project.' : 'All projects.' ?></h1><p class="muted"><?= $language === 'id' ? 'Kumpulan project yang dipilih dan dipublikasikan di dkwibowo.' : 'A collection of selected projects published on dkwibowo.' ?></p></div>
        <?php if ($items === []): ?>
            <div class="empty-state panel"><p><?= $language === 'id' ? 'Belum ada project yang dipublikasikan.' : 'No projects have been published yet.' ?></p></div>
        <?php else: ?>
            <div class="archive-list">
                <?php foreach ($items as $index => $item): ?>
                    <?php $cover = is_string($item['cover_image'] ?? null) && str_starts_with($item['cover_image'], 'storage/media/') ? '/media/' . ltrim(substr($item['cover_image'], strlen('storage/media/')), '/') : ''; ?>
                    <a class="archive-item reveal" href="/<?= $e($language) ?>/content/<?= $e($item['slug']) ?>">
                        <span class="archive-number"><?= $e(str_pad((string) ($index + 1 + (($page - 1) * 5)), 2, '0', STR_PAD_LEFT)) ?></span>
                        <?php if ($cover !== ''): ?><img src="<?= $e($cover) ?>" alt="" loading="lazy" width="320" height="200"><?php else: ?><div class="archive-fallback" aria-hidden="true"></div><?php endif; ?>
                        <span class="archive-info"><span class="tag-label"><?= $e(str_replace('_', ' ', (string) ($item['project_type'] ?? 'creative_work'))) ?></span><strong><?= $e($language === 'id' ? $item['title_id'] : $item['title_en']) ?></strong><span class="muted">/<?= $e($item['slug']) ?></span></span><span class="archive-arrow" aria-hidden="true">↗</span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ($totalPages > 1): ?><nav class="pagination" aria-label="Pagination"><?php for ($number = 1; $number <= $totalPages; $number++): ?><a class="<?= $number === $page ? 'is-active' : '' ?>" href="/<?= $e($language) ?>/project?page=<?= $number ?>"><?= $number ?></a><?php endfor; ?></nav><?php endif; ?>
        <?php endif; ?>
    </div>
</section>
