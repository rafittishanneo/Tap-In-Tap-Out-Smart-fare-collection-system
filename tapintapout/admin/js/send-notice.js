// Basic client-side validation for Send Notice

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('noticeForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const title = document.getElementById('title').value.trim();
        const body  = document.getElementById('body').value.trim();

        if (!title || !body) {
            e.preventDefault();
            alert('Title and message are required.');
            return;
        }
        if (title.length > 100) {
            e.preventDefault();
            alert('Title is too long (max 100 characters).');
            return;
        }
        if (body.length > 1000) {
            e.preventDefault();
            alert('Message is too long (max 1000 characters).');
        }
    });
});
