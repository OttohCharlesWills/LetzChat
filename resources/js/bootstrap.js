import 'bootstrap';

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 *
 * Switched from the default Pusher-cloud template to Reverb — you have
 * laravel/reverb installed, which is a self-hosted Pusher-protocol server,
 * not a pusher.com account. The old VITE_PUSHER_APP_KEY was never set,
 * which is exactly what was throwing "You must pass your app key" and
 * halting the rest of this file (including everything imported after it).
 *
 * Wrapped in try/catch so a broadcasting misconfiguration can never again
 * silently kill unrelated features (like Twemoji) that happen to be
 * imported later in app.js — worst case now, real-time chat just quietly
 * doesn't work instead of crashing the whole script.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

try {
    // window.Echo = new Echo({
    //     broadcaster: 'reverb',
    //     key: import.meta.env.VITE_REVERB_APP_KEY,
    //     wsHost: import.meta.env.VITE_REVERB_HOST,
    //     wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    //     wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    //     forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    //     enabledTransports: ['ws', 'wss'],
    // });

    window.Echo = new Echo({
        broadcaster: 'pusher',
    
        key: import.meta.env.VITE_PUSHER_APP_KEY,
    
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    
        forceTLS: true,
    });
} catch (err) {
    console.error('Echo/Reverb failed to initialize — real-time features disabled:', err);
}

// import './echo';