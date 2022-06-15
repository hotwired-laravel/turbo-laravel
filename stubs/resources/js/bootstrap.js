import _ from "lodash";
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * The Laravel Echo setup is commented out because it doesn't work out of the box
 * as you will need to setup a WebSocket service to be able to use it. Once that's
 * handled, you may uncomment the following lines to start receiving WebSockets.
 *
 * See: https://github.com/tonysm/turbo-laravel/#broadcasting-turbo-streams-over-websockets-with-laravel-echo
 */

// import Echo from "laravel-echo";
// import Pusher from "pusher-js";

// window.Echo = new Echo({
//     broadcaster: "pusher",
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: process.env.MIX_PUSHER_APP_USE_SSL === "true",
//     disableStats: true,
//     wsHost: process.env.MIX_PUSHER_APP_HOST,
//     wsPort: process.env.MIX_PUSHER_APP_PORT || null,
// });

// document.addEventListener("turbo:before-fetch-request", (e) => {
//     e.detail.fetchOptions.headers["X-Socket-ID"] = window.Echo.socketId();
// });

/**
 * Turbo turns forms and links into AJAX requests using `fetch`. Non-GET form requests in Laravel need a special kind of value called
 * "The CSRF Token" to be allowed to enter your app. When rendering forms in background, such as when you're broadcasting HTML to
 * all users which contain a form on it, we don't have the receiving users' tokens, so we need to add it to the fetch request.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

document.addEventListener("turbo:before-fetch-request", (e) => {
    if (token) {
        e.detail.fetchOptions.headers["X-CSRF-Token"] = token.content;
    } else {
        console.error("CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token");
    }
});
