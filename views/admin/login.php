<section class="admin-login-page">
    <div class="admin-login-orbit admin-login-orbit-one" aria-hidden="true"></div>
    <div class="admin-login-orbit admin-login-orbit-two" aria-hidden="true"></div>
    <div class="admin-login-card">
        <div class="admin-login-brand" aria-hidden="true">d<span>k</span></div>
        <p class="admin-kicker">Private workspace</p>
        <h1>Welcome back.</h1>
        <p class="admin-login-lede">Masuk untuk mengelola project, story, dan content portfolio Anda.</p>
        <a class="admin-google-button" href="<?= htmlspecialchars($loginUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <span class="google-mark" aria-hidden="true">G</span>
            <span>Continue with Google</span>
            <span class="login-arrow" aria-hidden="true">↗</span>
        </a>
        <p class="admin-login-note"><span class="secure-dot" aria-hidden="true"></span> Secure access for authorized administrators</p>
    </div>
    <p class="admin-login-footer"><a href="/id">← Back to portfolio</a><span>© <?= date('Y') ?> dkwibowo</span></p>
</section>
