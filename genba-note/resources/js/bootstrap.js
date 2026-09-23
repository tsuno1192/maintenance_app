/**
 * Breeze 標準のフロントエンド HTTP ヘルパ。
 *
 * axios は将来の XHR 連携用。CSRF は meta タグ経由で付与する。
 */
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
