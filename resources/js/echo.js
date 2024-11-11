import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'fd867c95fcf23f864c2e',
    cluster: 'ap1',
    forceTLS: true,
});
