<?php
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$skills = [
    ['eyebrow' => '01', 'title' => 'AI & Machine Learning', 'body' => 'Building practical AI systems, intelligent workflows, and data-informed products.'],
    ['eyebrow' => '02', 'title' => 'Backend Systems', 'body' => 'Designing reliable APIs, data models, and services that scale with the product.'],
    ['eyebrow' => '03', 'title' => 'Product Engineering', 'body' => 'Connecting robust architecture with clear, thoughtful user experiences.'],
    ['eyebrow' => '04', 'title' => 'DevOps & Cloud', 'body' => 'Implementing scalable infrastructure and streamlining development workflows.'],
    ['eyebrow' => '05', 'title' => 'Data Engineering', 'body' => 'Building pipelines, data warehouses, and analytics solutions for actionable insights.'],
    ['eyebrow' => '06', 'title' => 'Security & Compliance', 'body' => 'Ensuring systems are secure, compliant, and resilient against threats.'],
];
$experience = [
    ['period' => 'Jan 2023 - Present · 3 yrs 8 mos', 'role' => 'Chief Executive Officer', 'company' => 'CV Sinergi Teknokarya · Full-time', 'location' => 'Jember, East Java, Indonesia · Hybrid', 'mark' => 'Sinergi'],
    ['period' => 'Jan 2024 - Apr 2026 · 2 yrs 4 mos', 'role' => 'Senior Software Engineer', 'company' => 'PT. Sinergi Gula Nusantara (SGN) · Full-time', 'location' => 'Surabaya, East Java, Indonesia · Hybrid', 'skills' => 'Software Industry, Software Infrastructure and +1 skill', 'mark' => 'SGN'],
    ['period' => 'Jul 2016 - Jan 2024 · 7 yrs 7 mos', 'role' => 'Senior Software Engineer', 'company' => 'PT Perkebunan Nusantara XI · Full-time', 'location' => 'Kota Surabaya, East Java, Indonesia · Hybrid', 'skills' => 'Software Infrastructure, Software Industry and +1 skill', 'mark' => 'PN XI'],
    ['period' => 'Jan 2012 - Jan 2015 · 3 yrs 1 mo', 'role' => 'IT Support Specialist', 'company' => 'UNIVERSITAS JEMBER · Full-time', 'location' => 'Jember, East Java, Indonesia', 'mark' => 'UNEJ'],
    ['period' => 'Jan 2009 - Jan 2013 · 4 yrs 1 mo', 'role' => 'Junior Software Engineer', 'company' => 'cv trust solusindo · Full-time', 'location' => 'Surabaya, East Java, Indonesia · On-site', 'mark' => 'trust'],
];
?>
<section class="hero section-shell" id="about">
    <div class="hero-orbit" aria-hidden="true"></div>
    <div class="container hero-grid">
        <div class="hero-copy reveal">
            <p class="eyebrow"><span class="status-dot"></span><?= $e($copy['eyebrow']) ?></p>
            <h1><?= $e($copy['heading']) ?></h1>
            <p class="hero-lede"><?= $e($copy['body']) ?></p>
            <div class="hero-actions">
                <a class="button button-primary" href="mailto:me@dkwibowo.my.id"><?= $e($copy['primary_cta']) ?> <span aria-hidden="true">↗</span></a>
                <a class="button button-secondary" href="#work"><?= $e($copy['secondary_cta']) ?></a>
            </div>
            <div class="hero-meta">
                <span><?= $e($copy['availability']) ?></span>
                <span class="meta-divider" aria-hidden="true"></span>
                <a href="https://www.linkedin.com/in/dirgahayu/">LinkedIn</a>
                <a href="https://github.com/dirg-bisma">GitHub</a>
            </div>
        </div>
        <div class="hero-portrait reveal stagger-2" data-parallax="0.05">
            <div class="portrait-glow" aria-hidden="true"></div>
            <figure class="portrait-frame">
                <img src="/assets/img/profile.webp" alt="Dirga Hayu, Full-Stack Engineer and AI practitioner" width="896" height="1152">
                <figcaption><span>dkwibowo</span><span>01 / PROFILE</span></figcaption>
            </figure>
        </div>
    </div>
    <a class="scroll-cue" href="#skills"><span>SCROLL</span><i aria-hidden="true"></i></a>
</section>

<section class="section-shell" id="skills">
    <div class="container">
        <div class="section-heading reveal"><p class="eyebrow">Capability</p><h2>How I work across the stack.</h2><p class="muted">A focused toolkit for turning ambitious ideas into dependable products.</p></div>
        <div class="skill-grid">
            <?php foreach ($skills as $skill): ?>
                <article class="panel skill-card reveal">
                    <span class="card-index"><?= $e($skill['eyebrow']) ?></span>
                    <h3><?= $e($skill['title']) ?></h3>
                    <p class="muted"><?= $e($skill['body']) ?></p>
                    <span class="card-arrow" aria-hidden="true">↗</span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-shell section-darker" id="experience">
    <div class="container">
        <div class="section-heading reveal"><p class="eyebrow">Experience</p><h2>Building with context, not just code.</h2></div>
        <div class="timeline">
            <?php foreach ($experience as $item): ?>
                <article class="timeline-item reveal">
                    <div class="timeline-marker" aria-hidden="true"></div>
                    <div class="experience-mark" aria-hidden="true"><?= $e($item['mark']) ?></div>
                    <div class="timeline-copy"><h3><?= $e($item['role']) ?></h3><p class="experience-company"><?= $e($item['company']) ?></p><p class="timeline-period"><?= $e($item['period']) ?></p><p class="muted timeline-location"><?= $e($item['location']) ?></p><?php if (!empty($item['skills'])): ?><p class="experience-skills"><span aria-hidden="true">◇</span> <?= $e($item['skills']) ?></p><?php endif; ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-shell" id="work">
    <div class="container">
        <div class="section-heading section-heading-row reveal"><div><p class="eyebrow">Selected work</p><h2>Recent projects, built with intent.</h2></div><a class="text-link" href="/<?= $e($language) ?>/project">View all projects <span aria-hidden="true">↗</span></a></div>
        <?php if ($items === []): ?>
            <div class="empty-state panel"><p><?= $language === 'id' ? 'Project published akan tampil di sini.' : 'Published projects will appear here.' ?></p><a class="button button-secondary" href="/<?= $e($language) ?>/project">Browse project archive</a></div>
        <?php else: ?>
            <div class="project-grid">
                <?php foreach (array_slice($items, 0, 5) as $index => $item): ?>
                    <?php $cover = is_string($item['cover_image'] ?? null) && str_starts_with($item['cover_image'], 'storage/media/') ? '/media/' . ltrim(substr($item['cover_image'], strlen('storage/media/')), '/') : ''; ?>
                    <a class="project-card panel reveal" href="/<?= $e($language) ?>/content/<?= $e($item['slug']) ?>">
                        <?php if ($cover !== ''): ?><img src="<?= $e($cover) ?>" alt="" loading="lazy" width="1200" height="760"><?php else: ?><div class="project-fallback" aria-hidden="true"><span><?= $e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span></div><?php endif; ?>
                        <div class="project-overlay"></div>
                        <div class="project-content"><span class="tag-label"><?= $e(str_replace('_', ' ', (string) ($item['project_type'] ?? 'creative_work'))) ?></span><h3><?= $e($language === 'id' ? $item['title_id'] : $item['title_en']) ?></h3><span class="project-link">Explore project ↗</span></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
