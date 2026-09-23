(() => {
    const toggle = document.getElementById('tmq-nav-toggle');
    const closeBtn = document.getElementById('tmq-nav-close');
    const sidebar = document.getElementById('tmq-sidebar');
    const overlay = document.getElementById('tmq-sidebar-overlay');

    if (!toggle || !sidebar || !overlay) {
        return;
    }

    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        overlay.hidden = !open;
        overlay.classList.toggle('is-open', open);
        document.body.classList.toggle('tmq-nav-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
    };

    toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open')));
    closeBtn?.addEventListener('click', () => setOpen(false));
    overlay.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
})();
