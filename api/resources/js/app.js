import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-dismiss="alert"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const alert = btn.closest('.alert');
            if (alert) alert.remove();
        });
    });
});
