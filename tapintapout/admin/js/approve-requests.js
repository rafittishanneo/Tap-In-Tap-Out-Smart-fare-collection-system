// Approve / reject confirmation dialogs

document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.js-confirm');

    buttons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            const message = this.getAttribute('data-message') ||
                'Are you sure you want to continue?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
