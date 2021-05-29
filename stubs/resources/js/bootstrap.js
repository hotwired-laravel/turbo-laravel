window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: process.env.MIX_PUSHER_APP_USE_SSL === "true",
    disableStats: true,
    wsHost: process.env.MIX_PUSHER_APP_HOST,
    wsPort: process.env.MIX_PUSHER_APP_PORT || null,
});

document.addEventListener('turbo:before-fetch-request', (e) => {
    e.detail.fetchOptions.headers['X-Socket-ID'] = window.Echo.socketId();
});
