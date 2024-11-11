import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '5409e448d421808b3abf',
    cluster: 'ap1',
    forceTLS: true,
});
