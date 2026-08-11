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
})();
