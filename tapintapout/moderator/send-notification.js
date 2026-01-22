// Client-side validation for Send Notification

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('notifyForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const textarea = document.getElementById('message');
        const text = textarea.value.trim();

        if (!text) {
            e.preventDefault();
            alert('Message cannot be empty.');
            return;
        }

        if (text.length > 500) {
            e.preventDefault();
            alert('Message is too long. Please keep it under 500 characters.');
        }
    });
});
