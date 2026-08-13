(() => {
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-menu]');
    if (menuToggle && menu) {
        menuToggle.addEventListener('click', () => {
            const open = menu.classList.toggle('is-open');
            menuToggle.setAttribute('aria-expanded', String(open));
        });
        menu.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                menu.classList.remove('is-open');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const navbar = document.querySelector('[data-navbar]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (navbar) {
        window.addEventListener('scroll', () => navbar.classList.toggle('is-scrolled', window.scrollY > 24), {passive: true});
    }

    const reveals = document.querySelectorAll('.reveal');
    if (reducedMotion || !('IntersectionObserver' in window)) {
        reveals.forEach((element) => element.classList.add('is-visible'));
    } else {
        const observer = new IntersectionObserver((entries, instance) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    instance.unobserve(entry.target);
                }
            });
        }, {threshold: .12, rootMargin: '0px 0px -40px'});
        reveals.forEach((element) => observer.observe(element));
    }

    const parallax = document.querySelectorAll('[data-parallax]');
    if (!reducedMotion && parallax.length) {
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (ticking) return;
            window.requestAnimationFrame(() => {
                parallax.forEach((element) => {
                    const speed = Number(element.dataset.parallax || 0.05);
                    element.style.transform = `translateY(${window.scrollY * speed}px)`;
                });
                ticking = false;
            });
            ticking = true;
        }, {passive: true});
    }

    const coverUpload = document.querySelector('[data-cover-upload]');
    if (coverUpload) {
        const fileInput = coverUpload.querySelector('[data-cover-file]');
        const pathInput = coverUpload.querySelector('[data-cover-path]');
        const preview = coverUpload.querySelector('[data-cover-preview]');
        const label = coverUpload.querySelector('[data-cover-label]');
        const status = coverUpload.querySelector('[data-cover-status]');
        const csrf = coverUpload.closest('form')?.querySelector('[name="_csrf"]')?.value;

        fileInput?.addEventListener('change', async () => {
            const file = fileInput.files?.[0];
            if (!file) return;
            const data = new FormData();
            data.append('_csrf', csrf || '');
            data.append('file', file);
            label.textContent = 'Uploading…';
            status.textContent = 'Uploading cover image…';
            fileInput.disabled = true;
            try {
                const response = await fetch(coverUpload.dataset.uploadUrl, {method: 'POST', body: data});
                const result = await response.json();
                if (!response.ok || !result.path) throw new Error(result.error || 'Upload failed.');
                pathInput.value = result.path;
                preview.src = '/media/' + result.path.replace(/^storage\/media\//, '');
                preview.classList.remove('is-hidden');
                label.textContent = 'Replace cover image';
                status.textContent = 'Cover uploaded and ready to save.';
            } catch (error) {
                label.textContent = 'Choose cover image';
                status.textContent = error.message || 'Upload failed.';
                fileInput.value = '';
            } finally {
                fileInput.disabled = false;
            }
        });
    }
})();
