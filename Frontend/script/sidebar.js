(() => {
    console.log('Sidebar script loaded');
    
    // Sidebar HTML template
    const sidebarHTML = `
<button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false">
    <span class="sidebar-toggle__icon" aria-hidden="true"></span>
    <span class="sr-only">Toggle navigation</span>
</button>
<div class="sidebar-backdrop" id="sidebarBackdrop" hidden></div>
<aside id="sidebar" class="sidebar" aria-label="Primary navigation">
    <div class="logo">
        <a href="homePage.php">
            <img src="Pictures/logo.jpeg" alt="EcoGo Logo">
        </a>
    </div>
    <nav>
        <a href="homePage.php" data-page="recycling">
            <img src="Pictures/sidebar/recycle-sign.png" alt="Home Icon">
            <p>Recycling Program</p>
        </a>
        <a href="energyPage.php" data-page="energy">
            <img src="Pictures/sidebar/lamp.png" alt="Home Icon">
            <p>Energy Conservation Tips</p>
        </a>
        <a href="communityPage.php" data-page="community">
            <img src="Pictures/sidebar/garden.png" alt="Home Icon">
            <p>Gardening Community</p>
        </a>
        <a href="swapPage.php" data-page="swap">
            <img src="Pictures/sidebar/swap.png" alt="Home Icon">
            <p>Swap Items</p>
        </a>
        <a href="inboxPage.html" data-page="inbox">
            <img src="Pictures/sidebar/inbox.png" alt="Home Icon">
            <p>Inbox</p>
        </a>
    </nav>
    <div class="profile">
        <a href="userProfile.php" data-page="user-profile">
            <img src="Pictures/sidebar/user.png" alt="Home Icon">
            <p>User Profile</p>
        </a>
    </div>
</aside>
    `;
    
    // Load sidebar
    const loadSidebar = () => {
        console.log('Loading sidebar...');
        // Insert sidebar at the beginning of body
        document.body.insertAdjacentHTML('afterbegin', sidebarHTML);
        console.log('Sidebar HTML inserted');
        
        // Set active link based on current page
        setActiveLink();
        
        // Initialize sidebar functionality
        initializeSidebar();
    };

    // Set active link based on data-page attribute
    const setActiveLink = () => {
        const currentPage = document.body.getAttribute('data-page');
        if (!currentPage) return;

        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        const links = sidebar.querySelectorAll('nav a, .profile a');
        links.forEach(link => {
            if (link.getAttribute('data-page') === currentPage) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    };

    // Initialize sidebar toggle and interactions
    const initializeSidebar = () => {
        const body = document.body;
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('[data-sidebar-toggle]');
        const backdrop = document.getElementById('sidebarBackdrop');
        const mobileBreakpoint = window.matchMedia('(max-width: 1024px)');

        if (!sidebar || !toggle) {
            console.error('Sidebar or toggle button not found');
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
    };

    // Load sidebar when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadSidebar);
    } else {
        loadSidebar();
    }
})();
