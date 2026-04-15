import './stimulus_bootstrap.js';
/*
 * NORSU Alumni Tracker — main JS entry point
 * Loaded via importmap (ES module = executes exactly once).
 */
import './styles/app.css';

/* ═══════════════════════════════════════════════════════════════
   SIDEBAR HELPERS
   ═══════════════════════════════════════════════════════════════ */
function closeSidebar() {
    const sb = document.getElementById('sidebar');
    const bd = document.getElementById('sidebarBackdrop');
    if (sb) sb.classList.remove('show');
    if (bd) bd.classList.remove('show');
}

function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    const bd = document.getElementById('sidebarBackdrop');
    if (sb) sb.classList.toggle('show');
    if (bd) bd.classList.toggle('show');
}

/* Update which sidebar link gets the .active highlight */
function updateActiveLink() {
    const currentPath = window.location.pathname;
    document.querySelectorAll('#sidebar .sidebar-link').forEach(link => {
        const href = link.getAttribute('href');
        if (!href) return;
        const isActive = href === currentPath
            || (href !== '/' && currentPath.startsWith(href));
        link.classList.toggle('active', isActive);
    });
}

/* ═══════════════════════════════════════════════════════════════
   EVENT DELEGATION (click) — survives every Turbo body swap
   ═══════════════════════════════════════════════════════════════ */
document.addEventListener('click', (e) => {
    /* Sidebar toggle button */
    if (e.target.closest('#sidebarToggle')) {
        toggleSidebar();
        return;
    }
    /* Backdrop click → close */
    if (e.target.id === 'sidebarBackdrop') {
        closeSidebar();
        return;
    }
    /* Sidebar nav-link → close sidebar on mobile */
    if (e.target.closest('#sidebar .sidebar-link')) {
        closeSidebar();
    }

    /* ── User dropdown toggle ── */
    const ddBtn = e.target.closest('#userDropdownBtn');
    if (ddBtn) {
        const menu = document.getElementById('userDropdownMenu');
        if (menu) menu.classList.toggle('hidden');
        return;
    }
    /* Close dropdown when clicking outside */
    if (!e.target.closest('#userDropdownWrap')) {
        const menu = document.getElementById('userDropdownMenu');
        if (menu) menu.classList.add('hidden');
    }

    /* Guest navbar toggle */
    if (e.target.closest('#guestNavToggle')) {
        const mobileNav = document.getElementById('guestNavMobile');
        if (mobileNav) mobileNav.classList.toggle('show');
    }
    /* Close guest nav when link clicked */
    if (e.target.closest('#guestNavMobile a')) {
        const mobileNav = document.getElementById('guestNavMobile');
        if (mobileNav) mobileNav.classList.remove('show');
    }

    /* Role selector buttons on registration page */
    const roleBtn = e.target.closest('.role-select-btn');
    if (roleBtn) {
        const radio = roleBtn.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
            const container = roleBtn.closest('.flex') || roleBtn.parentElement;
            container.querySelectorAll('.role-select-btn').forEach(btn => btn.classList.remove('active'));
            roleBtn.classList.add('active');
        }
    }

    /* ── Tab switching (custom data-tab-target) ── */
    const tabBtn = e.target.closest('[data-tab-target]');
    if (tabBtn) {
        e.preventDefault();
        const target = tabBtn.getAttribute('data-tab-target');
        /* Deactivate all tab buttons in same group */
        const tabGroup = tabBtn.closest('[data-tab-group]') || tabBtn.parentElement;
        tabGroup.querySelectorAll('[data-tab-target]').forEach(t => {
            t.classList.remove('border-norsu', 'text-norsu', 'border-blue-700', 'text-blue-700');
            t.classList.add('border-transparent', 'text-gray-500');
        });
        /* Activate clicked tab */
        tabBtn.classList.add('border-norsu', 'text-norsu');
        tabBtn.classList.remove('border-transparent', 'text-gray-500');
        /* Toggle panes */
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('hidden'));
        const panel = document.querySelector(target);
        if (panel) panel.classList.remove('hidden');
    }

    /* ── Modal open ── */
    const modalOpen = e.target.closest('[data-modal-target]');
    if (modalOpen) {
        e.preventDefault();
        const target = modalOpen.getAttribute('data-modal-target');
        const modal = document.getElementById(target);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }
    /* ── Modal close ── */
    if (e.target.closest('[data-modal-close]')) {
        const modal = e.target.closest('.modal-container');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
    /* ── Modal backdrop click ── */
    if (e.target.classList.contains('modal-backdrop-layer')) {
        const modal = e.target.closest('.modal-container');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
});

/* ═══════════════════════════════════════════════════════════════
   TURBO LIFECYCLE HOOKS
   ═══════════════════════════════════════════════════════════════ */
document.addEventListener('turbo:before-cache', () => {
    closeSidebar();
    const mobileNav = document.getElementById('guestNavMobile');
    if (mobileNav) mobileNav.classList.remove('show');
    /* Remove leftover modal containers so cached snapshot is clean */
    document.querySelectorAll('.modal-container:not(.hidden)').forEach(el => {
        el.classList.add('hidden');
        el.classList.remove('flex');
    });
});

document.addEventListener('turbo:before-visit', () => {
    closeSidebar();
});

document.addEventListener('turbo:load', () => {
    updateActiveLink();
    closeSidebar();
});
