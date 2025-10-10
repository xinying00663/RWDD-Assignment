(() => {
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.getElementById('sidebarBackdrop');
    const mobileBreakpoint = window.matchMedia('(max-width: 1024px)');

    if (!sidebar || !toggle) {
        return;
    }

    if (!sidebar.hasAttribute('tabindex')) {
        sidebar.setAttribute('tabindex', '-1');
    }

    const toggleSidebar = () => {
        const isOpen = body.classList.toggle('sidebar-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
        if (backdrop) {
            backdrop.toggleAttribute('hidden', !isOpen);
        }
    };

    const handleBreakpointChange = (event) => {
        if (!event.matches) {
            body.classList.remove('sidebar-open');
            toggle.setAttribute('aria-expanded', 'false');
            backdrop?.setAttribute('hidden', '');
        }
    };

    const closeSidebar = () => {
        body.classList.remove('sidebar-open');
        toggle.setAttribute('aria-expanded', 'false');
        backdrop?.setAttribute('hidden', '');
    };

    toggle.addEventListener('click', toggleSidebar);

    backdrop?.addEventListener('click', closeSidebar);

    sidebar.addEventListener('click', (event) => {
        if (
            mobileBreakpoint.matches &&
            event.target instanceof Element &&
            event.target.closest('a')
        ) {
            closeSidebar();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && body.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });

    mobileBreakpoint.addEventListener('change', handleBreakpointChange);
    handleBreakpointChange(mobileBreakpoint);
})();
