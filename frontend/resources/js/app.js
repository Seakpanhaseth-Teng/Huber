import './bootstrap';

/**
 * Huber — Vanilla JS replacements for Bootstrap interactions
 * Navbar toggle, dropdowns, accordion, alert dismissal
 */
document.addEventListener('DOMContentLoaded', () => {
    // --- Navbar Toggle (mobile hamburger) ---
    const toggler = document.querySelector('[data-toggle="navbar"]');
    const collapse = document.querySelector('.navbar-collapse');
    if (toggler && collapse) {
        toggler.addEventListener('click', () => {
            collapse.classList.toggle('show');
            toggler.classList.toggle('active');
            const expanded = toggler.getAttribute('aria-expanded') === 'true';
            toggler.setAttribute('aria-expanded', String(!expanded));
        });
        document.addEventListener('click', (e) => {
            if (!toggler.closest('.navbar') && collapse.classList.contains('show')) {
                collapse.classList.remove('show');
                toggler.classList.remove('active');
                toggler.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // --- Dropdown Toggle ---
    document.querySelectorAll('[data-toggle="dropdown"]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const menu = btn.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                document.querySelectorAll('.dropdown-menu.show').forEach((m) => m.classList.remove('show'));
                menu.classList.toggle('show');
            }
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu.show').forEach((m) => m.classList.remove('show'));
    });

    // --- Accordion ---
    document.querySelectorAll('[data-toggle="accordion"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            if (!targetId) return;
            const body = document.getElementById(targetId.replace('#', ''));
            if (!body) return;
            const isOpen = body.classList.contains('show');
            const accordion = btn.closest('[data-accordion]');
            if (accordion) {
                accordion.querySelectorAll('.accordion-collapse.show').forEach((b) => {
                    if (b.id !== targetId.replace('#', '')) {
                        b.classList.remove('show');
                        const trigger = accordion.querySelector(`[data-target="#${b.id}"]`);
                        if (trigger) trigger.classList.add('collapsed');
                    }
                });
            }
            body.classList.toggle('show');
            btn.classList.toggle('collapsed', isOpen);
        });
    });

    // --- Alert Dismissal ---
    document.querySelectorAll('[data-dismiss="alert"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const alert = btn.closest('.alert');
            if (alert) alert.remove();
        });
    });
});
