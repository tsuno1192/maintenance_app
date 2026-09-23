import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * WebSocket (Laravel Echo) — Reverb / Pusher 利用時のみ有効化。
 * VITE_REVERB_* または VITE_PUSHER_* が無い場合は午睡画面がポーリングにフォールバックする。
 */
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;

if (reverbKey || pusherKey) {
    Promise.all([
        import('laravel-echo'),
        import('pusher-js'),
    ]).then(([{ default: Echo }, PusherModule]) => {
        window.Pusher = PusherModule.default;

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey || pusherKey,
            wsHost: import.meta.env.VITE_REVERB_HOST || import.meta.env.VITE_PUSHER_HOST || window.location.hostname,
            wsPort: Number(import.meta.env.VITE_REVERB_PORT || import.meta.env.VITE_PUSHER_PORT || 80),
            wssPort: Number(import.meta.env.VITE_REVERB_PORT || import.meta.env.VITE_PUSHER_PORT || 443),
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
        });
    }).catch(() => {
        // Echo 未インストール時は無視（午睡画面はポーリング）
    });
}
