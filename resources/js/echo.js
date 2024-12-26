import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_CLUSTER,
    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: import.meta.env.VITE_PUSHER_PORT,
    forceTLS: false,
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws'],
});

window.Echo.private(`rows.${window.userId}`)
.listen('.AllRowsCreated', (event) => {
    updateNewRowsNotification(event.rows_count);
})

function updateNewRowsNotification(count) {
    const notification = document.getElementById('newRowsNotification');
    if (notification) {
        const message = document.getElementById('newRowsMessage');
        const refreshButton = document.getElementById('refreshButton');

        notification.style.display = 'block';
        message.textContent = `Новые строки добавлены: ${count}.`;

        refreshButton.addEventListener('click', () => {
            window.location.reload();
        });
    }
}

