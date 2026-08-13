(() => {
    const adminThemeToggle = document.querySelector('[data-admin-theme-toggle]');
    if (adminThemeToggle && document.body.classList.contains('admin-body')) {
        const savedTheme = localStorage.getItem('admin-theme') || 'dark';
        const setAdminTheme = (theme) => {
            const light = theme === 'light';
            document.body.dataset.theme = theme;
            adminThemeToggle.setAttribute('aria-pressed', String(light));
            adminThemeToggle.querySelector('[data-admin-theme-icon]').textContent = light ? '☀' : '☾';
            adminThemeToggle.querySelector('[data-admin-theme-label]').textContent = light ? 'Light' : 'Dark';
        };
        setAdminTheme(savedTheme);
        adminThemeToggle.addEventListener('click', () => {
            const theme = document.body.dataset.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('admin-theme', theme);
            setAdminTheme(theme);
        });
    }

    const siteThemeToggle = document.querySelector('[data-site-theme-toggle]');
    if (siteThemeToggle && !document.body.classList.contains('admin-body')) {
        const setSiteTheme = (theme) => {
            const light = theme === 'light';
            document.body.dataset.theme = theme;
            siteThemeToggle.setAttribute('aria-pressed', String(light));
            siteThemeToggle.querySelector('[data-site-theme-icon]').textContent = light ? '☀' : '☾';
            siteThemeToggle.querySelector('[data-site-theme-label]').textContent = light ? 'Light' : 'Dark';
        };
        setSiteTheme(localStorage.getItem('site-theme') || 'dark');
        siteThemeToggle.addEventListener('click', () => {
            const theme = document.body.dataset.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('site-theme', theme);
            setSiteTheme(theme);
        });
    }

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

    document.querySelectorAll('[data-markdown-editor]').forEach((editor) => {
        const input = editor.querySelector('[data-markdown-input]');
        const preview = editor.querySelector('[data-markdown-preview]');
        const csrf = editor.closest('form')?.querySelector('[name="_csrf"]')?.value || '';
        let previewTimer;

        const imageButton = document.createElement('button');
        imageButton.type = 'button';
        imageButton.className = 'markdown-image-button';
        imageButton.textContent = '▧';
        imageButton.title = 'Insert image (JPG, PNG, GIF, WebP, SVG · max 2 MB)';
        imageButton.setAttribute('aria-label', imageButton.title);
        imageButton.dataset.markdownAction = 'image';
        editor.querySelector('.markdown-toolbar')?.insertBefore(imageButton, editor.querySelector('[data-markdown-action="preview"]'));
        const imageInput = document.createElement('input');
        imageInput.type = 'file';
        imageInput.accept = 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml';
        imageInput.hidden = true;
        editor.appendChild(imageInput);

        imageButton.addEventListener('click', () => imageInput.click());
        imageInput.addEventListener('change', async () => {
            const file = imageInput.files?.[0];
            if (!file) return;
            const data = new FormData();
            data.append('_csrf', csrf);
            data.append('file', file);
            imageButton.disabled = true;
            imageButton.textContent = '…';
            try {
                const response = await fetch('/admin/upload/inline', {method: 'POST', body: data});
                const body = await response.text();
                const result = JSON.parse(body);
                if (!response.ok || !result.path) throw new Error(result.error || 'Image upload failed.');
                const mediaPath = '/' + result.path.replace(/^storage\/media\//, 'media/');
                const markdown = `![${file.name}](${mediaPath})`;
                input.setRangeText(markdown, input.selectionStart, input.selectionEnd, 'end');
                input.focus();
                input.dispatchEvent(new Event('input', {bubbles: true}));
            } catch (error) {
                window.alert(error.message || 'Image upload failed.');
            } finally {
                imageButton.disabled = false;
                imageButton.textContent = '▧';
                imageInput.value = '';
            }
        });

        const replaceSelection = (before, after = before) => {
            const start = input.selectionStart;
            const end = input.selectionEnd;
            const selected = input.value.slice(start, end) || 'text';
            input.setRangeText(before + selected + after, start, end, 'select');
            input.focus();
            input.dispatchEvent(new Event('input', {bubbles: true}));
        };

        const renderPreview = async () => {
            const data = new FormData();
            data.append('_csrf', csrf);
            data.append('markdown', input.value);
            try {
                const response = await fetch('/admin/preview/markdown', {method: 'POST', body: data});
                const body = await response.text();
                let result;
                try {
                    result = JSON.parse(body);
                } catch {
                    throw new Error(`Preview request failed (${response.status}). Please refresh the page and try again.`);
                }
                if (!response.ok) throw new Error(result.error || 'Preview failed.');
                preview.innerHTML = '<span class="field-hint">Preview</span>' + result.html;
            } catch (error) {
                preview.innerHTML = '<span class="field-hint">Preview unavailable</span><p>' + (error.message || 'Preview failed.') + '</p>';
            }
        };

        editor.querySelectorAll('[data-markdown-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.dataset.markdownAction;
                if (action === 'heading') replaceSelection('## ', '');
                if (action === 'bold') replaceSelection('**');
                if (action === 'italic') replaceSelection('*');
                if (action === 'list') replaceSelection('- ', '');
                if (action === 'table') {
                    const table = '| Parameter | Detail |\n| --- | --- |\n| Item | Description |\n';
                    input.setRangeText(table, input.selectionStart, input.selectionEnd, 'end');
                    input.focus();
                    input.dispatchEvent(new Event('input', {bubbles: true}));
                }
                if (action === 'preview') {
                    const visible = preview.hidden;
                    preview.hidden = !visible;
                    button.classList.toggle('is-active', visible);
                    if (visible) renderPreview();
                }
            });
        });

        input.addEventListener('input', () => {
            if (preview.hidden) return;
            window.clearTimeout(previewTimer);
            previewTimer = window.setTimeout(renderPreview, 350);
        });
    });
})();
