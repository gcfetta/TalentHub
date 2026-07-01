// main.js — TalentHub

document.addEventListener('DOMContentLoaded', () => {

    // Auto-ocultar alertas después de 4 segundos
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });

    // Resalta la fila de la tabla bajo el puntero (soporte extra para navegadores viejos)
    document.querySelectorAll('.table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', () => row.classList.add('is-hover'));
        row.addEventListener('mouseleave', () => row.classList.remove('is-hover'));
    });

    // Pequeño efecto de "press" en los botones
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mousedown', () => btn.style.transform = 'scale(0.97)');
        btn.addEventListener('mouseup', () => btn.style.transform = '');
        btn.addEventListener('mouseleave', () => btn.style.transform = '');
    });
});

// Confirmar antes de acciones destructivas
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
});