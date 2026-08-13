<?php

$title = $title ?? 'Portfolio';
$language = $language ?? 'id';
$metaRobots = $metaRobots ?? null;
$seo = $seo ?? [];
$isAdmin = str_starts_with((string) ($_SERVER['REQUEST_URI'] ?? ''), '/admin');
?>
<!doctype html>
<html lang="<?= htmlspecialchars($language, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10131a">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/site.css">
    <link rel="stylesheet" href="/assets/css/content.css">
    <link rel="stylesheet" href="/assets/css/editor.css">
    <?php if ($isAdmin): ?><link rel="stylesheet" href="/assets/css/admin.css"><?php endif; ?>
    <link rel="icon" href="/assets/img/admin-icon.ico" sizes="any">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if (!empty($seo['description'])): ?>
        <meta name="description" content="<?= htmlspecialchars($seo['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if (!empty($seo['canonical'])): ?>
        <link rel="canonical" href="<?= htmlspecialchars($seo['canonical'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <?php endif; ?>
    <?php foreach (($seo['alternates'] ?? []) as $alternate => $url): ?>
        <link rel="alternate" hreflang="<?= htmlspecialchars($alternate, ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <?php endforeach; ?>
    <?php if (!empty($seo['canonical'])): ?>
        <meta property="og:title" content="<?= htmlspecialchars($seo['title'] ?? $title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <meta property="og:description" content="<?= htmlspecialchars($seo['description'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <meta property="og:url" content="<?= htmlspecialchars($seo['canonical'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <?php if (!empty($seo['og_image'])): ?><meta property="og:image" content="<?= htmlspecialchars($seo['og_image'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?php endif; ?>
    <?php endif; ?>
    <?php if (isset($seo['schema']) && $seo['schema'] !== null): ?>
        <script type="application/ld+json"><?= json_encode($seo['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
    <?php if ($metaRobots !== null): ?>
        <meta name="robots" content="<?= htmlspecialchars($metaRobots, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
</head>
<body class="site-body<?= $isAdmin ? ' admin-body' : '' ?>">
<?php if ($isAdmin): ?><button class="admin-theme-toggle" type="button" data-admin-theme-toggle aria-pressed="false"><span data-admin-theme-icon>☾</span><span data-admin-theme-label>Dark</span></button><?php endif; ?>
<header class="site-header" data-navbar>
    <nav class="site-nav container" aria-label="Primary navigation">
        <a class="wordmark" href="/<?= htmlspecialchars($language, ENT_QUOTES, 'UTF-8') ?>">dkwibowo</a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu" data-menu-toggle>
            <span class="sr-only">Open menu</span>
            <span aria-hidden="true">☰</span>
        </button>
        <div class="nav-menu" id="primary-menu" data-menu>
            <a href="/<?= $language ?>#work">Work</a>
            <a href="/<?= $language ?>#skills">Skills</a>
            <a href="/<?= $language ?>#experience">Experience</a>
            <a href="/<?= $language ?>/project">Project</a>
            <a href="/<?= $language ?>#about">About</a>
            <a href="/<?= $language ?>#contact">Contact</a>
            <a class="nav-language" href="/<?= $language === 'id' ? 'en' : 'id' ?>" lang="<?= $language === 'id' ? 'en' : 'id' ?>"><?= $language === 'id' ? 'EN' : 'ID' ?></a>
            <?php if (!$isAdmin): ?><button class="site-theme-toggle" type="button" data-site-theme-toggle aria-pressed="false"><span data-site-theme-icon>☾</span><span data-site-theme-label>Dark</span></button><?php endif; ?>
        </div>
    </nav>
</header>
<main>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div id="main-content">
        <?= $body ?>
    </div>
</main>
<?php if (!$isAdmin): ?><footer class="site-footer" id="contact">
    <div class="container footer-grid">
        <div>
            <p class="eyebrow">dkwibowo</p>
            <h2><?= $language === 'id' ? 'Mari bangun sesuatu yang berarti.' : 'Let’s build something meaningful.' ?></h2>
            <p class="muted"><?= $language === 'id' ? 'Terbuka untuk percakapan tentang product, engineering, dan AI.' : 'Open to conversations about product, engineering, and AI.' ?></p>
        </div>
        <div class="footer-links">
            <a href="mailto:me@dkwibowo.my.id">me@dkwibowo.my.id</a>
            <a href="https://wa.me/+628113416622" target="_blank">WhatsApp</a>
            <a href="https://www.linkedin.com/in/dirgahayu/" target="_blank">LinkedIn</a>
            <a href="https://github.com/dirg-bisma" target="_blank">GitHub</a>
            <a href="https://medium.com/@dirg.zeus" target="_blank">Medium</a>
        </div>
    </div>
    <div class="container footer-bottom"><span>© <?= date('Y') ?> dkwibowo</span><a href="#main-content">Back to top ↑</a></div>
</footer><?php endif; ?>
<script src="/assets/js/site.js" defer></script>
</body>
</html>
