<?php $e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
<article class="content-page container">
    <header>
        <h1><?= $e($contentTitle) ?></h1>
    </header>
    <section class="content-body"><?= $body ?></section>
    <p class="content-switch"><a class="text-link" href="/<?= $e($language === 'id' ? 'en' : 'id') ?>/content/<?= $e($content['slug']) ?>">Switch language ↗</a></p>
</article>
